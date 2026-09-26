<?php
// services/EnvLoader.php — Lightweight .env File Parser
// Loads environment variables from a .env file at the project root.
// No Composer dependency required — works standalone on any PHP 7.4+ setup.

class EnvLoader
{
    /**
     * Load a .env file and populate $_ENV + putenv().
     *
     * @param string $path Absolute path to the .env file
     * @return bool True if file was loaded, false if file not found
     */
    public static function load(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            // Split on the first '=' only
            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));

            // Strip surrounding quotes (single or double)
            if (
                (strlen($value) >= 2) &&
                (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                 ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Only set if not already defined (real env vars take precedence)
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Get an environment variable with a fallback default.
     *
     * @param string $key   The environment variable name
     * @param mixed  $default Fallback value if key is not set
     * @return string|mixed
     */
    public static function get(string $key, $default = '')
    {
        $val = getenv($key);
        return ($val !== false && $val !== '') ? $val : $default;
    }
}

// Auto-load .env from project root when this file is included
EnvLoader::load(dirname(__DIR__) . '/.env');
?>
