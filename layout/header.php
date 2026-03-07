<?php 
// Memuat koneksi database dari folder config
require_once 'config/database.php'; 

// Opsional: Jika Anda ingin memastikan variabel koneksi tersedia (misal nama variabelnya $conn)
if (!$conn) {
    die("Koneksi gagal: variabel database tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SDI - Yayasan Ponpes Hidayatullah Balikpapan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
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
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Mencegah icon loncat saat transisi width */
        .sidebar-link-icon {
            min-width: 1.5rem;
            display: flex;
            justify-content: center;
        }
        
        /* Animasi halu untuk card */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="bg-denim-950 text-slate-300 font-sans h-screen flex overflow-hidden selection:bg-neon-blue selection:text-white relative">

    <div id="mobile-overlay" class="fixed inset-0 bg-denim-900/80 backdrop-blur-sm z-30 hidden transition-opacity lg:hidden"></div>