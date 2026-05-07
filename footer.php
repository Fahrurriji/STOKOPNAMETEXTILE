</main> <!-- Penutup .page-content -->
</div> <!-- Penutup .main-content -->
</div> <!-- Penutup .app-wrapper -->

<!-- FOOTER INFO (Optional) -->
<footer class="text-center" style="padding:20px;">
    &copy; <?= date('Y') ?> <strong>Textile Gudang</strong> - System Stock Opname
</footer>

<script>
// =====================================================
// GLOBAL SCRIPTS
// =====================================================

// 1. Page loader - Menggunakan class 'hidden' sesuai script kamu
window.addEventListener('load', function() {
    setTimeout(() => {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.style.opacity = '0'; // Transisi halus
            setTimeout(() => loader.remove(), 500);
        }
    }, 400);
});

// 2. Sidebar toggle (mobile) 
// SINKRONISASI: CSS kamu mungkin menggunakan class 'active' atau 'open'. 
// Saya sesuaikan agar konsisten dengan class 'open' di script kamu.
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

// 3. Close sidebar on outside click
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// 4. Auto-dismiss alerts (Sangat berguna untuk notifikasi sukses/gagal)
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'all .5s ease';
        a.style.opacity = '0';
        a.style.transform = 'translateY(-10px)';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);

// 5. Confirm delete
function confirmDelete(url, nama) {
    if (confirm('Hapus "' + nama + '"?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = url;
    }
}

// 6. Number format (Hanya angka)
function formatRupiah(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    input.value = val;
}

// 7. Toggle password visibility (Untuk halaman Kelola Pengguna atau Profile)
function togglePassword(id) {
    const input = document.getElementById(id);
    // Mencari icon di dalam button yang memanggil fungsi ini
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.className = 'fas fa-eye-slash'; }
    } else {
        input.type = 'password';
        if (icon) { icon.className = 'fas fa-eye'; }
    }
}

// 8. Live Search Table (Sangat efisien untuk mencari barang/pemasok tanpa reload)
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? '' : 'none';
    }
}

// 9. Modal helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
        // Tambahkan sedikit animasi jika ada di CSS
        modal.classList.add('fade-in'); 
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

// Close modal on overlay click
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}
</script>
</body>
</html>