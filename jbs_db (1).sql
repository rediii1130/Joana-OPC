-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 06:19 AM
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
-- Database: `jbs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `business_info`
--

CREATE TABLE `business_info` (
  `id` int(11) NOT NULL DEFAULT 1,
  `business_name` varchar(255) DEFAULT 'Joana''s Black Sheep OPC',
  `address` text DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email_recovery` varchar(255) DEFAULT NULL,
  `vat_percent` decimal(5,2) DEFAULT 12.00,
  `low_stock_threshold` int(11) DEFAULT 5,
  `receipt_footer` text DEFAULT NULL,
  `system_status` varchar(20) DEFAULT 'Online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_info`
--

INSERT INTO `business_info` (`id`, `business_name`, `address`, `contact_no`, `email_recovery`, `vat_percent`, `low_stock_threshold`, `receipt_footer`, `system_status`) VALUES
(1, 'Joana\'s Black Sheep OPC', 'Tanay, Rizal', '093608396932424hkh', 'joanablacksheep@email.com', 12.00, 10, 'Thank you for shopping!', 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `wholesale_price` decimal(10,2) DEFAULT 0.00,
  `pcs_per_box` int(11) DEFAULT 1,
  `stock` int(11) DEFAULT 0,
  `sold` int(11) DEFAULT 0,
  `price_wholesale` decimal(10,2) DEFAULT 0.00,
  `wholesale_qty` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `wholesale_price`, `pcs_per_box`, `stock`, `sold`, `price_wholesale`, `wholesale_qty`) VALUES
