<?php
include 'koneksi.php';

/* Ambil nomor halaman saat ini */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; /* Jumlah catatan per halaman */
$offset = ($page - 1) * $limit; /* Hitung offset untuk query SQL */

/* Ambil kata kunci pencarian */
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : ''; /* Ambil kata kunci pencarian */

/* Query untuk menghitung total siswa */
$query_total = "SELECT COUNT(*) AS total_siswa FROM tbl_siswa WHERE nama LIKE '%$keyword%'";
$result_total = mysqli_query($koneksi, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_siswa = $row_total['total_siswa'];

/* Ambil siswa dengan pagination dan pencarian */
$query = "SELECT * FROM tbl_siswa WHERE nama LIKE '%$keyword%' LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Siswa👨‍🎓👩‍🎓</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Daftar Siswa</h2>

<form action="" method="GET" style="margin-bottom: 20px;">
    <label for="keyword">Cari Nama Siswa:</label>
    <input type="text" id="keyword" name="keyword" placeholder="Masukkan nama siswa"
           value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit">Cari</button>
</form>

<p>Total Siswa: <?php echo $total_siswa; ?></p>
<p><a href="tambah.php">+ Tambah Siswa</a> |
    <a href="presensi.php">Tambah Presensi</a></p>

<table border="1">
    <thead>
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>NIS</th>
        <th>Kelas</th>
        <th>Alamat</th>
        <th>Foto</th>
        <th>Presensi Terbaru</th>  <!-- Kolom baru -->
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
        // Ambil data presensi terbaru untuk siswa ini
        $siswa_id = $row['id'];
        $query_presensi = "SELECT tanggal, status_kehadiran, keterangan FROM tbl_presensi WHERE siswa_id = '$siswa_id' ORDER BY tanggal DESC LIMIT 1";
        $result_presensi = mysqli_query($koneksi, $query_presensi);
        $presensi = mysqli_fetch_assoc($result_presensi);
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td><?php echo htmlspecialchars($row['nis']); ?></td>
            <td><?php echo htmlspecialchars($row['kelas']); ?></td>
            <td><?php echo htmlspecialchars($row['alamat']); ?></td>
            <td>
                <?php if (!empty($row['foto'])): ?>
                    <img src='uploads/<?php echo htmlspecialchars($row['foto']); ?>' width='50'>
                <?php else: ?>
                    Tidak ada foto
                <?php endif; ?>
            </td>
            <td>
                <?php if ($presensi): ?>
                    <?php echo htmlspecialchars($presensi['tanggal']); ?> -
                    <?php echo htmlspecialchars($presensi['status_kehadiran']); ?>
                    <?php if (!empty($presensi['keterangan'])): ?>
                        (<?php echo htmlspecialchars($presensi['keterangan']); ?>)
                    <?php endif; ?>
                <?php else: ?>
                    Belum ada presensi
                <?php endif; ?>
            </td>
            <td>
                <a href='edit.php?id=<?php echo $row['id']; ?>'>Edit</a> |
                <a href='hapus.php?id=<?php echo $row['id']; ?>'
                   onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<div>
    <a href="?page=<?php echo max(1, $page - 1); ?>">Halaman Sebelumnya</a> |
    <a href="?page=<?php echo $page + 1; ?>">Halaman Berikutnya</a>
</div>

</body>
</html>