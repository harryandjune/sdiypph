<?php
session_start();

// Jika belum login, paksa ke halaman login.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Fungsi bantu untuk membatasi akses berdasarkan role (Otorisasi)
function proteksiRole($roles_izinkan) {
    if (!in_array($_SESSION['role'], $roles_izinkan)) {
        echo "<div style='background:#020617; color:#f43f5e; padding:100px; text-align:center; height:100vh; font-family:sans-serif;'>
                <h1 style='font-size:50px;'>🚫 AKSES DITOLAK</h1>
                <p style='color:#94a3b8;'>Maaf, role <b>" . $_SESSION['role'] . "</b> tidak diizinkan membuka halaman ini.</p>
                <a href='index.php' style='color:#6366f1; text-decoration:none;'>Kembali ke Dashboard</a>
              </div>";
        exit;
    }
}