<?php
// Aktifkan error reporting untuk membantu debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';

$upload_error = "";
$target_dir = "uploads/";

// Coba buat direktori jika tidak ada
if (!is_dir($target_dir)) {
    if (mkdir($target_dir, 0755, true)) {
        echo "Uploads directory created successfully.<br>";
    } else {
        echo "Failed to create uploads directory. Check permissions!<br>";
        $upload_error = "Failed to create uploads directory. Check server permissions.";
    }
}

$id = $_GET['id'];

// Ambil data siswa
$query_siswa = "SELECT * FROM tbl_siswa WHERE id = ?";
$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "i", $id);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$row_siswa = mysqli_fetch_assoc($result_siswa);
mysqli_stmt_close($stmt_siswa);

if (!$row_siswa) {
    die("Siswa tidak ditemukan.");
}

// Ambil data presensi terbaru
$query_presensi = "SELECT id_presensi, tanggal, status_kehadiran, keterangan FROM tbl_presensi WHERE siswa_id = ? ORDER BY tanggal DESC LIMIT 1";
$stmt_presensi = mysqli_prepare($koneksi, $query_presensi);
mysqli_stmt_bind_param($stmt_presensi, "i", $id);
mysqli_stmt_execute($stmt_presensi);
$result_presensi = mysqli_stmt_get_result($stmt_presensi);
$row_presensi = mysqli_fetch_assoc($result_presensi);
mysqli_stmt_close($stmt_presensi);


