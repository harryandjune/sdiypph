<?php
// Proteksi: Hanya Superadmin yang boleh mengakses halaman ini
// if ($_SESSION['role'] !== 'superadmin') {
//     echo "<div class='p-10 text-center'><h1 class='text-rose-500 font-bold'>Akses Ditolak!</h1><p class='text-slate-400'>Halaman ini hanya untuk Superadmin.</p></div>";
//     return;
// }

// --- LOGIKA CRUD USER ---

// 1. Ambil list Pegawai untuk dihubungkan ke akun (sdi_id)
$list_sdi = $conn->query("SELECT id, nama FROM data_sdi ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Hapus User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    echo "<script>window.location.href='index.php?page=manajemen_user';</script>";
    exit;
}

// 3. Tambah / Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $sdi_id = !empty($_POST['sdi_id']) ? $_POST['sdi_id'] : null;
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nomor_tlp = $_POST['nomor_tlp'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    if ($id) {
        // UPDATE
        if (!empty($_POST['password'])) {
            // Jika password diisi (Ganti Password)
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET sdi_id=?, username=?, email=?, nomor_tlp=?, password=?, role=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$sdi_id, $username, $email, $nomor_tlp, $password, $role, $status, $id]);
        } else {
            // Jika password kosong (Tetap pakai password lama)
            $sql = "UPDATE users SET sdi_id=?, username=?, email=?, nomor_tlp=?, role=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$sdi_id, $username, $email, $nomor_tlp, $role, $status, $id]);
        }
    } else {
        // INSERT BARU
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (sdi_id, username, email, nomor_tlp, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$sdi_id, $username, $email, $nomor_tlp, $password, $role, $status]);
    }
    echo "<script>window.location.href='index.php?page=manajemen_user';</script>";
    exit;
}

// 4. Ambil Data Users untuk Tabel (JOIN dengan data_sdi)
$query = "SELECT u.*, s.nama as nama_pegawai 
          FROM users u 
          LEFT JOIN data_sdi s ON u.sdi_id = s.id 
          ORDER BY u.id DESC";
$results = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// 5. Ambil data untuk Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id_edit]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="flex flex-col p-2 md:p-4">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-denim-800 p-6 rounded-2xl border border-slate-700 shadow-lg">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-indigo-500/10 rounded-lg">
                    <i class="fa-solid fa-user-gear text-indigo-400"></i>
                </div>
                Manajemen Pengguna
            </h1>
            <p class="text-sm text-slate-400 mt-1">Kelola hak akses sistem dan akun pegawai</p>
        </div>
        <button onclick="toggleModal('modalUser')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg font-bold">
            <i class="fa-solid fa-user-plus"></i> Buat User Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-denim-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700 text-cyan-400 text-[11px] uppercase tracking-widest font-black">
                        <th class="px-6 py-4">User & Email</th>
                        <th class="px-6 py-4">Relasi Pegawai</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Login Terakhir</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-slate-300 italic">
                    <?php foreach ($results as $row): ?>
                        <tr class="hover:bg-slate-700/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white not-italic"><?= htmlspecialchars($row['username']) ?></div>
                                <div class="text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($row['email']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?= $row['nama_pegawai'] ? '<span class="text-indigo-300 font-medium">'.$row['nama_pegawai'].'</span>' : '<span class="text-slate-600">- Tanpa Profil -</span>' ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-900 border border-slate-700 rounded text-[10px] font-bold text-cyan-500 uppercase">
                                    <?= $row['role'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="<?= $row['status'] == 'Active' ? 'text-emerald-500' : 'text-rose-500' ?> text-xs font-bold">
                                    ● <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[11px] text-slate-500 font-mono">
                                <?= $row['last_login'] ? date('d/m/y H:i', strtotime($row['last_login'])) : 'Belum pernah' ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="?page=manajemen_user&edit=<?= $row['id'] ?>" class="p-2 hover:text-indigo-400 transition-colors"><i class="fa-solid fa-edit"></i></a>
                                    <a href="?page=manajemen_user&delete=<?= $row['id'] ?>" onclick="return confirm('Hapus user ini?')" class="p-2 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash-can"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM -->
<div id="modalUser" class="<?= isset($edit_data) ? 'flex' : 'hidden' ?> fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-denim-800 border border-slate-700 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white uppercase italic tracking-tighter">
                <?= isset($edit_data) ? 'Edit Pengguna' : 'Buat Pengguna Baru' ?>
            </h3>
            <button onclick="window.location.href='?page=manajemen_user'" class="text-slate-500 hover:text-white"><i class="fa-solid fa-xmark text-2xl"></i></button>
        </div>

        <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Pilih Pegawai (Relasi Profil)</label>
                <select name="sdi_id" class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">-- Bukan Pegawai (NULL) --</option>
                    <?php foreach ($list_sdi as $sdi): ?>
                        <option value="<?= $sdi['id'] ?>" <?= (isset($edit_data) && $edit_data['sdi_id'] == $sdi['id']) ? 'selected' : '' ?>><?= $sdi['nama'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Username</label>
                <input type="text" name="username" value="<?= $edit_data['username'] ?? '' ?>" required class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Email</label>
                <input type="email" name="email" value="<?= $edit_data['email'] ?? '' ?>" required class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">No. Telepon</label>
                <input type="text" name="nomor_tlp" value="<?= $edit_data['nomor_tlp'] ?? '' ?>" class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-black text-rose-500 uppercase tracking-widest mb-2 font-bold">Password <?= isset($edit_data) ? '(Isi jika ingin ganti)' : '' ?></label>
                <input type="password" name="password" <?= isset($edit_data) ? '' : 'required' ?> class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="••••••••">
            </div>

            <div>
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Role Akses</label>
                <select name="role" required class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 transition">
                    <?php $roles = ['superadmin', 'admin_sdi', 'ketua halaqah', 'kepala unit', 'sdi']; 
                    foreach($roles as $r): ?>
                        <option value="<?= $r ?>" <?= (isset($edit_data) && $edit_data['role'] == $r) ? 'selected' : '' ?>><?= strtoupper($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Status Akun</label>
                <select name="status" required class="w-full bg-denim-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="Active" <?= (isset($edit_data) && $edit_data['status'] == 'Active') ? 'selected' : '' ?>>AKTIF</option>
                    <option value="Inactive" <?= (isset($edit_data) && $edit_data['status'] == 'Inactive') ? 'selected' : '' ?>>NON-AKTIF</option>
                </select>
            </div>

            <div class="md:col-span-2 mt-6 flex gap-4">
                <button type="button" onclick="window.location.href='?page=manajemen_user'" class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase tracking-widest">Batal</button>
                <button type="submit" class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl active:scale-95 transition-all">Simpan Akun</button>
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