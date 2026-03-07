<?php
// --- SIMULASI LOGIN (Ganti dengan ID Pegawai yang sedang login dari Session Anda) ---
$user_sdi_id = 1; // Contoh: ID Ahmad Fauzi (Ketua)

// --- 1. VALIDASI HARI & WAKTU (SABTU PAGI) ---
$hari_ini = date('N'); // 1 (Senin) s/d 7 (Minggu). Sabtu adalah 6.
$jam_sekarang = (int)date('H');
$is_saturday = ($hari_ini == 6);
// Batas jam sabtu pagi (Misal: Jam 05:00 sampai 11:00)
$is_morning = ($jam_sekarang >= 5 && $jam_sekarang <= 11);

// Debugging: Buka baris di bawah jika ingin mengetes di luar hari sabtu
// $is_saturday = true; $is_morning = true; 

// --- 2. CEK APAKAH USER ADALAH KETUA HALAQAH ---
$stmt_ketua = $conn->prepare("SELECT halaqah_id FROM anggota_halaqah WHERE sdi_id = ? AND peran = 'Ketua' LIMIT 1");
$stmt_ketua->execute([$user_sdi_id]);
$data_ketua = $stmt_ketua->fetch(PDO::FETCH_ASSOC);

if (!$data_ketua) {
    echo "<div class='p-10 text-center'><h1 class='text-rose-500 font-bold'>Akses Ditolak!</h1><p class='text-slate-400'>Halaman ini hanya untuk Ketua Halaqah.</p></div>";
    return;
}

$halaqah_id = $data_ketua['halaqah_id'];

// Ambil Nama Halaqah
$stmt_h = $conn->prepare("SELECT nama_halaqah FROM halaqah WHERE id = ?");
$stmt_h->execute([$halaqah_id]);
$nama_halaqah = $stmt_h->fetchColumn();

// --- 3. LOGIKA SIMPAN ABSEN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_absen'])) {
    $tanggal = date('Y-m-d');
    $statuses = $_POST['status']; // Array [sdi_id => status]
    
    foreach ($statuses as $sdi_id => $status) {
        // Cek apakah sudah absen hari ini (mencegah double input)
        $cek = $conn->prepare("SELECT id FROM absen_halaqah WHERE sdi_id = ? AND tanggal = ?");
        $cek->execute([$sdi_id, $tanggal]);
        
        if ($cek->rowCount() == 0) {
            $ins = $conn->prepare("INSERT INTO absen_halaqah (halaqah_id, sdi_id, tanggal, status) VALUES (?, ?, ?, ?)");
            $ins->execute([$halaqah_id, $sdi_id, $tanggal, $status]);
        } else {
            // Update jika sudah ada
            $upd = $conn->prepare("UPDATE absen_halaqah SET status = ? WHERE sdi_id = ? AND tanggal = ?");
            $upd->execute([$status, $sdi_id, $tanggal]);
        }
    }
    echo "<script>alert('Absensi berhasil disimpan!'); window.location.href='index.php?page=absen_sabtu';</script>";
    exit;
}

// --- 4. AMBIL DAFTAR ANGGOTA HALAQAH ---
$query_anggota = "SELECT s.id, s.nama, ah.peran 
                  FROM anggota_halaqah ah
                  JOIN data_sdi s ON ah.sdi_id = s.id
                  WHERE ah.halaqah_id = ?
                  ORDER BY CASE WHEN ah.peran = 'Ketua' THEN 1 ELSE 2 END, s.nama ASC";
