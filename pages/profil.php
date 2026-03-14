<?php
$user_id = $_SESSION['user_id'];
$sdi_id  = $_SESSION['sdi_id'] ?? null;

// --- 1. LOGIKA UPDATE PER TAB ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tab Data Pribadi (Insert/Update)
    if (isset($_POST['save_personal'])) {
        $nama = $_POST['nama']; $jk = $_POST['jenis_kelamin'];
        $tempat = $_POST['tempat_lahir']; $tgl = $_POST['tanggal_lahir'];
        $nik = $_POST['nik']; $tmt = $_POST['tmt'] ?? date('Y-m-d');

        if (empty($sdi_id)) {
            $ins = $conn->prepare("INSERT INTO data_sdi (nama, jenis_kelamin, tempat_lahir, tanggal_lahir, nik, tmt) VALUES (?,?,?,?,?,?)");
            $ins->execute([$nama, $jk, $tempat, $tgl, $nik, $tmt]);
            $new_sdi_id = $conn->lastInsertId();
            $conn->prepare("UPDATE users SET sdi_id = ? WHERE id = ?")->execute([$new_sdi_id, $user_id]);
            $_SESSION['sdi_id'] = $new_sdi_id;
        } else {
            $sql = "UPDATE data_sdi SET nama=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, nik=? WHERE id=?";
            $conn->prepare($sql)->execute([$nama, $jk, $tempat, $tgl, $nik, $sdi_id]);
        }
        echo "<script>window.location.href='index.php?page=profil&tab=pribadi';</script>"; exit;
    }

    // Tab Info Akun
    if (isset($_POST['save_account'])) {
        $conn->prepare("UPDATE users SET email=?, nomor_tlp=? WHERE id=?")->execute([$_POST['email'], $_POST['nomor_tlp'], $user_id]);
        echo "<script>window.location.href='index.php?page=profil&tab=akun';</script>"; exit;
    }

    // --- LOGIKA SIMPAN PENDIDIKAN (FIXED NULL) ---
    if (isset($_POST['save_education'])) {
        $tahun_lulus = !empty($_POST['tahun_lulus']) ? $_POST['tahun_lulus'] : null; // Ubah ke NULL jika kosong
        $sql = "INSERT INTO pendidikan_sdi (sdi_id, jenjang, nama_instansi, jurusan, tahun_lulus) VALUES (?,?,?,?,?)";
        $conn->prepare($sql)->execute([$sdi_id, $_POST['jenjang'], $_POST['nama_instansi'], $_POST['jurusan'], $tahun_lulus]);
        echo "<script>window.location.href='index.php?page=profil&tab=pendidikan';</script>";
        exit;
    }

    // --- LOGIKA SIMPAN SERTIFIKASI (FIXED NULL) ---
    if (isset($_POST['save_certification'])) {
        $pangkat = $_POST['pangkat_terakhir'];
        $jenis   = $_POST['jenis_sertifikasi'];
        $tahun   = !empty($_POST['tahun_sertifikat']) ? $_POST['tahun_sertifikat'] : null; // Ubah ke NULL jika kosong
        $nomor   = $_POST['nomor_sertifikat'];
        $file_name = $_POST['old_file'] ?? null;

        if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] == 0) {
            $target_dir = "assets/docs/sertifikat/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $ext = pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION);
            $file_name = "cert_" . $sdi_id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $target_dir . $file_name);
        }

        $cek = $conn->prepare("SELECT id FROM sertifikasi_sdi WHERE sdi_id = ?");
        $cek->execute([$sdi_id]);

        if ($cek->rowCount() > 0) {
            $sql = "UPDATE sertifikasi_sdi SET pangkat_terakhir=?, jenis_sertifikasi=?, tahun_sertifikat=?, nomor_sertifikat=?, file_sertifikat=? WHERE sdi_id=?";
            $conn->prepare($sql)->execute([$pangkat, $jenis, $tahun, $nomor, $file_name, $sdi_id]);
        } else {
            $sql = "INSERT INTO sertifikasi_sdi (sdi_id, pangkat_terakhir, jenis_sertifikasi, tahun_sertifikat, nomor_sertifikat, file_sertifikat) VALUES (?,?,?,?,?,?)";
            $conn->prepare($sql)->execute([$sdi_id, $pangkat, $jenis, $tahun, $nomor, $file_name]);
        }
        echo "<script>window.location.href='index.php?page=profil&tab=sertifikasi';</script>";
        exit;
    }

    // Logika Upload Foto Profil
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $target_dir = "assets/img/profil/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $file_name = "sdi_" . $sdi_id . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_dir . $file_name)) {
            $conn->prepare("UPDATE data_sdi SET foto = ? WHERE id = ?")->execute([$file_name, $sdi_id]);
            echo "<script>window.location.href='index.php?page=profil';</script>"; exit;
        }
    }
}

