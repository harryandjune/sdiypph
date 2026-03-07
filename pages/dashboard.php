<?php
// --- AMBIL DATA RIIL DARI DATABASE ---

// 1. Total Pegawai (Tabel data_sdi)
$total_pegawai = $conn->query("SELECT COUNT(*) FROM data_sdi")->fetchColumn() ?: 0;

// 2. Hadir Hari Ini (Tabel absen_halaqah)
$tanggal_sekarang = date('Y-m-d');
$hadir_hari_ini = $conn->query("SELECT COUNT(*) FROM absen_halaqah WHERE tanggal = '$tanggal_sekarang' AND status = 'Hadir'")->fetchColumn() ?: 0;

// 3. Izin / Sakit Hari Ini (Tabel absen_halaqah)
$izin_sakit = $conn->query("SELECT COUNT(*) FROM absen_halaqah WHERE tanggal = '$tanggal_sekarang' AND status IN ('Izin', 'Sakit')")->fetchColumn() ?: 0;

// 4. User Aktif (Tabel users)
$user_aktif = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'Active'")->fetchColumn() ?: 0;

// 5. Kalkulasi Persentase Kehadiran
$persentase_hadir = ($total_pegawai > 0) ? round(($hadir_hari_ini / $total_pegawai) * 100, 1) : 0;

// 6. Ambil 5 Aktivitas Absensi Terbaru (JOIN data_sdi untuk ambil nama)
$query_aktivitas = "SELECT a.*, s.nama 
                    FROM absen_halaqah a 
                    JOIN data_sdi s ON a.sdi_id = s.id 
                    ORDER BY a.id DESC LIMIT 5";
$aktivitas = $conn->query($query_aktivitas)->fetchAll(PDO::FETCH_ASSOC);

// 7. Ambil jumlah pegawai yang TMT-nya bulan ini (sebagai ganti data kontrak)
$bulan_ini = date('m');
$pegawai_baru_bulan_ini = $conn->query("SELECT COUNT(*) FROM data_sdi WHERE MONTH(tmt) = '$bulan_ini'")->fetchColumn() ?: 0;
?>

