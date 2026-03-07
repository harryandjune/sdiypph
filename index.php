<?php
// Letakkan di paling atas sebelum include layout apa pun
require_once 'auth/auth_check.php'; 
// 1. Mulai Output Buffering (WAJIB di baris paling atas)
// Ini memungkinkan fungsi header() bekerja di mana saja meskipun HTML sudah terlanjur di-include
ob_start();


// Baris-baris include Anda yang sudah ada...
$page = isset($_GET['page']) ? basename($_GET['page']) : 'dashboard';

include __DIR__ . '/layout/header.php';
// ... dst

// 2. Simple router logic
$page = isset($_GET['page']) ? basename($_GET['page']) : 'dashboard';

// 3. Masukkan Layout Utama (Header, Sidebar, Navbar)
// Pastikan koneksi database ada di dalam header.php seperti yang Anda buat sebelumnya
include __DIR__ . '/layout/header.php';
include __DIR__ . '/layout/sidebar.php';
include __DIR__ . '/layout/navbar.php';

// 4. Content Area
// Bagian ini akan membungkus konten halaman Anda agar responsif terhadap sidebar
echo '<main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-denim-950">'; // Background gelap dasar
echo '<div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">';

$pageFile = __DIR__ . '/pages/' . $page . '.php';
// Tambahkan baris ini di bawahnya untuk melihat path mana yang dicari PHP:
// echo "File yang dicari: " . $pageFile;

if (file_exists($pageFile)) {
    include $pageFile;
} else {
    http_response_code(404);
    include __DIR__ . '/pages/404.php';
}

echo '</div>'; // Tutup div overflow-y-auto
echo '</main>'; // Tutup main

// 5. Masukkan Layout Footer
include __DIR__ . '/layout/footer.php';

// 6. Akhiri Output Buffering dan kirim ke browser
ob_end_flush();
?>