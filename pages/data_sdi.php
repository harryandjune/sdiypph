<?php
// --- LOGIKA SETTING PAGINASI & PENCARIAN ---
$limit = 10; // Jumlah baris per halaman
$halaman_aktif = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($halaman_aktif - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';

// --- LOGIKA CRUD ---

// 1. Logika Impor CSV (Tetap sama)
if (isset($_POST['import_data'])) {
    if ($_FILES['file_csv']['name']) {
        $filename = $_FILES['file_csv']['tmp_name'];
        $file = fopen($filename, "r");
        fgetcsv($file); 
        $success_count = 0;
        while (($column = fgetcsv($file, 1000, ",")) !== FALSE) {
            $nama           = !empty($column[0]) ? $column[0] : 'Tanpa Nama';
            $jenis_kelamin  = !empty($column[1]) ? strtoupper($column[1]) : 'L';
            $tempat_lahir   = !empty($column[2]) ? $column[2] : '-';
            $tanggal_lahir  = !empty($column[3]) ? date('Y-m-d', strtotime(str_replace('/', '-', $column[3]))) : '1900-01-01';
            $nik            = !empty($column[4]) ? $column[4] : null; 
            $tmt            = !empty($column[5]) ? date('Y-m-d', strtotime(str_replace('/', '-', $column[5]))) : date('Y-m-d');

            try {
                $sql = "INSERT INTO data_sdi (nama, jenis_kelamin, tempat_lahir, tanggal_lahir, nik, tmt) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $res = $stmt->execute([$nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $nik, $tmt]);
                if($res) $success_count++;
            } catch (PDOException $e) { continue; }
        }
        fclose($file);
        header("Location: index.php?page=data_sdi&notif=Data berhasil diimpor: $success_count");
        exit;
    }
}

// 2. Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM data_sdi WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    header("Location: index.php?page=data_sdi");
    exit;
}

// 3. Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['import_data'])) {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nik = !empty($_POST['nik']) ? $_POST['nik'] : null;
    $tmt = $_POST['tmt'];

    if ($id) {
        $sql = "UPDATE data_sdi SET nama=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, nik=?, tmt=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $nik, $tmt, $id]);
    } else {
        $sql = "INSERT INTO data_sdi (nama, jenis_kelamin, tempat_lahir, tanggal_lahir, nik, tmt) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $nik, $tmt]);
    }
    header("Location: index.php?page=data_sdi");
    exit;
}

