<?php
// Vercel Serverless Gateway Router for EcoSwap PHP

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Fallback for root path
if ($uri === '/' || $uri === '') {
    $uri = '/index.php';
}

// Locate the requested file in the parent root directory
$file = dirname(__DIR__) . $uri;

if (file_exists($file) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    // Set script paths correctly for the requested script context
    $_SERVER['SCRIPT_FILENAME'] = $file;
    $_SERVER['SCRIPT_NAME'] = $uri;
    $_SERVER['PHP_SELF'] = $uri;
    
    // Change working directory to the project root directory so relative includes/reads work seamlessly
    chdir(dirname(__DIR__));
    
    require $file;
    exit;
}

// Let Vercel serve static files directly if it falls through
return false;
?>
