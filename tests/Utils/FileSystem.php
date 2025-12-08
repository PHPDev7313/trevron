<?php

function safe_unlink(string $file): void
{
    if (!file_exists($file)) {
        return;
    }

    for ($i = 0; $i < 5; $i++) {
        if (@unlink($file)) {
            return;
        }
        usleep(50000); // windows lock delay
    }

    unlink($file); // final attempt
}

function safe_rmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $files = new RecursiveDirectoryIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        if ($file->isDir()) {
            safe_rmdir($file->getPathname());
        } else {
            safe_unlink($file->getPathname());
        }
    }

    for ($i = 0; $i < 5; $i++) {
        if (@rmdir($dir)) {
            return;
        }
        usleep(50000);
    }
    rmdir($dir); // final attemp
}