$stmt_a = $conn->prepare($query_anggota);
$stmt_a->execute([$halaqah_id]);
$anggota = $stmt_a->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah sudah absen hari ini untuk tampilan
$tanggal_hari_ini = date('Y-m-d');
$absen_done = $conn->query("SELECT sdi_id, status FROM absen_halaqah WHERE halaqah_id = $halaqah_id AND tanggal = '$tanggal_hari_ini'")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="flex flex-col p-2 md:p-4">
    <!-- Header Section -->
    <div class="mb-6 bg-denim-800 p-6 rounded-2xl border border-slate-700 shadow-lg">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-white italic uppercase tracking-tighter">
                    <i class="fa-solid fa-calendar-check text-neon-blue mr-2"></i> Absensi Sabtu Pagi
                </h1>
                <p class="text-sm text-slate-400 mt-1">Kelompok: <span class="text-neon-indigo font-bold"><?= htmlspecialchars($nama_halaqah) ?></span></p>
            </div>
            <div class="text-right">
                <div class="text-lg font-mono text-white"><?= date('d F Y') ?></div>
                <div class="text-xs text-slate-500 uppercase tracking-widest font-bold">Waktu Server: <?= date('H:i') ?> WITA</div>
            </div>
        </div>
    </div>

    <?php if (!$is_saturday || !$is_morning): ?>
        <!-- Tampilan Jika Bukan Waktu Absen -->
        <div class="bg-amber-500/10 border border-amber-500/20 p-10 rounded-3xl text-center">
            <i class="fa-solid fa-clock-rotate-left text-5xl text-amber-500 mb-4 opacity-50"></i>
            <h2 class="text-xl font-bold text-amber-500">Absensi Belum Dibuka</h2>
            <p class="text-slate-400 mt-2 max-w-md mx-auto">Halaman absensi hanya dapat diakses pada hari <span class="text-white font-bold">Sabtu Pagi (05:00 - 11:00)</span>. Silakan kembali pada waktu yang ditentukan.</p>
        </div>
    <?php else: ?>
        <!-- Form Absen -->
        <form action="" method="POST" class="space-y-4">
            <div class="bg-denim-800 rounded-3xl border border-slate-700 shadow-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-700 text-[10px] text-neon-blue uppercase tracking-[0.2em] font-black">
                            <th class="px-6 py-4">Nama Anggota</th>
                            <th class="px-6 py-4">Peran</th>
                            <th class="px-6 py-4 text-center">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <?php foreach ($anggota as $row): 
                            $saved_status = $absen_done[$row['id']] ?? 'Hadir';
                        ?>
                            <tr class="hover:bg-slate-700/10 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-bold text-white"><?= htmlspecialchars($row['nama']) ?></div>
                                    <?php if($row['id'] == $user_sdi_id): ?>
                                        <span class="text-[9px] bg-neon-blue/20 text-neon-blue px-2 py-0.5 rounded uppercase font-black">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs text-slate-500"><?= $row['peran'] ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center items-center gap-3 md:gap-6">
                                        <?php 
                                        $options = [
                                            'Hadir' => 'bg-emerald-500', 
                                            'Izin' => 'bg-amber-500', 
                                            'Sakit' => 'bg-blue-500', 
                                            'Alfa' => 'bg-rose-500'
                                        ];
                                        foreach ($options as $opt => $color): ?>
                                            <label class="flex flex-col items-center gap-1 cursor-pointer group">
                                                <input type="radio" name="status[<?= $row['id'] ?>]" value="<?= $opt ?>" 
                                                    <?= ($saved_status == $opt) ? 'checked' : '' ?>
                                                    class="peer hidden">
                                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl border-2 border-slate-700 flex items-center justify-center text-[10px] font-black uppercase text-slate-500 peer-checked:border-none peer-checked:text-white <?= str_replace('bg-', 'peer-checked:bg-', $color) ?> transition-all duration-200 group-hover:border-slate-500">
                                                    <?= substr($opt, 0, 1) ?>
                                                </div>
                                                <span class="text-[9px] uppercase font-bold text-slate-600 peer-checked:text-slate-300"><?= $opt ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end p-4">
                <button type="submit" name="submit_absen" class="w-full md:w-auto bg-gradient-to-r from-neon-blue to-neon-indigo text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:shadow-neon-blue/20 transition-all active:scale-95">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Absensi Hari Ini
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>