<div class="flex-1 overflow-y-auto p-4 md:p-8 z-10">
    <!-- Stat Cards Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
        
        <!-- Total Pegawai -->
        <div class="bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6 relative overflow-hidden group hover:border-neon-blue/50 hover:shadow-[0_0_20px_rgba(56,189,248,0.1)] transition-all">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-neon-blue to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400 font-medium mb-1">Total Pegawai</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-white"><?= number_format($total_pegawai) ?></h3>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-slate-700/50 flex items-center justify-center text-neon-blue group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs md:text-sm">
                <span class="text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded flex items-center gap-1">
                    <i class="fa-solid fa-user-check text-[10px]"></i> Aktif
                </span>
                <span class="text-slate-500 ml-2">Dalam Database</span>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6 relative overflow-hidden group hover:border-neon-indigo/50 hover:shadow-[0_0_20px_rgba(99,102,241,0.1)] transition-all">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-neon-indigo to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400 font-medium mb-1">Hadir (Hari Ini)</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-white"><?= $hadir_hari_ini ?></h3>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-slate-700/50 flex items-center justify-center text-neon-indigo group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-fingerprint text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs md:text-sm">
                <span class="text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded"><?= $persentase_hadir ?>%</span>
                <span class="text-slate-500 ml-2">Tingkat kehadiran</span>
            </div>
        </div>

        <!-- Cuti / Sakit -->
        <div class="bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6 relative overflow-hidden group hover:border-amber-400/50 hover:shadow-[0_0_20px_rgba(251,191,36,0.1)] transition-all">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400 font-medium mb-1">Izin / Sakit</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-white"><?= $izin_sakit ?></h3>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-slate-700/50 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-house-medical text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs md:text-sm">
                <span class="text-amber-400 font-bold"><?= $izin_sakit ?></span>
                <span class="text-slate-500 ml-1 italic">Tercatat hari ini</span>
            </div>
        </div>

        <!-- User Aktif (Sistem) -->
        <div class="bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6 relative overflow-hidden group hover:border-rose-400/50 hover:shadow-[0_0_20px_rgba(244,63,94,0.1)] transition-all">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400 font-medium mb-1">User Aktif</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-white"><?= $user_aktif ?></h3>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-slate-700/50 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-shield text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs md:text-sm">
                <span class="text-rose-400 bg-rose-400/10 px-2 py-0.5 rounded">Sistem</span>
                <span class="text-slate-500 ml-2">Pengguna terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Aktivitas Terbaru -->
        <div class="xl:col-span-2 bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-base md:text-lg font-bold text-white italic">Aktivitas Absensi Terbaru</h2>
                <a href="?page=absen_sabtu" class="text-xs md:text-sm text-neon-blue hover:text-white transition-colors">Lihat Semua</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="text-slate-400 text-sm border-b border-slate-700">
                            <th class="pb-3 font-medium uppercase text-[10px] tracking-widest">Nama Pegawai</th>
                            <th class="pb-3 font-medium uppercase text-[10px] tracking-widest text-center">Status</th>
                            <th class="pb-3 font-medium uppercase text-[10px] tracking-widest text-right">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-300">
                        <?php if (empty($aktivitas)): ?>
                            <tr>
                                <td colspan="3" class="py-10 text-center text-slate-500 italic">Belum ada aktivitas hari ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($aktivitas as $act): ?>
                                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30 transition-colors">
                                    <td class="py-4 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-neon-blue border border-neon-blue/20 flex items-center justify-center font-bold text-[10px] shrink-0 uppercase">
                                            <?= substr($act['nama'], 0, 2) ?>
                                        </div>
                                        <span class="whitespace-nowrap font-medium italic text-white"><?= htmlspecialchars($act['nama']) ?></span>
                                    </td>
                                    <td class="py-4 text-center">
                                        <?php 
                                            $color = 'slate-400';
                                            if($act['status'] == 'Hadir') $color = 'emerald-400';
                                            elseif($act['status'] == 'Izin') $color = 'amber-400';
                                            elseif($act['status'] == 'Sakit') $color = 'blue-400';
                                            elseif($act['status'] == 'Alfa') $color = 'rose-400';
                                        ?>
                                        <span class="px-2 py-1 rounded-full bg-<?= $color ?>/10 text-<?= $color ?> text-[10px] font-bold border border-<?= $color ?>/20 whitespace-nowrap">
                                            <?= $act['status'] ?>
                                        </span>
                                    </td>
                                    <td class="py-4 text-right text-slate-500 font-mono text-xs">
                                        <?= date('d M Y', strtotime($act['tanggal'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side: Actions -->
        <div class="bg-denim-800 border border-slate-700 rounded-2xl p-5 md:p-6 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-neon-blue/5 rounded-full blur-2xl"></div>
            <h2 class="text-base md:text-lg font-bold text-white mb-6">Tindakan Cepat</h2>

            <div class="space-y-3 md:space-y-4">
                <!-- Tambah Pegawai Link -->
                <a href="?page=data_sdi" class="w-full flex items-center justify-between p-3 md:p-4 rounded-xl border border-slate-600/50 bg-slate-700/30 hover:bg-neon-indigo/10 hover:border-neon-indigo/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-slate-800 flex items-center justify-center text-neon-blue group-hover:text-neon-indigo transition-colors"><i class="fa-solid fa-user-plus"></i></div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-white">Kelola Pegawai</p>
                            <p class="text-[10px] md:text-xs text-slate-400">Database & Profil SDI</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-500 group-hover:text-neon-blue transition-colors"></i>
                </a>

                <!-- Manajemen User Link -->
                <a href="?page=manajemen_user" class="w-full flex items-center justify-between p-3 md:p-4 rounded-xl border border-slate-600/50 bg-slate-700/30 hover:bg-neon-blue/10 hover:border-neon-blue/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-slate-800 flex items-center justify-center text-emerald-400 transition-colors"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-white">Manajemen User</p>
                            <p class="text-[10px] md:text-xs text-slate-400">Hak akses & role sistem</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-500 group-hover:text-emerald-400 transition-colors"></i>
                </a>
            </div>

            <!-- Calendar Event Mockup -->
            <div class="mt-6 md:mt-8 p-4 rounded-xl bg-slate-900 border border-slate-700 flex items-center gap-4">
                <div class="text-center shrink-0">
                    <p class="text-xl md:text-2xl font-bold text-neon-blue"><?= date('d') ?></p>
                    <p class="text-[10px] md:text-xs text-slate-400 uppercase tracking-widest"><?= date('M') ?></p>
                </div>
                <div class="border-l border-slate-600 pl-4">
                    <p class="text-sm font-medium text-white line-clamp-1">Data Entry Terakhir</p>
                    <p class="text-[10px] md:text-xs text-slate-400 mt-1 italic"><i class="fa-regular fa-clock mr-1"></i> Hari ini, <?= date('H:i') ?> WITA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 text-center text-xs text-slate-500 font-bold uppercase tracking-widest">
        &copy; <?= date('Y') ?> Yayasan Pondok Pesantren Hidayatullah Balikpapan.
    </div>
</div>