<?php
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

// NOTE(jcrasta) - This was stolen from Laravel 5.4 so we can use the new mix output format.

if (!function_exists('mix')) {
    /**
     * Get the path to a versioned Mix file.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    function mix($path, $manifestDirectory = '')
    {
        static $manifest;
        if (!starts_with($path, '/')) {
            $path = "/{$path}";
        }
        if ($manifestDirectory && !starts_with($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        if (file_exists(public_path($manifestDirectory . '/hot'))) {
            return new HtmlString("http://localhost:8080{$path}");
        }
        if (!$manifest) {
            if (!file_exists($manifestPath = public_path($manifestDirectory . '/mix-manifest.json'))) {
                throw new Exception('The Mix manifest does not exist.');
            }
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
        if (!array_key_exists($path, $manifest)) {
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your " .
                'webpack.mix.js output paths and try again.'
            );
        }

        return new HtmlString($manifestDirectory . $manifest[$path]);
    }
}
