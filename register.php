<?php
session_start();
require_once 'config/database.php';

// Ambil semua pegawai yang belum punya akun untuk opsi dropdown
$list_sdi = $conn->query("SELECT id, nama FROM data_sdi WHERE id NOT IN (SELECT sdi_id FROM users WHERE sdi_id IS NOT NULL) ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Header tetap sama seperti kode Anda sebelumnya -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | Sumber Daya Insani</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        denim: {
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617'
                        },
                        neon: {
                            blue: '#38bdf8',
                            indigo: '#6366f1'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Tambahkan jQuery dan Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* 1. Reset Select2 Container agar sesuai tinggi input lain */
        .select2-container--default .select2-selection--single {
            background-color: #020617 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            height: 50px !important;
            /* Samakan dengan h-12 atau py-3 */
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        /* 2. Warna Teks Render */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
            /* slate-50 */
            padding-left: 15px !important;
            font-size: 0.875rem;
        }

        /* 3. Menghilangkan panah bawaan agar lebih bersih (opsional) */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 10px !important;
        }

        /* 4. Dropdown Styling */
        .select2-dropdown {
            background-color: #0f172a !important;
            /* denim-900 */
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
            z-index: 9999;
        }

        /* 5. Kotak Pencarian di dalam Dropdown (PENTING) */
        .select2-container--open .select2-search--dropdown .select2-search__field {
            background-color: #020617 !important;
            border: 1px solid #6366f1 !important;
            /* neon-indigo */
            border-radius: 0.5rem !important;
            color: white !important;
            padding: 8px 12px !important;
            outline: none !important;
        }

        /* 6. Hasil Pencarian */
        .select2-results__option {
            padding: 10px 15px !important;
            font-size: 0.875rem;
            color: #94a3b8 !important;
        }

        .select2-results__option--highlighted[aria-selected] {
            background-color: #6366f1 !important;
            /* neon-indigo */
            color: white !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #1e293b !important;
        }
    </style>
</head>

<body class="bg-denim-950 text-slate-300 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden">

    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-neon-indigo/5 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-neon-blue/5 rounded-full blur-[120px]"></div>

    <div class="w-full max-w-2xl z-10 animate-fade-up">
        <div class="flex items-center justify-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-neon-indigo to-neon-blue flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-mosque text-white text-xl"></i>
            </div>
            <div class="text-left">
                <h1 class="text-xl font-black text-white uppercase tracking-tighter leading-none">Sumber Daya <span class="text-neon-blue">Insani</span></h1>
                <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold mt-1">Yayasan Ponpes Hidayatullah Balikpapan</p>
            </div>
        </div>

        <div class="glass-effect rounded-[2.5rem] p-8 shadow-2xl">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white italic">Registrasi Pegawai</h2>
                <p class="text-slate-400 text-sm">Pilih nama Anda untuk menghubungkan data profil otomatis.</p>
            </div>

            <form action="auth/proses_register.php" method="POST" id="registerForm" class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Kolom Nama Lengkap dengan Select2 -->
                <div class="md:col-span-2 space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <div class="relative custom-select2-container">
                        <select name="nama_sdi" id="nama_sdi" required>
                            <option value="">-- Ketik atau Pilih Nama Anda --</option>
                            <?php foreach ($list_sdi as $sdi): ?>
                                <!-- Kita simpan ID sebagai value -->
                                <option value="<?= $sdi['id'] ?>"><?= htmlspecialchars($sdi['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="text-[9px] text-slate-500 mt-1 italic">* Jika nama Anda tidak muncul di pilihan, silakan ketik nama lengkap Anda lalu tekan Enter.</p>
                </div>

                <!-- Username -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]"></i>
                        <input type="text" name="username" required
                            class="w-full bg-denim-950 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-neon-indigo outline-none transition-all placeholder:text-slate-800"
                            placeholder="username">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Email Resmi</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]"></i>
                        <input type="email" name="email" required
                            class="w-full bg-denim-950 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-neon-indigo outline-none transition-all placeholder:text-slate-800"
                            placeholder="nama@mail.com">
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Nomor WhatsApp</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]"></i>
                        <input type="text" name="nomor_tlp" required
                            class="w-full bg-denim-950 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-neon-indigo outline-none transition-all placeholder:text-slate-800"
                            placeholder="081234xxx">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]"></i>
                        <input type="password" name="password" id="password" required
                            class="w-full bg-denim-950 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-neon-indigo outline-none transition-all placeholder:text-slate-800"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-neon-blue uppercase tracking-widest ml-1">Konfirmasi Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]"></i>
                        <input type="password" name="confirm_password" id="confirm_password" required
                            class="w-full bg-denim-950 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-neon-indigo outline-none transition-all placeholder:text-slate-800"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-2 pt-6">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-neon-indigo to-neon-blue text-white font-black text-xs uppercase tracking-[0.2em] py-4 rounded-2xl shadow-xl hover:shadow-neon-indigo/20 transform active:scale-[0.98] transition-all">
                        Daftar Akun Sekarang <i class="fa-solid fa-paper-plane ml-2"></i>
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center pt-6 border-t border-slate-800/50">
                <p class="text-xs text-slate-500">Sudah memiliki akun? <a href="login.php" class="text-neon-blue font-bold hover:text-neon-indigo transition-colors ml-1 uppercase tracking-wider">Masuk di sini</a></p>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        const $namaSdi = $('#nama_sdi').select2({
            tags: true, 
            placeholder: "Ketik atau Pilih Nama Anda",
            allowClear: true,
            width: '100%',
            // minimumInputLength dihapus agar UX lebih instan
        });

        // TRICK: Langsung Fokus ke Input saat Dropdown Terbuka
        $(document).on('select2:open', () => {
            // Memberikan delay sedikit agar DOM benar-benar siap
            setTimeout(() => {
                const searchField = document.querySelector('.select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 10);
        });
    });
</script>

</body>

</html>