<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// BURAYI GÜNCELLEYİN: Yeni şifrenizi aşağıya yazın
$newPassword = 'YeniSifreniz123!';
$username = 'admin';

try {
    $database = new App\Config\Database();
    $conn = $database->getConnection();

    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    $sql = "UPDATE admin_users SET password_hash = :pass WHERE username = :user";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pass', $passwordHash);
    $stmt->bindParam(':user', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Şifre başarıyla güncellendi!<br>";
        echo "Kullanıcı: $username<br>";
        echo "Yeni Şifre: $newPassword";
    } else {
        echo "Şifre güncellenemedi veya kullanıcı bulunamadı (ya da şifre zaten aynı).";
    }

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
