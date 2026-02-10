-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 08:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir_fharaz`
--

-- --------------------------------------------------------

--
-- Table structure for table `detailpenjualan`
--

CREATE TABLE `detailpenjualan` (
  `DetailID` int(11) NOT NULL,
  `PenjualanID` int(11) NOT NULL,
  `ProdukID` int(11) NOT NULL,
  `JumlahProduk` int(11) NOT NULL,
  `HargaSatuan` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailpenjualan`
--

INSERT INTO `detailpenjualan` (`DetailID`, `PenjualanID`, `ProdukID`, `JumlahProduk`, `HargaSatuan`, `Subtotal`) VALUES
(1, 1, 2, 14, 4000.00, 56000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `PelangganID` int(11) NOT NULL,
  `NamaPelanggan` varchar(255) NOT NULL,
  `Alamat` text NOT NULL,
  `NomorTelepon` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `PenjualanID` int(11) NOT NULL,
  `KodeTransaksi` varchar(50) NOT NULL,
  `TanggalPenjualan` datetime NOT NULL DEFAULT current_timestamp(),
  `TotalHarga` decimal(10,2) NOT NULL,
  `JumlahBayar` decimal(10,2) NOT NULL,
  `Kembalian` decimal(10,2) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PelangganID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`PenjualanID`, `KodeTransaksi`, `TanggalPenjualan`, `TotalHarga`, `JumlahBayar`, `Kembalian`, `UserID`, `PelangganID`) VALUES
(1, 'TRX202602101919', '2026-02-10 09:12:40', 56000.00, 60000.00, 4000.00, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `ProdukID` int(11) NOT NULL,
  `KodeProduk` varchar(50) NOT NULL,
  `NamaProduk` varchar(255) NOT NULL,
  `Kategori` varchar(100) DEFAULT NULL,
  `Harga` decimal(10,2) NOT NULL,
  `Stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`ProdukID`, `KodeProduk`, `NamaProduk`, `Kategori`, `Harga`, `Stok`, `created_at`, `updated_at`) VALUES
(1, 'PRD001', 'Indomie Goreng', 'Makanan', 3500.00, 100, '2026-02-10 01:22:30', '2026-02-10 01:22:30'),
(2, 'PRD002', 'Aqua 600ml', 'Minuman', 99999999.99, 10, '2026-02-10 01:22:30', '2026-02-10 02:20:19'),
(3, 'PRD003', 'Teh Botol Sosro', 'Minuman', 5000.00, 10, '2026-02-10 01:22:30', '2026-02-10 02:16:46'),
(4, 'PRD004', 'Silverqueen Coklat', 'Makanan', 15000.00, 10, '2026-02-10 01:22:30', '2026-02-10 02:17:08'),
(5, 'PRD005', 'Oreo Biskuit', 'Makanan', 12000.00, 40, '2026-02-10 01:22:30', '2026-02-10 01:22:30'),
(6, 'PRD006', 'Susu Ultra 1L', 'Minuman', 18000.00, 25, '2026-02-10 01:22:30', '2026-02-10 01:22:30'),
(7, 'PRD007', 'Mie Sedaap Goreng', 'Makanan', 3500.00, 80, '2026-02-10 01:22:30', '2026-02-10 01:22:30'),
(8, 'PRD008', 'Coca Cola 500ml', 'Minuman', 6000.00, 10, '2026-02-10 01:22:30', '2026-02-10 02:15:55'),
(9, 'PRD009', 'Coklat valentine', 'Makanan', 99999999.99, 19, '2026-02-10 02:21:05', '2026-02-10 02:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `RiwayatID` int(11) NOT NULL,
  `ProdukID` int(11) NOT NULL,
  `Jenis` enum('masuk','keluar','penyesuaian') NOT NULL,
  `Jumlah` int(11) NOT NULL,
  `StokSebelum` int(11) NOT NULL,
  `StokSesudah` int(11) NOT NULL,
  `Keterangan` text DEFAULT NULL,
  `UserID` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`RiwayatID`, `ProdukID`, `Jenis`, `Jumlah`, `StokSebelum`, `StokSesudah`, `Keterangan`, `UserID`, `created_at`) VALUES
(1, 2, 'keluar', 49, 50, 1, 'menipis', 2, '2026-02-10 01:33:20'),
(2, 2, 'keluar', 14, 1, -13, 'Penjualan TRX202602101919', 1, '2026-02-10 02:12:40'),
(3, 2, 'masuk', 50, -13, 37, 'nambah 50 stok', 1, '2026-02-10 02:13:11'),
(4, 2, 'keluar', 17, 37, 20, '-', 1, '2026-02-10 02:15:12'),
(5, 2, 'keluar', 10, 20, 10, '0', 1, '2026-02-10 02:15:21'),
(6, 8, 'keluar', 50, 60, 10, '-', 1, '2026-02-10 02:15:55'),
(7, 3, 'masuk', 65, 75, 140, '-', 1, '2026-02-10 02:16:23'),
(8, 3, 'keluar', 130, 140, 10, '-', 1, '2026-02-10 02:16:46'),
(9, 4, 'keluar', 20, 30, 10, '-', 1, '2026-02-10 02:17:08'),
(10, 9, 'masuk', 15, 0, 15, 'Stok awal produk', 1, '2026-02-10 02:21:05'),
(11, 9, 'masuk', 4, 15, 19, '-', 1, '2026-02-10 02:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `NamaLengkap` varchar(255) NOT NULL,
  `Role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Username`, `Password`, `NamaLengkap`, `Role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin', '2026-02-10 01:22:30'),
(2, 'petugas1', '570c396b3fc856eceb8aa7357f32af1a', 'Petugas Satu', 'petugas', '2026-02-10 01:22:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD PRIMARY KEY (`DetailID`),
  ADD KEY `PenjualanID` (`PenjualanID`),
  ADD KEY `ProdukID` (`ProdukID`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`PelangganID`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`PenjualanID`),
  ADD UNIQUE KEY `KodeTransaksi` (`KodeTransaksi`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PelangganID` (`PelangganID`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`ProdukID`),
  ADD UNIQUE KEY `KodeProduk` (`KodeProduk`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`RiwayatID`),
  ADD KEY `ProdukID` (`ProdukID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  MODIFY `DetailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `PenjualanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `ProdukID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `RiwayatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD CONSTRAINT `detailpenjualan_ibfk_1` FOREIGN KEY (`PenjualanID`) REFERENCES `penjualan` (`PenjualanID`) ON DELETE CASCADE,
  ADD CONSTRAINT `detailpenjualan_ibfk_2` FOREIGN KEY (`ProdukID`) REFERENCES `produk` (`ProdukID`) ON DELETE CASCADE;

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `riwayat_stok_ibfk_1` FOREIGN KEY (`ProdukID`) REFERENCES `produk` (`ProdukID`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_stok_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
