<?php
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "sdiypph";

try {
    // Membuat koneksi PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Mengatur mode error PDO menjadi Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hapus atau beri komentar baris di bawah ini jika aplikasi sudah berjalan normal
    // echo "Koneksi berhasil!";
    
} catch(PDOException $e) {
    // Menampilkan pesan error jika koneksi gagal
    die("Koneksi database gagal: " . $e->getMessage());
}
?>