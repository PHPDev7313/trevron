<?php

namespace JDS\Controller;

use JDS\Auditor\CentralizedLogger;
use JDS\Http\FileNotFoundException;
use JDS\Http\HttpRuntimeException;
use JDS\Http\InvalidArgumentException;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Processing\ErrorProcessor;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


abstract class AbstractController
{

    protected ?ContainerInterface $container = null;
    protected Request $request;
    protected string $routePath;
    protected CentralizedLogger $logger;

    /**
     * Sets the container instance and configures the application mode based on the container's APP_DEV setting.
     *
     * @param ContainerInterface $container The container instance to be set.
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ControllerRuntimeException
     * @throws NotFoundExceptionInterface
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
        $this->validateContainer();
        $this->setRoutePath($this->container->get('config')->get('routePath'));
        $this->logger = $this->container->get('manager')->getLogger('audit');
    }

    public function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    /**
     * Validates the container instance to ensure it has been properly initialized.
     *
     * @return void
     * @throws ControllerRuntimeException If the container is not properly initialized.
     */
    private function validateContainer(): void
    {
        if (!$this->container) {
            $exitCode = 60;
            ErrorProcessor::process(
                new ControllerRuntimeException("Container is not properly initialized.", $exitCode, null),
                $exitCode,
                sprintf("Container is not properly initialized. Code: %d", $exitCode)
            );
            exit($exitCode);
        }
    }

