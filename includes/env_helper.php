<?php
/**
 * Simple .env Loader
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;

        // Split by first "="
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            
            // Remove quotes if present
            $value = trim($value, '"\'');

            // Register to $_ENV, $_SERVER and putenv
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}

// Load .env from root
loadEnv(dirname(__DIR__) . '/.env');

/**
 * Helper to get env variable with fallback
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}
?>