(1, 'KIKYAM PLATE (SILVER)', 'PAPER TRAY', 13.00, 0.00, 1, 797, 17, 250.00, 400),
(3, 'HOTDOG PLATE (WHITE)', 'PAPER TRAY', 14.00, 0.00, 1, 1196, 99, 260.00, 400),
(4, 'BOWL 220cc', 'Paper Bowl', 38.00, 0.00, 1, 10, 6, 730.00, 1000),
(5, 'HOTDOG PLATE (SILVER)', 'PAPER TRAY', 15.00, 0.00, 1, 1197, 6, 280.00, 400),
(6, 'KIKYAM PLATE (WHITE)', 'PAPER TSRY ', 12.00, 0.00, 1, 1594, 2, 230.00, 400),
(8, 'CUP 6OZ', 'Paper Cup', 30.00, 0.00, 1, 1005, 0, 490.00, 1000),
(9, 'CUP 8OZ', 'Paper Cup', 33.00, 0.00, 1, 1999, 0, 620.00, 1000),
(10, 'CUP 10oz', 'PAPER CUP', 28.00, 0.00, 1, 2980, 0, 660.00, 1000),
(11, 'CUP 12OZ', 'PAPER CUP', 45.00, 0.00, 1, 1000, 0, 790.00, 1000),
(12, 'CUP 16OZ', 'PAPER CUP', 65.00, 0.00, 1, 3982, 0, 1200.00, 1000),
(13, 'CUP 22OZ', 'PAPER CUP', 85.00, 0.00, 1, 1999, 0, 730.00, 1000),
(14, 'CUP 5OZ', 'PAPER CUP', 25.00, 0.00, 1, 2998, 0, 460.00, 1000),
(15, 'CUP 3OZ', 'PAPER CUP', 38.00, 0.00, 1, 5998, 0, 730.00, 2000),
(16, 'PAPER PLATE', 'PAPER WARES', 18.00, 0.00, 1, 0, 0, 330.00, 400),
(17, 'BOWL 260cc', 'PAPER BOWL', 47.00, 0.00, 1, 3964, 0, 820.00, 1000),
(18, 'BOWL 320CC', 'PAPER BOWL', 48.00, 0.00, 1, 3971, 0, 840.00, 1000),
(19, 'BOWL 390CC', 'PAPER BOWL', 55.00, 0.00, 1, 4981, 0, 1050.00, 1000),
(20, 'BOWL 520CC', 'PAPER BOWL', 65.00, 0.00, 1, 3978, 0, 1200.00, 500),
(21, 'FOOTLONG PLATE (SILVER)', 'PAPER TRAY', 15.00, 0.00, 1, 1199, 0, 280.00, 400),
(22, 'FOOTLONG PLATE (WHITE)', 'PAPER TRAY', 14.00, 0.00, 1, 1594, 0, 280.00, 400),
(26, 'grdfgdf', 'rgdfgd', 20.00, 0.00, 1, 10, 0, 50.00, 50);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `sales_person` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `total_amount`, `sales_person`, `created_at`) VALUES
(1, 'INV-7E1CEA', 200.00, NULL, '2026-01-03 10:54:02'),
(2, 'INV-4A5BCA', 50.00, NULL, '2026-01-03 11:06:54'),
(3, 'INV-043313', 350.00, NULL, '2026-01-03 13:38:13'),
(4, 'INV-A8D627', 320.00, NULL, '2026-01-03 14:09:16'),
(5, '#0005', 300.00, NULL, '2026-01-03 14:18:31'),
(6, '#0006', 120.00, NULL, '2026-01-03 14:27:27'),
(7, '#0007', 120.00, NULL, '2026-01-03 14:27:52'),
(8, '#0008', 200.00, NULL, '2026-01-03 14:31:33'),
(9, '#0009', 50.00, NULL, '2026-01-03 14:34:28'),
(10, '#0010', 200.00, NULL, '2026-01-03 14:45:49'),
(11, '#0011', 220.00, NULL, '2026-01-03 14:51:43'),
(12, '#0012', 200.00, NULL, '2026-01-03 14:55:44'),
(13, '#0013', 200.00, NULL, '2026-01-03 14:57:51'),
(14, '#0014', 120.00, NULL, '2026-01-03 15:21:47'),
(15, '#0015', 120.00, NULL, '2026-01-03 15:40:00'),
(16, '#0016', 580.00, NULL, '2026-01-04 04:07:32'),
(17, '#0017', 500.00, NULL, '2026-01-04 04:08:15'),
(18, '#0018', 200.00, NULL, '2026-01-04 04:18:39'),
(19, '#0019', 100.00, NULL, '2026-01-04 04:19:12'),
(20, '#0020', 4100.00, NULL, '2026-01-04 04:19:28'),
(21, '#0021', 100.00, NULL, '2026-01-04 04:21:44'),
(22, '#0022', 360.00, NULL, '2026-01-04 04:55:53'),
(23, '#0023', 290.00, NULL, '2026-01-04 04:57:52'),
(24, '#0024', 50.00, NULL, '2026-01-04 05:25:00'),
(25, '#0025', 400.00, NULL, '2026-01-04 08:58:33'),
(26, '#0026', 450.00, NULL, '2026-01-04 08:59:44'),
(27, '#0027', 450.00, NULL, '2026-01-04 12:19:47'),
(28, '#0028', 400.00, NULL, '2026-01-04 12:58:56'),
(29, '#0029', 400.00, NULL, '2026-01-04 13:00:51'),
(30, 'INV-00030', 500.00, 'admin', '2026-01-06 05:20:21'),
(31, 'INV-00031', 50.00, 'admin', '2026-01-06 05:20:35'),
(32, 'INV-00032', 150.00, 'admin', '2026-01-06 05:20:51'),
(33, 'INV-00033', 50.00, 'admin', '2026-01-06 05:51:22'),
(34, 'INV-00034', 50.00, 'admin', '2026-01-06 06:27:31'),
(35, 'INV-00035', 50.00, 'admin', '2026-01-06 06:51:28'),
(36, 'INV-00036', 30.00, 'admin', '2026-01-06 08:07:42'),
(37, 'INV-00037', 50.00, 'admin', '2026-01-06 08:18:38'),
(38, 'INV-00038', 450.00, 'admin', '2026-01-06 08:25:12'),
(39, 'INV-00039', 85.00, 'admin', '2026-01-06 09:50:38'),
(40, 'INV-00040', 400.00, 'admin', '2026-01-06 09:58:18'),
(41, 'INV-00041', 880.00, 'admin', '2026-01-06 09:59:43'),
(42, 'INV-00042', 50.00, 'admin', '2026-01-06 10:50:53'),
(43, 'INV-00043', 35.00, 'admin', '2026-01-06 11:02:25'),
(44, 'INV-00044', 50.00, 'admin', '2026-01-06 11:19:22'),
(45, 'INV-00045', 35.00, 'admin', '2026-01-06 11:24:25'),
(46, 'INV-00046', 180.00, 'admin', '2026-01-07 09:43:13'),
(47, 'INV-00047', 60.00, 'admin', '2026-01-10 05:04:28'),
(48, 'INV-00048', 230.00, 'admin', '2026-01-10 09:43:46'),
(49, 'INV-00049', 30.00, 'admin', '2026-01-10 10:40:27'),
(50, 'INV-00050', 50.00, 'admin', '2026-01-10 12:15:57'),
(51, 'INV-00051', 47.00, 'admin', '2026-01-24 11:16:56'),
(52, 'INV-00052', 95.00, 'admin', '2026-01-24 11:29:59'),
(53, 'INV-00053', 28.00, 'admin', '2026-01-24 11:33:45'),
(54, 'INV-00054', 240.00, 'admin', '2026-01-24 11:43:54'),
(55, 'INV-00055', 570.00, 'admin', '2026-01-24 11:47:35'),
(56, 'INV-00056', 55.00, 'admin', '2026-01-24 12:04:34'),
(57, 'INV-00057', 48.00, 'admin', '2026-01-24 12:14:22'),
(58, 'INV-00058', 65.00, 'admin', '2026-01-24 12:23:14'),
(59, 'INV-00059', 47.00, 'admin', '2026-01-24 12:46:04'),
(60, 'INV-00060', 242.00, 'admin', '2026-01-24 12:49:39'),
(61, 'INV-00061', 150.00, 'admin', '2026-01-24 12:55:43'),
(62, 'INV-00062', 28.00, 'admin', '2026-01-24 12:55:54'),
(63, 'INV-00063', 195.00, 'admin', '2026-01-24 13:01:28'),
(64, 'INV-00064', 178.00, 'admin', '2026-01-24 13:01:44'),
(65, 'INV-00065', 160.00, 'admin', '2026-01-24 13:11:20'),
(66, 'INV-00066', 278.00, 'admin', '2026-01-25 10:02:35'),
(67, 'INV-00067', 103.00, 'admin', '2026-01-25 10:10:07'),
(68, 'INV-00068', 261.00, 'cashier', '2026-01-26 05:22:27'),
(69, 'INV-00069', 177.00, 'cashier', '2026-01-26 05:27:57'),
(70, 'INV-00070', 42.00, 'admin', '2026-01-26 05:31:40'),
(71, 'INV-00071', 120.00, 'admin', '2026-01-26 05:35:49'),
(72, 'INV-00072', 45.00, 'admin', '2026-01-26 05:39:52'),
(73, 'INV-00073', 12.00, 'admin', '2026-01-26 05:42:02'),
(74, 'INV-00074', 47.00, 'admin', '2026-01-26 05:45:45'),
(75, 'INV-00075', 113.00, 'admin', '2026-01-26 05:51:42'),
(76, 'INV-00076', 93.00, 'admin', '2026-01-26 05:58:51'),
(77, 'INV-00077', 28.00, 'admin', '2026-01-26 06:02:00'),
(78, 'INV-00078', 325.00, 'admin', '2026-01-26 06:05:06'),
(79, 'INV-00079', 28.00, 'admin', '2026-01-26 06:09:40'),
(80, 'INV-00080', 100.00, 'admin', '2026-01-26 06:20:47'),
(81, 'INV-00081', 120.00, 'admin', '2026-01-26 06:31:36'),
(82, 'INV-00082', 65.00, 'admin', '2026-01-26 06:33:32'),
(83, 'INV-00083', 65.00, 'admin', '2026-01-26 06:37:39'),
(84, 'INV-00084', 95.00, 'admin', '2026-01-26 06:52:09'),
(85, 'INV-00085', 55.00, 'admin', '2026-01-26 06:56:33'),
(86, 'INV-00086', 77.00, 'admin', '2026-01-26 07:00:38'),
(87, 'INV-00087', 95.00, 'admin', '2026-01-26 07:09:03'),
(88, 'INV-00088', 93.00, 'admin', '2026-01-26 07:13:48'),
(89, 'INV-00089', 55.00, 'admin', '2026-01-26 08:35:10'),
(90, 'INV-00090', 65.00, 'admin', '2026-01-26 08:53:01'),
(91, 'INV-00091', 48.00, 'admin', '2026-01-26 09:10:46'),
(92, 'INV-00092', 28.00, 'admin', '2026-01-26 12:20:42'),
(93, 'INV-00093', 28.00, 'admin', '2026-01-26 12:46:30'),
(94, 'INV-00094', 12.00, 'admin', '2026-01-26 12:50:19'),
(95, 'INV-00095', 110.00, 'admin', '2026-01-26 12:54:43'),
(96, 'INV-00096', 47.00, 'admin', '2026-01-26 13:25:26'),
(97, 'INV-00097', 48.00, 'admin', '2026-01-26 13:26:50'),
(98, 'INV-00098', 65.00, 'admin', '2026-01-26 13:32:10'),
(99, 'INV-00099', 47.00, 'admin', '2026-01-26 13:48:38'),
(100, 'INV-00100', 130.00, 'admin', '2026-01-26 14:00:59'),
(101, 'INV-00101', 165.00, 'admin', '2026-01-26 14:05:07'),
(102, 'INV-00102', 189.00, 'admin', '2026-01-30 13:22:55'),
(103, 'INV-00103', 94.00, 'admin', '2026-01-30 13:43:05'),
(104, 'INV-00104', 178.00, 'admin', '2026-01-30 14:00:16'),
(105, 'INV-00105', 96.00, 'admin', '2026-02-05 14:33:22'),
(106, 'INV-00106', 1341.00, 'admin', '2026-02-05 14:41:50'),
(107, 'INV-00107', 96.00, 'cashier', '2026-02-05 14:54:53'),
(108, 'INV-00108', 94.00, 'admin', '2026-02-05 15:18:54'),
(109, 'INV-00109', 110.00, 'admin', '2026-02-05 15:22:29'),
(110, 'INV-00110', 94.00, 'admin', '2026-02-05 15:29:19'),
(111, 'INV-00111', 48.00, 'cashier', '2026-02-05 15:30:09'),
(112, 'INV-00112', 48.00, 'admin', '2026-02-05 16:14:19'),
(113, 'INV-00113', 65.00, 'admin', '2026-02-06 03:13:34'),
(114, 'INV-00114', 95.00, 'admin', '2026-02-06 03:38:35'),
(115, 'INV-00115', 47.00, 'admin', '2026-02-11 04:33:00'),
(116, 'INV-00116', 65.00, 'admin', '2026-02-13 11:39:13'),
(117, 'INV-00117', 94.00, 'admin', '2026-02-14 05:34:02'),
(118, 'INV-00118', 151.00, 'admin', '2026-02-14 05:55:22'),
(119, 'INV-00119', 47.00, 'admin', '2026-02-15 13:25:17'),
(120, 'INV-00120', 96.00, 'admin', '2026-02-15 13:29:52'),
(121, 'INV-00121', 110.00, 'admin', '2026-02-15 13:41:40'),
(122, 'INV-00122', 47.00, 'admin', '2026-02-15 13:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_at_sale` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `product_id`, `quantity`, `price_at_sale`) VALUES
(1, 1, 1, 1, 120.00),
(2, 1, 2, 1, 80.00),
(3, 2, 4, 1, 50.00),
(4, 3, 1, 1, 120.00),
(5, 3, 2, 1, 80.00),
(6, 3, 4, 3, 50.00),
(7, 4, 1, 1, 120.00),
(8, 4, 2, 1, 80.00),
(9, 4, 3, 1, 50.00),
(10, 4, 5, 1, 50.00),
(11, 4, 6, 1, 20.00),
(12, 5, 1, 1, 120.00),
(13, 5, 2, 1, 80.00),
(14, 5, 4, 1, 50.00),
(15, 5, 5, 1, 50.00),
(16, 6, 1, 1, 120.00),
(17, 7, 1, 1, 120.00),
(18, 8, 1, 1, 120.00),
(19, 8, 2, 1, 80.00),
(20, 9, 3, 1, 50.00),
(21, 10, 1, 1, 120.00),
(22, 10, 2, 1, 80.00),
(23, 11, 1, 1, 120.00),
(24, 11, 2, 1, 80.00),
(25, 11, 6, 1, 20.00),
(26, 12, 1, 1, 120.00),
(27, 12, 2, 1, 80.00),
(28, 13, 1, 1, 120.00),
(29, 13, 2, 1, 80.00),
(30, 14, 1, 1, 120.00),
(31, 15, 1, 1, 120.00),
(32, 16, 1, 4, 120.00),
(33, 16, 3, 2, 50.00),
(34, 17, 3, 10, 50.00),
(35, 18, 5, 4, 50.00),
(36, 19, 4, 1, 50.00),
(37, 19, 3, 1, 50.00),
(38, 20, 3, 82, 50.00),
(39, 21, 3, 2, 50.00),
(40, 22, 4, 1, 50.00),
(41, 22, 5, 2, 50.00),
(42, 22, 1, 1, 120.00),
(43, 22, 3, 1, 50.00),
(44, 22, 6, 2, 20.00),
(45, 23, 4, 1, 50.00),
(46, 23, 5, 1, 50.00),
(47, 23, 1, 1, 120.00),
(48, 23, 3, 1, 50.00),
(49, 23, 6, 1, 20.00),
(50, 24, 4, 1, 50.00),
(51, 25, 5, 1, 400.00),
(52, 26, 5, 1, 50.00),
(53, 26, 5, 1, 400.00),
(54, 27, 4, 1, 50.00),
(55, 27, 4, 1, 400.00),
(56, 28, 5, 1, 400.00),
(57, 29, 4, 1, 400.00),
(58, 30, 9, 1, 500.00),
(59, 31, 5, 1, 50.00),
(60, 32, 8, 5, 30.00),
(61, 33, 5, 1, 50.00),
(62, 34, 5, 1, 50.00),
(63, 35, 5, 1, 50.00),
(64, 36, 8, 1, 30.00),
(65, 37, 4, 1, 50.00),
(66, 38, 5, 1, 400.00),
(67, 38, 5, 1, 50.00),
(68, 39, 5, 1, 50.00),
(69, 39, 9, 1, 35.00),
(70, 40, 5, 1, 400.00),
(71, 41, 4, 1, 50.00),
(72, 41, 5, 2, 400.00),
(73, 41, 8, 1, 30.00),
(74, 42, 5, 1, 50.00),
(75, 43, 9, 1, 35.00),
(76, 44, 10, 1, 50.00),
(77, 45, 9, 1, 35.00),
(78, 46, 8, 6, 30.00),
(79, 47, 8, 2, 30.00),
(80, 48, 5, 1, 50.00),
(81, 48, 8, 6, 30.00),
(82, 49, 8, 1, 30.00),
(83, 50, 5, 1, 50.00),
(84, 51, 17, 1, 47.00),
(85, 52, 18, 1, 48.00),
(86, 52, 17, 1, 47.00),
(87, 53, 10, 1, 28.00),
(88, 54, 18, 5, 48.00),
(89, 55, 17, 1, 47.00),
(90, 55, 10, 1, 28.00),
(91, 55, 12, 5, 65.00),
(92, 55, 13, 2, 85.00),
(93, 56, 19, 1, 55.00),
(94, 57, 18, 1, 48.00),
(95, 58, 20, 1, 65.00),
(96, 59, 17, 1, 47.00),
(97, 60, 17, 1, 47.00),
(98, 60, 20, 3, 65.00),
(99, 61, 12, 1, 65.00),
(100, 61, 13, 1, 85.00),
(101, 62, 10, 1, 28.00),
(102, 63, 12, 3, 65.00),
(103, 64, 17, 1, 47.00),
(104, 64, 18, 1, 48.00),
(105, 64, 19, 1, 55.00),
(106, 64, 10, 1, 28.00),
(107, 65, 17, 1, 47.00),
(108, 65, 18, 1, 48.00),
(109, 65, 20, 1, 65.00),
(110, 66, 20, 1, 65.00),
(111, 66, 19, 1, 55.00),
(112, 66, 18, 1, 48.00),
(113, 66, 11, 1, 45.00),
(114, 66, 12, 1, 65.00),
(115, 67, 18, 1, 48.00),
(116, 67, 19, 1, 55.00),
(117, 68, 17, 4, 47.00),
(118, 68, 10, 1, 28.00),
(119, 68, 11, 1, 45.00),
(120, 69, 10, 3, 28.00),
(121, 69, 14, 1, 25.00),
(122, 69, 15, 1, 38.00),
(123, 69, 8, 1, 30.00),
(124, 70, 1, 1, 13.00),
(125, 70, 3, 1, 14.00),
(126, 70, 5, 1, 15.00),
(127, 71, 19, 1, 55.00),
(128, 71, 20, 1, 65.00),
(129, 72, 11, 1, 45.00),
(130, 73, 6, 1, 12.00),
(131, 74, 17, 1, 47.00),
(132, 75, 18, 1, 48.00),
(133, 75, 20, 1, 65.00),
(134, 76, 10, 1, 28.00),
(135, 76, 12, 1, 65.00),
(136, 77, 10, 1, 28.00),
(137, 78, 12, 5, 65.00),
(138, 79, 10, 1, 28.00),
(139, 80, 3, 1, 14.00),
(140, 80, 5, 2, 15.00),
(141, 80, 22, 4, 14.00),
(142, 81, 19, 1, 55.00),
(143, 81, 20, 1, 65.00),
(144, 82, 20, 1, 65.00),
(145, 83, 20, 1, 65.00),
(146, 84, 17, 1, 47.00),
(147, 84, 18, 1, 48.00),
(148, 85, 19, 1, 55.00),
(149, 86, 3, 1, 14.00),
(150, 86, 1, 1, 13.00),
(151, 86, 6, 3, 12.00),
(152, 86, 22, 1, 14.00),
(153, 87, 17, 1, 47.00),
(154, 87, 18, 1, 48.00),
(155, 88, 20, 1, 65.00),
(156, 88, 10, 1, 28.00),
(157, 89, 19, 1, 55.00),
(158, 90, 20, 1, 65.00),
(159, 91, 18, 1, 48.00),
(160, 92, 10, 1, 28.00),
(161, 93, 10, 1, 28.00),
(162, 94, 6, 1, 12.00),
(163, 95, 19, 2, 55.00),
(164, 96, 17, 1, 47.00),
(165, 97, 18, 1, 48.00),
(166, 98, 20, 1, 65.00),
(167, 99, 17, 1, 47.00),
(168, 100, 12, 2, 65.00),
(169, 101, 19, 3, 55.00),
(170, 102, 17, 3, 47.00),
(171, 102, 18, 1, 48.00),
(172, 103, 17, 2, 47.00),
(173, 104, 18, 1, 48.00),
(174, 104, 20, 2, 65.00),
(175, 105, 18, 2, 48.00),
(176, 106, 17, 3, 47.00),
(177, 106, 12, 1, 1200.00),
(178, 107, 18, 2, 48.00),
(179, 108, 17, 2, 47.00),
(180, 109, 19, 2, 55.00),
(181, 110, 17, 2, 47.00),
(182, 111, 18, 1, 48.00),
(183, 112, 18, 1, 48.00),
(184, 113, 20, 1, 65.00),
(185, 114, 17, 1, 47.00),
(186, 114, 18, 1, 48.00),
(187, 115, 17, 1, 47.00),
(188, 116, 20, 1, 65.00),
(189, 117, 17, 2, 47.00),
(190, 118, 18, 2, 48.00),
(191, 118, 19, 1, 55.00),
(192, 119, 17, 1, 47.00),
(193, 120, 18, 2, 48.00),
(194, 121, 19, 2, 55.00),
(195, 122, 17, 1, 47.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','inventory checker') DEFAULT 'cashier',
  `profile_pic` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`, `profile_pic`, `email`, `birthday`, `age`, `reset_token`, `reset_expiry`, `reset_code`, `token_expiry`) VALUES
(4, NULL, 'cashier', '$2y$10$kMLOZVggHSSDwddEJ6PKeODJkXI5pklkYiYghifClBKwNyTDK4yCK', 'cashier', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL),
(5, NULL, 'admin', '$2y$10$8HuJQBs7rS7jyCeeGFDwNOHrv/lBPDIVn8iaeaEYcS8NnaY.gzqAi', 'admin', NULL, 'deleonkeyt71@gmail.com', NULL, NULL, '364305', NULL, NULL, '2026-02-15 22:12:28'),
(7, NULL, 'inventory checker', '$2y$10$p.vtEudJWm9MUuR6s7aDwun68/lXWYKPTIYbWziLvgLTm2OufLzcq', 'inventory checker', NULL, '', NULL, NULL, '885553', NULL, NULL, '2026-02-12 15:35:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `business_info`
--
ALTER TABLE `business_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
