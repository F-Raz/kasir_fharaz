# Sistem Kasir - Point of Sale System

Sistem kasir (Point of Sale) berbasis web menggunakan PHP, MySQL, dan Bootstrap 5 dengan tampilan modern dan responsif. Sistem ini dirancang untuk admin dan petugas kasir tanpa modul pelanggan.

## 🎯 Fitur Utama

### 1. **Autentikasi**
- ✅ Login untuk Admin dan Petugas
- ✅ Registrasi (tersedia untuk semua user)
- ✅ Logout
- ✅ Session management

### 2. **Dashboard**
- Statistik real-time (total produk, transaksi, stok, pendapatan)
- Statistik hari ini
- Alert stok menipis
- Riwayat transaksi terakhir
- Produk terlaris

### 3. **Pembelian / Kasir**
- Sistem keranjang belanja interaktif
- Pencarian produk by nama/kode
- Filter kategori
- Perhitungan otomatis total dan kembalian
- Update stok otomatis
- Generate kode transaksi otomatis

### 4. **Pendataan Barang**
- CRUD produk lengkap
- Auto-generate kode produk
- Kategori produk
- Monitor stok real-time
- Alert stok rendah

### 5. **Kelola Stok Barang**
- Stok masuk
- Stok keluar
- Penyesuaian stok
- Riwayat perubahan stok lengkap dengan:
  - Waktu perubahan
  - User yang melakukan
  - Keterangan
  - Stok sebelum dan sesudah

### 6. **Laporan Penjualan**
- Filter berdasarkan tanggal
- Detail transaksi lengkap
- Total pendapatan
- Cetak/Print laporan
- Export-ready

### 7. **Kelola User** (Admin Only)
- Tambah/Edit/Hapus user
- Role management (Admin/Petugas)
- Ganti password

## 💻 Teknologi yang Digunakan

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework CSS:** Bootstrap 5.3.0
- **Icons:** Bootstrap Icons 1.10.0

## 📋 Persyaratan Sistem

- Web Server (Apache/Nginx)
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ / MariaDB 10.2+
- Extension PHP: mysqli
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🚀 Instalasi

### Langkah 1: Persiapan
```bash
# Clone atau Download project
git clone [repository-url]
# atau extract ZIP file ke folder htdocs/www
```

### Langkah 2: Database
1. Buka phpMyAdmin atau MySQL client
2. Import file `database.sql`
   ```sql
   mysql -u root -p < database.sql
   ```
3. Database `dbkasir_pelanggan` akan dibuat otomatis

### Langkah 3: Konfigurasi
1. Buka file `config.php`
2. Sesuaikan pengaturan database:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbkasir_pelanggan');
```

### Langkah 4: Akses Aplikasi
1. Buka browser
2. Akses: `http://localhost/nama-folder-project`
3. Login menggunakan akun default

## 🔐 Akun Default

### Admin
- Username: `admin`
- Password: `admin123`

### Petugas
- Username: `petugas1`
- Password: `petugas123`

**⚠️ PENTING:** Segera ganti password setelah login pertama!

## 📁 Struktur File

```
sistem-kasir/
│
├── config.php           # Konfigurasi database & fungsi helper
├── index.php            # Halaman login
├── register.php         # Halaman registrasi
├── dashboard.php        # Dashboard utama
├── pembelian.php        # Kasir/transaksi penjualan
├── produk.php           # Manajemen produk
├── stok.php             # Kelola stok barang
├── laporan.php          # Laporan penjualan
├── user.php             # Kelola user (admin only)
├── logout.php           # Logout handler
├── database.sql         # File database
└── README.md            # Dokumentasi
```

## 🎨 Fitur Desain

- **Modern Gradient Design** - Warna ungu-biru yang elegan
- **Responsive Layout** - Otomatis menyesuaikan semua device
- **Smooth Animations** - Transisi smooth dan hover effects
- **Clean UI/UX** - Interface yang intuitif dan mudah digunakan
- **Color-coded Status** - Status stok dengan warna badge
- **Modal Dialogs** - Form modern dengan modal Bootstrap
- **Alert Notifications** - Feedback real-time ke user

## 📖 Cara Penggunaan

