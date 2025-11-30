<?php
namespace App\Services;

class TemplateService
{
    public function getTemplate(string $packageType, string $deploymentType): ?array
    {
        // For MVP, we only have one starter template
        $templatePath = $_ENV['TEMPLATE_STORAGE_PATH'] . '/starter-static-v1.0.0';

        if (!is_dir($templatePath)) {
            // Fallback for testing if env path is not set or invalid
            $templatePath = __DIR__ . '/../templates/websites/starter-static-v1.0.0';
        }

        return [
            'id' => 'starter-static-v1.0.0',
            'path' => $templatePath,
            'type' => 'static'
        ];
    }

    public function customizeTemplate(string $templatePath, array $customData): string
    {
        // Create a temporary directory for this deployment
        $tempDir = sys_get_temp_dir() . '/deployment_' . uniqid();
        if (!mkdir($tempDir, 0777, true)) {
            throw new \Exception("Failed to create temp directory: $tempDir");
        }

        // Copy template files to temp directory
        $this->recursiveCopy($templatePath, $tempDir);

        // Replace placeholders in all files
        $this->replacePlaceholdersInDir($tempDir, $customData);

        return $tempDir;
    }

    public function verifyTemplateChecksum(string $templatePath, string $checksum): bool
    {
        // TODO: Implement checksum verification for security
        return true;
    }

    private function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private function replacePlaceholdersInDir($dir, $data)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['html', 'css', 'js', 'txt'])) {
                $content = file_get_contents($file->getRealPath());

                foreach ($data as $key => $value) {
                    $placeholder = '{{' . strtoupper($key) . '}}';
                    $content = str_replace($placeholder, $value, $content);
                }

                file_put_contents($file->getRealPath(), $content);
            }
        }
    }
}
