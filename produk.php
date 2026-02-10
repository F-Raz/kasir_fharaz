<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

// Tambah produk
if (isset($_POST['tambah'])) {
    $kode = generateKodeProduk();
    $nama = clean_input($_POST['nama']);
    $kategori = clean_input($_POST['kategori']);
    $harga = clean_input($_POST['harga']);
    $stok = clean_input($_POST['stok']);
    
    $query = "INSERT INTO produk (KodeProduk, NamaProduk, Kategori, Harga, Stok) 
              VALUES ('$kode', '$nama', '$kategori', '$harga', '$stok')";
    if (mysqli_query($conn, $query)) {
        $produk_id = mysqli_insert_id($conn);
        
        // Catat riwayat stok awal
        if ($stok > 0) {
            $user_id = $_SESSION['user_id'];
            $riwayat = "INSERT INTO riwayat_stok (ProdukID, Jenis, Jumlah, StokSebelum, StokSesudah, Keterangan, UserID)
                       VALUES ('$produk_id', 'masuk', '$stok', 0, '$stok', 'Stok awal produk', '$user_id')";
            mysqli_query($conn, $riwayat);
        }
        
        $success = "Produk berhasil ditambahkan dengan kode $kode!";
    } else {
        $error = "Gagal menambahkan produk!";
    }
}

// Edit produk
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $nama = clean_input($_POST['nama']);
    $kategori = clean_input($_POST['kategori']);
    $harga = clean_input($_POST['harga']);
    
    $query = "UPDATE produk SET NamaProduk='$nama', Kategori='$kategori', Harga='$harga' WHERE ProdukID='$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Produk berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate produk!";
    }
}

// Hapus produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM produk WHERE ProdukID='$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Produk berhasil dihapus!";
    } else {
        $error = "Gagal menghapus produk! Produk mungkin sudah digunakan dalam transaksi.";
    }
}

// Ambil data produk
$produk_list = mysqli_query($conn, "SELECT * FROM produk ORDER BY NamaProduk");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Sistem Kasir</title>
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
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header i {
            font-size: 2.5rem;
            color: white;
        }
        .sidebar-header h5 {
            color: white;
            margin-top: 10px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
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
            <a class="nav-link" href="pelanggan.php">
                <i class="bi bi-people me-2"></i>Data pelanggan
            </a>
            <a class="nav-link active" href="produk.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-box-seam me-2"></i>Data Produk</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah Produk
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($produk = mysqli_fetch_assoc($produk_list)): ?>
                                <tr>
                                    <td><strong><?php echo $produk['KodeProduk']; ?></strong></td>
                                    <td><?php echo $produk['NamaProduk']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $produk['Kategori']; ?></span></td>
                                    <td><?php echo rupiah($produk['Harga']); ?></td>
                                    <td>
                                        <?php if ($produk['Stok'] < 10): ?>
                                            <span class="badge bg-danger"><?php echo $produk['Stok']; ?></span>
                                        <?php elseif ($produk['Stok'] < 20): ?>
                                            <span class="badge bg-warning"><?php echo $produk['Stok']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $produk['Stok']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $produk['ProdukID']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?php echo $produk['ProdukID']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?php echo $produk['ProdukID']; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $produk['ProdukID']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Produk</label>
                                                        <input type="text" class="form-control" value="<?php echo $produk['KodeProduk']; ?>" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Produk</label>
                                                        <input type="text" class="form-control" name="nama" value="<?php echo $produk['NamaProduk']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kategori</label>
                                                        <input type="text" class="form-control" name="kategori" value="<?php echo $produk['Kategori']; ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga</label>
                                                        <input type="number" class="form-control" name="harga" value="<?php echo $produk['Harga']; ?>" required>
                                                    </div>
                                                    <div class="alert alert-info">
                                                        <small><i class="bi bi-info-circle"></i> Stok dikelola di menu "Kelola Stok"</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit" class="btn btn-primary">Update</button>
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
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle"></i> Kode produk akan di-generate otomatis</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" name="kategori" placeholder="Contoh: Makanan, Minuman">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok Awal</label>
                            <input type="number" class="form-control" name="stok" value="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