    /**
     * Sets the request instance to be used in the application.
     *
     * @param Request $request The request instance to be set.
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // Check if it's exactly 10 digits
        if (strlen($cleaned) === 10) {
            return sprintf('(%s) %s-%s',
                substr($cleaned, 0, 3),
                substr($cleaned, 3, 3),
                substr($cleaned, 6)
            );
        }

        // Return the original string if it doesn't match the expected length
        return $phone;
    }

    /**
     * Handles the upload and processing of image files.
     *
     * @param string|null $images The key in the $_FILES array referencing the uploaded images.
     * @param int|null $numFiles The maximum number of files allowed for upload.
     * @param string|null $storePath The directory where the uploaded images should be stored.
     *
     * @return array Returns an array containing information about the processed images.
     */
    protected function handleImageUpload(?string $images = null, ?int $numFiles = null, ?string $storePath = null): array
    {
        if (is_null($images) || is_null($numFiles) || is_null($storePath)) {
            $exitCode = 14;
            ErrorProcessor::process(
                new InvalidArgumentException("Invalid arguments provided."),
                $exitCode,
                sprintf("Invalid arguments provided. Code: %d", $exitCode)
            );
            exit($exitCode);
        }
        if (!is_dir($storePath) || !is_writable($storePath)) {
            $exitCode = 15;
            ErrorProcessor::process(
                new FolderNotWritableException("The provided path is not writable."),
                $exitCode,
                "The provided path is not writable.",
            );
            exit($exitCode);
        }
        $imageInfos = [];
        try {
            // validate $_FILES structure
            if (!isset($_FILES[$images]) || !is_array($_FILES[$images]) || !isset($_FILES[$images]["error"])) {
                throw new InvalidFileSubmissionException("No valid files uploaded for key: $images.");
            }
            if (count($_FILES[$images]["error"]) > $numFiles) {
                throw new FileUploadLimitException("Too many files uploaded! Maximum allowed is {$numFiles}!");
            }

            // process each uploaded file
            foreach ($_FILES[$images]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $fileName = $_FILES[$images]["name"][$key] ?? null;
                    $fileType = $_FILES[$images]["type"][$key] ?? null;
                    $tmpName = $_FILES[$images]["tmp_name"][$key] ?? null;
                    if (empty($fileName) || empty($fileType) || empty($tmpName)) {
                        throw new ImageFilenameException("File upload data is incomplete for file index: {$key}.");
                    }

                    if (!$this->checkFileUploadName($fileName)) {
                        throw new ImageFilenameException("Invalid file name: {$fileName}.");
                    }

                    $imgExtension = $this->getImageExtensionByType($fileType);
                    if (is_null($imgExtension)) {
                        throw new UnsupportedImageTypeException("Unsupported file type: {$fileType}.");
                    }
                    $imageInfo = $this->processImage($tmpName, $imgExtension, $storePath);
                    $imageInfos[] = $imageInfo;
                } else {
                    // log and display specific upload error codes
                    $this->logFileUploadError($error, $key);
                }
            }
        } catch (ImageProcessingException|FileNotFoundException $e) {
            // handle specific image processing or missing file errors
            $exitCode = 70;
            ErrorProcessor::process(
                $e,
                $exitCode,
                sprintf("Image processing error. Code: %d. Please contact support.", $exitCode)
            );
            exit($exitCode);
        } catch (Throwable $e) {
            // handle any unexpected errors
            $exitCode = 79;
            ErrorProcessor::process(
                $e,
                $exitCode,
                sprintf("Unexpected error during image upload. Code: %d", $exitCode)
            );
            exit($exitCode);
        }
        return $imageInfos;
    }

    /**
     * Validates the provided file name using a regex pattern.
     *
     * @param string $filename The file name to validate.
     *
     * @return bool Returns true if the file name is valid, otherwise false.
     */
    private function checkFileUploadName(string $filename): bool
    {
        try {
            return (bool)preg_match("~^[-0-9A-Z_.]{1,75}$~i", $filename);
        } catch (Throwable $e) {
            // see StatusCodeManager->CODES for code 73 explanation
            // log unexpected regex-related errors and return false
            $exitCode = 73;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "Filename for image upload is invalid. Please contact support.",
            );
        }
        return false;
    }

    /**
     * Resolves the file extension corresponding to the provided image MIME type.
     *
     * @param string $imageType The MIME type of the image.
     *
     * @return string Returns the file extension associated with the given image MIME type.
     *
     * @throws UnsupportedImageTypeException If the MIME type is not supported.
     * @throws Throwable If an unexpected error occurs during the operation.
     */
    private function getImageExtensionByType(string $imageType): ?string
    {
        $mimeToExtension = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/webp' => 'webp',
            'application/octet-stream' => 'heic',
        ];

        try {
            // normalize input MIME type to avoid case mismatches
            $imageType = strtolower($imageType);

            // check if the MIME type exists in the map
            if (array_key_exists($imageType, $mimeToExtension)) {
                return $mimeToExtension[$imageType];
            }
            $exitCode = 72;
            $errorMessage = sprintf("Unsupported image type: %s.", $imageType);
            ErrorProcessor::process(
                new UnsupportedImageTypeException($errorMessage),
                $exitCode,
                $errorMessage,
            );
        } catch (Throwable $e) {
            // handle any unexpected errors; log and rethrow or return a default value
            $exitCode = 79;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "Unexpected error during image type detection! Please contact admin."
            );
            exit($exitCode);
        }
        return null;
    }

    /**
     * Processes an uploaded image by saving it, optionally converting its format, and generating storage URLs.
     *
     * @param string $tmpName The temporary file name of the uploaded image.
     * @param string $img_extension The original file extension of the uploaded image.
     * @param string $storePath The directory where the processed image should be saved.
     *
     * @return array An associative array containing the URLs and file type of the processed image:
     *               - 'image_filename': The URL to the saved image.
     *               - 'thumbnail_filename': The URL to the thumbnail of the saved image.
     *               - 'image_type': The file type of the processed image.
     */
    private function processImage(string $tmpName, string $img_extension, string $storePath): array
    {
        // convert image to this new extension
        $new_extension = "webp";

        // new filename
        $new_filename = uniqid('gallery-', false);

        // where to store the new image for 'gallery'
        $imageUrl = "$storePath/$new_filename.$new_extension";

        // where to store the new image for '_thumbnail'
        $thumbnailUrl = "$storePath/$new_filename" . "_thumbnail.$new_extension";

        try {
            // step 1: move the uploaded file to the target location
            $originalFilePath = "$storePath/$new_filename.$img_extension";
            if (!move_uploaded_file($tmpName, $originalFilePath)) {
                $exitCode = 78;
                $e = new ImageUploadException("Failed to move uploaded file.");
                ErrorProcessor::process(
                    $e,
                    $exitCode,
                    "Upload Image File Failed to move."
                );
                exit($exitCode);
            }

            // step 2: if necessary, convert the image to the new extension
            if ($img_extension !== $new_extension) {
                $convertedFilePath = "$storePath/$new_filename.$new_extension";
                try {
                    $this->convertImage($originalFilePath, $convertedFilePath);
                    if (!unlink($originalFilePath)) {
                        $exitCode = 76;
                        $e = new ImageRuntimeException("Failed to delete original file.");
                        ErrorProcessor::process(
                            $e,
                            $exitCode,
                            "Failed to delete original file."
                        );
                        exit($exitCode);
                    }
                } catch (ImageProcessingException $e) {
                    $exitCode = 77;
                    ErrorProcessor::process(
                        $e,
                        $exitCode,
                        sprintf("Failed to convert image to %s. ", $new_extension)
                    );
                    exit($exitCode);
                } catch (Throwable $e) {
                    $exitCode = 79;
                    ErrorProcessor::process(
                        $e,
                        $exitCode,
                        "Unexpected error during image conversion!"
                    );
                    exit($exitCode);
                }
            }
            // return the result
            return [
                'image_filename' => $imageUrl, 'thumbnail_filename' => $thumbnailUrl, 'image_type' => $new_extension
            ];
        } catch (Throwable $e) {
            $exitCode = 77;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "An unexpected error occurred while processing the image.",
            );
        }
        return [];
    }

