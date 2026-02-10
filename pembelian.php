<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $produk_id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah'];
    
    // Cek stok
    $query = "SELECT * FROM produk WHERE ProdukID = '$produk_id'";
    $result = mysqli_query($conn, $query);
    $produk = mysqli_fetch_assoc($result);
    
    if ($produk['Stok'] >= $jumlah) {
        if (isset($_SESSION['cart'][$produk_id])) {
            $_SESSION['cart'][$produk_id]['jumlah'] += $jumlah;
        } else {
            $_SESSION['cart'][$produk_id] = array(
                'kode' => $produk['KodeProduk'],
                'nama' => $produk['NamaProduk'],
                'harga' => $produk['Harga'],
                'jumlah' => $jumlah
            );
        }
    }
}

// Hapus dari keranjang
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Update jumlah
if (isset($_POST['update_qty'])) {
    $produk_id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah'];
    if ($jumlah > 0) {
        $_SESSION['cart'][$produk_id]['jumlah'] = $jumlah;
    }
}

// Proses checkout
if (isset($_POST['checkout'])) {
    $jumlah_bayar = $_POST['jumlah_bayar'];
    $total_harga = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total_harga += $item['harga'] * $item['jumlah'];
    }
    
    if ($jumlah_bayar >= $total_harga) {
        $kembalian = $jumlah_bayar - $total_harga;
        $kode_transaksi = generateKodeTransaksi();
        $user_id = $_SESSION['user_id'];
        
        // Insert penjualan
        $query = "INSERT INTO penjualan (KodeTransaksi, TotalHarga, JumlahBayar, Kembalian, UserID) 
                  VALUES ('$kode_transaksi', '$total_harga', '$jumlah_bayar', '$kembalian', '$user_id')";
        
        if (mysqli_query($conn, $query)) {
            $penjualan_id = mysqli_insert_id($conn);
            
            // Insert detail dan update stok
            foreach ($_SESSION['cart'] as $id => $item) {
                $subtotal = $item['harga'] * $item['jumlah'];
                $query_detail = "INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, HargaSatuan, Subtotal) 
                                VALUES ('$penjualan_id', '$id', '{$item['jumlah']}', '{$item['harga']}', '$subtotal')";
                mysqli_query($conn, $query_detail);
                
                // Update stok
                $query_update = "UPDATE produk SET Stok = Stok - {$item['jumlah']} WHERE ProdukID = '$id'";
                mysqli_query($conn, $query_update);
                
                // Catat riwayat stok
                $stok_query = mysqli_query($conn, "SELECT Stok FROM produk WHERE ProdukID = '$id'");
                $stok_data = mysqli_fetch_assoc($stok_query);
                $stok_sekarang = $stok_data['Stok'];
                $stok_sebelum = $stok_sekarang + $item['jumlah'];
                
                $riwayat = "INSERT INTO riwayat_stok (ProdukID, Jenis, Jumlah, StokSebelum, StokSesudah, Keterangan, UserID)
                           VALUES ('$id', 'keluar', '{$item['jumlah']}', '$stok_sebelum', '$stok_sekarang', 'Penjualan $kode_transaksi', '$user_id')";
                mysqli_query($conn, $riwayat);
            }
            
            // Simpan data untuk struk
            $_SESSION['last_transaction'] = array(
                'kode' => $kode_transaksi,
                'total' => $total_harga,
                'bayar' => $jumlah_bayar,
                'kembalian' => $kembalian,
                'items' => $_SESSION['cart']
            );
            
            // Kosongkan keranjang
            $_SESSION['cart'] = array();
            
            echo "<script>
                    alert('Transaksi berhasil!\\nKode: $kode_transaksi\\nKembalian: " . rupiah($kembalian) . "');
                    window.location='pembelian.php';
                  </script>";
        }
    } else {
        echo "<script>alert('Jumlah bayar kurang!');</script>";
    }
}

// Ambil data produk
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

$query = "SELECT * FROM produk WHERE Stok > 0";
if ($search) {
    $query .= " AND (NamaProduk LIKE '%$search%' OR KodeProduk LIKE '%$search%')";
}
if ($kategori) {
    $query .= " AND Kategori = '$kategori'";
}
$query .= " ORDER BY NamaProduk";
$produk_list = mysqli_query($conn, $query);

// Ambil kategori
$kategori_list = mysqli_query($conn, "SELECT DISTINCT Kategori FROM produk WHERE Kategori IS NOT NULL ORDER BY Kategori");

