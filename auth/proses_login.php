<?php
session_start();
require_once '../config/database.php'; // Sesuaikan path koneksi Anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identity = $_POST['identity']; // Bisa username atau email
    $password = $_POST['password'];

    // 1. Cari user berdasarkan username atau email
    $stmt = $conn->prepare("SELECT u.*, s.nama FROM users u 
                            LEFT JOIN data_sdi s ON u.sdi_id = s.id 
                            WHERE u.username = ? OR u.email = ? LIMIT 1");
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verifikasi Password
    if ($user && password_verify($password, $user['password'])) {
        
        if ($user['status'] !== 'Active') {
            header("Location: ../login.php?error=status");
            exit;
        }

        // 3. Simpan data ke SESSION
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['sdi_id']    = $user['sdi_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['nama_user'] = $user['nama'] ?? $user['username'];
        $_SESSION['role']      = $user['role'];

        // 4. Update waktu login terakhir
        $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

        header("Location: ../index.php?page=dashboard");
        exit;
    } else {
        // Gagal login
        header("Location: ../login.php?error=failed");
        exit;
    }
}