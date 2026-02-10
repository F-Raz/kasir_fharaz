<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$success = '';
$error = '';

// Tambah user
if (isset($_POST['tambah'])) {
    $username = clean_input($_POST['username']);
    $password = md5($_POST['password']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $role = clean_input($_POST['role']);
    
    $check = mysqli_query($conn, "SELECT * FROM user WHERE Username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $query = "INSERT INTO user (Username, Password, NamaLengkap, Role) VALUES ('$username', '$password', '$nama_lengkap', '$role')";
        if (mysqli_query($conn, $query)) {
            $success = "User berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan user!";
        }
    }
}

// Edit user
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $username = clean_input($_POST['username']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $role = clean_input($_POST['role']);
    
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $query = "UPDATE user SET Username='$username', Password='$password', NamaLengkap='$nama_lengkap', Role='$role' WHERE UserID='$id'";
    } else {
        $query = "UPDATE user SET Username='$username', NamaLengkap='$nama_lengkap', Role='$role' WHERE UserID='$id'";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "User berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate user!";
    }
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if ($id != $_SESSION['user_id']) {
        $query = "DELETE FROM user WHERE UserID='$id'";
        if (mysqli_query($conn, $query)) {
            $success = "User berhasil dihapus!";
        } else {
            $error = "Gagal menghapus user!";
        }
    } else {
        $error = "Tidak bisa menghapus akun sendiri!";
    }
}

// Ambil data user
$user_list = mysqli_query($conn, "SELECT * FROM user ORDER BY NamaLengkap");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Sistem Kasir</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-people me-2"></i>Kelola User</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah User
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
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($user_list)): ?>
                                <tr>
                                    <td><?php echo $user['UserID']; ?></td>
                                    <td><?php echo $user['NamaLengkap']; ?></td>
                                    <td><strong><?php echo $user['Username']; ?></strong></td>
                                    <td>
                                        <?php if ($user['Role'] == 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Petugas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#modalEdit<?php echo $user['UserID']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($user['UserID'] != $_SESSION['user_id']): ?>
                                            <a href="?hapus=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?php echo $user['UserID']; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $user['UserID']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Lengkap</label>
                                                        <input type="text" class="form-control" name="nama_lengkap" 
                                                               value="<?php echo $user['NamaLengkap']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Username</label>
                                                        <input type="text" class="form-control" name="username" 
                                                               value="<?php echo $user['Username']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Password (kosongkan jika tidak diubah)</label>
                                                        <input type="password" class="form-control" name="password">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Role</label>
                                                        <select name="role" class="form-select">
                                                            <option value="admin" <?php echo $user['Role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                            <option value="petugas" <?php echo $user['Role'] == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                                                        </select>
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
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="petugas" selected>Petugas</option>
                            </select>
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
