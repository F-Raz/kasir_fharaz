<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Filter tanggal
$dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-01');
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');

// Query laporan
$query = "SELECT p.*, u.NamaLengkap
          FROM penjualan p 
          JOIN user u ON p.UserID = u.UserID 
          WHERE DATE(p.TanggalPenjualan) BETWEEN '$dari' AND '$sampai'
          ORDER BY p.TanggalPenjualan DESC";
$laporan = mysqli_query($conn, $query);

// Hitung total
$total_transaksi = mysqli_num_rows($laporan);
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(TotalHarga), 0) as total FROM penjualan WHERE DATE(TanggalPenjualan) BETWEEN '$dari' AND '$sampai'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
        @media print {
            .no-print { display: none; }
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body class="laporan-body">
    <!-- Sidebar -->
    <div class="sidebar no-print">
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
            <a class="nav-link" href="stok.php">
                <i class="bi bi-boxes me-3"></i>Kelola Stok
            </a>
            <a class="nav-link active" href="laporan.php">
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
        <h3 class="mb-4"><i class="bi bi-file-earmark-text me-2"></i>Laporan Penjualan</h3>

        <!-- Filter -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" name="dari" value="<?php echo $dari; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="sampai" value="<?php echo $sampai; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Transaksi</h6>
                        <h2 class="text-primary fw-bold"><?php echo $total_transaksi; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Pendapatan</h6>
                        <h2 class="text-success fw-bold"><?php echo rupiah($total_pendapatan); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Print -->
        <div class="mb-3 no-print">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer me-2"></i>Cetak Laporan
            </button>
        </div>

        <!-- Tabel Laporan -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Periode: <?php echo tgl_indo($dari); ?> - <?php echo tgl_indo($sampai); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th>Bayar</th>
                                <th>Kembalian</th>
                                <th class="no-print">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            mysqli_data_seek($laporan, 0);
                            while ($data = mysqli_fetch_assoc($laporan)): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo $data['KodeTransaksi']; ?></strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($data['TanggalPenjualan'])); ?></td>
                                    <td><?php echo $data['NamaLengkap']; ?></td>
                                    <td><?php echo rupiah($data['TotalHarga']); ?></td>
                                    <td><?php echo rupiah($data['JumlahBayar']); ?></td>
                                    <td><?php echo rupiah($data['Kembalian']); ?></td>
                                    <td class="no-print">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#detail<?php echo $data['PenjualanID']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Detail -->
                                <div class="modal fade" id="detail<?php echo $data['PenjualanID']; ?>">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Transaksi <?php echo $data['KodeTransaksi']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                $detail = mysqli_query($conn, "SELECT dp.*, p.NamaProduk, p.KodeProduk 
                                                                              FROM detailpenjualan dp 
                                                                              JOIN produk p ON dp.ProdukID = p.ProdukID 
                                                                              WHERE dp.PenjualanID = '{$data['PenjualanID']}'");
                                                ?>
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Produk</th>
                                                            <th>Harga</th>
                                                            <th>Jumlah</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($item = mysqli_fetch_assoc($detail)): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php echo $item['NamaProduk']; ?><br>
                                                                    <small class="text-muted"><?php echo $item['KodeProduk']; ?></small>
                                                                </td>
                                                                <td><?php echo rupiah($item['HargaSatuan']); ?></td>
                                                                <td><?php echo $item['JumlahProduk']; ?></td>
                                                                <td><?php echo rupiah($item['Subtotal']); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3">Total</th>
                                                            <th><?php echo rupiah($data['TotalHarga']); ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="3">Bayar</th>
                                                            <th><?php echo rupiah($data['JumlahBayar']); ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="3">Kembalian</th>
                                                            <th><?php echo rupiah($data['Kembalian']); ?></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
