<?php
include 'koneksi.php';

$id = $_GET['id'];

/* Prepared statement */

$query = "SELECT foto FROM tbl_siswa WHERE id = ?";

$stmt = mysqli_prepare($koneksi, $query);

/* Bind parameter */
mysqli_stmt_bind_param($stmt, "i", $id); // "i" karena id adalah integer

/* Eksekusi statement */
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_bind_result($stmt, $photo);
    mysqli_stmt_fetch($stmt);

    /* Tutup statement SELECT setelah mengambil hasilnya */
    mysqli_stmt_close($stmt); // Penting: Tutup statement SELECT di sini!

    // Now delete the student record
    $delete_query = "DELETE FROM tbl_siswa WHERE id = ?";
    $delete_stmt = mysqli_prepare($koneksi, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);

    // Delete the photo file from the uploads directory
    if (file_exists("uploads/" . $photo)) {
        unlink("uploads/" . $photo);
    }
    
    header("Location: index.php");
    exit;
} else {
    echo "Error deleting student: " . mysqli_error($koneksi);
}

?>