//    private function newConvertImage(string $filename, string $outfile): void
//    {
//        // Ensure the file exists
//        if (!file_exists($filename)) {
//            throw new FileNotFoundException("File not found: $filename");
//        }
//
//        try {
//            // Load the image using Imagick
//            $image = new Imagick($filename);
//
//            // Convert the image to WebP format or any format from the $outfile extension
//            $image->setImageFormat(pathinfo($outfile, PATHINFO_EXTENSION));
//            $image->writeImage($outfile);
//
//            // Create a 150x150 thumbnail
//            $thumbnail = clone $image;
//            $thumbnail->thumbnailImage(150, 150, true);
//
//            // Determine the thumbnail path
//            $thumbnailPath = str_replace(".webp", "_thumbnail.webp", $outfile);
//            $thumbnail->writeImage($thumbnailPath);
//
//            // Clear Imagick resources
//            $image->clear();
//            $thumbnail->clear();
//        } catch (ImagickException $e) {
//            // Handle Imagick errors
//            $exitCode = 77;
//             ErrorProcessor::process(
//                 $e,
//                 $exitCode,
//                 "Failed to convert image to WebP."
//             );
//        } catch (Throwable $e) {
//            $exitCode = 79;
//            ErrorProcessor::process(
//                $e,
//                $exitCode,
//                "Unexpected error during image conversion!"
//            );
//        }
//    }

    private function logFileUploadError(int $error, int $key): void
    {
        $message = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File size exceeds limit for file index: {$key}.",
            UPLOAD_ERR_PARTIAL => "File upload was incomplete for file index: {$key}.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk for file index: {$key}.",
            UPLOAD_ERR_EXTENSION => "File upload stopped by PHP extension for file index: {$key}.",
            default => "Unknown file upload error for file index: {$key}.",
        };
        $exitCode = 74;
        ErrorProcessor::process(
            new ImageUploadException($message),
            $exitCode,
            "An error occurred during file upload. Please contact support."
        );
        exit($exitCode);
    }

    /**
     * Renders a template with the specified parameters and returns a Response instance.
     *
     * @param string $template The name of the template to render.
     * @param array $parameters An associative array of variables to pass to the template.
     * @param Response|null $response An optional Response instance to populate. If null, a new Response is created.
     * @return Response The rendered response containing the content of the template.
     * @throws Throwable If an unexpected error occurs during rendering.
     */
    public function render(string $template, array $parameters = [], Response $response = null):
    Response
    {
        // initialize the response if not provided
        $response ??= new Response();

        try {
            // prepare and validate required components
            $this->prepareTemplateEngine();

            // render the template
            $response->setContent($this->renderTemplateContent($template, $parameters));

            // set cache headers
            $this->setCacheHeaders();

            return $response;
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            // handle Twig-specific errors
            $exitCode = 50;
            $this->handleRenderError(
                $e,
                $response,
                "An error occurred while rendering the template. Please contact support.",
                $exitCode
            );
        } catch (Throwable $e) {
            // handle unexpected errors
            $exitCode = 59; //
            ErrorProcessor::process(
                $e,
                $exitCode,
                "An unexpected error occurred. Please contact support.",
            );
            exit($exitCode);
        }
        return $response;
    }

    /**
     * Prepares the template engine by validating the container and Twig configuration.
     *
     * @return void
     * @throws ControllerRuntimeException
     * @throws TwigRuntimeException
     */
    private function prepareTemplateEngine(): void
    {
        $this->validateContainer();
        $this->validateTwig();
    }

    /**
     * Validates the existence of the Twig service in the container.
     *
     * @return void
     * @throws TwigRuntimeException If the Twig service is not initialized in the container.
     */
    private function validateTwig(): void
    {
        if (!$this->container->has('twig')) {
            $exitCode = 58;
            ErrorProcessor::process(
                new TwigRuntimeException("Twig is not properly initialized.", $exitCode, null),
                $exitCode,
                sprintf("Twig is not properly initialized. Code: %d", $exitCode),
            );
            exit($exitCode);
        }
    }

    /**
     * Renders the content of a template with the provided parameters.
     *
     * @param string $template The name or path of the template to be rendered.
     * @param array $parameters An associative array of variables to pass to the template.
     * @return string The rendered template content as a string.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ControllerRuntimeException If the template rendering fails.
     */
    private function renderTemplateContent(string $template, array $parameters): string
    {
        $twig = $this->container->get('twig');
        return $twig->render($template, $parameters);
    }

    /**
     * Sets HTTP cache headers to prevent caching by the client or intermediary servers.
     *
     * @return void
     */
    private function setCacheHeaders(): void
    {
        header('Cache-Control: no-cache, no-store, must-revalidate', true);
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Handles a rendering error by displaying the error details, setting the response content, and updating the response status code.
     *
     * @param Throwable $e The throwable object representing the error.
     * @param Response $response The response instance to be updated.
     * @param string $message The error message to be displayed and set in the response.
     * @param int $status The HTTP status code associated with the error.
     * @return void
     */
    private function handleRenderError(Throwable $e, Response $response, string $message, int $status): void
    {
        ErrorProcessor::process(
            $e,
            500,
            $message
        );
        $response->setContent($message);
        $response->setStatus(500);
    }

    private function convertImage(string $filename, string $outfile): void // keep for now for reference
    {
        // Validate input file
        if (!file_exists($filename)) {
            $exitCode = 77;
            ErrorProcessor::process(
                new FileNotFoundException("File not found: $filename"),
                $exitCode.
                sprintf("Unable to find image file: %s", $filename)
            );
            exit($exitCode);
        }

        $thumbnailPath = str_replace(".webp", "_thumbnail.webp", $outfile);

        // Convert the image
        $output = [];
        $returnCode = null;
        exec("magick " . escapeshellarg($filename) . " " . escapeshellarg($outfile) . " 2>&1", $output, $returnCode);
        if ($returnCode !== 0) {
            $exitCode = 77;
            ErrorProcessor::process(
                new ImageProcessingException("Image conversion failed for $filename: " . implode("\n", $output)),
                $exitCode,
                sprintf("Image conversion failed for $filename: %s", implode("\n", $output))
            );
            exit($exitCode);
        }

        $returnCode = null;
        // Generate the thumbnail
        exec("magick " . escapeshellarg($filename) . " -thumbnail 150x150 " . escapeshellarg($thumbnailPath) . " 2>&1", $output, $returnCode);
        if ($returnCode !== 0) {
            $exitCode = 77;
            ErrorProcessor::process(
                new ImageProcessingException("Thumbnail generation failed for $filename: " . implode("\n", $output)),
                $exitCode,
                sprintf("Thumbnail generation failed for $filename: %s", implode("\n", $output))
            );
            exit($exitCode);
        }
    }
    
    public function setPathToJson(?string $path=null): string|bool
    {
        if (is_null($path)) {
            return false;
        }
        // build the full path to the file
        $basePath = $this->container->get('config')->get('appPath');
        $fullPath = $basePath . $path;
        
        // extract the directory path
        $directory = dirname($fullPath);
        
        // check if the directory exists, if not, create it
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                // if directory cannot be created, handle the error
                throw new HttpRuntimeException('Directory could not be created.');
            }
        }
        
        // Set the path to the JSON file
        return $fullPath;
    }
    
}