if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $kelas = $_POST['kelas'];
    $alamat = $_POST['alamat'];

    $nama_file = $row_siswa['foto']; // Default ke foto yang ada

    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        // Debug: Periksa isi $_FILES
        echo "<pre> \$_FILES contents: ";
        var_dump($_FILES);
        echo "</pre>";

        $nama_file = basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nama_file;

        echo "Target file sebelum move_uploaded_file: " . $target_file . "<br>"; // Debugging

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $errors = array();

        // Check if image file is a actual image or fake image
        $check = @getimagesize($_FILES["foto"]["tmp_name"]);
        if ($check === false) {
            $errors['foto'] = "File is not an image.";
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $errors['foto'] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        }

        // Check file size (optional - limit to 2MB)
        if ($_FILES["foto"]["size"] > 2000000) {
            $errors['foto'] = "Sorry, your file is too large. Max 2MB.";
        }

        // Handle upload errors explicitly
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            switch ($_FILES['foto']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errors['foto'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errors['foto'] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors['foto'] = "The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    // Ini bukan error jika pengguna tidak mengunggah file baru.
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errors['foto'] = "Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errors['foto'] = "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errors['foto'] = "File upload stopped by extension.";
                    break;
                default:
                    $errors['foto'] = "Unknown upload error.";
                    break;
            }
        }

        // If there are no errors, try to upload the file
        if (empty($errors)) {
            echo "Filename before upload: " . $nama_file . "<br>"; // Debugging

            // Tambahkan pemeriksaan izin sebelum memindahkan file
            if (is_writable(dirname($target_file))) {
                echo "The target directory is writable.<br>";
            } else {
                echo "The target directory is NOT writable! Check permissions.<br>";
                $errors['foto'] = "The target directory is not writable. Check server permissions.";
            }

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                echo "File uploaded successfully: " . $nama_file . "<br>"; // Debugging
                echo "Filename after upload: " . $nama_file . "<br>";  // Debugging
                echo "Target file: " . $target_file . "<br>"; // Debugging
                if (file_exists($target_file)) {
                    echo "File exists in uploads directory.<br>";
                } else {
                    echo "File DOES NOT exist in uploads directory!<br>"; // THIS IS IMPORTANT
                }
            } else {
                $errors['foto'] = "Sorry, there was an error uploading your file.";
                echo "Upload error - target file: " . $target_file . "<br>"; // Debugging
            }
        }

        if (!empty($errors)) {
            $upload_error = implode("<br>", $errors);
        }
    }

    // Update the student data
    $query_update_siswa = "UPDATE tbl_siswa SET nama = ?, nis = ?, kelas = ?, alamat = ?, foto = ? WHERE id = ?";
    $stmt_update_siswa = mysqli_prepare($koneksi, $query_update_siswa);
    mysqli_stmt_bind_param($stmt_update_siswa, "sssssi", $nama, $nis, $kelas, $alamat, $nama_file, $id);

    // Debug: Tampilkan query dan parameter
    echo "SQL Query (siswa): " . $query_update_siswa . "<br>"; //debug
    echo "SQL Parameters (siswa): nama=$nama, nis=$nis, kelas=$kelas, alamat=$alamat, foto=$nama_file, id=$id <br>"; //debug

    // Update the presensi data
    $tanggal = $_POST['tanggal'];
    $status_kehadiran = $_POST['status_kehadiran'];
    $keterangan = $_POST['keterangan'];
    $id_presensi = $_POST['id_presensi'];  // Pastikan Anda mengirimkan ini dari form

    $query_update_presensi = "UPDATE tbl_presensi SET tanggal = ?, status_kehadiran = ?, keterangan = ? WHERE id_presensi = ?";
    $stmt_update_presensi = mysqli_prepare($koneksi, $query_update_presensi);
    mysqli_stmt_bind_param($stmt_update_presensi, "sssi", $tanggal, $status_kehadiran, $keterangan, $id_presensi);

    // Debug: Tampilkan query dan parameter
    echo "SQL Query (presensi): " . $query_update_presensi . "<br>"; //debug
    echo "SQL Parameters (presensi): tanggal=$tanggal, status_kehadiran=$status_kehadiran, keterangan=$keterangan, id_presensi=$id_presensi <br>"; //debug

    // Execute both queries
    if (mysqli_stmt_execute($stmt_update_siswa) && mysqli_stmt_execute($stmt_update_presensi)) {
        header("Location: index.php");
        exit;
    } else {
        $upload_error = "Error updating: " . mysqli_error($koneksi);
    }

    mysqli_stmt_close($stmt_update_siswa);
    mysqli_stmt_close($stmt_update_presensi);

}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h2>Edit Siswa</h2>

    <?php if (!empty($upload_error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($upload_error); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nama:</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($row_siswa['nama']); ?>" required><br>

        <label>NIS:</label>
        <input type="text" name="nis" value="<?= htmlspecialchars($row_siswa['nis']); ?>" required><br>

        <label>Kelas:</label>
        <input type="text" name="kelas" value="<?= htmlspecialchars($row_siswa['kelas']); ?>" required><br>

        <label>Alamat:</label>
        <textarea name="alamat" required><?= htmlspecialchars($row_siswa['alamat']); ?></textarea><br>

        <label>Foto:</label>
        <input type="file" name="foto" accept="image/jpeg, image/png, image/jpg"><br><br>
        <img src="uploads/<?= htmlspecialchars($row_siswa['foto']); ?>" alt="Current Photo" width="100"><br>

        <h3>Edit Presensi =</h3>  <!-- Bagian baru untuk edit presensi -->
        <?php if ($row_presensi): ?>
            <label>Tanggal:</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($row_presensi['tanggal']); ?>" required><br>

            <label>Status Kehadiran:</label>
            <select name="status_kehadiran">
                <option value="Hadir" <?= ($row_presensi['status_kehadiran'] == 'Hadir') ? 'selected' : '' ?>>Hadir</option>
                <option value="Sakit" <?= ($row_presensi['status_kehadiran'] == 'Sakit') ? 'selected' : '' ?>>Sakit</option>
                <option value="Izin" <?= ($row_presensi['status_kehadiran'] == 'Izin') ? 'selected' : '' ?>>Izin</option>
                <option value="Alpa" <?= ($row_presensi['status_kehadiran'] == 'Alpa') ? 'selected' : '' ?>>Alpa</option>
            </select><br>

            <label>Keterangan:</label>
            <textarea name="keterangan"><?= htmlspecialchars($row_presensi['keterangan']); ?></textarea><br>

            <input type="hidden" name="id_presensi" value="<?= htmlspecialchars($row_presensi['id_presensi']); ?>">  <!-- Penting untuk mengirimkan ID presensi -->
        <?php else: ?>
            <p>Belum ada presensi untuk siswa ini.</p>
        <?php endif; ?>

        <button type="submit" name="submit">Update</button>
        <h1>/</h1>
        <a href="index.php">kembali</a>
    </form>
</body>

</html>