<main class="flex-1 flex flex-col h-screen overflow-hidden relative w-full">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-neon-indigo/10 blur-[120px] rounded-full pointer-events-none"></div>

    <header class="h-20 bg-denim-900/80 backdrop-blur-md border-b border-slate-700/50 flex items-center justify-between px-4 md:px-8 z-10 sticky top-0 shrink-0">
        <div class="flex items-center gap-4 md:gap-6">
            <button id="toggle-sidebar" class="text-slate-400 hover:text-white bg-denim-800 hover:bg-slate-700 border border-slate-600 w-10 h-10 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div>
                <h1 class="text-xl md:text-2xl font-bold text-white tracking-tight uppercase">Overview <span class="text-neon-blue">SDI</span></h1>
                <p class="text-[10px] md:text-xs text-slate-400 hidden sm:block uppercase tracking-widest font-medium"><?php echo date('l, d F Y'); ?></p>
            </div>
        </div>

        <div class="flex items-center gap-4 md:gap-6">
            <!-- Kolom Cari -->
            <div class="relative hidden lg:block">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                <input type="text" placeholder="Cari pegawai..." class="bg-denim-950/50 border border-slate-700 text-white text-sm rounded-xl pl-10 pr-4 py-2 focus:outline-none focus:border-neon-blue focus:ring-1 focus:ring-neon-blue transition-all w-64 placeholder-slate-600">
            </div>

            <!-- Notifikasi -->
            <button class="relative text-slate-400 hover:text-white transition-colors p-2 bg-slate-800/30 rounded-lg">
                <i class="fa-regular fa-bell text-lg"></i>
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-denim-900"></span>
            </button>

            <!-- Profil & Logout Container -->
            <div class="flex items-center gap-3 sm:border-l sm:border-slate-700 sm:pl-6">
                <!-- Data Profil (Dinamis dari Session) -->
                <div class="flex items-center gap-3 cursor-pointer group">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_user']) ?>&background=6366f1&color=fff" alt="Admin" class="w-9 h-9 rounded-xl ring-2 ring-slate-700 group-hover:ring-neon-blue transition-all shrink-0">
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold text-white leading-none"><?= $_SESSION['nama_user'] ?></p>
                        <p class="text-[10px] text-neon-blue mt-1 uppercase font-black tracking-tighter italic"><?= $_SESSION['role'] ?></p>
                    </div>
                </div>

                <!-- Tombol Logout -->
                <a href="logout.php"
                    onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
                    class="ml-2 flex items-center justify-center w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white border border-rose-500/20 transition-all shadow-lg shadow-rose-900/10"
                    title="Keluar dari Sistem">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </a>
            </div>
        </div>
    </header>