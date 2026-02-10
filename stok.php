<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

// Proses update stok
if (isset($_POST['update_stok'])) {
    $produk_id = clean_input($_POST['produk_id']);
    $jenis = clean_input($_POST['jenis']);
    $jumlah = clean_input($_POST['jumlah']);
    $keterangan = clean_input($_POST['keterangan']);
    $user_id = $_SESSION['user_id'];
    
    // Ambil stok sekarang
    $query_stok = mysqli_query($conn, "SELECT Stok FROM produk WHERE ProdukID = '$produk_id'");
    $data_stok = mysqli_fetch_assoc($query_stok);
    $stok_sebelum = $data_stok['Stok'];
    
    if ($jenis == 'masuk') {
        $stok_sesudah = $stok_sebelum + $jumlah;
        $query = "UPDATE produk SET Stok = Stok + $jumlah WHERE ProdukID = '$produk_id'";
    } elseif ($jenis == 'keluar') {
        if ($stok_sebelum >= $jumlah) {
            $stok_sesudah = $stok_sebelum - $jumlah;
            $query = "UPDATE produk SET Stok = Stok - $jumlah WHERE ProdukID = '$produk_id'";
        } else {
            $error = "Stok tidak mencukupi!";
        }
    } else { // penyesuaian
        $stok_sesudah = $jumlah;
        $query = "UPDATE produk SET Stok = $jumlah WHERE ProdukID = '$produk_id'";
    }
    
    if (!$error) {
        if (mysqli_query($conn, $query)) {
            // Catat riwayat
            $riwayat = "INSERT INTO riwayat_stok (ProdukID, Jenis, Jumlah, StokSebelum, StokSesudah, Keterangan, UserID)
                       VALUES ('$produk_id', '$jenis', '$jumlah', '$stok_sebelum', '$stok_sesudah', '$keterangan', '$user_id')";
            mysqli_query($conn, $riwayat);
            $success = "Stok berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate stok!";
        }
    }
}

// Ambil data produk
$produk_list = mysqli_query($conn, "SELECT * FROM produk ORDER BY NamaProduk");

// Ambil riwayat stok
$riwayat_list = mysqli_query($conn, "SELECT r.*, p.NamaProduk, p.KodeProduk, u.NamaLengkap
                                     FROM riwayat_stok r
                                     JOIN produk p ON r.ProdukID = p.ProdukID
                                     JOIN user u ON r.UserID = u.UserID
                                     ORDER BY r.created_at DESC
                                     LIMIT 20");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stok - Sistem Kasir</title>
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
            <a class="nav-link" href="produk.php">
                <i class="bi bi-box-seam me-3"></i>Data Produk
            </a>
            <a class="nav-link active" href="stok.php">
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
        <h3 class="mb-4"><i class="bi bi-boxes me-2"></i>Kelola Stok Barang</h3>

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

        <div class="row">
            <!-- Daftar Produk & Update Stok -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Daftar Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($produk_list, 0);
                                    while ($produk = mysqli_fetch_assoc($produk_list)): 
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $produk['NamaProduk']; ?></strong><br>
                                                <small class="text-muted"><?php echo $produk['KodeProduk']; ?></small>
                                            </td>
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
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#modalStok<?php echo $produk['ProdukID']; ?>">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Update Stok -->
                                        <div class="modal fade" id="modalStok<?php echo $produk['ProdukID']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Stok - <?php echo $produk['NamaProduk']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <div class="alert alert-info">
                                                                <strong>Stok Saat Ini:</strong> <?php echo $produk['Stok']; ?> unit
                                                            </div>
                                                            <input type="hidden" name="produk_id" value="<?php echo $produk['ProdukID']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Jenis Transaksi</label>
                                                                <select name="jenis" class="form-select" required>
                                                                    <option value="masuk">Stok Masuk (+)</option>
                                                                    <option value="keluar">Stok Keluar (-)</option>
                                                                    <option value="penyesuaian">Penyesuaian (Set Langsung)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Jumlah</label>
                                                                <input type="number" class="form-control" name="jumlah" min="1" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Keterangan</label>
                                                                <textarea class="form-control" name="keterangan" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="update_stok" class="btn btn-primary">Update Stok</button>
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

            <!-- Riwayat Stok -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan Stok</h5>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php while ($riwayat = mysqli_fetch_assoc($riwayat_list)): ?>
                                <div class="card mb-2">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo $riwayat['NamaProduk']; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $riwayat['KodeProduk']; ?></small>
                                            </div>
                                            <span class="badge 
                                                <?php 
                                                echo $riwayat['Jenis'] == 'masuk' ? 'bg-success' : 
                                                     ($riwayat['Jenis'] == 'keluar' ? 'bg-danger' : 'bg-warning'); 
                                                ?>">
                                                <?php echo strtoupper($riwayat['Jenis']); ?>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <small>
                                                <?php echo $riwayat['StokSebelum']; ?> → <?php echo $riwayat['StokSesudah']; ?> unit
                                                (<?php echo $riwayat['Jumlah']; ?>)
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> <?php echo $riwayat['NamaLengkap']; ?> |
                                                <i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?>
                                            </small>
                                            <br>
                                            <small><em><?php echo $riwayat['Keterangan']; ?></em></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