// 4. Ambil Data dengan Paginasi & Pencarian
$params = [];
$where_sql = "";
if ($search != '') {
    $where_sql = " WHERE nama LIKE ? OR nik LIKE ? OR tempat_lahir LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Hitung total baris untuk paginasi
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM data_sdi $where_sql");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_halaman = ceil($total_rows / $limit);

// Ambil data limit
$sql_data = "SELECT * FROM data_sdi $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt_data = $conn->prepare($sql_data);
$stmt_data->execute($params);
$results = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// 5. Ambil data untuk Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM data_sdi WHERE id = ?");
    $stmt->execute([$id_edit]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="flex flex-col p-2 md:p-4">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-denim-800 p-6 rounded-2xl border border-slate-700 shadow-lg">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-neon-blue/10 rounded-lg">
                    <i class="fa-solid fa-users-gear text-neon-blue"></i>
                </div>
                Database Pegawai SDI
            </h1>
            <p class="text-sm text-slate-400 mt-1 italic">Menampilkan <?= count($results) ?> dari <?= $total_rows ?> total data</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3">
            <!-- Kolom Pencarian -->
            <form action="" method="GET" class="relative group">
                <input type="hidden" name="page" value="data_sdi">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama / NIK..." 
                    class="w-full md:w-64 bg-denim-900 border border-slate-600 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-neon-blue outline-none transition-all">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-500 group-focus-within:text-neon-blue transition-colors text-xs"></i>
            </form>

            <div class="flex gap-2">
                <button onclick="toggleModal('modalImport')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-4 py-2.5 rounded-xl border border-slate-600 font-bold text-xs transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-csv"></i> IMPOR
                </button>
                <button onclick="toggleModal('modalForm')" class="bg-neon-indigo hover:shadow-[0_0_15px_rgba(99,102,241,0.4)] text-white px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> BARU
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-denim-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden mb-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700 text-neon-blue text-[11px] uppercase tracking-widest font-black">
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4 text-center">L/P</th>
                        <th class="px-6 py-4">Tempat, Tgl Lahir</th>
                        <th class="px-6 py-4">NIK</th>
                        <th class="px-6 py-4">TMT Kerja</th>
                        <th class="px-6 py-4 text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-slate-300">
                    <?php if (empty($results)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                            <tr class="hover:bg-slate-700/20 transition-all">
                                <td class="px-6 py-4 font-bold text-white"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $row['jenis_kelamin'] == 'L' ? 'bg-blue-500/10 text-blue-400' : 'bg-pink-500/10 text-pink-400' ?>">
                                        <?= $row['jenis_kelamin'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= htmlspecialchars($row['tempat_lahir']) ?>, <span class="text-xs text-slate-500"><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs italic text-slate-400">
                                    <?= $row['nik'] ? htmlspecialchars($row['nik']) : '<span class="text-slate-600 font-sans">- Belum Ada -</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-neon-blue font-bold text-sm">
                                    <?= date('d M Y', strtotime($row['tmt'])) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="?page=data_sdi&edit=<?= $row['id'] ?>&p=<?= $halaman_aktif ?>&search=<?= $search ?>" class="text-neon-blue hover:text-white"><i class="fa-solid fa-pen text-xs"></i></a>
                                        <a href="?page=data_sdi&delete=<?= $row['id'] ?>" onclick="return confirm('Hapus data ini?')" class="text-rose-500 hover:text-white"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_halaman > 1): ?>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-2 px-2">
        <div class="text-xs text-slate-500 italic">
            Halaman <span class="text-slate-300 font-bold"><?= $halaman_aktif ?></span> dari <span class="text-slate-300 font-bold"><?= $total_halaman ?></span>
        </div>
        <div class="flex items-center gap-1">
            <!-- Tombol Previous -->
            <?php if ($halaman_aktif > 1): ?>
                <a href="?page=data_sdi&p=<?= $halaman_aktif - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-400 hover:text-white hover:border-neon-blue transition-all text-xs">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php
            $range = 2;
            for ($i = 1; $i <= $total_halaman; $i++): 
                if($i == 1 || $i == $total_halaman || ($i >= $halaman_aktif - $range && $i <= $halaman_aktif + $range)):
            ?>
                <a href="?page=data_sdi&p=<?= $i ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-2 rounded-lg text-xs font-bold transition-all border <?= $i == $halaman_aktif ? 'bg-neon-blue text-denim-900 border-neon-blue' : 'bg-slate-800 text-slate-400 border-slate-700 hover:border-slate-500' ?>">
                    <?= $i ?>
                </a>
            <?php elseif($i == $halaman_aktif - $range - 1 || $i == $halaman_aktif + $range + 1): ?>
                <span class="text-slate-600">...</span>
            <?php endif; endfor; ?>

            <!-- Tombol Next -->
            <?php if ($halaman_aktif < $total_halaman): ?>
                <a href="?page=data_sdi&p=<?= $halaman_aktif + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-400 hover:text-white hover:border-neon-blue transition-all text-xs">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL IMPORT & FORM (Tetap sama seperti kode sebelumnya) -->
<!-- ... (Bagian Modal Import & Modal Form tidak berubah) ... -->



<!-- MODAL IMPORT -->
<div id="modalImport" class="hidden fixed inset-0 z-50 items-center justify-center bg-denim-950/90 backdrop-blur-sm p-4">
    <div class="bg-denim-800 border border-slate-700 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <i class="fa-solid fa-file-csv text-neon-blue"></i> Upload Data CSV
        </h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="border-2 border-dashed border-slate-600 rounded-2xl p-6 text-center hover:border-neon-blue transition-all mb-6">
                <input type="file" name="file_csv" accept=".csv" required class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-neon-blue file:text-denim-900 hover:file:bg-cyan-400">
                <p class="text-[10px] text-slate-500 mt-4 italic">Format kolom: nama, jenis_kelamin (L/P), tempat_lahir, tgl_lahir, nik, tmt</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="toggleModal('modalImport')" class="flex-1 py-3 text-slate-400 text-xs font-bold uppercase tracking-widest">Batal</button>
                <button type="submit" name="import_data" class="flex-[2] bg-neon-blue text-denim-900 py-3 rounded-xl font-black text-xs uppercase tracking-widest active:scale-95 transition-all">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div id="modalForm" class="<?= isset($edit_data) ? 'flex' : 'hidden' ?> fixed inset-0 z-50 items-center justify-center bg-denim-950/90 backdrop-blur-sm p-4">
    <div class="bg-denim-800 border border-slate-700 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden p-8">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-black text-white italic uppercase tracking-tighter">
                <i class="fa-solid <?= isset($edit_data) ? 'fa-user-pen' : 'fa-user-plus' ?> mr-2 text-neon-blue"></i>
                <?= isset($edit_data) ? 'Edit Profil' : 'Pegawai Baru' ?>
            </h3>
            <button onclick="window.location.href='?page=data_sdi'" class="text-slate-500 hover:text-white transition-all"><i class="fa-solid fa-xmark text-2xl"></i></button>
        </div>
        
        <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-neon-blue uppercase mb-2 tracking-widest">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= $edit_data['nama'] ?? '' ?>" required class="w-full bg-denim-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-neon-blue outline-none transition shadow-inner">
            </div>

            <div>
                <label class="block text-[10px] font-black text-neon-blue uppercase mb-2 tracking-widest">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full bg-denim-900 border border-slate-600 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-neon-blue transition">
                    <option value="L" <?= (isset($edit_data) && $edit_data['jenis_kelamin'] == 'L') ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= (isset($edit_data) && $edit_data['jenis_kelamin'] == 'P') ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-neon-blue uppercase mb-2 tracking-widest">NIK (Opsional)</label>
                <input type="text" name="nik" value="<?= $edit_data['nik'] ?? '' ?>" class="w-full bg-denim-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-neon-blue outline-none transition" placeholder="Boleh dikosongkan">
            </div>

            <div>
                <label class="block text-[10px] font-black text-neon-blue uppercase mb-2 tracking-widest">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="<?= $edit_data['tempat_lahir'] ?? '' ?>" required class="w-full bg-denim-900 border border-slate-600 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-neon-blue">
            </div>

            <div>
                <label class="block text-[10px] font-black text-neon-blue uppercase mb-2 tracking-widest">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= $edit_data['tanggal_lahir'] ?? '' ?>" required class="w-full bg-denim-900 border border-slate-600 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-neon-blue">
            </div>

            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-neon-indigo uppercase mb-2 tracking-widest">Terhitung Mulai Tanggal (TMT)</label>
                <input type="date" name="tmt" value="<?= $edit_data['tmt'] ?? '' ?>" required class="w-full bg-denim-900 border-2 border-neon-indigo/30 rounded-xl px-4 py-3 text-white font-bold outline-none focus:ring-2 focus:ring-neon-indigo">
            </div>

            <div class="md:col-span-2 mt-4 flex gap-4">
                <button type="button" onclick="window.location.href='?page=data_sdi'" class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase tracking-widest">Batal</button>
                <button type="submit" class="flex-[2] bg-gradient-to-r from-neon-blue to-neon-indigo text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl active:scale-95 transition-all">Simpan Data Pegawai</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
        document.getElementById(id).classList.toggle('flex');
    }
    
</script>