// --- 2. AMBIL DATA TERKINI ---
$query = "SELECT u.*, s.*, h.nama_halaqah, ah.peran as peran_halaqah, ah.tanggal_bergabung as tgl_gabung_halaqah,
          cert.pangkat_terakhir, cert.jenis_sertifikasi, cert.tahun_sertifikat, cert.nomor_sertifikat, cert.file_sertifikat
          FROM users u 
          LEFT JOIN data_sdi s ON u.sdi_id = s.id 
          LEFT JOIN anggota_halaqah ah ON s.id = ah.sdi_id
          LEFT JOIN halaqah h ON ah.halaqah_id = h.id
          LEFT JOIN sertifikasi_sdi cert ON s.id = cert.sdi_id
          WHERE u.id = ?";
$stmt = $conn->prepare($query); $stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pendidikan = $sdi_id ? $conn->query("SELECT * FROM pendidikan_sdi WHERE sdi_id = '$sdi_id' ORDER BY tahun_lulus DESC")->fetchAll(PDO::FETCH_ASSOC) : [];
$is_complete = !empty($user['sdi_id']);
?>

<div class="p-2 md:p-4 animate-in fade-in duration-700">
    <!-- HEADER PROFIL -->
    <div class="relative mb-6">
        <div class="h-40 rounded-[1.5rem] bg-denim-900 overflow-hidden relative border border-slate-800 shadow-lg">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-denim-950/90 to-transparent"></div>
            <div class="absolute top-0 right-0 p-6">
                <button class="bg-white/5 hover:bg-white/10 backdrop-blur-md text-white px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest transition border border-white/10">
                    <i class="fa-solid fa-camera mr-1.5"></i> Ganti Sampul
                </button>
            </div>
        </div>

        <div class="px-6 flex flex-col md:flex-row items-center md:items-end -mt-12 gap-5 relative z-10">
            <div class="relative group">
                <div class="w-32 h-32 rounded-[1.5rem] border-[4px] border-denim-950 shadow-2xl overflow-hidden bg-denim-800">
                    <?php $path = !empty($user['foto']) ? 'assets/img/profil/' . $user['foto'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama'] ?? $user['username']) . '&background=6366f1&color=fff&size=200'; ?>
                    <img src="<?= $path ?>" class="w-full h-full object-cover">
                </div>
                <form id="form_foto" action="" method="POST" enctype="multipart/form-data" class="hidden">
                    <input type="file" name="foto_profil" id="input_foto" accept="image/*" onchange="document.getElementById('form_foto').submit()">
                </form>
                <button type="button" onclick="document.getElementById('input_foto').click()" class="absolute -bottom-1 -right-1 bg-neon-blue text-denim-950 p-2 rounded-xl shadow-lg border-2 border-denim-950 hover:scale-110 transition">
                    <i class="fa-solid fa-camera text-[10px]"></i>
                </button>
            </div>
            
            <div class="flex-1 text-center md:text-left pb-1">
                <div class="flex items-center justify-center md:justify-start gap-2">
                    <h1 class="text-xl font-black text-white tracking-tight uppercase italic"><?= $user['nama'] ?? $user['username'] ?></h1>
                    <span class="bg-emerald-500 w-2 h-2 rounded-full shadow-lg"></span>
                </div>
                <p class="text-slate-400 font-medium text-[10px] tracking-widest uppercase mt-0.5">
                    <i class="fa-solid fa-fingerprint text-neon-blue"></i> ID: <?= $user['sdi_id'] ? 'SDI-'.$user['sdi_id'] : 'UNREGISTERED' ?>
                </p>
            </div>

            <div class="flex gap-2 pb-2">
                <button onclick="toggleModal('modalMasterEdit')" class="bg-denim-800 border border-slate-700 text-slate-300 px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest shadow-sm hover:bg-slate-700 transition-all active:scale-95">
                    <i class="fa-solid fa-gear mr-1.5 text-neon-blue"></i> Pengaturan Profil
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- BENTO: DATA PERSONAL -->
        <div class="lg:col-span-8 bg-denim-800 p-6 rounded-[2rem] border border-slate-700/50 shadow-sm relative overflow-hidden">
            <h3 class="text-[10px] font-black text-slate-500 mb-8 flex items-center uppercase tracking-[0.2em] italic">
                <span class="w-8 h-8 bg-neon-blue/10 text-neon-blue rounded-lg flex items-center justify-center mr-3 border border-neon-blue/20">
                    <i class="fa-solid fa-address-card text-xs"></i>
                </span> Data Personal
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-6">
                <div class="border-l-2 border-slate-700 pl-4">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">NIK KTP</p>
                    <p class="text-slate-200 font-bold text-sm"><?= $user['nik'] ?: '-' ?></p>
                </div>
                <div class="border-l-2 border-slate-700 pl-4">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Tempat, Tgl Lahir</p>
                    <p class="text-slate-200 font-bold text-sm"><?= $user['tempat_lahir'] ?? '-' ?>, <?= !empty($user['tanggal_lahir']) ? date('d M Y', strtotime($user['tanggal_lahir'])) : '-' ?></p>
                </div>
                <div class="border-l-2 border-slate-700 pl-4">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Gender</p>
                    <p class="text-slate-200 font-bold text-sm"><?= ($user['jenis_kelamin'] ?? '') == 'L' ? 'Laki-laki' : (($user['jenis_kelamin'] ?? '') == 'P' ? 'Perempuan' : '-') ?></p>
                </div>
                <div class="border-l-2 border-slate-700 pl-4">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Mulai Tugas (TMT)</p>
                    <p class="text-neon-blue font-black text-sm uppercase italic"><?= !empty($user['tmt']) ? date('d F Y', strtotime($user['tmt'])) : '-' ?></p>
                </div>
            </div>
        </div>

        <!-- BENTO: PEMBINAAN -->
        <div class="lg:col-span-4 bg-denim-950 p-6 rounded-[2rem] shadow-xl border border-indigo-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-neon-indigo rounded-full blur-[60px] opacity-10"></div>
            <h3 class="text-white font-black text-[10px] mb-6 italic uppercase tracking-widest flex items-center">
                <i class="fa-solid fa-shield-halved mr-2 text-neon-indigo"></i> Pembinaan
            </h3>
            <div class="space-y-4">
                <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest mb-1">Nama Kelompok</p>
                    <p class="text-white font-bold text-sm"><?= $user['nama_halaqah'] ?? '<span class="text-slate-700 italic">Belum Ada</span>' ?></p>
                </div>
                <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest mb-1">Peran</p>
                    <p class="text-neon-indigo font-black text-sm italic uppercase"><?= $user['peran_halaqah'] ?? 'ANGGOTA' ?></p>
                </div>
            </div>
        </div>

        <!-- BENTO: PENDIDIKAN -->
        <div class="lg:col-span-8 bg-denim-800 p-6 rounded-[2rem] border border-slate-700/50 shadow-sm">
            <h3 class="text-[10px] font-black text-slate-400 flex items-center uppercase tracking-widest italic mb-8">
                <i class="fa-solid fa-graduation-cap mr-3 text-indigo-400"></i> Riwayat Pendidikan
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (empty($pendidikan)): ?>
                    <p class="text-slate-600 text-[10px] italic">Belum ada data.</p>
                <?php else: foreach($pendidikan as $p): ?>
                    <div class="flex items-center p-4 bg-slate-900/40 rounded-2xl border border-slate-700/30 group">
                        <div class="w-10 h-10 bg-denim-900 rounded-xl flex items-center justify-center text-slate-600 group-hover:text-indigo-400 transition shadow-inner">
                            <i class="fa-solid fa-building-columns text-xs"></i>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-[8px] font-black text-indigo-400 uppercase tracking-tighter"><?= $p['jenjang'] ?> - <?= $p['tahun_lulus'] ?></p>
                            <h4 class="text-xs font-black text-white leading-tight mt-0.5 truncate uppercase italic"><?= htmlspecialchars($p['nama_instansi']) ?></h4>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- BENTO: SERTIFIKASI -->
        <div class="lg:col-span-4 bg-denim-800 p-6 rounded-[2rem] border border-slate-700/50 shadow-sm relative overflow-hidden">
            <h3 class="text-[10px] font-black text-slate-500 mb-6 flex items-center uppercase tracking-[0.2em] italic">
                <i class="fa-solid fa-award mr-2 text-amber-500"></i> Sertifikasi
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pangkat Terakhir</p>
                    <p class="text-slate-200 font-bold text-sm"><?= $user['pangkat_terakhir'] ?? '-' ?></p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Status Sertifikasi</p>
                    <p class="text-amber-500 font-black text-xs uppercase italic"><?= $user['jenis_sertifikasi'] ?? 'Belum Ada' ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MASTER EDIT -->
<div id="modalMasterEdit" class="<?= !$is_complete ? 'flex' : 'hidden' ?> fixed inset-0 z-[100] items-center justify-center bg-denim-950/90 backdrop-blur-md p-4">
    <div class="bg-denim-800 border border-slate-700 w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in duration-300">
        
        <div class="p-6 border-b border-slate-700 flex justify-between items-center bg-slate-900/40">
            <div>
                <h3 class="text-sm font-black text-white uppercase italic tracking-tight"><?= $is_complete ? 'Edit Informasi' : 'Lengkapi Profil' ?></h3>
                <p class="text-[9px] text-neon-blue font-bold uppercase tracking-widest mt-0.5 italic">Integrasi Database SDI</p>
            </div>
            <?php if($is_complete): ?>
                <button onclick="toggleModal('modalMasterEdit')" class="text-slate-500 hover:text-white transition-all"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
            <?php endif; ?>
        </div>

        <!-- TAB NAVIGATION -->
        <div class="flex border-b border-slate-700 px-6 bg-slate-900/20 overflow-x-auto no-scrollbar">
            <button onclick="switchTab('pribadi')" id="btn-pribadi" class="tab-btn py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-neon-blue text-white">Pribadi</button>
            <button onclick="switchTab('akun')" id="btn-akun" class="tab-btn py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-500">Akun</button>
            <button onclick="switchTab('pendidikan')" id="btn-pendidikan" class="tab-btn py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-500">Pendidikan</button>
            <button onclick="switchTab('sertifikasi')" id="btn-sertifikasi" class="tab-btn py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-500">Sertifikasi</button>
        </div>

        <div class="p-6 overflow-y-auto max-h-[60vh] custom-scrollbar">
            <!-- TAB 1: PRIBADI -->
            <div id="content-pribadi" class="tab-content block animate-in fade-in">
                <form action="" method="POST" class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1 tracking-widest">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= $user['nama'] ?? '' ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-neon-blue">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">NIK KTP</label>
                            <input type="text" name="nik" value="<?= $user['nik'] ?? '' ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Gender</label>
                            <select name="jenis_kelamin" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                                <option value="" disabled>-- Pilih --</option>
                                <option value="L" <?= ($user['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= ($user['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="<?= $user['tempat_lahir'] ?? '' ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Tgl Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?= $user['tanggal_lahir'] ?? '' ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                        </div>
                    </div>
                    <?php if(!$is_complete): ?>
                    <div>
                        <label class="block text-[9px] font-black text-neon-indigo uppercase mb-1 tracking-widest">TMT Bergabung</label>
                        <input type="date" name="tmt" required class="w-full bg-denim-950 border border-neon-indigo/30 rounded-xl px-4 py-2 text-xs text-white outline-none">
                    </div>
                    <?php endif; ?>
                    <button type="submit" name="save_personal" class="mt-4 bg-neon-blue text-denim-950 font-black uppercase text-[10px] py-4 rounded-xl active:scale-95 transition-all shadow-lg shadow-neon-blue/20">Simpan Data Pribadi</button>
                </form>
            </div>

            <!-- TAB 2: AKUN -->
            <div id="content-akun" class="tab-content hidden animate-in fade-in">
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Email Resmi</label>
                        <input type="email" name="email" value="<?= $user['email'] ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">No. WhatsApp</label>
                        <input type="text" name="nomor_tlp" value="<?= $user['nomor_tlp'] ?>" required class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                    </div>
                    <button type="submit" name="save_account" class="w-full bg-neon-blue text-denim-950 font-black uppercase text-[10px] py-4 rounded-xl active:scale-95 transition-all mt-4">Update Info Akun</button>
                </form>
            </div>

            <!-- TAB 3: PENDIDIKAN -->
            <div id="content-pendidikan" class="tab-content hidden animate-in fade-in">
                <form action="" method="POST" class="grid grid-cols-1 gap-4 bg-denim-950/50 p-4 rounded-2xl border border-slate-700">
                    <h4 class="text-[9px] font-black text-neon-blue uppercase italic">+ Tambah Pendidikan</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <select name="jenjang" required class="w-full bg-denim-950 border border-slate-700 rounded-lg px-3 py-2 text-[10px] text-white outline-none">
                            <option>SD</option><option>SMP</option><option>SMA/SMK</option><option>D3</option><option>S1</option><option>S2</option><option>S3</option>
                        </select>
                        <input type="number" name="tahun_lulus" placeholder="Tahun Lulus" required class="w-full bg-denim-950 border border-slate-700 rounded-lg px-3 py-2 text-[10px] text-white outline-none">
                    </div>
                    <input type="text" name="nama_instansi" placeholder="Nama Sekolah/Univ" required class="w-full bg-denim-950 border border-slate-700 rounded-lg px-3 py-2 text-[10px] text-white outline-none">
                    <input type="text" name="jurusan" placeholder="Jurusan (Opsional)" class="w-full bg-denim-950 border border-slate-700 rounded-lg px-3 py-2 text-[10px] text-white outline-none">
                    <button type="submit" name="save_education" class="bg-indigo-600 text-white font-black uppercase text-[9px] py-3 rounded-lg hover:bg-indigo-500 transition-all">Submit Pendidikan</button>
                </form>
            </div>

            <!-- TAB 4: SERTIFIKASI -->
            <div id="content-sertifikasi" class="tab-content hidden animate-in fade-in">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="old_file" value="<?= $user['file_sertifikat'] ?? '' ?>">
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Pangkat / Golongan Terakhir</label>
                        <input type="text" name="pangkat_terakhir" value="<?= $user['pangkat_terakhir'] ?? '' ?>" placeholder="Contoh: Penata Muda, III/a" class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Jenis Sertifikasi</label>
                            <select name="jenis_sertifikasi" class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                                <option value="Belum Sertifikasi" <?= ($user['jenis_sertifikasi'] ?? '') == 'Belum Sertifikasi' ? 'selected' : '' ?>>Belum</option>
                                <option value="Guru" <?= ($user['jenis_sertifikasi'] ?? '') == 'Guru' ? 'selected' : '' ?>>Guru</option>
                                <option value="Dosen" <?= ($user['jenis_sertifikasi'] ?? '') == 'Dosen' ? 'selected' : '' ?>>Dosen</option>
                                <option value="Lainnya" <?= ($user['jenis_sertifikasi'] ?? '') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Tahun Sertifikat</label>
                            <input type="number" name="tahun_sertifikat" value="<?= $user['tahun_sertifikat'] ?? '' ?>" placeholder="YYYY" class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Nomor Sertifikat</label>
                        <input type="text" name="nomor_sertifikat" value="<?= $user['nomor_sertifikat'] ?? '' ?>" class="w-full bg-denim-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Upload Sertifikat (PDF/JPG)</label>
                        <input type="file" name="file_sertifikat" class="w-full text-[10px] text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[9px] file:font-black file:bg-neon-blue file:text-denim-950 transition-all">
                    </div>
                    <button type="submit" name="save_certification" class="w-full bg-neon-blue text-denim-950 font-black uppercase text-[10px] py-4 rounded-xl active:scale-95 transition-all mt-2">Simpan Sertifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if(modal) {
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    }

    function switchTab(tabId) {
        // Sembunyikan semua konten tab
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.add('hidden');
            c.classList.remove('block');
        });
        // Tampilkan tab yang dipilih
        const target = document.getElementById('content-' + tabId);
        if(target) {
            target.classList.remove('hidden');
            target.classList.add('block');
        }

        // Update gaya tombol tab
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-neon-blue', 'text-white');
            b.classList.add('border-transparent', 'text-slate-500');
        });
        const activeBtn = document.getElementById('btn-' + tabId);
        if(activeBtn) {
            activeBtn.classList.add('border-neon-blue', 'text-white');
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
        }
    }

    <?php if(isset($_GET['tab'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            toggleModal('modalMasterEdit');
            switchTab('<?= $_GET['tab'] ?>');
        });
    <?php endif; ?>
</script>