<?php

declare(strict_types=1);

class Theme
{
    public static function copyTemplate(string $themeId, string $domain, array $paths): void
    {
        $src = $paths['templates'] . '/' . $themeId;
        $dst = $paths['sites'] . '/' . $domain;
        self::recurseCopy($src, $dst);
    }

    public static function replaceCSS(string $domain, array $config, array $paths): void
    {
        $cssFile = $paths['sites'] . '/' . $domain . '/style.css';
        $css = file_get_contents($cssFile);
        $css = preg_replace('/--primary-color:\\s*[^;]+;/', '--primary-color: ' . $config['primary_color'] . ';', $css);
        $css = preg_replace('/--secondary-color:\\s*[^;]+;/', '--secondary-color: ' . $config['secondary_color'] . ';', $css);
        $css = preg_replace('/--font-family:\\s*[^;]+;/', "--font-family: '{$config['font']}', sans-serif;", $css);
        file_put_contents($cssFile, $css);
    }

    public static function injectLogo(string $domain, string $logoPath, array $paths): void
    {
        if (!file_exists($logoPath)) {
            return;
        }
        copy($logoPath, $paths['sites'] . '/' . $domain . '/assets/logo.png');
    }

    public static function generateHtaccess(string $domain, array $paths): void
    {
        $content = "Options -Indexes\nErrorDocument 404 /index.html\n<FilesMatch \"\\.(env|ini|phar|php|sh)$\">\nDeny from all\n</FilesMatch>\n<IfModule mod_headers.c>\nHeader set Cache-Control \"max-age=31536000, public\"\n</IfModule>\n";
        file_put_contents($paths['sites'] . '/' . $domain . '/.htaccess', $content);
    }

    public static function injectSEO(string $domain, string $title, string $description, array $paths): void
    {
        $file = $paths['sites'] . '/' . $domain . '/index.html';
        $html = file_get_contents($file);
        $meta = "<meta name=\"title\" content=\"{$title}\">\n<meta name=\"description\" content=\"{$description}\">";
        $html = str_replace('</head>', $meta . "\n</head>", $html);
        file_put_contents($file, $html);
    }

    private static function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst, 0775, true);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcPath = "$src/$file";
            $dstPath = "$dst/$file";
            if (is_dir($srcPath)) {
                self::recurseCopy($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }
}
