<?php
namespace App\Services;

class FtpDeploymentService
{
    public function deploy(
        string $localPath,
        string $domainName,
        string $ftpUsername,
        string $ftpPassword
    ): array {
        $ftpHost = $_ENV['FTP_HOST'];
        $ftpPort = $_ENV['FTP_PORT'] ?? 21;
        $basePath = $_ENV['FTP_BASE_PATH'] ?? '/public_html/clients';

        // Connect to FTP
        $conn = ftp_connect($ftpHost, $ftpPort);
        if (!$conn) {
            throw new \Exception("Could not connect to FTP host: $ftpHost");
        }

        // Login
        if (!@ftp_login($conn, $ftpUsername, $ftpPassword)) {
            ftp_close($conn);
            throw new \Exception("FTP login failed for user: $ftpUsername");
        }

        // Passive mode is usually required
        ftp_pasv($conn, true);

        // Create remote directory structure: /public_html/clients/domain.com
        $remoteDir = $basePath . '/' . $domainName;
        $this->createRemoteDirRecursive($conn, $remoteDir);

        // Upload files
        $result = $this->uploadDirectory($conn, $localPath, $remoteDir);

        ftp_close($conn);

        return [
            'success' => true,
            'remote_path' => $remoteDir,
            'files_uploaded' => $result['count']
        ];
    }

    private function createRemoteDirRecursive($conn, $path)
    {
        $parts = explode('/', trim($path, '/'));
        $current = '';
        foreach ($parts as $part) {
            $current .= '/' . $part;
            if (!@ftp_chdir($conn, $current)) {
                if (!@ftp_mkdir($conn, $current)) {
                    // Directory might already exist or permission error
                }
            }
        }
    }

    private function uploadDirectory($conn, $localDir, $remoteDir)
    {
        $count = 0;
        $d = dir($localDir);
        while ($file = $d->read()) {
            if ($file != "." && $file != "..") {
                $localFile = $localDir . '/' . $file;
                $remoteFile = $remoteDir . '/' . $file;

                if (is_dir($localFile)) {
                    $this->createRemoteDirRecursive($conn, $remoteFile);
                    $result = $this->uploadDirectory($conn, $localFile, $remoteFile);
                    $count += $result['count'];
                } else {
                    if (ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
                        $count++;
                    } else {
                        throw new \Exception("Failed to upload file: $file");
                    }
                }
            }
        }
        $d->close();
        return ['count' => $count];
    }
}
