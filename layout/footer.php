    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');

        let isCollapsed = false;

        function collapseSidebar() {
            // remove all wide-width classes including large breakpoint
            sidebar.classList.remove('w-64', 'md:w-64', 'lg:w-64');
            sidebar.classList.add('w-20', 'lg:w-20');
            // fade out text then hide so it doesn't overflow
            sidebarTexts.forEach(el => {
                el.classList.add('opacity-0');
                setTimeout(() => el.classList.add('hidden'), 300);
            });
        }

        function expandSidebar() {
            sidebar.classList.remove('w-20', 'lg:w-20');
            sidebar.classList.add('w-64', 'md:w-64', 'lg:w-64');
            sidebarTexts.forEach(el => {
                el.classList.remove('hidden');
                setTimeout(() => el.classList.remove('opacity-0'), 10);
            });
        }

        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth >= 1024) {
                // desktop toggle
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    collapseSidebar();
                } else {
                    expandSidebar();
                }
            } else {
                // mobile slide
                sidebar.classList.toggle('-translate-x-full');
                mobileOverlay.classList.toggle('hidden');
            }
        });

        // Tutup sidebar saat area kosong (overlay) di-klik pada versi mobile
        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });

        // Reset state jika layar di-resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                mobileOverlay.classList.add('hidden');
                sidebar.classList.remove('-translate-x-full');
            } else {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('hidden');
                }

                // restore to expanded/mobile default
                isCollapsed = false;
                sidebar.classList.remove('lg:w-20', 'w-20');
                sidebar.classList.add('lg:w-64', 'w-64', 'md:w-64');
                sidebarTexts.forEach(el => el.classList.remove('hidden', 'opacity-0'));
            }
        });
    </script>

    <!-- jQuery dan Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk elemen dengan class select2
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
</body>
</html>