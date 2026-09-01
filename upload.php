<?php
require 'config.php';
require 'partials/auth_check.php';

$sql = "SELECT id, nomor_perkara, judul_perkara FROM perkara ORDER BY tanggal_dibuat DESC";
$result = mysqli_query($conn, $sql);

// Kalau baru saja nambah perkara, perkara itu langsung dipilih otomatis di dropdown
$perkara_terpilih = isset($_GET['perkara_id']) ? (int) $_GET['perkara_id'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Dokumen — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<div class="page-shell">
    <div class="page-head">
        <div>
            <h2 class="serif">Upload Dokumen Perkara</h2>
            <p>Tambahkan berkas baru ke perkara yang sesuai</p>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'gagal'): ?>
            <div class="flash gagal">Upload gagal. Pastikan file berformat PDF dan semua kolom terisi, lalu coba lagi.</div>
        <?php elseif ($_GET['status'] === 'sebagian_gagal'): ?>
            <div class="flash gagal">Sebagian file berhasil diupload, tapi ada juga yang gagal (kemungkinan bukan format PDF). Cek kembali daftar berkas.</div>
        <?php elseif ($_GET['status'] === 'perkara_sukses'): ?>
            <div class="flash sukses">Perkara baru berhasil ditambahkan. Sekarang lanjutkan unggah dokumennya di bawah ini.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <form action="proses_upload.php" method="POST" enctype="multipart/form-data">

            <label for="perkara_id">Pilih Perkara</label>
            <select name="perkara_id" id="perkara_id" required>
                <option value="">-- Pilih Perkara --</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $perkara_terpilih) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nomor_perkara']) ?> - <?= htmlspecialchars($row['judul_perkara']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <p class="field-hint">Perkara belum ada di daftar? <a href="tambah_perkara.php">Tambah perkara baru</a></p>

            <label for="jenis_dokumen">Jenis Dokumen</label>
            <input type="text" name="jenis_dokumen" id="jenis_dokumen" placeholder="Contoh: BAP, Surat Dakwaan" required>

            <label>Pilih File (PDF)</label>
            <div class="file-picker">
                <button type="button" id="btn_tambah_file" class="btn btn-secondary btn-sm">+ Tambah File</button>
                <span id="file_kosong_note" class="field-hint" style="margin:0 0 0 10px;">Belum ada file dipilih</span>
            </div>
            <ul id="daftar_file_terpilih" class="file-list"></ul>
            <input type="file" id="file_dokumen_hidden" accept="application/pdf" multiple style="display:none;">
            <p class="field-hint">Klik "+ Tambah File" berkali-kali untuk memilih file satu per satu, atau tahan Ctrl untuk pilih beberapa file sekaligus. Semua file akan tercatat sebagai jenis dokumen yang sama di atas.</p>

            <div style="margin-top:24px;">
                <button type="submit" id="btn_submit_upload" class="btn btn-primary">Upload Dokumen</button>
            </div>
        </form>
    </div>
</div>

<footer class="pidsus-footer">
    <strong>Kejaksaan Negeri Kota Kediri</strong> — Bidang Tindak Pidana Khusus<br>
    Sistem Arsip Berkas Digital
</footer>

<script>
    // ---- Fitur pilih file bertahap (satu-satu atau sekaligus) ----
    const inputAsli = document.getElementById('file_dokumen_hidden');
    const btnTambah = document.getElementById('btn_tambah_file');
    const daftarEl = document.getElementById('daftar_file_terpilih');
    const kosongNote = document.getElementById('file_kosong_note');
    const form = document.querySelector('form[action="proses_upload.php"]');
    let fileTerpilih = [];

    btnTambah.addEventListener('click', () => inputAsli.click());

    inputAsli.addEventListener('change', () => {
        for (const file of inputAsli.files) {
            // Hindari file yang sama persis (nama + ukuran) kepilih dobel
            const sudahAda = fileTerpilih.some(f => f.name === file.name && f.size === file.size);
            if (!sudahAda) fileTerpilih.push(file);
        }
        inputAsli.value = ''; // reset biar bisa pilih file yang sama lagi kalau perlu
        renderDaftarFile();
    });

    function renderDaftarFile() {
        daftarEl.innerHTML = '';
        kosongNote.style.display = fileTerpilih.length === 0 ? 'inline' : 'none';

        fileTerpilih.forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'file-list-item';
            li.innerHTML = `
                <span class="file-list-name">${file.name}</span>
                <button type="button" class="file-list-remove" data-index="${index}">Hapus</button>
            `;
            daftarEl.appendChild(li);
        });

        // Pasang event tombol hapus per baris
        daftarEl.querySelectorAll('.file-list-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                fileTerpilih.splice(parseInt(btn.dataset.index), 1);
                renderDaftarFile();
            });
        });
    }

    // Sebelum form dikirim, satukan semua file yang sudah dikumpulkan ke input asli
    form.addEventListener('submit', (e) => {
        if (fileTerpilih.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 file PDF sebelum upload.');
            return;
        }
        const dataTransfer = new DataTransfer();
        fileTerpilih.forEach(file => dataTransfer.items.add(file));

        // Buat input file[] final yang benar-benar dikirim ke server
        const inputFinal = document.createElement('input');
        inputFinal.type = 'file';
        inputFinal.name = 'file_dokumen[]';
        inputFinal.multiple = true;
        inputFinal.style.display = 'none';
        inputFinal.files = dataTransfer.files;
        form.appendChild(inputFinal);
    });

    if (window.location.search.includes('status=')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
</body>
</html>