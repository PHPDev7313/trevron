<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonBuilderInterface;
use JDS\FileSystem\FileNameGenerator;
use JDS\FileSystem\FilePathValidator;
use JDS\FileSystem\JsonFileWriter;

class JsonBuilder implements JsonBuilderInterface
{
    public function __construct(
        private JsonEncoder $encoder,
        private JsonFileWriter $writer,
        private FilePathValidator $pathValidator,
        private FileNameGenerator $fileNameGen
    )
    {
    }

    public function save(mixed $data, string $directory, string $baseName): array
    {
        $encode = $this->encoder->encode($data);
        if (!$encode['success']) {
            return $encode;
        }

        $fileName = $this->fileNameGen->generate($baseName);
        $fullPath = $this->fileNameGen->makeUnique($directory, $fileName);

        $pathCheck = $this->pathValidator->validate($fullPath);
        if (!$pathCheck['success']) {
            return $pathCheck;
        }

        return $this->writer->write($pathCheck['path'], $encode['data']);
    }
}

