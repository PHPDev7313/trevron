<?php

namespace JDS\Console\Command;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Exceptions\ConsoleOptionException;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * The name used to call the command.
     */
    protected string $name;

    /**
     * One-line description for --help.
     */
    protected string $description;

    /**
     * Multiline usage information.
     */
    protected array $usage = [];

    /**
     * Option schema:
     *
     *
     * [
     *
     *     'up' => ['type' => 'int', 'required' => false],
     *
     *      'down' => ['type' => 'int', 'required' => false],
     *
     * ]
     *
     *
     */
    protected array $options = [];

    /**
     * Main execution wrapper - do NOT override.
     */
    final public function execute(array $params = []): int
    {
        //
        // built-in help
        //
        if (isset($params['help']) || isset($params['h'])) {
            $this->displayHelp();
            return 0;
        }

        //
        // validate options
        //
        $this->validateOptions($params);

        //
        // call the actual command logic
        //
        return $this->handle($params);
    }

    /**
     * Child commands must implement this.
     */
    abstract protected function handle(array $params): int;

    /**
     * Built-in help formatting.
     */
    private function displayHelp(): void
    {
        echo "\nCommand: {$this->name}\n";
        echo "Description: {$this->description}\n\n";

        if (!empty($this->usage)) {
            echo "Usage:\n";
            foreach ($this->usage as $line) {
                echo "  {$line}\n";
            }
            echo "\n";
        }

        if (!empty($this->options)) {
            echo "Options:\n";
            foreach ($this->options as $opt => $meta) {
                $req = $meta['required'] ?? false ? '(required)' : '(optional)';
                $type = $meta['type'] ?? 'string';
                echo "  --{$opt}   {$req}  type: {$type}\n";
            }
        }
        echo "\n";
    }

    /**
     * Validates input options based on $this->options schema.
     */
    private function validateOptions(array $params): void
    {
        foreach ($this->options as $opt => $meta) {

            //
            // required?
            //
            if (($meta['required'] ?? false) && !array_key_exists($opt, $params)) {
                throw new ConsoleOptionException("Missing required option: --{$opt}");
            }

            if (!array_key_exists($opt, $params)) {
                continue;
            }

            $value = $params[$opt];

            //
            // type validation
            //
            switch ($meta['type'] ?? null) {
                case 'int':
                    if (!is_numeric($value)) {
                        throw new ConsoleOptionException("Option --{$opt} must be an integer");
                    }
                    break;
                case 'bool':
                    if (!in_array($value, ['0', '1',0,1,true,false], true)) {
                        throw new ConsoleOptionException("Option --{$opt} must be a boolean");
                    }
                    break;
                case 'string':
                default:
                    //
                    // no validation needed
                    //
                    break;
            }
        }
    }

    //
    // Output Helpers
    //
    protected function line(string $msg): void
    {
        echo $msg . PHP_EOL;
    }

    protected function info(string $msg): void
    {
        echo "[INFO]  {$msg}" . PHP_EOL;
    }

    protected function warn(string $msg): void
    {
        echo "[WARN]  {$msg}" . PHP_EOL;
    }

    protected function error(string $msg): void
    {
        echo "[ERROR] {$msg}" . PHP_EOL;
    }

    //
    // Accessors
    //
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}

