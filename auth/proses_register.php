<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_nama = $_POST['nama_sdi']; // Bisa berupa ID (angka) atau Nama Baru (teks)
    $username = $_POST['username'];
    // ... ambil data lainnya (email, password, dll)

    $sdi_id = null;
    
    // Cek apakah input_nama adalah angka (ID Pegawai)
    if (is_numeric($input_nama)) {
        $sdi_id = $input_nama;
    } else {
        // Jika teks, kita simpan sdi_id sebagai NULL dulu. 
        // Nama akan kita simpan sementara di session atau handle di profil nanti.
        $sdi_id = null;
    }

    // Proses Insert ke tabel users
    $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $sql = "INSERT INTO users (sdi_id, username, email, nomor_tlp, password, role, status) VALUES (?, ?, ?, ?, ?, 'sdi', 'Active')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$sdi_id, $username, $_POST['email'], $_POST['nomor_tlp'], $pass_hash]);

        echo "<script>alert('Registrasi Berhasil!'); window.location.href='../login.php';</script>";
    } catch (PDOException $e) {
        // Handle error
    }
}