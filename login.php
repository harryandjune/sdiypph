<?php
session_start();

// Jika user sudah login, langsung lempar ke dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php?page=dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sumber Daya Insani - Hidayatullah Balikpapan</title>
    <!-- Tailwind & Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        denim: {
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
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
    <style>
        .glass-effect {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-button:hover {
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .animate-shake { animation: shake 0.2s ease-in-out 0s 2; }
    </style>
</head>

<body class="bg-denim-950 flex items-center justify-center min-h-screen p-4 relative overflow-hidden">
    
    <!-- Dekorasi Cahaya Latar -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-neon-indigo/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-neon-blue/10 rounded-full blur-[120px]"></div>

    <div class="w-full max-w-md z-10">
        <!-- Logo & Judul Branding -->
        <div class="flex items-center gap-4 mb-10 px-2 justify-center md:justify-start">
            <!-- Logo Box -->
            <div class="inline-flex shrink-0 items-center justify-center w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-neon-indigo to-neon-blue shadow-lg">
                <i class="fa-solid fa-mosque text-white text-2xl md:text-3xl"></i>
            </div>
            <!-- Text Branding -->
            <div class="flex flex-col">
                <h1 class="text-xl md:text-2xl font-black text-white leading-tight tracking-tighter uppercase">
                    Sumber Daya <span class="text-neon-blue">Insani</span>
                </h1>
                <p class="text-slate-500 font-bold tracking-tight text-[10px] md:text-xs uppercase">
                    Yayasan Ponpes Hidayatullah Balikpapan
                </p>
            </div>
        </div>

        <!-- Card Login -->
        <div class="glass-effect rounded-[2.5rem] p-8 md:p-10 shadow-2xl relative overflow-hidden border-t border-white/10">
            <h2 class="text-xl font-bold text-white mb-2">Selamat Datang</h2>
            <p class="text-slate-400 text-sm mb-8 italic text-balance">Silakan masuk untuk mengelola data operasional.</p>

            <!-- Notifikasi Error -->
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center gap-3 animate-shake">
                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                    <p class="text-xs text-rose-500 font-semibold italic">
                        <?php 
                            if($_GET['error'] == 'failed') echo "Username atau Password salah!";
                            elseif($_GET['error'] == 'status') echo "Akun Anda dinonaktifkan. Hubungi Admin.";
                            else echo "Terjadi kesalahan sistem.";
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <form action="auth/proses_login.php" method="POST" class="space-y-6">
                <!-- Input Username/Email -->
                <div>
                    <label class="block text-[10px] font-black text-neon-blue uppercase tracking-[0.2em] mb-2 ml-1">Username / Email</label>
                    <div class="relative group">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-neon-blue transition-colors"></i>
                        <input type="text" name="identity" required
                            class="w-full bg-denim-950/50 border border-slate-700 rounded-2xl pl-12 pr-4 py-4 text-white focus:ring-2 focus:ring-neon-blue focus:border-transparent outline-none transition-all placeholder:text-slate-700"
                            placeholder="username atau email">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <label class="block text-[10px] font-black text-neon-blue uppercase tracking-[0.2em] mb-2 ml-1">Kata Sandi</label>
                    <div class="relative group">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-neon-blue transition-colors"></i>
                        <input type="password" id="passwordField" name="password" required
                            class="w-full bg-denim-950/50 border border-slate-700 rounded-2xl pl-12 pr-12 py-4 text-white focus:ring-2 focus:ring-neon-blue focus:border-transparent outline-none transition-all placeholder:text-slate-700"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-600 hover:text-white transition-colors focus:outline-none">
                            <i id="eyeIcon" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Login -->
                <button type="submit" 
                    class="glow-button w-full bg-gradient-to-r from-neon-indigo to-neon-blue text-white font-black text-xs uppercase tracking-[0.3em] py-5 rounded-2xl shadow-xl transform active:scale-[0.98] transition-all">
                    Masuk Ke Sistem <i class="fa-solid fa-arrow-right-long ml-2"></i>
                </button>
            </form>

            <!-- Link ke Halaman Register -->
            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <p class="text-xs text-slate-500 font-medium">
                    Belum memiliki akun? 
                    <a href="register.php" class="text-neon-blue font-bold hover:text-neon-indigo transition-colors ml-1 uppercase tracking-wider">
                        Daftar Sekarang
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-10 flex flex-col items-center">
            <p class="text-slate-600 text-[10px] font-bold uppercase tracking-widest text-center">
                &copy; <?= date('Y') ?> Ponpes Hidayatullah Balikpapan
            </p>
            <div class="mt-2 px-3 py-1 bg-denim-900 border border-slate-800 rounded-full">
                <span class="text-[9px] text-slate-500 font-mono italic">Build v1.1.0 - Digital SDI</span>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('eyeIcon');
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                field.type = "password";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>

</html>