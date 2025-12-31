<?php

namespace JDS\Dbal\Migration;

use JDS\Exceptions\Http\FileNotFoundException;

final class MigrationFinder
{
    public function __construct(
        private readonly string $migrationsPath,
        private readonly MigrationNameNormalizer $normalizer
    ) {}

    /**
     * @return list<string> migration filenames
     */
    public function all(): array
    {
        if (!is_dir($this->migrationsPath)) {
            throw new FileNotFoundException(
                "Migration directory not found: {$this->migrationsPath}"
            );
        }

        $files = array_values(array_filter(
            scandir($this->migrationsPath),
            fn ($file) =>
                !in_array($file, ['.', '..', '.gitignore', 'm00000_template.php'], true)
        ));

        usort($files, function (string $a, string $b): int {
            return $this->normalizer->toInt($a) <=> $this->normalizer->toInt($b);
        });

        return $files;
    }

    public function findByInput(string $input): ?string
    {
        $target = $this->normalizer->normalize($input);

        foreach ($this->all() as $migration) {
            if ($this->normalizer->normalize($migration) === $target) {
                return $migration;
            }
        }

        return null;
    }
}

