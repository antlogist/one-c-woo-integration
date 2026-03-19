<?php

function load_composer_autoloader()
{
    $autoloadPath = get_theme_file_path() . '/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        include_once $autoloadPath;
    } else {
        throw new Exception('Composer autoloader was not found at: ' . $autoloadPath);
    }
}

try {
    load_composer_autoloader();
} catch (Exception $e) {
    error_log('Error loading Composer autoloader: ' . $e->getMessage());
}
