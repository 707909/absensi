<?php
// ================== KONEKSI ==================
$conn = mysqli_connect("localhost", "root", "", "db_absensi");
if (!$conn) die("Koneksi gagal");

// ================== HANDLE ACTION ==================

// TAMBAH
if (isset($_POST['tambah'])) {
    $id_siswa = $_POST['id_siswa'];
    $tanggal  = $_POST['tanggal'];
    $status   = $_POST['status'];

    mysqli_query($conn, "INSERT INTO absensi (id_siswa, tanggal, status)
                         VALUES ('$id_siswa','$tanggal','$status')");
    header("Location: index.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $id       = $_POST['id_absensi'];
    $id_siswa = $_POST['id_siswa'];
    $tanggal  = $_POST['tanggal'];
    $status   = $_POST['status'];

    mysqli_query($conn, "UPDATE absensi SET
                         id_siswa='$id_siswa',
                         tanggal='$tanggal',
                         status='$status'
                         WHERE id_absensi='$id'");
    header("Location: index.php");
    exit;
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM absensi WHERE id_absensi='$id'");
    header("Location: index.php");
    exit;
}

// ================== DATA ==================

$selected_kelas = $_GET['kelas'] ?? '';
$edit = null;

// DATA EDIT
if (isset($_GET['edit'])) {
    $id   = $_GET['edit'];
    $edit = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM absensi WHERE id_absensi='$id'")
    );
}

// DATA KELAS
$data_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");

// DATA SISWA (FILTER BY KELAS)
if ($selected_kelas) {
    $data_siswa = mysqli_query($conn,
        "SELECT * FROM siswa WHERE id_kelas='$selected_kelas' ORDER BY nama_siswa");
} else {
    $data_siswa = mysqli_query($conn,
        "SELECT * FROM siswa ORDER BY nama_siswa");
}

// DATA ABSENSI
$data_absensi = mysqli_query($conn, "
    SELECT a.*, s.nis, s.nama_siswa, k.nama_kelas
    FROM absensi a
    JOIN siswa s ON a.id_siswa = s.id_siswa
    JOIN kelas k ON s.id_kelas = k.id_kelas
    ORDER BY a.tanggal DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>CRUD Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg">

    <h1 class="text-2xl font-bold mb-6">Sistem Absensi</h1>

    <!-- ================= FILTER KELAS ================= -->
    <form method="GET" class="mb-6">
        <select name="kelas"
                onchange="this.form.submit()"
                class="border p-2 rounded w-60">
            <option value="">-- Pilih Kelas --</option>
            <?php while($k = mysqli_fetch_assoc($data_kelas)): ?>
                <option value="<?= $k['id_kelas']; ?>"
                    <?= ($selected_kelas == $k['id_kelas']) ? 'selected' : ''; ?>>
                    <?= $k['nama_kelas']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- ================= FORM ================= -->
    <form method="POST" class="grid grid-cols-4 gap-4 mb-8">

        <?php if($edit): ?>
            <input type="hidden" name="id_absensi"
                   value="<?= $edit['id_absensi']; ?>">
        <?php endif; ?>

        <!-- SISWA -->
        <select name="id_siswa" class="border p-2 rounded" required>
            <option value="">-- Pilih Siswa --</option>
            <?php while($s = mysqli_fetch_assoc($data_siswa)): ?>
                <option value="<?= $s['id_siswa']; ?>"
                    <?= ($edit && $edit['id_siswa'] == $s['id_siswa']) ? 'selected' : ''; ?>>
                    <?= $s['nis']; ?> - <?= $s['nama_siswa']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- TANGGAL -->
        <input type="date"
               name="tanggal"
               value="<?= $edit['tanggal'] ?? ''; ?>"
               class="border p-2 rounded"
               required>

        <!-- STATUS -->
        <select name="status" class="border p-2 rounded" required>
            <?php
            $status_list = ['hadir','izin','sakit','alpha'];
            foreach ($status_list as $st):
            ?>
                <option value="<?= $st; ?>"
                    <?= ($edit && $edit['status'] == $st) ? 'selected' : ''; ?>>
                    <?= ucfirst($st); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- BUTTON -->
        <?php if($edit): ?>
            <button type="submit"
                    name="update"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white rounded p-2">
                Update
            </button>
        <?php else: ?>
            <button type="submit"
                    name="tambah"
                    class="bg-blue-500 hover:bg-blue-600 text-white rounded p-2">
                Tambah
            </button>
        <?php endif; ?>

    </form>

    <!-- ================= TABLE ================= -->
    <table class="w-full border text-center">
        <thead class="bg-gray-200">
            <tr>
                <th class="border p-2">No</th>
                <th class="border p-2">Kelas</th>
                <th class="border p-2">NIS</th>
                <th class="border p-2">Nama</th>
                <th class="border p-2">Tanggal</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = mysqli_fetch_assoc($data_absensi)): ?>
            <tr>
                <td class="border p-2"><?= $no++; ?></td>
                <td class="border p-2"><?= $row['nama_kelas']; ?></td>
                <td class="border p-2"><?= $row['nis']; ?></td>
                <td class="border p-2"><?= $row['nama_siswa']; ?></td>
                <td class="border p-2"><?= $row['tanggal']; ?></td>
                <td class="border p-2 capitalize"><?= $row['status']; ?></td>
                <td class="border p-2 space-x-1">
                    <a href="?edit=<?= $row['id_absensi']; ?>"
                       class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded">
                        Edit
                    </a>
                    <a href="?hapus=<?= $row['id_absensi']; ?>"
                       onclick="return confirm('Yakin hapus?')"
                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                        Hapus
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>