// Hitung total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian - Sistem Kasir</title>
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
        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 12px;
            border: 2px solid #e9ecef;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-color: var(--primary-color);
        }
        .cart-card {
            position: sticky;
            top: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .cart-item {
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .badge-stock {
            position: absolute;
            top: 10px;
            right: 10px;
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
            <a class="nav-link active" href="pembelian.php">
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
        <h3 class="mb-4"><i class="bi bi-cart-check me-2"></i>Pembelian / Kasir</h3>
        
        <div class="row">
            <!-- Produk -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari produk..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="kategori" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                        <option value="<?php echo $kat['Kategori']; ?>" 
                                                <?php echo $kategori == $kat['Kategori'] ? 'selected' : ''; ?>>
                                            <?php echo $kat['Kategori']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Daftar Produk</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <div class="row g-3">
                            <?php while ($produk = mysqli_fetch_assoc($produk_list)): ?>
                                <div class="col-md-4">
                                    <div class="card product-card position-relative" 
                                         data-bs-toggle="modal" 
                                         data-bs-target="#modal<?php echo $produk['ProdukID']; ?>">
                                        <span class="badge bg-success badge-stock">
                                            Stok: <?php echo $produk['Stok']; ?>
                                        </span>
                                        <div class="card-body text-center">
                                            <i class="bi bi-box text-primary" style="font-size: 3rem;"></i>
                                            <h6 class="card-title mt-2 mb-1"><?php echo $produk['NamaProduk']; ?></h6>
                                            <small class="text-muted"><?php echo $produk['KodeProduk']; ?></small>
                                            <p class="mb-0 mt-2">
                                                <span class="badge bg-secondary"><?php echo $produk['Kategori']; ?></span>
                                            </p>
                                            <h5 class="text-success mt-2 mb-0"><?php echo rupiah($produk['Harga']); ?></h5>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal -->
                                    <div class="modal fade" id="modal<?php echo $produk['ProdukID']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?php echo $produk['NamaProduk']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <div class="text-center mb-3">
                                                            <i class="bi bi-box text-primary" style="font-size: 5rem;"></i>
                                                        </div>
                                                        <table class="table">
                                                            <tr>
                                                                <th>Kode</th>
                                                                <td><?php echo $produk['KodeProduk']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Kategori</th>
                                                                <td><?php echo $produk['Kategori']; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Harga</th>
                                                                <td class="text-success fw-bold"><?php echo rupiah($produk['Harga']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Stok</th>
                                                                <td><span class="badge bg-success"><?php echo $produk['Stok']; ?> unit</span></td>
                                                            </tr>
                                                        </table>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Jumlah Beli</label>
                                                            <input type="number" class="form-control form-control-lg" 
                                                                   name="jumlah" min="1" max="<?php echo $produk['Stok']; ?>" 
                                                                   value="1" required>
                                                        </div>
                                                        <input type="hidden" name="produk_id" value="<?php echo $produk['ProdukID']; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                                                            <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keranjang -->
            <div class="col-md-4">
                <div class="card cart-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($_SESSION['cart']) > 0): ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                    <div class="cart-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo $item['nama']; ?></h6>
                                                <small class="text-muted"><?php echo $item['kode']; ?></small>
                                            </div>
                                            <a href="?remove=<?php echo $id; ?>" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="produk_id" value="<?php echo $id; ?>">
                                                    <input type="number" name="jumlah" value="<?php echo $item['jumlah']; ?>" 
                                                           min="1" style="width: 60px;" class="form-control form-control-sm d-inline">
                                                    <button type="submit" name="update_qty" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block"><?php echo $item['jumlah']; ?> x <?php echo rupiah($item['harga']); ?></small>
                                                <strong class="text-success"><?php echo rupiah($item['harga'] * $item['jumlah']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="alert alert-info mt-3 mb-3">
                                <h4 class="mb-0">Total: <strong><?php echo rupiah($total); ?></strong></h4>
                            </div>

                            <form method="POST" onsubmit="return confirm('Proses transaksi?');">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Bayar</label>
                                    <input type="number" name="jumlah_bayar" class="form-control form-control-lg" 
                                           min="<?php echo $total; ?>" required autofocus>
                                    <small class="text-muted">Minimal: <?php echo rupiah($total); ?></small>
                                </div>
                                <button type="submit" name="checkout" class="btn btn-success w-100 btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Proses Pembayaran
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-cart-x" style="font-size: 5rem;"></i>
                                <p class="mt-3 mb-0">Keranjang kosong</p>
                                <small>Pilih produk untuk memulai transaksi</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
