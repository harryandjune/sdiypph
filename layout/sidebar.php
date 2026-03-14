<?php
// Mengambil halaman aktif dari URL, default adalah dashboard
$currentPage = $_GET['page'] ?? 'dashboard';
// Mengambil data session untuk proteksi menu
$userRole = $_SESSION['role'] ?? 'sdi'; 
?>

<aside id="sidebar" class="fixed lg:relative w-64 md:w-64 lg:w-64 bg-denim-800 border-r border-slate-700 flex flex-col transition-all duration-300 z-40 h-screen transform -translate-x-full lg:translate-x-0 shrink-0">

    <!-- Logo Section -->
    <div class="h-20 flex items-center justify-center border-b border-slate-700 px-4">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap w-full justify-center">
            <div class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-br from-neon-indigo to-neon-blue flex items-center justify-center shadow-[0_0_15px_rgba(56,189,248,0.4)]">
                <i class="fa-solid fa-mosque text-white text-xl"></i>
            </div>
            <div class="flex flex-col sidebar-text transition-opacity duration-300">
                <span class="text-white font-bold text-sm tracking-wider uppercase">SDI Hidayatullah</span>
                <span class="text-xs text-neon-blue font-medium">Balikpapan</span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-2">

        <!-- Dashboard Link -->
        <a href="?page=dashboard"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'dashboard' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Dashboard">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-chart-pie text-lg <?= $currentPage == 'dashboard' ? 'text-neon-blue' : 'group-hover:text-neon-blue' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Dashboard</span>
        </a>

        <!-- Profil Saya (Dinamis untuk semua User) -->
        <a href="?page=profil"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'profil' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Profil Saya">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-user-circle text-lg <?= $currentPage == 'profil' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Profil Saya</span>
        </a>

        <!-- Data Pegawai (Superadmin & Admin SDI Only) -->
        <?php if (in_array($userRole, ['superadmin', 'admin_sdi'])): ?>
        <a href="?page=data_sdi"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'data_sdi' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Data Pegawai">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-users text-lg <?= $currentPage == 'data_sdi' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Data Pegawai</span>
        </a>
        <?php endif; ?>

        <!-- Data Halaqah -->
        <a href="?page=halaqah"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
            <?= $currentPage == 'halaqah' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Data Halaqah">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-mosque text-lg <?= $currentPage == 'halaqah' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?> transition-colors duration-300"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Data Halaqah</span>
        </a>

        <!-- Anggota Halaqah -->
        <a href="?page=anggota_halaqah"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
            <?= $currentPage == 'anggota_halaqah' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Anggota Halaqah">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-users-rectangle text-lg <?= $currentPage == 'anggota_halaqah' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?> transition-colors duration-300"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Anggota Halaqah</span>
        </a>

        <!-- Absensi Sabtu (Ketua & Admin) -->
        <?php if (in_array($userRole, ['superadmin', 'ketua halaqah', 'admin_sdi'])): ?>
        <a href="?page=absen_sabtu"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
            <?= $currentPage == 'absen_sabtu' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Absensi Sabtu">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-calendar-day text-lg <?= $currentPage == 'absen_sabtu' ? 'text-neon-blue' : 'group-hover:text-neon-blue' ?> transition-colors duration-300"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Absensi Sabtu</span>
        </a>
        <?php endif; ?>

        <!-- Manajemen User (Superadmin Only) -->
        <?php if ($userRole === 'superadmin'): ?>
        <a href="?page=manajemen_user"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
            <?= $currentPage == 'manajemen_user' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Manajemen User">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-user-shield text-lg <?= $currentPage == 'manajemen_user' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?> transition-colors duration-300"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Manajemen User</span>
        </a>
        <?php endif; ?>

        <!-- Presensi & Cuti -->
        <a href="?page=presensi-cuti"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'presensi-cuti' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Presensi & Cuti">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-calendar-check text-lg <?= $currentPage == 'presensi-cuti' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Presensi & Cuti</span>
        </a>

        <!-- Penilaian Kinerja -->
        <a href="?page=penilaian-kinerja"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'penilaian-kinerja' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Penilaian Kinerja">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-chart-line text-lg <?= $currentPage == 'penilaian-kinerja' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Penilaian Kinerja</span>
        </a>
    </nav>

    <!-- Bottom Section: Settings Only -->
    <div class="p-4 border-t border-slate-700">
        <a href="?page=pengaturan"
            class="flex items-center gap-4 px-3 py-3 transition-all group overflow-hidden whitespace-nowrap rounded-xl 
           <?= $currentPage == 'pengaturan' ? 'bg-slate-700/50 text-white border border-slate-600/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>"
            title="Pengaturan">
            <div class="sidebar-link-icon">
                <i class="fa-solid fa-gear text-lg <?= $currentPage == 'pengaturan' ? 'text-neon-indigo' : 'group-hover:text-neon-indigo' ?>"></i>
            </div>
            <span class="font-medium sidebar-text transition-opacity duration-300">Pengaturan</span>
        </a>
    </div>
</aside>