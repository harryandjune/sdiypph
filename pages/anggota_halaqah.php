<?php
// --- 1. LOGIKA UPDATE INLINE (AJAX HANDLER) ---
if (isset($_POST['ajax_update_inline'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id = $_POST['id'];
    $halaqah_id = !empty($_POST['halaqah_id']) ? $_POST['halaqah_id'] : null;
    $peran = $_POST['peran'];

    try {
        $sql = "UPDATE anggota_halaqah SET halaqah_id = ?, peran = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$halaqah_id, $peran, $id]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- 2. LOGIKA SINKRONISASI OTOMATIS ---
$sqlSync = "INSERT INTO anggota_halaqah (sdi_id, halaqah_id, peran, tanggal_bergabung)
            SELECT id, NULL, 'Anggota', CURDATE()
            FROM data_sdi
            WHERE id NOT IN (SELECT sdi_id FROM anggota_halaqah)";
$conn->exec($sqlSync);

// --- 3. LOGIKA PENCARIAN & PAGINASI ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_sql = "";
$params = [];
if ($search != '') {
    $where_sql = " WHERE s.nama LIKE ? OR h.nama_halaqah LIKE ? ";
    $params = ["%$search%", "%$search%"];
}

$limit = 10;
$page_active = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_active - 1) * $limit;

$count_query = "SELECT COUNT(*) FROM anggota_halaqah ah JOIN data_sdi s ON ah.sdi_id = s.id LEFT JOIN halaqah h ON ah.halaqah_id = h.id $where_sql";
$stmt_count = $conn->prepare($count_query);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = ceil($total_data / $limit);

// --- 4. LOGIKA CRUD LAINNYA ---
if (isset($_POST['bulk_add'])) {
    $halaqah_id = $_POST['halaqah_id'];
    $sdi_ids = $_POST['sdi_ids'] ?? [];
    if (!empty($sdi_ids) && !empty($halaqah_id)) {
        $in  = str_repeat('?,', count($sdi_ids) - 1) . '?';
        $sql = "UPDATE anggota_halaqah SET halaqah_id = ? WHERE sdi_id IN ($in)";
        $stmt = $conn->prepare($sql);
        $params_bulk = array_merge([$halaqah_id], $sdi_ids);
        $stmt->execute($params_bulk);
        header("Location: index.php?page=anggota_halaqah&p=$page_active&search=$search");
        exit;
    }
}

if (isset($_GET['reset_group'])) {
    $id = $_GET['reset_group'];
    $conn->prepare("UPDATE anggota_halaqah SET halaqah_id = NULL WHERE id = ?")->execute([$id]);
    header("Location: index.php?page=anggota_halaqah&p=$page_active&search=$search");
    exit;
}

// --- 5. AMBIL DATA LIST ---
$query = "SELECT ah.*, h.nama_halaqah, s.nama as nama_sdi 
          FROM anggota_halaqah ah
          LEFT JOIN halaqah h ON ah.halaqah_id = h.id
          JOIN data_sdi s ON ah.sdi_id = s.id
          $where_sql
          ORDER BY (ah.halaqah_id IS NULL) DESC, h.id DESC, s.nama ASC
          LIMIT $limit OFFSET $offset";
$stmt_list = $conn->prepare($query);
$stmt_list->execute($params);
$results = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

$list_halaqah = $conn->query("SELECT id, nama_halaqah FROM halaqah ORDER BY nama_halaqah ASC")->fetchAll(PDO::FETCH_ASSOC);
$list_tersedia = $conn->query("SELECT s.id, s.nama FROM data_sdi s JOIN anggota_halaqah ah ON s.id = ah.sdi_id WHERE ah.halaqah_id IS NULL ORDER BY s.nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col p-2 md:p-4">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-denim-800 p-6 rounded-2xl border border-slate-700 shadow-lg">
        <div>
            <h1 class="text-2xl font-bold text-white italic"><i class="fa-solid fa-bolt text-neon-blue mr-2"></i> Anggota Halaqah</h1>
            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Klik pada kolom Kelompok/Peran untuk mengedit</p>
        </div>

        <div class="flex flex-col md:flex-row gap-3">
            <form action="" method="GET" class="relative group">
                <input type="hidden" name="page" value="anggota_halaqah">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama..." class="w-full md:w-64 bg-denim-900 border border-slate-600 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-neon-blue outline-none transition-all">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-500 group-focus-within:text-neon-blue transition-colors text-xs"></i>
            </form>
            <button onclick="toggleModal('modalBulk')" class="bg-neon-indigo text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 font-black uppercase text-[10px]">
                <i class="fa-solid fa-layer-group"></i> Input Massal
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-denim-800 rounded-3xl border border-slate-700 shadow-2xl overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700 text-neon-blue text-[10px] uppercase tracking-[0.2em] font-black">
                        <th class="px-6 py-5">Kelompok Halaqah</th>
                        <th class="px-6 py-5">Nama Anggota</th>
                        <th class="px-6 py-5">Peran</th>
                        <th class="px-6 py-5 text-center w-20">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-slate-300">
                    <?php foreach ($results as $row): ?>
                        <tr id="row-<?= $row['id'] ?>" class="hover:bg-slate-700/10 transition-all group">
                            <!-- Kolom Kelompok (Clickable) -->
                            <td class="px-6 py-4 cursor-pointer hover:bg-neon-blue/5 transition-colors" onclick="enterEditMode(<?= $row['id'] ?>)">
                                <div class="view-mode-<?= $row['id'] ?> font-bold text-white">
                                    <?= $row['nama_halaqah'] ?: '<span class="text-rose-500 text-[10px] font-black uppercase bg-rose-500/10 px-2 py-1 rounded">Pilih Kelompok</span>' ?>
                                </div>
                                <div class="edit-mode-<?= $row['id'] ?> hidden">
                                    <select onkeydown="handleEnter(event, <?= $row['id'] ?>)" class="inline-halaqah w-full bg-denim-900 border border-neon-blue rounded-lg px-2 py-1.5 text-xs text-white outline-none">
                                        <option value="">-- Tanpa Kelompok --</option>
                                        <?php foreach ($list_halaqah as $h): ?>
                                            <option value="<?= $h['id'] ?>" <?= ($row['halaqah_id'] == $h['id']) ? 'selected' : '' ?>><?= htmlspecialchars($h['nama_halaqah']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>

                            <!-- Kolom Nama (Read Only) -->
                            <td class="px-6 py-4 font-medium text-slate-400 group-hover:text-slate-200"><?= htmlspecialchars($row['nama_sdi']) ?></td>

                            <!-- Kolom Peran (Clickable) -->
                            <td class="px-6 py-4 cursor-pointer hover:bg-neon-blue/5 transition-colors" onclick="enterEditMode(<?= $row['id'] ?>)">
                                <div class="view-mode-<?= $row['id'] ?>">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-700 text-slate-400"><?= $row['peran'] ?></span>
                                </div>
                                <div class="edit-mode-<?= $row['id'] ?> hidden">
                                    <select onkeydown="handleEnter(event, <?= $row['id'] ?>)" class="inline-peran w-full bg-denim-900 border border-neon-blue rounded-lg px-2 py-1.5 text-xs text-white outline-none">
                                        <?php $roles = ['Anggota', 'Ketua', 'Sekretaris', 'Bendahara', 'Murabbi', 'Pembimbing'];
                                        foreach ($roles as $r): ?>
                                            <option value="<?= $r ?>" <?= ($row['peran'] == $r) ? 'selected' : '' ?>><?= $r ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="px-6 py-4 text-center">
                                <a href="?page=anggota_halaqah&reset_group=<?= $row['id'] ?>&p=<?= $page_active ?>&search=<?= $search ?>" 
                                   onclick="return confirm('Keluarkan dari kelompok?')" 
                                   class="text-slate-600 hover:text-rose-500 transition-colors" title="Keluarkan">
                                    <i class="fa-solid fa-user-xmark text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginasi (Sama seperti sebelumnya) -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mb-8">
            <a href="?page=anggota_halaqah&p=<?= max(1, $page_active - 1) ?>&search=<?= urlencode($search) ?>" class="p-2 bg-denim-800 border border-slate-700 rounded-lg text-slate-400 hover:text-neon-blue transition-all"><i class="fa-solid fa-chevron-left text-xs"></i></a>
            <?php for ($i = 1; $i <= $total_pages; $i++): 
                if($i == 1 || $i == $total_pages || ($i >= $page_active - 2 && $i <= $page_active + 2)): ?>
                <a href="?page=anggota_halaqah&p=<?= $i ?>&search=<?= urlencode($search) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg font-bold text-[10px] <?= $i == $page_active ? 'bg-neon-blue text-denim-900 shadow-[0_0_15px_rgba(56,189,248,0.4)]' : 'bg-denim-800 border border-slate-700 text-slate-400 hover:border-neon-blue' ?>"><?= $i ?></a>
            <?php endif; endfor; ?>
            <a href="?page=anggota_halaqah&p=<?= min($total_pages, $page_active + 1) ?>&search=<?= urlencode($search) ?>" class="p-2 bg-denim-800 border border-slate-700 rounded-lg text-slate-400 hover:text-neon-blue transition-all"><i class="fa-solid fa-chevron-right text-xs"></i></a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Bulk Add (Dipertahankan untuk kenyamanan) -->
<!-- ... (Bagian Modal Bulk Add tetap sama seperti sebelumnya) ... -->

<script>
    let activeEditId = null;

    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
        document.getElementById(id).classList.toggle('flex');
    }

    // Masuk ke Mode Edit
    function enterEditMode(id) {
        // Jika sedang mengedit baris lain, simpan dulu yang lama
        if (activeEditId !== null && activeEditId !== id) {
            saveInlineEdit(activeEditId);
        }

        activeEditId = id;
        document.getElementById('row-' + id).classList.add('bg-indigo-500/10', 'border-y', 'border-indigo-500/30');
        document.querySelectorAll('.view-mode-' + id).forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.edit-mode-' + id).forEach(el => el.classList.remove('hidden'));
    }

    // Deteksi tombol Enter
    function handleEnter(event, id) {
        if (event.key === "Enter") {
            saveInlineEdit(id);
        }
    }

    // Simpan Data
    function saveInlineEdit(id) {
        if (activeEditId === null) return;

        const row = document.getElementById('row-' + id);
        const halaqah_id = row.querySelector('.inline-halaqah').value;
        const peran = row.querySelector('.inline-peran').value;

        const formData = new FormData();
        formData.append('ajax_update_inline', '1');
        formData.append('id', id);
        formData.append('halaqah_id', halaqah_id);
        formData.append('peran', peran);

        fetch('index.php' + window.location.search, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Sembunyikan mode edit & refresh halaman untuk update tampilan teks JOIN
                // Kita refresh agar Nama Halaqah yang baru muncul menggantikan ID
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        });
        
        activeEditId = null;
    }

    // Klik di luar baris untuk simpan otomatis
    document.addEventListener('click', function(event) {
        if (activeEditId !== null) {
            const activeRow = document.getElementById('row-' + activeEditId);
            // Jika yang diklik bukan bagian dari baris yang sedang diedit
            if (!activeRow.contains(event.target)) {
                saveInlineEdit(activeEditId);
            }
        }
    }, true);

    // Modal Search
    function filterSDI() {
        let input = document.getElementById('searchSDIModal').value.toUpperCase();
        let items = document.getElementsByClassName('sdi-item');
        for (let i = 0; i < items.length; i++) {
            let name = items[i].querySelector('span').innerText;
            items[i].style.display = name.toUpperCase().includes(input) ? "" : "none";
        }
    }
</script>