### Melakukan Transaksi
1. Login ke sistem
2. Klik menu "Pembelian"
3. Cari atau klik produk yang ingin dibeli
4. Masukkan jumlah pembelian
5. Produk masuk ke keranjang
6. Masukkan jumlah bayar
7. Klik "Proses Pembayaran"

### Mengelola Produk
1. Klik menu "Data Produk"
2. Klik "Tambah Produk" untuk menambah
3. Kode produk di-generate otomatis
4. Klik icon pensil untuk edit
5. Klik icon tempat sampah untuk hapus

### Mengelola Stok
1. Klik menu "Kelola Stok"
2. Pilih produk yang akan diupdate
3. Pilih jenis: Masuk/Keluar/Penyesuaian
4. Masukkan jumlah dan keterangan
5. Sistem otomatis mencatat riwayat

### Melihat Laporan
1. Klik menu "Laporan"
2. Pilih rentang tanggal
3. Klik "Tampilkan"
4. Lihat detail dengan klik icon mata
5. Klik "Cetak Laporan" untuk print

## 🔒 Keamanan

- Password di-hash menggunakan MD5
- Session-based authentication
- SQL Injection protection dengan prepared statements
- Input sanitization & validation
- Role-based access control (RBAC)
- XSS protection

## 🛠️ Troubleshooting

### Error: Connection Failed
**Solusi:** 
- Pastikan MySQL/MariaDB berjalan
- Cek username & password di `config.php`
- Cek nama database sudah benar

### Error: Table doesn't exist
**Solusi:**
- Import ulang file `database.sql`
- Pastikan database `dbkasir_pelanggan` sudah dibuat

### Halaman Blank/Error 500
**Solusi:**
- Aktifkan display_errors di php.ini
- Cek error log di web server
- Pastikan semua extension PHP terinstall

### Stok Tidak Update
**Solusi:**
- Cek koneksi database
- Lihat riwayat stok untuk debugging
- Pastikan transaksi berhasil

## 📊 Database Schema

### Tabel: user
- UserID (PK)
- Username (Unique)
- Password (MD5)
- NamaLengkap
- Role (admin/petugas)
- created_at

### Tabel: produk
- ProdukID (PK)
- KodeProduk (Unique, Auto-generate)
- NamaProduk
- Kategori
- Harga
- Stok
- created_at, updated_at

### Tabel: penjualan
- PenjualanID (PK)
- KodeTransaksi (Unique, Auto-generate)
- TanggalPenjualan
- TotalHarga
- JumlahBayar
- Kembalian
- UserID (FK)

### Tabel: detailpenjualan
- DetailID (PK)
- PenjualanID (FK)
- ProdukID (FK)
- JumlahProduk
- HargaSatuan
- Subtotal

### Tabel: riwayat_stok
- RiwayatID (PK)
- ProdukID (FK)
- Jenis (masuk/keluar/penyesuaian)
- Jumlah
- StokSebelum
- StokSesudah
- Keterangan
- UserID (FK)
- created_at

## 🎯 Roadmap & Pengembangan

Fitur yang bisa ditambahkan:
- [ ] Export laporan ke Excel/PDF
- [ ] Grafik penjualan
- [ ] Barcode scanner
- [ ] Struk digital/print
- [ ] Multi-kasir concurrent
- [ ] Backup database otomatis
- [ ] Notifikasi email/SMS
- [ ] API REST untuk integrasi
- [ ] Mobile app
- [ ] Sistem diskon & promosi

## 📝 Catatan Penting

1. **Backup Regular** - Lakukan backup database secara berkala
2. **Update Password** - Ganti password default segera
3. **Browser Support** - Gunakan browser modern untuk hasil optimal
4. **Security** - Jangan expose aplikasi ke internet tanpa SSL
5. **Performance** - Monitor performa database untuk transaksi tinggi

## 🤝 Kontribusi

Proyek ini dibuat untuk tujuan edukasi. Kontribusi dan saran sangat diterima!

## 📄 Lisensi

Proyek ini dibuat untuk tujuan pembelajaran dan edukasi.

## 📞 Dukungan

Untuk pertanyaan, bug report, atau request fitur, silakan buat issue di repository ini.

---

**Developed with ❤️ using PHP & Bootstrap 5**

*Happy Coding!* 🚀
