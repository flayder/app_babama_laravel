<?php

declare(strict_types=1);
// Include Requests only if not already defined
if (false === class_exists('Requests')) {
    require_once __DIR__.'/libs/Requests-1.7.0/library/Requests.php';
}

try {
    Requests::register_autoloader();

    if (-1 === version_compare(Requests::VERSION, '1.6.0')) {
        throw new Exception('Requests class found but did not match');
    }
} catch (\Exception $e) {
    throw new Exception('Requests class found but did not match');
}

spl_autoload_register(function ($class): void {
    // project-specific namespace prefix
    $prefix = 'Razorpay\Api';

    // base directory for the namespace prefix
    $base_dir = __DIR__.'/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);

    if (0 !== strncmp($prefix, $class, $len)) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    //
    // replace the namespace prefix with the base directory,
    // replace namespace separators with directory separators
    // in the relative class name, append with .php
    //
    $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
