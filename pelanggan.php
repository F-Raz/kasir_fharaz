<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

// Tambah pelanggan
if (isset($_POST['tambah'])) {
    $nama = clean_input($_POST['nama']);
    $alamat = clean_input($_POST['alamat']);
    $telp = clean_input($_POST['telepon']);

    $query = "INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon)
              VALUES ('$nama', '$alamat', '$telp')";
    if (mysqli_query($conn, $query)) {
        $success = "Pelanggan berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan pelanggan!";
    }
}

// Edit pelanggan
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $nama = clean_input($_POST['nama']);
    $alamat = clean_input($_POST['alamat']);
    $telp = clean_input($_POST['telepon']);

    $query = "UPDATE pelanggan 
              SET NamaPelanggan='$nama', Alamat='$alamat', NomorTelepon='$telp'
              WHERE PelangganID='$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Data pelanggan berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate pelanggan!";
    }
}

// Hapus pelanggan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM pelanggan WHERE PelangganID='$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Pelanggan berhasil dihapus!";
    } else {
        $error = "Gagal menghapus pelanggan! Data mungkin sudah digunakan.";
    }
}

// Ambil data pelanggan
$pelanggan_list = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY NamaPelanggan");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }

        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .sidebar-header i {
            font-size: 2.5rem;
            color: #fff;
        }

        .sidebar-header h5 {
            color: #fff;
            margin-top: 10px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, .85);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 10px;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-shop-window"></i>
            <h5 class="mb-0">Sistem Kasir</h5>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2 me-3"></i>Dashboard
            </a>
            <a class="nav-link" href="pembelian.php">
                <i class="bi bi-cart-check me-3"></i>Pembelian
            </a>
            <a class="nav-link active" href="pelanggan.php">
                <i class="bi bi-people me-2"></i>Data pelanggan
            </a>
            <a class="nav-link" href="produk.php">
                <i class="bi bi-box-seam me-3"></i>Data Produk
            </a>
            <a class="nav-link" href="stok.php">
                <i class="bi bi-boxes me-3"></i>Kelola Stok
            </a>
            <a class="nav-link" href="laporan.php">
                <i class="bi bi-file-earmark-text me-3"></i>Laporan
            </a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a class="nav-link" href="user.php">
                    <i class="bi bi-people me-3"></i>Kelola User
                </a>
            <?php endif; ?>
            <hr class="text-white mx-3">
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-3"></i>Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between mb-4">
            <h3><i class="bi bi-people me-2"></i>Data Pelanggan</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pelanggan
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($pelanggan_list)): ?>
                            <tr>
                                <td><?= $p['NamaPelanggan'] ?></td>
                                <td><?= $p['Alamat'] ?></td>
                                <td><?= $p['NomorTelepon'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#edit<?= $p['PelangganID'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?hapus=<?= $p['PelangganID'] ?>"
                                        onclick="return confirm('Yakin hapus pelanggan ini?')"
                                        class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="edit<?= $p['PelangganID'] ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Pelanggan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $p['PelangganID'] ?>">
                                                <div class="mb-3">
                                                    <label>Nama</label>
                                                    <input type="text" name="nama" class="form-control"
                                                        value="<?= $p['NamaPelanggan'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Alamat</label>
                                                    <textarea name="alamat"
                                                        class="form-control"><?= $p['Alamat'] ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label>No. Telepon</label>
                                                    <input type="number" name="telepon" class="form-control"
                                                        value="<?= $p['NomorTelepon'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button class="btn btn-primary" name="edit">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5>Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>No. Telepon</label>
                        <input type="number" name="telepon" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" name="tambah">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>