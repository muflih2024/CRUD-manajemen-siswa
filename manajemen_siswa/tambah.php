<?php
include 'koneksi.php';

/* Aktifkan Error Reporting */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $kelas = $_POST['kelas'];
    $alamat = $_POST['alamat'];

        /* Validasi dan upload foto */

    if (empty($nama) || empty($nis) || empty($kelas) || empty($alamat)) {
        echo "Semua field harus diisi!";


    } else {
        /* Periksa apakah file diunggah */

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            /* Tangani unggahan file */

            $target_dir = "uploads/"; // Direktori tempat file akan diunggah (relatif)
            $nama_file = basename($_FILES["foto"]["name"]); // Ambil nama file
            $target_file = $target_dir . $nama_file; // Path lengkap ke file yang akan diunggah

            /* Validasi format file */

            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                echo "Hanya format JPG, JPEG, dan PNG yang diperbolehkan.";
                exit;
            }

            // Coba unggah file
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                /* File berhasil diunggah */

                echo "File berhasil diunggah ke " . htmlspecialchars($target_file);
            } else {
                /* File gagal diunggah */

                echo "Maaf, terjadi error saat mengunggah file.";
                exit;
            }
        } else {
            echo "Error uploading file.";
            exit;
        }


        /* Prepared statement untuk memasukkan data */


$query = "INSERT INTO tbl_siswa (nama, nis, kelas, alamat, foto) VALUES (?, ?, ?, ?, ?)"; 

        $stmt = mysqli_prepare($koneksi, $query);

        /* Periksa apakah prepared statement berhasil dibuat */

        if ($stmt === false) {
            die("Error preparing statement: " . mysqli_error($koneksi));
        }

        // Bind parameters
mysqli_stmt_bind_param($stmt, "sssss", $nama, $nis, $kelas, $alamat, $nama_file); 


        /* Eksekusi statement */

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
            exit;
        } else {
            echo "Error menambahkan siswa ke database: " . mysqli_error($koneksi);
        }

        /* Tutup statement */

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Tambah Siswa</h2>
    <form method="POST" enctype="multipart/form-data">

        <label>Nama:</label>
        <input type="text" name="nama" required><br>
        <label>NIS:</label>
        <input type="text" name="nis" required><br>
        <label>Kelas:</label>
        <input type="text" name="kelas" required><br>
        <label>Alamat:</label>
        <textarea name="alamat" required></textarea><br>
        <label>Foto:</label>
        <input type="file" name="foto" accept="image/jpeg, image/png, image/jpg" required><br><br>
        <button type="submit" name="submit">Simpan</button>
        <h1>/</h1>
        <a href="index.php">kembali</a>

    </form>
</body>
</html>
