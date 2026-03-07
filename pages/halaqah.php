<?php
// --- LOGIKA CRUD HALAQAH (VERSI SEDERHANA) ---

// 1. Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM halaqah WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    echo "<script>window.location.href='index.php?page=halaqah';</script>";
    exit;
}

// 2. Tambah / Edit Data (Handle POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nama_halaqah = $_POST['nama_halaqah'];
    $keterangan = $_POST['keterangan'];

    if ($id) {
        // Update
        $sql = "UPDATE halaqah SET nama_halaqah=?, keterangan=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nama_halaqah, $keterangan, $id]);
    } else {
        // Create
        $sql = "INSERT INTO halaqah (nama_halaqah, keterangan) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nama_halaqah, $keterangan]);
    }
    echo "<script>window.location.href='index.php?page=halaqah';</script>";
    exit;
}

// 3. Ambil Data untuk Tabel
$query = "SELECT * FROM halaqah ORDER BY id DESC";
$results = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// 4. Ambil data untuk Edit (Trigger Modal Edit)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM halaqah WHERE id = ?");
    $stmt->execute([$id_edit]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="flex flex-col p-2 md:p-4 animate-in fade-in duration-500">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-denim-800 p-6 rounded-2xl border border-slate-700 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-neon-indigo/10 rounded-lg">
                    <i class="fa-solid fa-mosque text-neon-indigo"></i>
                </div>
                Data Halaqah
            </h1>
            <p class="text-sm text-slate-400 mt-1">Daftar kelompok halaqah santri / pegawai</p>
        </div>
        <button onclick="toggleModal('modalHalaqah')" class="bg-neon-indigo hover:opacity-90 text-white px-6 py-3 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-neon-indigo/20 font-bold">
            <i class="fa-solid fa-plus"></i>
            Tambah Halaqah
        </button>
    </div>

    <!-- Main Table -->
    <div class="bg-denim-800 rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700 text-neon-blue uppercase text-xs tracking-widest font-black">
                        <th class="px-6 py-5 w-16 text-center">No</th>
                        <th class="px-6 py-5">Nama Halaqah</th>
                        <th class="px-6 py-5">Keterangan</th>
                        <th class="px-6 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-slate-300">
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i class="fa-solid fa-folder-open text-4xl text-slate-700 mb-3 block"></i>
                                <span class="text-slate-500 italic">Belum ada data halaqah.</span>
                            </td>
                        </tr>
                    <?php else: $no = 1; foreach ($results as $row): ?>
                        <tr class="hover:bg-slate-700/20 transition-colors group">
                            <td class="px-6 py-4 text-center font-mono text-slate-500 text-sm"><?= $no++ ?></td>
                            <td class="px-6 py-4 font-bold text-white group-hover:text-neon-blue transition-colors">
                                <?= htmlspecialchars($row['nama_halaqah']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400 italic">
                                <?= htmlspecialchars($row['keterangan'] ?: '-') ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-3">
                                    <a href="?page=halaqah&edit=<?= $row['id'] ?>" class="p-2.5 bg-slate-900/50 hover:bg-neon-blue hover:text-white rounded-xl transition-all text-neon-blue shadow-inner" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="?page=halaqah&delete=<?= $row['id'] ?>" onclick="return confirm('Hapus data halaqah ini?')" class="p-2.5 bg-slate-900/50 hover:bg-rose-500 hover:text-white rounded-xl transition-all text-rose-500 shadow-inner" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM (TAMBAH/EDIT) -->
<div id="modalHalaqah" class="<?= isset($edit_data) ? 'flex' : 'hidden' ?> fixed inset-0 z-50 items-center justify-center bg-denim-950/90 backdrop-blur-md p-4">
    <div class="bg-denim-800 border border-slate-700 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in duration-300">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-700 flex justify-between items-center bg-slate-900/40">
            <h3 class="text-xl font-bold text-white tracking-tight italic">
                <i class="fa-solid <?= isset($edit_data) ? 'fa-pen-to-square' : 'fa-plus-circle' ?> mr-2 text-neon-blue"></i>
                <?= isset($edit_data) ? 'Edit Halaqah' : 'Halaqah Baru' ?>
            </h3>
            <button onclick="window.location.href='?page=halaqah'" class="text-slate-500 hover:text-white transition-colors">
                <i class="fa-solid fa-circle-xmark text-2xl"></i>
            </button>
        </div>
        
        <form action="" method="POST" class="p-8">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="space-y-6">
                <!-- Nama Halaqah -->
                <div>
                    <label class="block text-[10px] font-black text-neon-blue uppercase tracking-[0.2em] mb-2 ml-1">Nama Halaqah</label>
                    <input type="text" name="nama_halaqah" value="<?= $edit_data['nama_halaqah'] ?? '' ?>" required
                        class="w-full bg-denim-900 border border-slate-600 rounded-2xl px-5 py-4 text-white focus:ring-2 focus:ring-neon-blue outline-none transition-all placeholder:text-slate-600 shadow-inner" placeholder="Contoh: Abu Bakar Ash-Shiddiq">
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-[10px] font-black text-neon-blue uppercase tracking-[0.2em] mb-2 ml-1">Keterangan</label>
                    <textarea name="keterangan" rows="4"
                        class="w-full bg-denim-900 border border-slate-600 rounded-2xl px-5 py-4 text-white focus:ring-2 focus:ring-neon-blue outline-none transition-all placeholder:text-slate-600 shadow-inner" placeholder="Tambahkan deskripsi kelompok halaqah..."><?= $edit_data['keterangan'] ?? '' ?></textarea>
                </div>
            </div>

            <div class="mt-10 flex gap-4">
                <button type="button" onclick="window.location.href='?page=halaqah'" 
                    class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase tracking-widest hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                    class="flex-[2] bg-gradient-to-r from-neon-blue to-neon-indigo text-denim-950 hover:from-neon-indigo hover:to-neon-blue hover:text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.1em] shadow-xl active:scale-95 transition-all duration-300">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }

    // Tutup modal jika area luar (backdrop) diklik
    window.onclick = function(event) {
        const modal = document.getElementById('modalHalaqah');
        if (event.target == modal) {
            window.location.href = '?page=halaqah';
        }
    }
</script>