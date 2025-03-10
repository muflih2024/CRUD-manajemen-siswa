<?php
include 'koneksi.php';

// Inisialisasi variabel pesan dan error
$pesan = "";
$error = "";

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $siswa_id = isset($_POST['siswa_id']) ? trim($_POST['siswa_id']) : ''; // Tambahkan trim() untuk menghapus whitespace
    $tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';    // Tambahkan trim() untuk menghapus whitespace
    $status_kehadiran = isset($_POST['status_kehadiran']) ? trim($_POST['status_kehadiran']) : ''; // Tambahkan trim() untuk menghapus whitespace
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : ''; // Tambahkan trim() untuk menghapus whitespace


    // Validasi data
    if (empty($siswa_id) || !is_numeric($siswa_id)) {
        $error = "ID Siswa harus diisi dan berupa angka.";
    } elseif (empty($tanggal)) {
        $error = "Tanggal harus diisi.";
    } elseif (empty($status_kehadiran)) {
        $error = "Status Kehadiran harus dipilih.";
    } else {
        // Gunakan prepared statement untuk mencegah SQL injection
        $query = "INSERT INTO tbl_presensi (siswa_id, tanggal, status_kehadiran, keterangan) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            // Bind parameter ke prepared statement
            mysqli_stmt_bind_param($stmt, "isss", $siswa_id, $tanggal, $status_kehadiran, $keterangan);

            // Eksekusi prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $pesan = "Data presensi berhasil disimpan.";
            } else {
                $error = "Terjadi kesalahan saat menyimpan data: " . mysqli_error($koneksi);
            }

            // Tutup statement
            mysqli_stmt_close($stmt);
        } else {
            $error = "Terjadi kesalahan dalam persiapan statement: " . mysqli_error($koneksi);
        }
    }
}

// Ambil daftar siswa dari database untuk ditampilkan di dropdown
$query_siswa = "SELECT id, nama FROM tbl_siswa ORDER BY nama ASC"; // Urutkan berdasarkan nama
$result_siswa = mysqli_query($koneksi, $query_siswa);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Presensi Siswa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Agar padding tidak mempengaruhi lebar total */
        }

        textarea {
            resize: vertical; /* Biarkan pengguna menyesuaikan tinggi textarea */
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #3e8e41;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Form Presensi Siswa</h2>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($pesan); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="siswa_id">Nama Siswa:</label>
            <select name="siswa_id" id="siswa_id">
                <option value="">Pilih Siswa</option> <!-- Opsi default -->
                <?php
                if (mysqli_num_rows($result_siswa) > 0) {
                    while ($row_siswa = mysqli_fetch_assoc($result_siswa)) {
                        echo '<option value="' . htmlspecialchars($row_siswa['id']) . '">' . htmlspecialchars($row_siswa['nama']) . '</option>';
                    }
                } else {
                    echo '<option value="">Tidak ada siswa</option>';
                }
                ?>
            </select><br><br>

            <label for="tanggal">Tanggal:</label>
            <input type="date" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>"><br><br>

            <label for="status_kehadiran">Status Kehadiran:</label>
            <select name="status_kehadiran" id="status_kehadiran">
                <option value="">Pilih Status</option> <!-- Opsi default -->
                <option value="Hadir">ada</option>
                <option value="Sakit">S</option>
                <option value="Izin">I</option>
                <option value="Alpa">A</option>
            </select><br><br>

            <label for="keterangan">Keterangan:</label><br>
            <textarea id="keterangan" name="keterangan" rows="4" cols="50"></textarea><br><br>

            <button type="submit">Simpan Presensi</button>
        </form>

        <p><a href="index.php">Kembali ke Daftar Siswa</a></p>
    </div>
</body>
</html>