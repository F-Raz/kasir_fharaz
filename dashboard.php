<?php
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil data statistik
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk"))['total'];
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(TotalHarga), 0) as total FROM penjualan"))['total'];
$stok_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(Stok), 0) as total FROM produk"))['total'];

// Transaksi hari ini
$today = date('Y-m-d');
$transaksi_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan WHERE DATE(TanggalPenjualan) = '$today'"))['total'];
$pendapatan_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(TotalHarga), 0) as total FROM penjualan WHERE DATE(TanggalPenjualan) = '$today'"))['total'];

// Produk stok menipis (kurang dari 20)
$stok_menipis = mysqli_query($conn, "SELECT * FROM produk WHERE Stok < 20 ORDER BY Stok ASC LIMIT 5");

// Transaksi terakhir
$transaksi_terakhir = mysqli_query($conn, "SELECT p.*, u.NamaLengkap, u.Username 
    FROM penjualan p 
    JOIN user u ON p.UserID = u.UserID 
    ORDER BY p.TanggalPenjualan DESC LIMIT 8");

// Produk terlaris (berdasarkan total terjual)
$produk_terlaris = mysqli_query($conn, "SELECT pr.NamaProduk, SUM(dp.JumlahProduk) as total_terjual, pr.Stok
    FROM detailpenjualan dp
    JOIN produk pr ON dp.ProdukID = pr.ProdukID
    GROUP BY dp.ProdukID
    ORDER BY total_terjual DESC
    LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kasir</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 0;
        }
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-area {
            padding: 30px;
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            background: white;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card .card-body {
            padding: 25px;
        }
        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .card-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            background: white;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
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
            <a class="nav-link active" href="dashboard.php">
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
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold">Dashboard</h4>
                <small class="text-muted">Selamat datang di sistem kasir</small>
            </div>
            <div class="user-info">
                <div>
                    <div class="text-end">
                        <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>
                        <br>
                        <small class="text-muted"><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Produk</p>
                                <h2 class="mb-0 fw-bold"><?php echo $total_produk; ?></h2>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> Item</small>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Transaksi</p>
                                <h2 class="mb-0 fw-bold"><?php echo $total_transaksi; ?></h2>
                                <small class="text-info"><i class="bi bi-graph-up"></i> Transaksi</small>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-cart-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Stok</p>
                                <h2 class="mb-0 fw-bold"><?php echo $stok_total; ?></h2>
                                <small class="text-warning"><i class="bi bi-boxes"></i> Unit</small>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Pendapatan</p>
                                <h4 class="mb-0 fw-bold"><?php echo rupiah($total_pendapatan); ?></h4>
                                <small class="text-success"><i class="bi bi-currency-dollar"></i> Revenue</small>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today Stats & Stock Alert -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-calendar-date text-primary me-2"></i>Statistik Hari Ini
                            </h5>
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="p-3 bg-light rounded-3">
                                        <h3 class="text-primary fw-bold mb-1"><?php echo $transaksi_hari_ini; ?></h3>
                                        <small class="text-muted">Transaksi Hari Ini</small>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="p-3 bg-light rounded-3">
                                        <h5 class="text-success fw-bold mb-1"><?php echo rupiah($pendapatan_hari_ini); ?></h5>
                                        <small class="text-muted">Pendapatan Hari Ini</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Stok Menipis
                            </h5>
                            <?php if (mysqli_num_rows($stok_menipis) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($produk = mysqli_fetch_assoc($stok_menipis)): ?>
                                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                            <span><?php echo $produk['NamaProduk']; ?></span>
                                            <span class="badge bg-danger rounded-pill"><?php echo $produk['Stok']; ?> unit</span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">
                                    <i class="bi bi-check-circle text-success"></i> Semua stok aman
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions & Best Sellers -->
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card card-custom">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-clock-history text-primary me-2"></i>Transaksi Terakhir
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Tanggal</th>
                                            <th>Kasir</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($transaksi_terakhir) > 0): ?>
                                            <?php while ($trx = mysqli_fetch_assoc($transaksi_terakhir)): ?>
                                                <tr>
                                                    <td><strong><?php echo $trx['KodeTransaksi']; ?></strong></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($trx['TanggalPenjualan'])); ?></td>
                                                    <td><?php echo $trx['NamaLengkap']; ?></td>
                                                    <td><strong class="text-success"><?php echo rupiah($trx['TotalHarga']); ?></strong></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-trophy text-warning me-2"></i>Produk Terlaris
                            </h5>
                            <?php if (mysqli_num_rows($produk_terlaris) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php 
                                    $rank = 1;
                                    while ($produk = mysqli_fetch_assoc($produk_terlaris)): 
                                    ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-start">
                                                <div class="badge bg-warning text-dark me-2"><?php echo $rank++; ?></div>
                                                <div class="flex-grow-1">
                                                    <strong><?php echo $produk['NamaProduk']; ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Terjual: <?php echo $produk['total_terjual']; ?> | 
                                                        Stok: <?php echo $produk['Stok']; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">Belum ada data penjualan</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
