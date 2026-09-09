-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2026 at 10:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mwi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `admin_role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `user_id`, `admin_role`, `status`, `created_at`, `updated_at`) VALUES
(1, 23, 'super_admin', 'active', '2026-09-02 01:04:40', '2026-09-02 01:04:40');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sessions`
--

CREATE TABLE `auth_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_sessions`
--

INSERT INTO `auth_sessions` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `created_at`, `last_used_at`) VALUES
(1, 1, 'c3aa0f4078b48a58d3e14249968f06a3c60accf306a9edba1c819d1b23c4cc3b', '2026-09-04 22:10:32', NULL, '2026-08-29 01:40:32', NULL),
(2, 2, '34be17fe9dd5ae24e60153ffc01315d8896150c4f249313f3dc3e1e460ae56e7', '2026-09-04 22:12:49', NULL, '2026-08-29 01:42:49', NULL),
(3, 2, 'b22a1e9532bd177198151564ea64ff3f224f69288e7982179bf7eaa87db9965a', '2026-09-04 22:27:19', NULL, '2026-08-29 01:57:19', NULL),
(4, 2, '388e11beb6d4492ec1d4248c6b77aaa20a85e0a9483a36af4a298ddf08bbeaa9', '2026-09-04 22:32:35', NULL, '2026-08-29 02:02:35', NULL),
(5, 3, '3b9651176130d8c096821142e62281c1e4111b3a892c661e2f6aa9a7ba5ae527', '2026-09-05 16:52:46', NULL, '2026-08-29 20:22:46', NULL),
(6, 4, '3a3ecc88c49d370c25a553614f129f4329b357b8274e291cf35e714c1733fc2a', '2026-09-05 16:58:42', NULL, '2026-08-29 20:28:42', NULL),
(7, 5, '064048e974dd41968fecb3e454eb7506bc1b75248925bcc73c703120c7392ab8', '2026-09-05 17:02:03', NULL, '2026-08-29 20:32:03', NULL),
(8, 6, 'ca91234bb4c339a1fdddbbfbe01e3c42c541410f0a519c56cc2516eab112cb7e', '2026-09-05 17:07:53', NULL, '2026-08-29 20:37:53', NULL),
(9, 7, 'c152244037a3af9dcfe3e8e66a043361838e9c500923dbff9c9838fbf8a50cf2', '2026-09-05 17:50:31', NULL, '2026-08-29 21:20:31', NULL),
(10, 8, 'b20e388b521ba73d48c8cc146b58a0029a8a09ec202ca7d863da92a67e149575', '2026-09-05 20:19:37', NULL, '2026-08-29 23:49:37', NULL),
(11, 9, '85fbd49901135132fe614d85d4bf1e249e768c8154e137bb41fcd803aeffe473', '2026-09-05 20:48:56', NULL, '2026-08-30 00:18:56', NULL),
(12, 10, 'f457b5a68965dbedc7b2d4912a65d30717514202ac9ef0d963a3349c2dd1ef91', '2026-09-05 21:05:07', NULL, '2026-08-30 00:35:07', '2026-08-30 00:39:32'),
(13, 11, 'b164de9137c5dc928d356ec5589f86998603a2ef6b2b15ba4b56e351fa7d7dab', '2026-09-05 23:00:34', NULL, '2026-08-30 02:30:34', '2026-08-30 02:31:25'),
(14, 12, '6ae22cb5b03fd6ebe53d0903c5ac3454d4719b348291c3b009e67a59a23ba91c', '2026-09-05 23:09:29', NULL, '2026-08-30 02:39:29', '2026-08-30 02:40:13'),
(15, 13, 'c4f016fddb02a2efcbae1b3abf4787b49d7d7ec49c98e744407224d496e87907', '2026-09-06 12:34:13', NULL, '2026-08-30 16:04:13', '2026-08-30 16:04:56'),
(16, 14, 'adf8db5c220fdffb084f8b5b9680a48fcc6125bb2b2dfb13ebb609ad4e896b64', '2026-09-06 12:44:54', NULL, '2026-08-30 16:14:54', '2026-08-30 16:19:21'),
(17, 15, '83f52086f482f4b2027e731de46d7d20c9917b7b481b5d7df38ebf769ab799ed', '2026-09-06 14:19:08', NULL, '2026-08-30 17:49:08', '2026-08-30 18:03:08'),
(18, 16, '4e85a105d52bf014ff4445c8c87a90bb999f6d210b17cdad585607be60aa67fe', '2026-09-06 14:33:54', NULL, '2026-08-30 18:03:54', '2026-08-30 18:15:32'),
(19, 17, '18f8d9147b74e9fdbcb29e851d37d8c152e3b8e095f946c31439eb314531e0e0', '2026-09-06 14:46:43', NULL, '2026-08-30 18:16:43', '2026-08-30 18:28:03'),
(20, 18, 'a59bdfb27d1bfde39c2701c3eddf63ec79d91ae32a398aed23c99315f3e9d3fa', '2026-09-06 17:15:58', NULL, '2026-08-30 20:45:58', '2026-08-30 22:54:41'),
(21, 17, 'fb6d138c90785928294bee2cdeadac6bebc98fc4fd00251e14a391ad4e03523e', '2026-09-06 19:25:47', NULL, '2026-08-30 22:55:47', '2026-08-30 23:24:45'),
(22, 19, 'b4a68ea7c2b54651acbd4851d540ec694dc708b84ce6a8ad75c5b1369dc72fdb', '2026-09-06 20:01:26', NULL, '2026-08-30 23:31:26', '2026-08-31 00:10:38'),
(23, 17, 'bfff13b0819643ba15426c6a50e1c72a0b78f2cc9ee33a0e3f6acf8025fa142c', '2026-09-06 20:41:03', NULL, '2026-08-31 00:11:03', '2026-08-31 01:18:43'),
(24, 17, '1f4789e7080f744ae28e2319bd8075a3ac816a14c0469620681601dcd1b7ce14', '2026-09-06 21:50:29', NULL, '2026-08-31 01:20:29', '2026-08-31 02:42:47'),
(25, 17, 'ed25e1b3f2334c4aaaca9171950584466a4befeb73dd424c265b98e211bf89b0', '2026-09-07 00:14:50', NULL, '2026-08-31 03:44:50', '2026-08-31 04:00:30'),
(26, 17, '77fd953e63230d5b28856e357db3c35aa263a5c3923b92a87138866201c29aa8', '2026-09-07 00:31:48', NULL, '2026-08-31 04:01:48', '2026-08-31 04:01:48'),
(27, 17, '29de1c7169b2344c832ca5fa1421af1cb714881093b32f8d5f10e5e83d977b08', '2026-09-07 00:35:47', NULL, '2026-08-31 04:05:47', '2026-08-31 05:14:48'),
(28, 20, '54c4448cc5209ea8ae4bf28baf4d58b1a38c6f121ccc67d4fdccb7dc8d41f722', '2026-09-07 01:45:27', NULL, '2026-08-31 05:15:27', '2026-08-31 22:00:06'),
(29, 21, 'df232503c8bdc6e7cfce9553fd2e51bda550116cb62ff2f106303df4b5e43b78', '2026-09-07 19:36:05', NULL, '2026-08-31 23:06:05', '2026-08-31 23:31:13'),
(30, 17, '9d7b5d78afaa275ab2f0515bfda9c3146349323c712958dceae83b2e8e18ebde', '2026-09-07 20:02:19', NULL, '2026-08-31 23:32:19', '2026-08-31 23:32:19'),
(31, 14, 'ab4575846ad9d67d2c1e62dd458ac7ef49839a2b3fce58dd75a6bc96a694b95b', '2026-09-07 20:04:48', NULL, '2026-08-31 23:34:48', '2026-08-31 23:36:53'),
(32, 22, 'f2a52f72d6572fab8eb43682b55d88bb1347e15922f2caf1c8b08204c6566954', '2026-09-07 20:08:54', NULL, '2026-08-31 23:38:54', '2026-08-31 23:39:36'),
(33, 22, 'd1e49a9b1fe08b0ea453a8c05e2a28dcbd847b3da29be041ea03086535acde71', '2026-09-07 20:13:19', NULL, '2026-08-31 23:43:19', '2026-09-01 09:51:37'),
(34, 23, 'ad2fa0664f48361fab74fc2ec03b03d9e4969bcc87b3cd1210c558737c5b42eb', '2026-09-08 21:58:39', '2026-09-02 01:29:20', '2026-09-02 01:28:39', '2026-09-02 01:29:20'),
(35, 23, '9b06bfbf6a24a4e8ba37165c152112a0d849e72e78bae38ecf58ce657e06a7dc', '2026-09-08 22:03:53', '2026-09-02 01:33:58', '2026-09-02 01:33:53', '2026-09-02 01:33:58'),
(36, 23, '13be6b15d54946757accdcfd6b88eb5f08679b9599e362dcb21713a4f3777e09', '2026-09-08 22:04:22', NULL, '2026-09-02 01:34:22', '2026-09-02 02:26:45'),
(37, 22, '8619bf431fc149b24df7c2bef40dfe617d2daae8291709d45de3d07536038a3c', '2026-09-08 22:57:05', NULL, '2026-09-02 02:27:05', '2026-09-02 02:27:34'),
(38, 23, 'a5131fed1e5931fd74810ad1ab7b9072aef5e5b03f2eb47c5a50d25de4d56b78', '2026-09-08 22:57:30', NULL, '2026-09-02 02:27:30', NULL),
(39, 23, 'eef60ea0eeb25d9c4321fc54530c1d216af8ae411e831ad2555b5263e4650374', '2026-09-08 22:57:34', NULL, '2026-09-02 02:27:34', NULL),
(40, 23, 'ba6106eacae734827bcdf1d1145fee532e605ab1020f5a1e97c60704df8b3880', '2026-09-08 22:57:45', '2026-09-02 02:28:29', '2026-09-02 02:27:45', '2026-09-02 02:28:29'),
(41, 22, '0b51afc155661a7f2394a1d9337d5c61ef611dc9cbf8e9c628e71b4b6fe5d5c4', '2026-09-08 22:58:56', NULL, '2026-09-02 02:28:56', '2026-09-02 02:51:12'),
(42, 23, 'a982579b80a25d824c055cdd39cdc54ee477fabe7e6376f289088b43bfbc5b17', '2026-09-08 23:21:38', '2026-09-02 02:52:05', '2026-09-02 02:51:38', '2026-09-02 02:52:05'),
(43, 22, '213b43d2a7cf5073ab2dc68fa5923d2247f8543c86c0859bb96c4303f6675724', '2026-09-08 23:22:31', NULL, '2026-09-02 02:52:31', '2026-09-02 22:05:17'),
(44, 17, '350cdc3556074b36ebea9ede13915b5d93872fc11634a14f6984029356c0a74a', '2026-09-09 17:40:20', NULL, '2026-09-02 21:10:20', '2026-09-02 21:10:20'),
(45, 23, '33063a36d6466aff10099a4f06ea82717c7669ac6153eb483f531bb45f74e5ca', '2026-09-09 17:42:59', '2026-09-02 22:31:03', '2026-09-02 21:12:59', '2026-09-02 22:31:03'),
(46, 21, '9e4fd0393cad47e3028ccf3285affb2d63605c825a44d439cebaa3fc8abeca5a', '2026-09-09 18:37:23', NULL, '2026-09-02 22:07:23', '2026-09-03 00:57:54'),
(47, 23, '0a5cda7a89a29824f1dda12e8d3831cb206d19908caccc4874bc391cb8a757b1', '2026-09-09 19:01:13', '2026-09-02 22:31:24', '2026-09-02 22:31:13', '2026-09-02 22:31:24'),
(48, 22, '4961b34397ac375eeb2a1c2d117f9ed6a66f8f7bb1f658abb2994857814fa625', '2026-09-09 19:01:35', NULL, '2026-09-02 22:31:35', '2026-09-02 23:49:06'),
(49, 24, '0c17cbff0979b6cce1308a377e16435609f2c803dd3e04e0d6fb07ec6ef94052', '2026-09-09 19:03:40', NULL, '2026-09-02 22:33:40', '2026-09-02 22:51:45'),
(50, 23, '3a084d71c02c1183fecf7d9db92b066fc52b2632553aacb420daa45b46c71117', '2026-09-09 19:06:34', NULL, '2026-09-02 22:36:34', '2026-09-02 22:37:03'),
(51, 25, '8cb61eae761381925ae6385904caaaf9f39139e6dd664661d58b9f32d83ac889', '2026-09-09 19:23:00', NULL, '2026-09-02 22:53:00', '2026-09-02 22:53:37'),
(52, 17, '47e04bce0f9e1a21fffaeeceabf71222992ad500d12ebb372b370c60a832dc7a', '2026-09-09 21:58:43', NULL, '2026-09-03 01:28:43', '2026-09-03 01:28:53'),
(53, 26, '6c6302b920fdc3150e943785304475c662ae9e20413fc91cffd4abc5d86d79f7', '2026-09-09 22:00:13', NULL, '2026-09-03 01:30:13', '2026-09-03 01:44:37'),
(54, 27, 'f14a577185842cdb72510172a3f1e84b05d453973b39c3e89f74ea46a684ccf5', '2026-09-09 22:16:42', NULL, '2026-09-03 01:46:42', '2026-09-03 03:15:24'),
(55, 28, '40af9ff83042a907843638b950f1a294e817f89ca02627c20bc0dbe695e286ce', '2026-09-10 17:42:34', NULL, '2026-09-03 21:12:34', '2026-09-03 22:08:57'),
(56, 23, '514bd37d12ac10b5ad20525e106bb25a4ac9c785455ae33150ca5e2bb0353759', '2026-09-10 17:44:26', NULL, '2026-09-03 21:14:26', '2026-09-03 22:12:25'),
(57, 29, '85ba17b688288ab3e7167f2196cd9f2e32f775cd1d1bcaba8b7976c878505730', '2026-09-10 18:44:01', NULL, '2026-09-03 22:14:01', '2026-09-04 00:50:46'),
(58, 29, '597594a037bc37ff826f2895f94549de1ed6aba25c1097728a6f44f3e2bae060', '2026-09-10 21:21:03', NULL, '2026-09-04 00:51:03', '2026-09-04 00:52:10'),
(59, 30, '872e3d001473ea75b2e82eebecf7f87bc3526bcb764be8ee887f60909357d573', '2026-09-10 21:22:53', NULL, '2026-09-04 00:52:53', '2026-09-04 02:01:36'),
(60, 23, '5d7634adaf72823c7b247efa3f809d6819911bd077e448069bd83bac085e5429', '2026-09-10 22:30:41', NULL, '2026-09-04 02:00:41', NULL),
(61, 23, 'c2cb9fc4d594d39e8e8732094b27cea9b99df57095ffc9776e81f1dedd9598ad', '2026-09-10 22:30:43', NULL, '2026-09-04 02:00:43', NULL),
(62, 17, '058780f938acdf45bea2da61d141a462f55ff5ad5f8d1910594898e033cabc05', '2026-09-10 22:37:05', NULL, '2026-09-04 02:07:05', '2026-09-04 02:09:42'),
(63, 23, 'f947ea49fb21e3fb00fe851d10f71f862558a203020906d581402f811ed0123f', '2026-09-10 22:37:30', NULL, '2026-09-04 02:07:30', NULL),
(64, 23, 'ef716d015b420df690196ba831d7a9c3d740f94ccb22bf18448a306a71ca79f3', '2026-09-10 22:38:41', NULL, '2026-09-04 02:08:41', NULL),
(65, 23, 'aca6bafe28c64d109d70b75373a8408c864a03ed9c0dcf92ca66fa1d1a6c1f3b', '2026-09-10 22:39:21', NULL, '2026-09-04 02:09:21', NULL),
(66, 31, '47c03ff3d057f15e0eaa64188b2aaa7fabb6d4038317fd8f92b733221398b51f', '2026-09-10 22:40:12', NULL, '2026-09-04 02:10:12', '2026-09-04 02:15:25'),
(67, 23, '28e518624cc28da4fbad768341dbfde0b8d714dc2fcb7fa3f06fc87afacdc2b9', '2026-09-10 22:45:44', NULL, '2026-09-04 02:15:44', '2026-09-04 02:21:23'),
(68, 29, '7aac43330866b36ee1bc5a6f83907566ebe2e8a2e704f6ad62943c3786f4b6c9', '2026-09-10 22:46:47', NULL, '2026-09-04 02:16:47', '2026-09-04 02:25:11'),
(69, 17, '1ccbdf8da05805198dda27b91ab3b03baf80351d40effdff2f2ca00ef4c09931', '2026-09-12 19:30:17', NULL, '2026-09-05 23:00:17', '2026-09-05 23:00:27'),
(70, 17, '06946c73c48aaecac6107925b0012833b9d385d250511433957d12949a639ec8', '2026-09-12 19:44:55', NULL, '2026-09-05 23:14:55', '2026-09-05 23:14:56'),
(71, 32, 'e35998b474d876f06cc937c15156f36af55eb6ca43455713d619b7feba9fa126', '2026-09-12 19:48:39', NULL, '2026-09-05 23:18:39', '2026-09-05 23:20:13'),
(72, 33, 'd0486ffde281c0fd49b110f85b2d4e4c1eb585ef62448cef32cb98fa43d8074f', '2026-09-12 19:57:50', NULL, '2026-09-05 23:27:50', '2026-09-05 23:29:33'),
(73, 34, 'd7a537526e286b11380683a914ae5be466f55bd34da70b94e179e4d8dd7b1c53', '2026-09-12 20:11:58', NULL, '2026-09-05 23:41:58', '2026-09-05 23:53:41'),
(74, 35, 'eaad3371b2f7a4ad7bec692d105ca1adbcf2b1583356a12d188378e0bda5b80e', '2026-09-12 20:24:20', NULL, '2026-09-05 23:54:20', '2026-09-06 00:07:57'),
(75, 36, 'a5cf0d597c77fbf95f9527afc8ba40afe9308124ee9a7810b17990f5c6850dd8', '2026-09-12 20:38:45', NULL, '2026-09-06 00:08:45', '2026-09-06 00:35:49'),
(76, 37, 'aa2e6b21b614915a037895ef59eeb981da9021c0a7ab89c305a3f593b1ddd150', '2026-09-12 21:06:59', NULL, '2026-09-06 00:36:59', '2026-09-06 00:37:40'),
(77, 38, '14e04d3c6189bcf239d759be1ae20acece187bae2efb2e1f348785ec20889bc5', '2026-09-12 21:08:13', NULL, '2026-09-06 00:38:13', '2026-09-06 00:38:47'),
(78, 39, 'dd19d53e3670afdfc351c4da1f44b4f2358de7e74709b8d8fc865a63f6c645a4', '2026-09-12 21:11:12', NULL, '2026-09-06 00:41:12', '2026-09-06 00:47:11'),
(79, 40, 'fdd5c1412b0e953913a2f1e57d4c466fb509ee9d92efab81f6ae5c7dfd7aab21', '2026-09-12 21:17:48', NULL, '2026-09-06 00:47:48', '2026-09-06 01:19:58'),
(80, 40, '6fb42a36621ed86261cc5b7d4423c2eb90584fdaed1f333aac620489c0ba8018', '2026-09-12 21:50:11', NULL, '2026-09-06 01:20:11', '2026-09-06 01:34:01'),
(81, 41, '021f7d75c7dc0a513318bd9fc4d3b28ab59eb290986f313f957e5985ebe7dbf9', '2026-09-12 22:04:50', NULL, '2026-09-06 01:34:50', '2026-09-06 03:00:13'),
(82, 42, '08c96172de86981df711f3b2549333eabc9e1664f11813cb7f06b99c582f51ff', '2026-09-12 23:31:17', '2026-09-06 23:12:20', '2026-09-06 03:01:17', '2026-09-06 23:12:20'),
(83, 43, '1f3dcc2f05ee8b636f72d6b734e767554d47e9fa8697aa4f9b7a1e4f61766646', '2026-09-13 05:24:07', NULL, '2026-09-06 08:54:07', '2026-09-06 09:07:30'),
(84, 23, '4b51df76d7fedc4701bdb002ac7a6036e5bf81681be7bcba1ffca865bd611f7c', '2026-09-13 05:38:21', '2026-09-06 13:15:06', '2026-09-06 09:08:21', '2026-09-06 13:15:06'),
(85, 44, '5a4e10c25aa01ef4edcf14b16562c4807a7058c6bd825d15e064fbd3cec679a0', '2026-09-13 10:33:26', NULL, '2026-09-06 14:03:26', '2026-09-06 14:31:08'),
(86, 23, '347bdedd6dd38628bc459c151cb4c29061a08d93b63bd64bbec29a10db3defe1', '2026-09-13 11:02:45', NULL, '2026-09-06 14:32:45', '2026-09-07 01:18:32'),
(87, 41, 'a3f680ddbcfefd3677a439407c2263ed84e4bb52942b86e2203700dd8c78b347', '2026-09-13 11:08:38', NULL, '2026-09-06 14:38:38', '2026-09-06 14:49:50'),
(88, 29, 'f70908c28ef488909c7e4b949a93ecbd425a008d419c3ed3d28a9a0721061af5', '2026-09-13 11:21:15', NULL, '2026-09-06 14:51:15', '2026-09-06 15:30:48'),
(89, 24, '6d44292364c1b605885083591a7ea9330441e8a0994487f12a8528be8e941c33', '2026-09-13 12:04:13', NULL, '2026-09-06 15:34:13', '2026-09-06 15:34:16'),
(90, 45, 'e4308ece0d12ac5c8b806e1e8bf4a4395043880c8eba94974b98f5fb0242a321', '2026-09-13 12:07:29', NULL, '2026-09-06 15:37:29', '2026-09-06 16:23:14'),
(91, 46, '896a9a39e5e9ce52d49b7e00f96f33726d8aee383f388841c0905a12b8db9e38', '2026-09-13 12:54:22', NULL, '2026-09-06 16:24:22', '2026-09-06 23:07:39'),
(92, 42, '813bb2de9664f113195a3bd13ee4f7e387d8e5f3a0d44652bc211cb4e14fa447', '2026-09-13 19:42:38', NULL, '2026-09-06 23:12:38', '2026-09-07 01:21:57'),
(93, 44, 'a23374a596da088b679eca1f6f9ab2e7e9530898dd79f95dc469bdb5f0c4868a', '2026-09-13 20:15:50', NULL, '2026-09-06 23:45:50', '2026-09-06 23:49:51'),
(94, 46, '17ec27db4fd56208515dc49cffd667014a47a37a3d121b96c6462d7daa5c8b7a', '2026-09-13 20:19:03', NULL, '2026-09-06 23:49:03', '2026-09-06 23:49:35'),
(95, 47, 'c117babe27aec2d1c01f887d0f2b1b24d0b67055b8c1ce3fb47be7bbc25bc0af', '2026-09-13 20:20:28', NULL, '2026-09-06 23:50:28', '2026-09-07 00:03:28'),
(96, 48, '9859fffccc5380e013e2e36f3b697df0cc7af3f0c602cbf062b58bd4904bd658', '2026-09-13 20:23:29', NULL, '2026-09-06 23:53:29', '2026-09-07 00:09:07'),
(97, 46, 'c3dcd8d25cf9daddd86c78c0510df6188a58c0982479d7601c202fb839610b7a', '2026-09-13 20:44:23', NULL, '2026-09-07 00:14:23', '2026-09-07 00:14:34'),
(98, 49, '31d545f753a7eedd2386b8fa5a2dbe8aae4decfbf743a30a4435e4ff09cf58f2', '2026-09-13 21:20:20', NULL, '2026-09-07 00:50:20', '2026-09-07 01:18:33'),
(99, 23, 'ae0d38dcbb9208d045dab0e69d5d6d579c21c59cccee7a7315a9422fe27343c7', '2026-09-13 21:57:21', NULL, '2026-09-07 01:27:21', '2026-09-07 01:27:23'),
(100, 42, 'b4752273634469bf28710295302ea77b2868172abed9ffce00719cf8e178ad2d', '2026-09-13 21:57:45', NULL, '2026-09-07 01:27:45', '2026-09-07 01:31:26'),
(101, 42, '67e61a3474e939d6a5e5b557b3cff39a00b03549c60e7d19dcdb6582898bf77d', '2026-09-13 22:01:48', NULL, '2026-09-07 01:31:48', '2026-09-07 01:37:07'),
(102, 23, 'c851b22ff98c8457c0ac381aa9a0cb5fec3bb7e9416ee9d65bc2ad15c709e3af', '2026-09-14 17:57:52', '2026-09-07 21:39:46', '2026-09-07 21:27:52', '2026-09-07 21:39:46'),
(103, 42, '4f2ec82c62e8742c17b77a5ea26816fd7a0facd11cc1d0e7c93ce7ed87c988ea', '2026-09-14 18:09:59', NULL, '2026-09-07 21:39:59', '2026-09-08 00:26:18'),
(104, 50, 'ed43b166cf8c2498d4202a1b74a740bff629f60af55999081e3ae3e91b9a597a', '2026-09-14 21:00:35', NULL, '2026-09-08 00:30:35', '2026-09-08 00:32:06'),
(105, 51, '69f7b9c1a033b733faac6074c5514dce82a5a15de9a6af71281b86046066a7bb', '2026-09-15 20:21:30', NULL, '2026-09-08 23:51:30', '2026-09-08 23:54:17'),
(107, 23, 'ed85030b7512b06981dad8b18ab716d1a63a4d337f3177bdf956a2dba6c54d7f', '2026-09-15 20:45:27', '2026-09-09 00:15:45', '2026-09-09 00:15:27', '2026-09-09 00:15:45'),
(108, 34, 'ff29dc8d85fd3217918736e4e385169f70f1b24d14acc47c794bb7798b3701b8', '2026-09-15 20:45:59', NULL, '2026-09-09 00:15:59', '2026-09-09 00:29:55'),
(109, 23, '7bb4a3a65916e99569ce808956e380f9e9423678b0ae497dda3dfb9ca4ef1f3a', '2026-09-15 20:49:49', NULL, '2026-09-09 00:19:49', '2026-09-09 00:46:17'),
(110, 53, '21efc935c154828496bd33eb591d2fa4ba2ab54b2c4d79b5988602cc92e43b25', '2026-09-16 18:37:34', NULL, '2026-09-09 22:07:34', '2026-09-09 22:08:11'),
(112, 23, 'acc3614e6194dae9155b0c5cb455f3fb60c4f4c40b0541c00bbe9eb3ece7e626', '2026-09-16 19:01:05', NULL, '2026-09-09 22:31:05', '2026-09-10 00:39:14'),
(113, 48, 'adb689b3e67272f40a83b5ff8d9bb31bafd4456bae0a23156d2ae9d18f86e557', '2026-09-16 19:02:13', NULL, '2026-09-09 22:32:13', '2026-09-09 22:39:16'),
(115, 51, 'd5544b5abeb4abbaa513d641afc3e2aec34a2e34e722d9fc34e8965559e72acf', '2026-09-16 19:31:14', NULL, '2026-09-09 23:01:14', '2026-09-10 01:55:46'),
(116, 49, '9c7ec0497141e59821c9b31ffe7a59955e1f486b89a7d37445e623c24c0bd8a4', '2026-09-16 21:04:57', NULL, '2026-09-10 00:34:57', '2026-09-10 01:26:40'),
(117, 23, '806c612765b2fe7395c3b8d6f41c0d2f41720542561fe471aeda4202baec6a1f', '2026-09-16 21:14:10', NULL, '2026-09-10 00:44:10', '2026-09-10 01:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `deleteddata`
--

CREATE TABLE `deleteddata` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `original_user_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `place` varchar(150) DEFAULT NULL,
  `delete_reason` varchar(1000) NOT NULL,
  `delete_source` varchar(50) DEFAULT NULL,
  `deleted_by_admin_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `account_data` longtext DEFAULT NULL,
  `profile_data` longtext DEFAULT NULL,
  `preferences_data` longtext DEFAULT NULL,
  `preference_values_data` longtext DEFAULT NULL,
  `related_data` longtext DEFAULT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deleteddata`
--

INSERT INTO `deleteddata` (`id`, `original_user_id`, `member_id`, `phone`, `full_name`, `place`, `delete_reason`, `delete_source`, `deleted_by_admin_user_id`, `account_data`, `profile_data`, `preferences_data`, `preference_values_data`, `related_data`, `deleted_at`) VALUES
(1, 54, 'MWI0054', '6544444444', 'Ijas', 'Kalpakancheri', 'be preserved for future reference.\nDelete Reason *anently remove the active profile and its related data. The deletion record will be pre', 'admin', 23, '{\"id\":54,\"member_id\":\"MWI0054\",\"phone\":\"6544444444\",\"role\":\"user\",\"account_status\":\"active\",\"otp_verified\":1,\"created_at\":\"2026-09-09 22:21:44\",\"updated_at\":\"2026-09-09 22:22:38\",\"last_login_at\":null}', '{\"id\":44,\"user_id\":54,\"profile_for\":\"self\",\"gender\":\"male\",\"full_name\":\"Ijas\",\"marital_status\":\"separated\",\"has_kids\":\"no\",\"number_of_kids\":null,\"kids_living_status\":null,\"date_of_birth\":\"1974-12-13\",\"height\":\"62.00\",\"district\":\"Malappuram\",\"state\":\"Kerala\",\"pincode\":\"676551\",\"house_name\":\"s\",\"place\":\"Kalpakancheri\",\"latitude\":\"10.9345992\",\"longitude\":\"75.9877014\",\"location_source\":\"map\",\"religion\":\"Christian\",\"sect\":\"\",\"muslim_group\":\"\",\"salafi_group\":\"\",\"caste\":\"\",\"sub_caste\":\"\",\"nakshatra\":\"\",\"rashi\":\"\",\"dosham\":\"\",\"denomination\":\"Other\",\"christian_sub_group\":\"Other\",\"parish_name\":\"as fghj\",\"highest_education\":\"Master\'s Degree\",\"specialization\":\"LLM\",\"job_title\":\"Accounts Manager\",\"job_sector\":\"Business / Self Employed\",\"weight\":null,\"body_type\":null,\"complexion\":null,\"physical_status\":null,\"secondary_mobile\":null,\"whatsapp_country_code\":null,\"whatsapp_number\":null,\"email\":null,\"college_university\":null,\"annual_income\":null,\"work_location\":null,\"work_location_type\":null,\"work_state\":null,\"work_district\":null,\"work_country\":null,\"work_city\":null,\"company_name\":null,\"father_name\":null,\"father_occupation\":null,\"father_status\":null,\"mother_name\":null,\"mother_occupation\":null,\"mother_status\":null,\"brothers\":null,\"sisters\":null,\"married_brothers\":null,\"married_sisters\":null,\"family_status\":null,\"home_type\":null,\"expectations\":null,\"registration_completed\":1,\"profile_status\":\"new\",\"completion_percentage\":100,\"home_verified\":0,\"created_at\":\"2026-09-09 22:21:56\",\"updated_at\":\"2026-09-09 22:22:38\"}', '{\"id\":37,\"user_id\":54,\"age_min\":43,\"age_max\":51,\"height_min\":\"48.00\",\"height_max\":\"62.00\",\"preferred_religion\":\"Christian\",\"acceptance_of_kids\":\"yes\",\"horoscope_required\":null,\"created_at\":\"2026-09-09 22:22:38\",\"updated_at\":\"2026-09-09 22:22:38\"}', '[{\"id\":893,\"user_id\":54,\"preference_type\":\"marital_status\",\"value\":\"separated\",\"created_at\":\"2026-09-09 22:22:38\"},{\"id\":894,\"user_id\":54,\"preference_type\":\"sect\",\"value\":\"Any\",\"created_at\":\"2026-09-09 22:22:38\"},{\"id\":895,\"user_id\":54,\"preference_type\":\"location\",\"value\":\"Malappuram\",\"created_at\":\"2026-09-09 22:22:38\"},{\"id\":896,\"user_id\":54,\"preference_type\":\"location\",\"value\":\"All Kerala\",\"created_at\":\"2026-09-09 22:22:38\"}]', '{\"photos\":[],\"payments\":[],\"verification_requests\":[],\"interests\":[],\"shortlists\":[],\"feedback\":[],\"admin_links\":[],\"file_paths\":[]}', '2026-09-09 22:53:11'),
(2, 55, 'MWI0055', '3126656565', 'Ijas', 'Vattathani', 'ove the active profile and its related data. The deletion record will be preserved for future referenc', 'admin', 23, '{\"id\":55,\"member_id\":\"MWI0055\",\"phone\":\"3126656565\",\"role\":\"user\",\"account_status\":\"active\",\"otp_verified\":1,\"created_at\":\"2026-09-09 22:46:10\",\"updated_at\":\"2026-09-09 22:47:26\",\"last_login_at\":null}', '{\"id\":45,\"user_id\":55,\"profile_for\":\"friend\",\"gender\":\"male\",\"full_name\":\"Ijas\",\"marital_status\":\"never_married\",\"has_kids\":null,\"number_of_kids\":null,\"kids_living_status\":null,\"date_of_birth\":\"1974-10-12\",\"height\":\"82.00\",\"district\":\"Malappuram\",\"state\":\"Kerala\",\"pincode\":\"676307\",\"house_name\":\"434343\",\"place\":\"Vattathani\",\"latitude\":\"10.9453859\",\"longitude\":\"75.8832624\",\"location_source\":\"map\",\"religion\":\"Hindu\",\"sect\":\"\",\"muslim_group\":\"\",\"salafi_group\":\"\",\"caste\":\"SC\",\"sub_caste\":\"Kanakkan\",\"nakshatra\":\"Bharani\",\"rashi\":\"Kumbham (Aquarius)\",\"dosham\":\"dont_know\",\"denomination\":\"\",\"christian_sub_group\":\"\",\"parish_name\":\"\",\"highest_education\":\"Master\'s Degree\",\"specialization\":\"LLM\",\"job_title\":\"Accountant\",\"job_sector\":\"Business / Self Employed\",\"weight\":null,\"body_type\":null,\"complexion\":null,\"physical_status\":null,\"secondary_mobile\":null,\"whatsapp_country_code\":null,\"whatsapp_number\":null,\"email\":null,\"college_university\":null,\"annual_income\":null,\"work_location\":null,\"work_location_type\":null,\"work_state\":null,\"work_district\":null,\"work_country\":null,\"work_city\":null,\"company_name\":null,\"father_name\":null,\"father_occupation\":null,\"father_status\":null,\"mother_name\":null,\"mother_occupation\":null,\"mother_status\":null,\"brothers\":null,\"sisters\":null,\"married_brothers\":null,\"married_sisters\":null,\"family_status\":null,\"home_type\":null,\"expectations\":null,\"registration_completed\":1,\"profile_status\":\"new\",\"completion_percentage\":100,\"home_verified\":0,\"created_at\":\"2026-09-09 22:46:22\",\"updated_at\":\"2026-09-09 22:47:26\"}', '{\"id\":38,\"user_id\":55,\"age_min\":18,\"age_max\":59,\"height_min\":\"48.00\",\"height_max\":\"82.00\",\"preferred_religion\":\"Hindu\",\"acceptance_of_kids\":\"yes\",\"horoscope_required\":null,\"created_at\":\"2026-09-09 22:47:26\",\"updated_at\":\"2026-09-09 22:47:26\"}', '[{\"id\":897,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"never_married\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":898,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"divorced\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":899,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"widowed\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":900,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"nikah_divorce\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":901,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"separated\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":902,\"user_id\":55,\"preference_type\":\"marital_status\",\"value\":\"awaiting_divorce\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":903,\"user_id\":55,\"preference_type\":\"caste\",\"value\":\"SC\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":904,\"user_id\":55,\"preference_type\":\"sub_caste\",\"value\":\"Cheruman\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":905,\"user_id\":55,\"preference_type\":\"sub_caste\",\"value\":\"Kanakkan\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":906,\"user_id\":55,\"preference_type\":\"sub_caste\",\"value\":\"Pulayan / Pulayar\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":907,\"user_id\":55,\"preference_type\":\"sub_caste\",\"value\":\"Parayan\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":908,\"user_id\":55,\"preference_type\":\"sub_caste\",\"value\":\"Kuravan\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":909,\"user_id\":55,\"preference_type\":\"education\",\"value\":\"PhD / Doctorate\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":910,\"user_id\":55,\"preference_type\":\"education\",\"value\":\"Master\'s Degree\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":911,\"user_id\":55,\"preference_type\":\"education\",\"value\":\"ITI / Technical Certificate\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":912,\"user_id\":55,\"preference_type\":\"education\",\"value\":\"Diploma\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":913,\"user_id\":55,\"preference_type\":\"career_sector\",\"value\":\"Government\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":914,\"user_id\":55,\"preference_type\":\"career_sector\",\"value\":\"Private\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":915,\"user_id\":55,\"preference_type\":\"career_sector\",\"value\":\"Business / Self Employed\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":916,\"user_id\":55,\"preference_type\":\"career_sector\",\"value\":\"Freelance\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":917,\"user_id\":55,\"preference_type\":\"location\",\"value\":\"Malappuram\",\"created_at\":\"2026-09-09 22:47:26\"},{\"id\":918,\"user_id\":55,\"preference_type\":\"location\",\"value\":\"All Kerala\",\"created_at\":\"2026-09-09 22:47:26\"}]', '{\"photos\":[],\"payments\":[],\"verification_requests\":[],\"interests\":[],\"shortlists\":[],\"feedback\":[],\"admin_links\":[],\"file_paths\":[]}', '2026-09-09 22:53:15'),
(3, 52, 'MWI0052', '1212111111', 'Ayesha Fathima', 'Parakkanni', 'esha Fathima\nPlace\nParakkanni\nThis will permanently r', 'admin', 23, '{\"id\":52,\"member_id\":\"MWI0052\",\"phone\":\"1212111111\",\"role\":\"user\",\"account_status\":\"active\",\"otp_verified\":1,\"created_at\":\"2026-09-08 23:58:52\",\"updated_at\":\"2026-09-09 00:00:18\",\"last_login_at\":null}', '{\"id\":42,\"user_id\":52,\"profile_for\":\"self\",\"gender\":\"male\",\"full_name\":\"Ayesha Fathima\",\"marital_status\":\"divorced\",\"has_kids\":\"yes\",\"number_of_kids\":1,\"kids_living_status\":\"with_me\",\"date_of_birth\":\"2001-12-13\",\"height\":\"71.00\",\"district\":\"Malappuram\",\"state\":\"Kerala\",\"pincode\":\"676314\",\"house_name\":\"Name\",\"place\":\"Parakkanni\",\"latitude\":\"11.0747951\",\"longitude\":\"75.9931982\",\"location_source\":\"map\",\"religion\":\"Hindu\",\"sect\":\"\",\"muslim_group\":\"\",\"salafi_group\":\"\",\"caste\":\"Namboothiri\",\"sub_caste\":\"\",\"nakshatra\":\"Uttara Phalguni (Uthram)\",\"rashi\":\"Kumbham (Aquarius)\",\"dosham\":\"no\",\"denomination\":\"\",\"christian_sub_group\":\"\",\"parish_name\":\"\",\"highest_education\":\"Master\'s Degree\",\"specialization\":\"MTech\",\"job_title\":\"Accounts Executive\",\"job_sector\":\"Business / Self Employed\",\"weight\":null,\"body_type\":\"\",\"complexion\":\"\",\"physical_status\":\"Normal\",\"secondary_mobile\":\"\",\"whatsapp_country_code\":\"+91\",\"whatsapp_number\":\"+9111111111152\",\"email\":\"\",\"college_university\":\"\",\"annual_income\":\"\",\"work_location\":null,\"work_location_type\":\"india_same_state\",\"work_state\":\"Kerala\",\"work_district\":\"l\",\"work_country\":\"\",\"work_city\":\"\",\"company_name\":\"\",\"father_name\":\"\",\"father_occupation\":\"\",\"father_status\":\"\",\"mother_name\":\"\",\"mother_occupation\":\"\",\"mother_status\":\"\",\"brothers\":null,\"sisters\":null,\"married_brothers\":null,\"married_sisters\":null,\"family_status\":\"Lower Middle Class\",\"home_type\":\"\",\"expectations\":null,\"registration_completed\":1,\"profile_status\":\"verified\",\"completion_percentage\":100,\"home_verified\":1,\"created_at\":\"2026-09-08 23:59:29\",\"updated_at\":\"2026-09-09 00:20:38\"}', '{\"id\":36,\"user_id\":52,\"age_min\":18,\"age_max\":59,\"height_min\":\"48.00\",\"height_max\":\"71.00\",\"preferred_religion\":\"Hindu\",\"acceptance_of_kids\":\"yes\",\"horoscope_required\":\"yes\",\"created_at\":\"2026-09-09 00:00:17\",\"updated_at\":\"2026-09-09 00:18:15\"}', '[{\"id\":873,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"divorced\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":874,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"never_married\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":875,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"nikah_divorce\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":876,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"separated\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":877,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"awaiting_divorce\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":878,\"user_id\":52,\"preference_type\":\"marital_status\",\"value\":\"widowed\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":879,\"user_id\":52,\"preference_type\":\"caste\",\"value\":\"Any\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":880,\"user_id\":52,\"preference_type\":\"career_sector\",\"value\":\"Any\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":881,\"user_id\":52,\"preference_type\":\"location\",\"value\":\"Malappuram\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":882,\"user_id\":52,\"preference_type\":\"location\",\"value\":\"All Kerala\",\"created_at\":\"2026-09-09 00:00:17\"},{\"id\":888,\"user_id\":52,\"preference_type\":\"family_status\",\"value\":\"any\",\"created_at\":\"2026-09-09 00:18:15\"},{\"id\":889,\"user_id\":52,\"preference_type\":\"physical_status\",\"value\":\"any\",\"created_at\":\"2026-09-09 00:18:15\"},{\"id\":890,\"user_id\":52,\"preference_type\":\"location_radius\",\"value\":\"any\",\"created_at\":\"2026-09-09 00:18:15\"},{\"id\":891,\"user_id\":52,\"preference_type\":\"income\",\"value\":\"any\",\"created_at\":\"2026-09-09 00:18:15\"},{\"id\":892,\"user_id\":52,\"preference_type\":\"complexion\",\"value\":\"any\",\"created_at\":\"2026-09-09 00:18:15\"}]', '{\"photos\":[{\"id\":25,\"user_id\":52,\"file_path\":\"uploads/profile-photos/3f7bc64569e769aef92269c74b4dc5c9.jpg\",\"is_primary\":1,\"display_order\":0,\"status\":\"active\",\"created_at\":\"2026-09-09 00:16:50\"}],\"payments\":[{\"id\":12,\"user_id\":52,\"plan_id\":2,\"amount\":\"399.00\",\"payment_method\":\"admin\",\"transaction_id\":null,\"payment_status\":\"success\",\"paid_at\":\"2026-09-08 20:50:10\",\"created_at\":\"2026-09-09 00:20:10\",\"updated_at\":\"2026-09-09 00:20:10\"}],\"verification_requests\":[{\"id\":11,\"user_id\":52,\"payment_id\":12,\"status\":\"verified\",\"requested_at\":\"2026-09-09 00:20:10\",\"started_at\":\"2026-09-09 00:20:27\",\"completed_at\":\"2026-09-09 00:20:38\",\"verified_by\":23,\"latitude\":\"10.9776000\",\"longitude\":\"76.2285000\",\"location_accuracy\":\"50000.00\",\"location_name\":null,\"location_place\":\"Parakkanni\",\"location_district\":\"Malappuram\",\"location_state\":\"Kerala\",\"verification_notes\":null,\"verification_photo_path\":\"uploads/home-verification/MWI0052_home_11_844e03185e2fb9b4.png\",\"created_at\":\"2026-09-09 00:20:10\",\"updated_at\":\"2026-09-09 00:20:38\"}],\"interests\":[{\"id\":17,\"sender_user_id\":52,\"receiver_user_id\":34,\"status\":\"pending\",\"created_at\":\"2026-09-09 00:21:18\",\"updated_at\":\"2026-09-09 00:21:18\",\"responded_at\":null},{\"id\":18,\"sender_user_id\":52,\"receiver_user_id\":15,\"status\":\"pending\",\"created_at\":\"2026-09-09 00:37:23\",\"updated_at\":\"2026-09-09 00:37:23\",\"responded_at\":null},{\"id\":19,\"sender_user_id\":52,\"receiver_user_id\":49,\"status\":\"pending\",\"created_at\":\"2026-09-09 00:38:08\",\"updated_at\":\"2026-09-09 00:38:08\",\"responded_at\":null}],\"shortlists\":[{\"id\":20,\"user_id\":52,\"shortlisted_user_id\":49,\"created_at\":\"2026-09-09 00:12:24\"},{\"id\":21,\"user_id\":34,\"shortlisted_user_id\":52,\"created_at\":\"2026-09-09 00:17:05\"}],\"feedback\":[],\"admin_links\":[],\"file_paths\":[\"uploads/profile-photos/3f7bc64569e769aef92269c74b4dc5c9.jpg\",\"uploads/home-verification/MWI0052_home_11_844e03185e2fb9b4.png\"]}', '2026-09-09 22:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `feedback_type` enum('suggestion','problem','confusing','feature','other') NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','reviewed','resolved') NOT NULL DEFAULT 'new',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `feedback_type`, `message`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 17, 'suggestion', 'our Message', 'new', NULL, '2026-08-31 04:30:04', '2026-08-31 04:30:04'),
(2, 17, 'problem', 'Your Message\nfeedback will be used only to improve our service a', 'new', NULL, '2026-08-31 04:32:48', '2026-08-31 04:32:48'),
(3, 26, 'suggestion', 'Your Message', 'new', NULL, '2026-09-03 01:35:36', '2026-09-03 01:35:36'),
(4, 42, 'suggestion', 'Your Mes', 'new', NULL, '2026-09-06 23:22:58', '2026-09-06 23:22:58'),
(5, 42, 'confusing', 'What would you like to tell us?Tell us what you think or what we can improveShare Your Feedback\nTell us what you think or what we can improve.\n\nWhat would you like to tell us?\n\nSuggestion', 'new', NULL, '2026-09-06 23:23:17', '2026-09-06 23:23:17'),
(6, 42, 'suggestion', 'What would you like to tel', 'new', NULL, '2026-09-06 23:24:13', '2026-09-06 23:24:13'),
(7, 42, 'feature', 'hat would you liour Messake to tell us?', 'new', NULL, '2026-09-06 23:24:32', '2026-09-06 23:24:32'),
(8, 42, 'suggestion', 'Message', 'new', NULL, '2026-09-06 23:26:15', '2026-09-06 23:26:15'),
(9, 42, 'other', 'ur Message', 'new', NULL, '2026-09-06 23:26:26', '2026-09-06 23:26:26');

-- --------------------------------------------------------

--
-- Table structure for table `interests`
--

CREATE TABLE `interests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_user_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `responded_at` datetime DEFAULT NULL
) ;

--
-- Dumping data for table `interests`
--

INSERT INTO `interests` (`id`, `sender_user_id`, `receiver_user_id`, `status`, `created_at`, `updated_at`, `responded_at`) VALUES
(1, 30, 29, 'pending', '2026-09-04 01:59:22', '2026-09-04 01:59:22', NULL),
(2, 40, 31, 'pending', '2026-09-06 01:20:28', '2026-09-06 01:20:28', NULL),
(3, 41, 40, 'pending', '2026-09-06 01:36:06', '2026-09-06 01:36:06', NULL),
(4, 41, 39, 'pending', '2026-09-06 01:36:08', '2026-09-06 01:36:08', NULL),
(5, 41, 30, 'pending', '2026-09-06 02:40:52', '2026-09-06 02:40:52', NULL),
(6, 41, 26, 'pending', '2026-09-06 02:40:54', '2026-09-06 02:40:54', NULL),
(7, 42, 29, 'accepted', '2026-09-06 14:37:37', '2026-09-06 14:51:29', '2026-09-06 14:51:29'),
(8, 41, 42, 'declined', '2026-09-06 14:45:02', '2026-09-06 14:48:23', '2026-09-06 14:48:23'),
(9, 42, 13, 'pending', '2026-09-06 15:43:02', '2026-09-06 15:43:02', NULL),
(10, 42, 31, 'pending', '2026-09-06 15:43:03', '2026-09-06 15:43:03', NULL),
(11, 45, 42, 'pending', '2026-09-06 15:43:20', '2026-09-06 15:43:20', NULL),
(12, 42, 46, 'accepted', '2026-09-06 16:28:21', '2026-09-06 19:05:15', '2026-09-06 19:05:15'),
(13, 46, 39, 'pending', '2026-09-06 23:07:23', '2026-09-06 23:07:23', NULL),
(14, 46, 40, 'pending', '2026-09-06 23:07:24', '2026-09-06 23:07:24', NULL),
(15, 46, 44, 'pending', '2026-09-06 23:07:34', '2026-09-06 23:07:34', NULL),
(16, 46, 43, 'pending', '2026-09-06 23:07:34', '2026-09-06 23:07:34', NULL),
(20, 51, 34, 'pending', '2026-09-10 00:33:55', '2026-09-10 00:33:55', NULL),
(21, 51, 49, 'pending', '2026-09-10 00:36:28', '2026-09-10 00:36:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `purpose` enum('registration','login','forgot_password','change_phone') NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `phone`, `purpose`, `otp_hash`, `expires_at`, `verified_at`, `attempts`, `created_at`) VALUES
(1, '9876543210', 'registration', '$2y$10$NDKGQ8.SSf3bceOjkl/GJ.MlB7zjuEx3bNisUWK6XWIsqtwJKx1Fa', '2026-08-28 21:21:39', '2026-08-29 00:47:22', 0, '2026-08-29 00:46:39'),
(2, '9745553598', 'registration', '$2y$10$GA/CoUX5VMPP5hR7P6viS.WQ8ODT9FVFYbKlaM6X5VSKyWXF8iGOm', '2026-08-28 21:39:39', '2026-08-29 01:06:06', 0, '2026-08-29 01:04:39'),
(3, '3432432434', 'registration', '$2y$10$tQ/kUl299dHUC6rF8R.v7uXGJ9XT/JK99yL0s.yoCe8o7A2JGnjcW', '2026-08-28 22:14:47', '2026-08-29 01:40:00', 0, '2026-08-29 01:39:47'),
(4, '9876543211', 'registration', '$2y$10$gu/ThbpPKNZlxlk.MBt2iOHM4tAexk4kzNTRX.CKltTsDZaACxGwa', '2026-08-28 22:16:58', '2026-08-29 01:42:15', 0, '2026-08-29 01:41:58'),
(5, '9876543211', 'registration', '$2y$10$lH87LeU9EQdSE8qD7c3JMelFHGvl/luQtRvWiugnGy6PUUnrzpkX.', '2026-08-28 22:17:15', '2026-08-29 01:42:31', 0, '2026-08-29 01:42:15'),
(6, '9854343211', 'registration', '$2y$10$Du4E3J7vvBIY8TivI.8TQe4E6oRbP5lKTbx6OAZogFY4mkVN10hZG', '2026-08-29 16:57:17', '2026-08-29 20:22:37', 0, '2026-08-29 20:22:17'),
(7, '9745554548', 'registration', '$2y$10$CTfKBmg8DSuDtyMlNjm4NOopw0I42W06/jVApYaP8Ri4KD5LihGIu', '2026-08-29 17:02:58', '2026-08-29 20:28:19', 0, '2026-08-29 20:27:58'),
(8, '9843543211', 'registration', '$2y$10$gHNkVCC6ZSrvMZNaCsYb.O.X4aXLd4LCoPRRq9bG9rNuzSrBhSSWC', '2026-08-29 17:06:44', '2026-08-29 20:31:57', 0, '2026-08-29 20:31:44'),
(9, '9854378211', 'registration', '$2y$10$TB6H7BuwpqPMKF1BiPkKf.oaSUGm4EYXE.obhpFdrJu0GDjgPmZYW', '2026-08-29 17:11:57', '2026-08-29 20:37:43', 0, '2026-08-29 20:36:57'),
(10, '9749443598', 'registration', '$2y$10$ufD4.9eCRZhFZKLZcqG0MOBsu0QuENI2HJKFRGyaGC.SzQcrPU29G', '2026-08-29 17:55:03', '2026-08-29 21:20:24', 0, '2026-08-29 21:20:03'),
(11, '9999999999', 'registration', '$2y$10$jgJryrEIsqmNHkwHyuSpG.bKLQyjqCPPSloDIK1KVDOdtJ6WblKHa', '2026-08-29 20:24:02', '2026-08-29 23:49:21', 0, '2026-08-29 23:49:02'),
(12, '9999999999', 'registration', '$2y$10$738Sbffuap3C2G6oZbkPdeHIlSyULUJv0i/YJCxUK8CqyvZ1mfrfG', '2026-08-29 20:24:21', '2026-08-29 23:49:30', 0, '2026-08-29 23:49:21'),
(13, '9999988888', 'registration', '$2y$10$WE0rrh7brSMcKdtlFf39/.NNfbCcjO5h5dG8RG6bwPdIxfh.rX/w6', '2026-08-29 20:53:35', '2026-08-30 00:18:47', 0, '2026-08-30 00:18:35'),
(14, '8989898989', 'registration', '$2y$10$QwiNCHH/mRhKIcggVA/sB.yn5jfAUGkjAEWQ11Jmqh3LlwhuZ3dQy', '2026-08-29 21:09:47', '2026-08-30 00:34:59', 0, '2026-08-30 00:34:47'),
(15, '9749443458', 'registration', '$2y$10$bt3HsPxBReXEn8oBU08aB.SWKczhj6BbBIsuwpJ0wpPncv2RPq/T.', '2026-08-29 23:05:17', '2026-08-30 02:30:27', 0, '2026-08-30 02:30:17'),
(16, '1111111118', 'registration', '$2y$10$bH2uNQwn5.4vD8Pe0CtMLeaVWPw9npEKz80b0t8MqM9VnEqs5OFsm', '2026-08-29 23:14:04', '2026-08-30 02:39:14', 0, '2026-08-30 02:39:04'),
(17, '4555555558', 'registration', '$2y$10$ViDQRSBQkiv3OJghR74gA.l7OWd77iPANwEmBirhbIaKvgMFOE.rm', '2026-08-30 12:38:44', '2026-08-30 16:03:57', 0, '2026-08-30 16:03:44'),
(18, '9876543210', 'registration', '$2y$10$.1TcB7dGB6pd5NHc8.uOy.PCm.KUuWfZlSxd04aGMoHrymZnOeYau', '2026-08-30 12:49:25', '2026-08-30 16:14:41', 0, '2026-08-30 16:14:25'),
(19, '3433343458', 'registration', '$2y$10$7182pJ9fJ2vduT9iTOuEWeL/rFL688hWyqwk/x.tvqjDaMn.I91Iq', '2026-08-30 14:23:42', '2026-08-30 17:48:51', 0, '2026-08-30 17:48:42'),
(20, '3454354354', 'registration', '$2y$10$kEUDlnXBy2lHKKTO22u48ecmaEIBnLSkcjNknL4u1sHmLn9fSJKKO', '2026-08-30 14:38:37', '2026-08-30 18:03:46', 0, '2026-08-30 18:03:37'),
(21, '2222222222', 'registration', '$2y$10$OXtBrwQQz96Q08prFpyKK.e30HOyBvWxKeQJpG5kb2Jltp3W6Z6rG', '2026-08-30 14:51:17', '2026-08-30 18:16:25', 0, '2026-08-30 18:16:17'),
(22, '9876451410', 'registration', '$2y$10$9tFUPZ7la6ok83q3H/BsBuBK1BXpJPsMr8i3uYKBVDeas6cJEf456', '2026-08-30 17:20:39', '2026-08-30 20:45:48', 0, '2026-08-30 20:45:39'),
(23, '4535345435', 'registration', '$2y$10$nbqQzVIpCegM5Bb.dizcXunCBCITIw.AEEKFgXh7zW1kLcoGgGXqu', '2026-08-30 20:02:44', '2026-08-30 23:28:54', 0, '2026-08-30 23:27:44'),
(24, '9749422222', 'registration', '$2y$10$ejc0UTI4lFkcMGOfBxpcQuXXKygn/V0zOnp.oeMGMWEztjuZCYel6', '2026-08-30 20:06:05', '2026-08-30 23:31:16', 0, '2026-08-30 23:31:05'),
(25, '2222222222', 'forgot_password', '$2y$10$ewlVh/KEoSMdTFrtXLrm4.tnl9wxe3w5ds.QhcL3Z49LKu7UW22mi', '2026-08-31 00:19:04', '2026-08-31 03:44:36', 0, '2026-08-31 03:44:04'),
(26, '2222222222', 'forgot_password', '$2y$10$oAMxryHSdbloyDAHLtcOU.QUnncbnfKkS0ZBAYnAAFIxQM70LlNcW', '2026-08-31 00:30:10', '2026-08-31 03:55:29', 0, '2026-08-31 03:55:10'),
(27, '2222222222', 'forgot_password', '$2y$10$KXu8fTdJKygXSNZVOarT2OivEl0JFIMyo94xzseELRYDzRScr7y5m', '2026-08-31 00:35:36', '2026-08-31 04:01:03', 0, '2026-08-31 04:00:36'),
(28, '2323232323', 'registration', '$2y$10$KfM/yDBpNrFM3e8NueRaF.kahQopvLg5TcOo5NrWfUPN.Ml0FcTlG', '2026-08-31 01:50:05', '2026-08-31 05:15:15', 0, '2026-08-31 05:15:05'),
(29, '4354354354', 'registration', '$2y$10$F3k9Sit0ea3DL.3g3eh9ne1VytiG/83kJEvQ/RN/DwP63EFKgWR4i', '2026-08-31 19:40:37', '2026-08-31 23:05:50', 0, '2026-08-31 23:05:37'),
(30, '2222222222', 'forgot_password', '$2y$10$JQsEpL2TPKO89N9UGR9r3eWem4XZg5xWO3cAZal1EJbdZNfDik/0i', '2026-08-31 20:06:50', '2026-08-31 23:32:12', 0, '2026-08-31 23:31:50'),
(31, '9876543210', 'forgot_password', '$2y$10$DF1UuKp/NDr8o8oWUXe9.e4d2dwcjQL0miSwzPzq7xqdtx/Q8rxGC', '2026-08-31 20:08:39', '2026-08-31 23:34:14', 0, '2026-08-31 23:33:39'),
(32, '1465456498', 'registration', '$2y$10$O.Hj.pUMRiAetGyl9Yxhdu1CjuLjKRqSjNg9WtawFqjHhpc1xjYHK', '2026-08-31 20:13:01', '2026-08-31 23:38:27', 0, '2026-08-31 23:38:01'),
(33, '1465456498', 'forgot_password', '$2y$10$9Im230hW8zB8G.5k80q31OjkXA/D4yuOZsAG.eGSqDRbn8hyA63pC', '2026-08-31 20:17:39', '2026-08-31 23:42:54', 0, '2026-08-31 23:42:39'),
(34, '4354354354', 'forgot_password', '$2y$10$G2GZScnfU6fw1XcBffH0EOAPojOEIMhmluZiUZ33LpRuzjdeFdjsK', '2026-09-01 23:01:11', '2026-09-02 02:26:31', 0, '2026-09-02 02:26:11'),
(35, '4354354354', 'forgot_password', '$2y$10$dHJHTm1SPTnRxtPNoBLWzO./EQ9OfyitjOCTBWq4QZRcxRk/S3jf2', '2026-09-02 18:41:48', '2026-09-02 22:07:13', 0, '2026-09-02 22:06:48'),
(36, '9711153598', 'registration', '$2y$10$V9zBoWzrxbMQK.g9CwLC7uAUBCRe9HtZiJDjglM780at0VTKOp8cy', '2026-09-02 19:08:09', '2026-09-02 22:33:22', 0, '2026-09-02 22:33:09'),
(37, '9745553598', 'registration', '$2y$10$wWF4Wx5AmmK/jYE8lKQKn.CPg6BRIBP1hYlO7naGdMtVYVjU9XMOm', '2026-09-02 19:27:27', '2026-09-02 22:52:48', 0, '2026-09-02 22:52:27'),
(38, '4353454354', 'registration', '$2y$10$.0pPzhlg2kHvxs.ipKtiMuN5XgMhFXcX3tGG7cdfuzNFOMz20e9pW', '2026-09-02 22:04:10', '2026-09-03 01:30:01', 0, '2026-09-03 01:29:10'),
(39, '6456546546', 'registration', '$2y$10$pyarlL5bLZNNdZSeT8Jl3OKMo.eZ5qbl21qFKcng2T9aI4fJVWl8C', '2026-09-02 22:21:12', '2026-09-03 01:46:27', 0, '2026-09-03 01:46:12'),
(40, '2222222227', 'registration', '$2y$10$ADui05cTzvigcATOJblRAOXPynrcvJ2jIf31O8WKm7/bpJrod6o6y', '2026-09-03 17:46:46', '2026-09-03 21:12:14', 0, '2026-09-03 21:11:46'),
(41, '9876543218', 'registration', '$2y$10$AQNP1Ta/ktxxRuwfbqGGl.nChmDOB8LAK4OI3hoyWGKhPZgcXWWBa', '2026-09-03 18:47:33', '2026-09-03 22:12:44', 0, '2026-09-03 22:12:33'),
(42, '4658146416', 'registration', '$2y$10$zHMxBHkcRyXfnM5k9qF0Lu9IKZl925dL80uu67H/mLlnX2WOcOk16', '2026-09-03 21:27:22', '2026-09-04 00:52:39', 0, '2026-09-04 00:52:22'),
(43, '1654465645', 'registration', '$2y$10$A7tT30N1M9VXN03YuDeXi.NWZWFFhVHe8kowJ18PbM6MUblGv9sMK', '2026-09-03 22:44:53', '2026-09-04 02:10:02', 0, '2026-09-04 02:09:53'),
(44, '9849849658', 'registration', '$2y$10$8Hk6NIjXIwplqQHJ3hZLneM0AUKn0WJCu4RCziaFjcej/dAWhAKDC', '2026-09-05 19:53:21', '2026-09-05 23:18:28', 0, '2026-09-05 23:18:21'),
(45, '2665656615', 'registration', '$2y$10$BjSFomqoMZvzPUI0aLGhseQ2K570ImmfvaNA9m2FSOolzETcCl2AS', '2026-09-05 20:02:28', '2026-09-05 23:27:36', 0, '2026-09-05 23:27:28'),
(46, '6511651656', 'registration', '$2y$10$F32z7dxicpUm5EB4Bg/KqeKu3vcSSPBCh77mRfom291tD5fd2IRXi', '2026-09-05 20:16:40', '2026-09-05 23:41:48', 0, '2026-09-05 23:41:40'),
(47, '1113123213', 'registration', '$2y$10$BKW9jpiTjQyInXVr6EQf5eknMyZySCY0nHKMAZ3ztcmbpxFA8cbmG', '2026-09-05 20:28:51', '2026-09-05 23:54:06', 0, '2026-09-05 23:53:51'),
(48, '8979777867', 'registration', '$2y$10$gg4yE/aYTxkiy.X7Vbjw8O4wZDiOewt1WuxpN/PrZ6JRrNsYLqbwq', '2026-09-05 20:43:16', '2026-09-06 00:08:35', 1, '2026-09-06 00:08:16'),
(49, '2266662222', 'registration', '$2y$10$nSOaIdoB5rnVFnP7f94NKuNts1ta5FXYvglnqM7hTUvlJF6nxSs/O', '2026-09-05 21:11:38', '2026-09-06 00:36:47', 0, '2026-09-06 00:36:38'),
(50, '5466465465', 'registration', '$2y$10$fd1fPzl/zts7fFJZtuLVFODIHkRXy0eEv2GuYi0sXSrptaez6O6Za', '2026-09-05 21:12:58', '2026-09-06 00:38:07', 0, '2026-09-06 00:37:58'),
(51, '9555543210', 'registration', '$2y$10$JAWzohMPdRRrRbhIpAojpOIoMuwHSTCyvwNmLfpTO7bWAgd1khljO', '2026-09-05 21:15:42', '2026-09-06 00:40:52', 0, '2026-09-06 00:40:42'),
(52, '3322222222', 'registration', '$2y$10$u0scp41emRo6.kDEWcmx/.fCfTsq4ACHBK05vtd99sNT2PQCSe9TW', '2026-09-05 21:22:22', '2026-09-06 00:47:39', 0, '2026-09-06 00:47:22'),
(53, '9749865456', 'registration', '$2y$10$abDaBNbm/Ly3emrD0D1PQOaZ2zOxfzku.GJkuldP6VswTj11uBBnG', '2026-09-05 22:09:26', '2026-09-06 01:34:39', 0, '2026-09-06 01:34:26'),
(54, '9451465110', 'registration', '$2y$10$pXwjvdvZwKXlTHVwkTbAQeob0p/v/CneDZyUB/1mi9gY/zp4rQ3We', '2026-09-05 23:35:44', '2026-09-06 03:01:02', 0, '2026-09-06 03:00:44'),
(55, '2222254354', 'registration', '$2y$10$utFFeg3hrcR4t6RT/JMJRegKbTEBnk7w6LWEgEwsnxM1nZ7OHOG0.', '2026-09-06 05:28:20', '2026-09-06 08:53:48', 0, '2026-09-06 08:53:20'),
(56, '3453453454', 'registration', '$2y$10$/nv/qpFDygvTpCeilwITnejqqWocb.O0MoUbx0efYtdTMIoV4TxGy', '2026-09-06 10:37:35', '2026-09-06 14:03:01', 0, '2026-09-06 14:02:35'),
(57, '6844445465', 'registration', '$2y$10$JsebHqlP4rYLcrfyYPz.i.0lFMDV6dhTNEJkvVoCXZOqk08jj2dsq', '2026-09-06 12:11:42', '2026-09-06 15:37:17', 0, '2026-09-06 15:36:42'),
(58, '3444543210', 'registration', '$2y$10$/X3OGf7aS8H491zo8c7Io.YjABW09z3O5Q//KRFSxB5KrXSkK.J1G', '2026-09-06 12:59:05', '2026-09-06 16:24:13', 0, '2026-09-06 16:24:05'),
(59, '5161661546', 'registration', '$2y$10$dX6h4HU74NHhstDAqaAqI.sfpNkei2npqGLlEFCkxNcf0oMooqYAy', '2026-09-06 20:25:02', '2026-09-06 23:50:18', 0, '2026-09-06 23:50:02'),
(60, '6666456498', 'registration', '$2y$10$vjA9soysIzshMOLF4DMVQ.vZ1mFK1oBaBx95OJsXdiyseJVKd8oEG', '2026-09-06 20:28:02', '2026-09-06 23:53:14', 0, '2026-09-06 23:53:02'),
(61, '3244444444', 'registration', '$2y$10$sCX1Mfze/8Vbc4/lbBC.teuITTwGReQaWcQzeUZg1qdbTrX/Nadxa', '2026-09-06 21:24:56', '2026-09-07 00:50:10', 0, '2026-09-07 00:49:56'),
(62, '9874587458', 'registration', '$2y$10$Zr5uU3ia.n/RZp0V0rk7Je9YHLbQpjkXOS.EZx7IOEjI8kN5vf1li', '2026-09-07 21:04:12', '2026-09-08 00:29:33', 0, '2026-09-08 00:29:12'),
(63, '1321321321', 'registration', '$2y$10$NVj2z0ZTGDZNsnzV9N3fU.1uicqyk3RhJ0xiEvR3F3AHgghMuOlXC', '2026-09-08 20:26:02', '2026-09-08 23:51:12', 0, '2026-09-08 23:51:02'),
(65, '3565454646', 'registration', '$2y$10$9yoljJLt9VJj.sNVeunE9.4hSTrLUZ1BMI9l5M6l4FsYfmanzIhtS', '2026-09-09 18:42:07', '2026-09-09 22:07:19', 0, '2026-09-09 22:07:07'),
(66, '5555555558', 'registration', '$2y$10$Rq9bwKW1mmO3WONzZJCvUubDr6MH.P/3rdLsbqjAWSBT87wv7kqI2', '2026-09-09 18:51:05', NULL, 0, '2026-09-09 22:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `payment_status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `plan_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 22, 2, 0.00, 'admin', NULL, 'success', '2026-09-01 22:54:05', '2026-09-02 02:24:05', '2026-09-02 02:24:05'),
(2, 21, 2, 399.00, 'admin', NULL, 'success', '2026-09-02 17:44:37', '2026-09-02 21:14:37', '2026-09-02 21:14:37'),
(3, 20, 2, 399.00, 'admin', NULL, 'success', '2026-09-02 17:44:44', '2026-09-02 21:14:44', '2026-09-02 21:14:44'),
(4, 24, 2, 399.00, 'admin', NULL, 'success', '2026-09-02 19:06:48', '2026-09-02 22:36:48', '2026-09-02 22:36:48'),
(5, 16, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 05:42:51', '2026-09-06 09:12:51', '2026-09-06 09:12:51'),
(6, 29, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 05:50:44', '2026-09-06 09:20:44', '2026-09-06 09:20:44'),
(7, 42, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 11:03:12', '2026-09-06 14:33:12', '2026-09-06 14:33:12'),
(8, 41, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 11:10:27', '2026-09-06 14:40:27', '2026-09-06 14:40:27'),
(9, 45, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 12:11:38', '2026-09-06 15:41:38', '2026-09-06 15:41:38'),
(10, 46, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 15:34:16', '2026-09-06 19:04:16', '2026-09-06 19:04:16'),
(11, 48, 2, 399.00, 'admin', NULL, 'success', '2026-09-06 20:36:39', '2026-09-07 00:06:39', '2026-09-07 00:06:39'),
(13, 51, 2, 399.00, 'admin', NULL, 'success', '2026-09-09 21:03:17', '2026-09-10 00:33:17', '2026-09-10 00:33:17');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price`, `duration_days`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Free', 0.00, NULL, 'Free plan', 1, '2026-09-02 02:19:04', '2026-09-02 03:17:18'),
(2, 'Basic', 399.00, 365, 'Verified Matrimony Access', 1, '2026-09-02 02:19:04', '2026-09-02 03:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `preference_values`
--

CREATE TABLE `preference_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `preference_type` varchar(50) NOT NULL,
  `value` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `preference_values`
--

INSERT INTO `preference_values` (`id`, `user_id`, `preference_type`, `value`, `created_at`) VALUES
(1, 11, 'marital_status', 'never_married', '2026-08-30 02:31:25'),
(2, 11, 'sect', 'Sunni', '2026-08-30 02:31:25'),
(3, 11, 'sunni_group', 'Sunni', '2026-08-30 02:31:25'),
(4, 11, 'location', 'Kozhikode', '2026-08-30 02:31:25'),
(5, 12, 'marital_status', 'awaiting_divorce', '2026-08-30 02:40:13'),
(6, 12, 'sect', 'Shafi', '2026-08-30 02:40:13'),
(7, 12, 'location', 'Kozhikode', '2026-08-30 02:40:13'),
(8, 13, 'marital_status', 'never_married', '2026-08-30 16:04:56'),
(9, 13, 'sect', 'Other', '2026-08-30 16:04:56'),
(10, 13, 'location', 'Kozhikode', '2026-08-30 16:04:56'),
(11, 14, 'marital_status', 'never_married', '2026-08-30 16:19:21'),
(12, 14, 'sect', 'Sunni', '2026-08-30 16:19:21'),
(13, 14, 'sunni_group', 'EK-Sunni', '2026-08-30 16:19:21'),
(14, 14, 'education', 'Bachelor\'s Degree', '2026-08-30 16:19:21'),
(15, 14, 'education', 'Master\'s Degree', '2026-08-30 16:19:21'),
(16, 14, 'career_sector', 'Business / Self Employed', '2026-08-30 16:19:21'),
(17, 14, 'career_sector', 'Private', '2026-08-30 16:19:21'),
(18, 14, 'career_sector', 'Government', '2026-08-30 16:19:21'),
(19, 14, 'location', 'Malappuram', '2026-08-30 16:19:21'),
(20, 14, 'location', 'Kozhikode', '2026-08-30 16:19:21'),
(21, 14, 'location', 'Kannur', '2026-08-30 16:19:21'),
(22, 14, 'location', 'Ernakulam', '2026-08-30 16:19:21'),
(23, 15, 'marital_status', 'divorced', '2026-08-30 17:50:06'),
(24, 15, 'caste', 'Nair', '2026-08-30 17:50:06'),
(25, 15, 'sub_caste', 'Menon', '2026-08-30 17:50:06'),
(26, 15, 'education', 'PhD / Doctorate', '2026-08-30 17:50:06'),
(27, 15, 'education', 'Professional Degree', '2026-08-30 17:50:06'),
(28, 15, 'education_specific', 'BAMS', '2026-08-30 17:50:06'),
(29, 15, 'education_specific', 'BHMS', '2026-08-30 17:50:06'),
(30, 15, 'education_specific', 'MBBS', '2026-08-30 17:50:06'),
(31, 15, 'education_specific', 'LLB', '2026-08-30 17:50:06'),
(32, 15, 'career_sector', 'Government', '2026-08-30 17:50:06'),
(33, 15, 'location', 'Palakkad', '2026-08-30 17:50:06'),
(34, 15, 'location', 'Idukki', '2026-08-30 17:50:06'),
(35, 15, 'location', 'Kannur', '2026-08-30 17:50:06'),
(36, 16, 'marital_status', 'never_married', '2026-08-30 18:04:34'),
(37, 16, 'sect', 'Shafi', '2026-08-30 18:04:34'),
(38, 16, 'location', 'Kozhikode', '2026-08-30 18:04:34'),
(39, 17, 'marital_status', 'divorced', '2026-08-30 18:23:45'),
(40, 17, 'sect', 'Jamat Islami', '2026-08-30 18:23:45'),
(41, 17, 'sect', 'Other', '2026-08-30 18:23:45'),
(42, 17, 'education', 'PhD / Doctorate', '2026-08-30 18:23:45'),
(43, 17, 'education', 'Master\'s Degree', '2026-08-30 18:23:45'),
(44, 17, 'education', 'Professional Degree', '2026-08-30 18:23:45'),
(45, 17, 'education_specific', 'BDS', '2026-08-30 18:23:45'),
(46, 17, 'education_specific', 'MBBS', '2026-08-30 18:23:45'),
(47, 17, 'career_sector', 'Business / Self Employed', '2026-08-30 18:23:45'),
(48, 17, 'career_sector', 'Freelance', '2026-08-30 18:23:45'),
(49, 17, 'location', 'Kozhikode', '2026-08-30 18:23:45'),
(50, 17, 'location', 'Ernakulam', '2026-08-30 18:23:45'),
(51, 18, 'marital_status', 'separated', '2026-08-30 20:47:00'),
(52, 18, 'marital_status', 'never_married', '2026-08-30 20:47:00'),
(53, 18, 'marital_status', 'widowed', '2026-08-30 20:47:00'),
(54, 18, 'marital_status', 'awaiting_divorce', '2026-08-30 20:47:00'),
(55, 18, 'marital_status', 'nikah_divorce', '2026-08-30 20:47:00'),
(56, 18, 'marital_status', 'divorced', '2026-08-30 20:47:00'),
(57, 18, 'sect', 'Catholic', '2026-08-30 20:47:00'),
(58, 18, 'sect', 'Pentecostal', '2026-08-30 20:47:00'),
(59, 18, 'sect', 'Orthodox', '2026-08-30 20:47:00'),
(60, 18, 'sect', 'Protestant', '2026-08-30 20:47:00'),
(61, 18, 'sect', 'Other', '2026-08-30 20:47:00'),
(62, 18, 'sub_caste', 'Syro-Malabar Catholic', '2026-08-30 20:47:00'),
(63, 18, 'education', 'PhD / Doctorate', '2026-08-30 20:47:00'),
(64, 18, 'education', 'Master\'s Degree', '2026-08-30 20:47:00'),
(65, 18, 'education', 'Bachelor\'s Degree', '2026-08-30 20:47:00'),
(66, 18, 'education', 'Professional Degree', '2026-08-30 20:47:00'),
(67, 18, 'education_specific', 'BUMS', '2026-08-30 20:47:00'),
(68, 18, 'education_specific', 'BDS', '2026-08-30 20:47:00'),
(69, 18, 'career_sector', 'Business / Self Employed', '2026-08-30 20:47:00'),
(70, 18, 'career_sector', 'Private', '2026-08-30 20:47:00'),
(71, 18, 'career_sector', 'Freelance', '2026-08-30 20:47:00'),
(72, 18, 'location', 'Kozhikode', '2026-08-30 20:47:00'),
(73, 18, 'location', 'Alappuzha', '2026-08-30 20:47:00'),
(74, 18, 'location', 'Idukki', '2026-08-30 20:47:00'),
(75, 19, 'marital_status', 'divorced', '2026-08-30 23:32:46'),
(76, 19, 'sect', 'Sunni', '2026-08-30 23:32:46'),
(77, 19, 'sunni_group', 'Sunni', '2026-08-30 23:32:46'),
(78, 19, 'education', 'Diploma', '2026-08-30 23:32:46'),
(79, 19, 'education', 'Professional Degree', '2026-08-30 23:32:46'),
(80, 19, 'education', 'PhD / Doctorate', '2026-08-30 23:32:46'),
(81, 19, 'education', 'Master\'s Degree', '2026-08-30 23:32:46'),
(82, 19, 'education', 'Bachelor\'s Degree', '2026-08-30 23:32:46'),
(83, 19, 'education', 'ITI / Technical Certificate', '2026-08-30 23:32:46'),
(84, 19, 'education', 'Plus Two / Higher Secondary', '2026-08-30 23:32:46'),
(85, 19, 'education', 'Others / Below 10th', '2026-08-30 23:32:46'),
(86, 19, 'education_specific', 'MBBS', '2026-08-30 23:32:46'),
(87, 19, 'education_specific', 'BAMS', '2026-08-30 23:32:46'),
(88, 19, 'education_specific', 'BHMS', '2026-08-30 23:32:46'),
(89, 19, 'career_sector', 'Private', '2026-08-30 23:32:46'),
(90, 19, 'career_sector', 'Freelance', '2026-08-30 23:32:46'),
(91, 19, 'location', 'Malappuram', '2026-08-30 23:32:46'),
(92, 19, 'location', 'Alappuzha', '2026-08-30 23:32:46'),
(93, 19, 'location', 'Kasaragod', '2026-08-30 23:32:46'),
(94, 20, 'marital_status', 'never_married', '2026-08-31 05:16:43'),
(95, 20, 'marital_status', 'divorced', '2026-08-31 05:16:43'),
(96, 20, 'marital_status', 'widowed', '2026-08-31 05:16:43'),
(97, 20, 'marital_status', 'nikah_divorce', '2026-08-31 05:16:43'),
(98, 20, 'marital_status', 'separated', '2026-08-31 05:16:43'),
(99, 20, 'marital_status', 'awaiting_divorce', '2026-08-31 05:16:43'),
(100, 20, 'sect', 'Sunni', '2026-08-31 05:16:43'),
(101, 20, 'sect', 'Hanafi', '2026-08-31 05:16:43'),
(102, 20, 'sect', 'Salafi', '2026-08-31 05:16:43'),
(103, 20, 'sect', 'Jamat Islami', '2026-08-31 05:16:43'),
(104, 20, 'sect', 'Shafi', '2026-08-31 05:16:43'),
(105, 20, 'sect', 'Other', '2026-08-31 05:16:43'),
(106, 20, 'sunni_group', 'AP-Sunni', '2026-08-31 05:16:43'),
(107, 20, 'sunni_group', 'EK-Sunni', '2026-08-31 05:16:43'),
(108, 20, 'sunni_group', 'Sunni', '2026-08-31 05:16:43'),
(109, 20, 'salafi_group', 'KNM (Mainstream)', '2026-08-31 05:16:43'),
(110, 20, 'salafi_group', 'KNM Markazu Dawa', '2026-08-31 05:16:43'),
(111, 20, 'salafi_group', 'Salafi Independent', '2026-08-31 05:16:43'),
(112, 20, 'salafi_group', 'Wisdom', '2026-08-31 05:16:43'),
(113, 20, 'salafi_group', 'Other Salafi / Mujahid', '2026-08-31 05:16:43'),
(114, 20, 'education', 'PhD / Doctorate', '2026-08-31 05:16:43'),
(115, 20, 'education', 'Master\'s Degree', '2026-08-31 05:16:43'),
(116, 20, 'education', 'Bachelor\'s Degree', '2026-08-31 05:16:43'),
(117, 20, 'education', 'Professional Degree', '2026-08-31 05:16:43'),
(118, 20, 'education', 'Diploma', '2026-08-31 05:16:43'),
(119, 20, 'education', 'ITI / Technical Certificate', '2026-08-31 05:16:43'),
(120, 20, 'education', 'Religious / Islamic Education', '2026-08-31 05:16:43'),
(121, 20, 'education', 'Plus Two / Higher Secondary', '2026-08-31 05:16:43'),
(122, 20, 'education', 'Others / Below 10th', '2026-08-31 05:16:43'),
(123, 20, 'education_specific', 'MBBS', '2026-08-31 05:16:43'),
(124, 20, 'education_specific', 'BAMS', '2026-08-31 05:16:43'),
(125, 20, 'education_specific', 'BUMS', '2026-08-31 05:16:43'),
(126, 20, 'education_specific', 'LLB', '2026-08-31 05:16:43'),
(127, 20, 'education_specific', 'CMA', '2026-08-31 05:16:43'),
(128, 20, 'education_specific', 'BHMS', '2026-08-31 05:16:43'),
(129, 20, 'education_specific', 'BDS', '2026-08-31 05:16:43'),
(130, 20, 'education_specific', 'BE / BTech', '2026-08-31 05:16:43'),
(131, 20, 'education_specific', 'CA', '2026-08-31 05:16:43'),
(132, 20, 'education_specific', 'CS', '2026-08-31 05:16:43'),
(133, 20, 'education_specific', 'Other Professional', '2026-08-31 05:16:43'),
(134, 20, 'education_specific', 'PharmD', '2026-08-31 05:16:43'),
(135, 20, 'career_sector', 'Any', '2026-08-31 05:16:43'),
(136, 20, 'location', 'Kozhikode', '2026-08-31 05:16:43'),
(137, 20, 'location', 'Ernakulam', '2026-08-31 05:16:43'),
(138, 20, 'location', 'Alappuzha', '2026-08-31 05:16:43'),
(139, 20, 'location', 'Idukki', '2026-08-31 05:16:43'),
(140, 20, 'location', 'Kannur', '2026-08-31 05:16:43'),
(141, 20, 'location', 'Kasaragod', '2026-08-31 05:16:43'),
(142, 20, 'location', 'Kollam', '2026-08-31 05:16:43'),
(143, 20, 'location', 'Kottayam', '2026-08-31 05:16:43'),
(144, 20, 'location', 'Malappuram', '2026-08-31 05:16:43'),
(145, 20, 'location', 'Palakkad', '2026-08-31 05:16:43'),
(146, 20, 'location', 'Pathanamthitta', '2026-08-31 05:16:43'),
(147, 20, 'location', 'Thiruvananthapuram', '2026-08-31 05:16:43'),
(148, 20, 'location', 'Thrissur', '2026-08-31 05:16:43'),
(149, 20, 'location', 'Wayanad', '2026-08-31 05:16:43'),
(150, 21, 'marital_status', 'never_married', '2026-08-31 23:08:09'),
(151, 21, 'marital_status', 'divorced', '2026-08-31 23:08:09'),
(152, 21, 'marital_status', 'widowed', '2026-08-31 23:08:09'),
(153, 21, 'marital_status', 'nikah_divorce', '2026-08-31 23:08:09'),
(154, 21, 'marital_status', 'separated', '2026-08-31 23:08:09'),
(155, 21, 'marital_status', 'awaiting_divorce', '2026-08-31 23:08:09'),
(156, 21, 'sect', 'Sunni', '2026-08-31 23:08:09'),
(157, 21, 'sect', 'Salafi', '2026-08-31 23:08:09'),
(158, 21, 'sect', 'Hanafi', '2026-08-31 23:08:09'),
(159, 21, 'sect', 'Jamat Islami', '2026-08-31 23:08:09'),
(160, 21, 'sect', 'Shafi', '2026-08-31 23:08:09'),
(161, 21, 'sect', 'Other', '2026-08-31 23:08:09'),
(162, 21, 'education', 'PhD / Doctorate', '2026-08-31 23:08:09'),
(163, 21, 'education', 'Master\'s Degree', '2026-08-31 23:08:09'),
(164, 21, 'education', 'Professional Degree', '2026-08-31 23:08:09'),
(165, 21, 'education', 'Bachelor\'s Degree', '2026-08-31 23:08:09'),
(166, 21, 'education', 'Diploma', '2026-08-31 23:08:09'),
(167, 21, 'education', 'ITI / Technical Certificate', '2026-08-31 23:08:09'),
(168, 21, 'education', 'Religious / Islamic Education', '2026-08-31 23:08:09'),
(169, 21, 'education', 'Plus Two / Higher Secondary', '2026-08-31 23:08:09'),
(170, 21, 'education', 'Others / Below 10th', '2026-08-31 23:08:09'),
(171, 21, 'career_sector', 'Business / Self Employed', '2026-08-31 23:08:09'),
(172, 21, 'career_sector', 'Freelance', '2026-08-31 23:08:09'),
(173, 21, 'career_sector', 'Government', '2026-08-31 23:08:09'),
(174, 21, 'career_sector', 'Private', '2026-08-31 23:08:09'),
(175, 21, 'location', 'Kannur', '2026-08-31 23:08:09'),
(176, 21, 'location', 'Alappuzha', '2026-08-31 23:08:09'),
(177, 21, 'location', 'Malappuram', '2026-08-31 23:08:09'),
(178, 22, 'marital_status', 'never_married', '2026-09-01 09:11:19'),
(179, 22, 'sect', 'Sunni', '2026-09-01 09:11:19'),
(180, 22, 'sunni_group', 'EK-Sunni', '2026-09-01 09:11:19'),
(181, 22, 'education', 'Diploma', '2026-09-01 09:11:19'),
(182, 22, 'education', 'Bachelor\'s Degree', '2026-09-01 09:11:19'),
(183, 22, 'career_sector', 'Business / Self Employed', '2026-09-01 09:11:19'),
(184, 22, 'career_sector', 'Private', '2026-09-01 09:11:19'),
(185, 22, 'location', 'Kozhikode', '2026-09-01 09:11:19'),
(186, 24, 'marital_status', 'never_married', '2026-09-02 22:35:13'),
(187, 24, 'marital_status', 'widowed', '2026-09-02 22:35:13'),
(188, 24, 'marital_status', 'divorced', '2026-09-02 22:35:13'),
(189, 24, 'marital_status', 'nikah_divorce', '2026-09-02 22:35:13'),
(190, 24, 'marital_status', 'separated', '2026-09-02 22:35:13'),
(191, 24, 'marital_status', 'awaiting_divorce', '2026-09-02 22:35:13'),
(192, 24, 'sect', 'Hanafi', '2026-09-02 22:35:13'),
(193, 24, 'career_sector', 'Business / Self Employed', '2026-09-02 22:35:13'),
(194, 24, 'location', 'Malappuram', '2026-09-02 22:35:13'),
(195, 25, 'marital_status', 'never_married', '2026-09-02 22:53:33'),
(196, 25, 'sect', 'Jamat Islami', '2026-09-02 22:53:33'),
(197, 25, 'location', 'Alappuzha', '2026-09-02 22:53:33'),
(198, 26, 'marital_status', 'never_married', '2026-09-03 01:31:24'),
(199, 26, 'marital_status', 'divorced', '2026-09-03 01:31:24'),
(200, 26, 'marital_status', 'widowed', '2026-09-03 01:31:24'),
(201, 26, 'marital_status', 'nikah_divorce', '2026-09-03 01:31:24'),
(202, 26, 'marital_status', 'separated', '2026-09-03 01:31:24'),
(203, 26, 'marital_status', 'awaiting_divorce', '2026-09-03 01:31:24'),
(204, 26, 'sect', 'Shafi', '2026-09-03 01:31:24'),
(205, 26, 'sect', 'Sunni', '2026-09-03 01:31:24'),
(206, 26, 'sect', 'Salafi', '2026-09-03 01:31:24'),
(207, 26, 'sect', 'Hanafi', '2026-09-03 01:31:24'),
(208, 26, 'sect', 'Jamat Islami', '2026-09-03 01:31:24'),
(209, 26, 'sect', 'Other', '2026-09-03 01:31:24'),
(210, 26, 'sunni_group', 'AP-Sunni', '2026-09-03 01:31:24'),
(211, 26, 'sunni_group', 'EK-Sunni', '2026-09-03 01:31:24'),
(212, 26, 'sunni_group', 'Sunni', '2026-09-03 01:31:24'),
(213, 26, 'salafi_group', 'KNM (Mainstream)', '2026-09-03 01:31:24'),
(214, 26, 'salafi_group', 'KNM Markazu Dawa', '2026-09-03 01:31:24'),
(215, 26, 'salafi_group', 'Salafi Independent', '2026-09-03 01:31:24'),
(216, 26, 'salafi_group', 'Wisdom', '2026-09-03 01:31:24'),
(217, 26, 'salafi_group', 'Other Salafi / Mujahid', '2026-09-03 01:31:24'),
(218, 26, 'education', 'PhD / Doctorate', '2026-09-03 01:31:24'),
(219, 26, 'education', 'Master\'s Degree', '2026-09-03 01:31:24'),
(220, 26, 'education', 'Bachelor\'s Degree', '2026-09-03 01:31:24'),
(221, 26, 'education', 'Professional Degree', '2026-09-03 01:31:24'),
(222, 26, 'education', 'Diploma', '2026-09-03 01:31:24'),
(223, 26, 'education', 'ITI / Technical Certificate', '2026-09-03 01:31:24'),
(224, 26, 'education', 'Religious / Islamic Education', '2026-09-03 01:31:24'),
(225, 26, 'education', 'Plus Two / Higher Secondary', '2026-09-03 01:31:24'),
(226, 26, 'education', 'Others / Below 10th', '2026-09-03 01:31:24'),
(227, 26, 'education_specific', 'MBBS', '2026-09-03 01:31:24'),
(228, 26, 'education_specific', 'BDS', '2026-09-03 01:31:24'),
(229, 26, 'education_specific', 'BHMS', '2026-09-03 01:31:24'),
(230, 26, 'education_specific', 'BAMS', '2026-09-03 01:31:24'),
(231, 26, 'education_specific', 'BUMS', '2026-09-03 01:31:24'),
(232, 26, 'education_specific', 'BE / BTech', '2026-09-03 01:31:24'),
(233, 26, 'education_specific', 'CA', '2026-09-03 01:31:24'),
(234, 26, 'education_specific', 'LLB', '2026-09-03 01:31:24'),
(235, 26, 'education_specific', 'CMA', '2026-09-03 01:31:24'),
(236, 26, 'education_specific', 'Other Professional', '2026-09-03 01:31:24'),
(237, 26, 'education_specific', 'PharmD', '2026-09-03 01:31:24'),
(238, 26, 'education_specific', 'CS', '2026-09-03 01:31:24'),
(239, 26, 'career_sector', 'Any', '2026-09-03 01:31:24'),
(240, 26, 'location', 'Alappuzha', '2026-09-03 01:31:24'),
(241, 26, 'location', 'Ernakulam', '2026-09-03 01:31:24'),
(242, 26, 'location', 'Idukki', '2026-09-03 01:31:24'),
(243, 26, 'location', 'Kannur', '2026-09-03 01:31:24'),
(244, 26, 'location', 'Kasaragod', '2026-09-03 01:31:24'),
(245, 26, 'location', 'Kollam', '2026-09-03 01:31:24'),
(246, 26, 'location', 'Kottayam', '2026-09-03 01:31:24'),
(247, 26, 'location', 'Kozhikode', '2026-09-03 01:31:24'),
(248, 26, 'location', 'Malappuram', '2026-09-03 01:31:24'),
(249, 26, 'location', 'Palakkad', '2026-09-03 01:31:24'),
(250, 26, 'location', 'Pathanamthitta', '2026-09-03 01:31:24'),
(251, 26, 'location', 'Thiruvananthapuram', '2026-09-03 01:31:24'),
(252, 26, 'location', 'Thrissur', '2026-09-03 01:31:24'),
(253, 26, 'location', 'Wayanad', '2026-09-03 01:31:24'),
(254, 27, 'marital_status', 'never_married', '2026-09-03 01:48:07'),
(255, 27, 'marital_status', 'divorced', '2026-09-03 01:48:07'),
(256, 27, 'marital_status', 'widowed', '2026-09-03 01:48:07'),
(257, 27, 'marital_status', 'awaiting_divorce', '2026-09-03 01:48:07'),
(258, 27, 'marital_status', 'separated', '2026-09-03 01:48:07'),
(259, 27, 'marital_status', 'nikah_divorce', '2026-09-03 01:48:07'),
(260, 27, 'sect', 'Sunni', '2026-09-03 01:48:07'),
(261, 27, 'sect', 'Salafi', '2026-09-03 01:48:07'),
(262, 27, 'sect', 'Hanafi', '2026-09-03 01:48:07'),
(263, 27, 'sect', 'Jamat Islami', '2026-09-03 01:48:07'),
(264, 27, 'sect', 'Shafi', '2026-09-03 01:48:07'),
(265, 27, 'sect', 'Other', '2026-09-03 01:48:07'),
(266, 27, 'sunni_group', 'EK-Sunni', '2026-09-03 01:48:07'),
(267, 27, 'sunni_group', 'AP-Sunni', '2026-09-03 01:48:07'),
(268, 27, 'sunni_group', 'Sunni', '2026-09-03 01:48:07'),
(269, 27, 'salafi_group', 'KNM (Mainstream)', '2026-09-03 01:48:07'),
(270, 27, 'salafi_group', 'KNM Markazu Dawa', '2026-09-03 01:48:07'),
(271, 27, 'salafi_group', 'Salafi Independent', '2026-09-03 01:48:07'),
(272, 27, 'salafi_group', 'Wisdom', '2026-09-03 01:48:07'),
(273, 27, 'salafi_group', 'Other Salafi / Mujahid', '2026-09-03 01:48:07'),
(274, 27, 'education', 'Professional Degree', '2026-09-03 01:48:07'),
(275, 27, 'education', 'Bachelor\'s Degree', '2026-09-03 01:48:07'),
(276, 27, 'education', 'ITI / Technical Certificate', '2026-09-03 01:48:07'),
(277, 27, 'education', 'Diploma', '2026-09-03 01:48:07'),
(278, 27, 'education', 'Plus Two / Higher Secondary', '2026-09-03 01:48:07'),
(279, 27, 'education', 'Religious / Islamic Education', '2026-09-03 01:48:07'),
(280, 27, 'education', 'Others / Below 10th', '2026-09-03 01:48:07'),
(281, 27, 'education', 'PhD / Doctorate', '2026-09-03 01:48:07'),
(282, 27, 'education', 'Master\'s Degree', '2026-09-03 01:48:07'),
(283, 27, 'education_specific', 'BAMS', '2026-09-03 01:48:07'),
(284, 27, 'education_specific', 'BE / BTech', '2026-09-03 01:48:07'),
(285, 27, 'education_specific', 'CMA', '2026-09-03 01:48:07'),
(286, 27, 'education_specific', 'BUMS', '2026-09-03 01:48:07'),
(287, 27, 'education_specific', 'LLB', '2026-09-03 01:48:07'),
(288, 27, 'education_specific', 'CA', '2026-09-03 01:48:07'),
(289, 27, 'education_specific', 'CS', '2026-09-03 01:48:07'),
(290, 27, 'education_specific', 'Other Professional', '2026-09-03 01:48:07'),
(291, 27, 'education_specific', 'PharmD', '2026-09-03 01:48:07'),
(292, 27, 'education_specific', 'MBBS', '2026-09-03 01:48:07'),
(293, 27, 'education_specific', 'BDS', '2026-09-03 01:48:07'),
(294, 27, 'education_specific', 'BHMS', '2026-09-03 01:48:07'),
(295, 27, 'career_sector', 'Any', '2026-09-03 01:48:07'),
(296, 27, 'location', 'Malappuram', '2026-09-03 01:48:07'),
(297, 27, 'location', 'Alappuzha', '2026-09-03 01:48:07'),
(298, 27, 'location', 'Ernakulam', '2026-09-03 01:48:07'),
(299, 27, 'location', 'Idukki', '2026-09-03 01:48:07'),
(300, 27, 'location', 'Kannur', '2026-09-03 01:48:07'),
(301, 27, 'location', 'Kasaragod', '2026-09-03 01:48:07'),
(302, 27, 'location', 'Kottayam', '2026-09-03 01:48:07'),
(303, 27, 'location', 'Kozhikode', '2026-09-03 01:48:07'),
(304, 27, 'location', 'Kollam', '2026-09-03 01:48:07'),
(305, 27, 'location', 'Palakkad', '2026-09-03 01:48:07'),
(306, 27, 'location', 'Pathanamthitta', '2026-09-03 01:48:07'),
(307, 27, 'location', 'Thiruvananthapuram', '2026-09-03 01:48:07'),
(308, 27, 'location', 'Thrissur', '2026-09-03 01:48:07'),
(309, 27, 'location', 'Wayanad', '2026-09-03 01:48:07'),
(366, 27, 'family_status', 'Lower Middle Class', '2026-09-03 02:31:52'),
(367, 27, 'family_status', 'Upper Middle Class', '2026-09-03 02:31:52'),
(368, 27, 'family_status', 'Middle Class', '2026-09-03 02:31:52'),
(369, 27, 'family_status', 'Affluent', '2026-09-03 02:31:52'),
(370, 27, 'physical_status', 'any', '2026-09-03 02:31:52'),
(371, 27, 'location_radius', 'Within 10 km', '2026-09-03 02:31:52'),
(372, 27, 'income', '₹2 - ₹5 Lakh', '2026-09-03 02:31:52'),
(373, 27, 'income', 'Below ₹2 Lakh', '2026-09-03 02:31:52'),
(374, 27, 'income', '₹5 - ₹10 Lakh', '2026-09-03 02:31:52'),
(375, 27, 'income', '₹10 - ₹15 Lakh', '2026-09-03 02:31:52'),
(376, 27, 'complexion', 'Very Fair', '2026-09-03 02:31:52'),
(377, 27, 'complexion', 'Fair', '2026-09-03 02:31:52'),
(378, 27, 'complexion', 'Wheatish', '2026-09-03 02:31:52'),
(379, 27, 'complexion', 'Medium', '2026-09-03 02:31:52'),
(380, 29, 'marital_status', 'nikah_divorce', '2026-09-03 22:15:54'),
(381, 29, 'marital_status', 'never_married', '2026-09-03 22:15:54'),
(382, 29, 'marital_status', 'divorced', '2026-09-03 22:15:54'),
(383, 29, 'marital_status', 'widowed', '2026-09-03 22:15:54'),
(384, 29, 'marital_status', 'awaiting_divorce', '2026-09-03 22:15:54'),
(385, 29, 'marital_status', 'separated', '2026-09-03 22:15:54'),
(386, 29, 'sect', 'Sunni', '2026-09-03 22:15:54'),
(387, 29, 'sect', 'Salafi', '2026-09-03 22:15:54'),
(388, 29, 'sect', 'Jamat Islami', '2026-09-03 22:15:54'),
(389, 29, 'sect', 'Hanafi', '2026-09-03 22:15:54'),
(390, 29, 'sect', 'Other', '2026-09-03 22:15:54'),
(391, 29, 'sect', 'Shafi', '2026-09-03 22:15:54'),
(392, 29, 'sunni_group', 'Sunni', '2026-09-03 22:15:54'),
(393, 29, 'sunni_group', 'AP-Sunni', '2026-09-03 22:15:54'),
(394, 29, 'sunni_group', 'EK-Sunni', '2026-09-03 22:15:54'),
(395, 29, 'salafi_group', 'KNM (Mainstream)', '2026-09-03 22:15:54'),
(396, 29, 'salafi_group', 'KNM Markazu Dawa', '2026-09-03 22:15:54'),
(397, 29, 'salafi_group', 'Wisdom', '2026-09-03 22:15:54'),
(398, 29, 'salafi_group', 'Other Salafi / Mujahid', '2026-09-03 22:15:54'),
(399, 29, 'education', 'PhD / Doctorate', '2026-09-03 22:15:54'),
(400, 29, 'education', 'Master\'s Degree', '2026-09-03 22:15:54'),
(401, 29, 'education', 'Bachelor\'s Degree', '2026-09-03 22:15:54'),
(402, 29, 'education', 'Professional Degree', '2026-09-03 22:15:54'),
(403, 29, 'education', 'ITI / Technical Certificate', '2026-09-03 22:15:54'),
(404, 29, 'education', 'Plus Two / Higher Secondary', '2026-09-03 22:15:54'),
(405, 29, 'education', 'Others / Below 10th', '2026-09-03 22:15:54'),
(406, 29, 'education_specific', 'PharmD', '2026-09-03 22:15:54'),
(407, 29, 'education_specific', 'Other Professional', '2026-09-03 22:15:54'),
(408, 29, 'career_sector', 'Business / Self Employed', '2026-09-03 22:15:54'),
(409, 29, 'location', 'Malappuram', '2026-09-03 22:15:54'),
(410, 29, 'location', 'Ernakulam', '2026-09-03 22:15:54'),
(411, 29, 'location', 'Alappuzha', '2026-09-03 22:15:54'),
(412, 29, 'location', 'Idukki', '2026-09-03 22:15:54'),
(413, 29, 'location', 'Kannur', '2026-09-03 22:15:54'),
(414, 29, 'location', 'Kasaragod', '2026-09-03 22:15:54'),
(415, 29, 'location', 'Kollam', '2026-09-03 22:15:54'),
(416, 29, 'location', 'Kottayam', '2026-09-03 22:15:54'),
(417, 29, 'location', 'Kozhikode', '2026-09-03 22:15:54'),
(418, 29, 'location', 'Palakkad', '2026-09-03 22:15:54'),
(419, 29, 'location', 'Pathanamthitta', '2026-09-03 22:15:54'),
(420, 29, 'location', 'Thiruvananthapuram', '2026-09-03 22:15:54'),
(421, 29, 'location', 'Thrissur', '2026-09-03 22:15:54'),
(422, 29, 'location', 'Wayanad', '2026-09-03 22:15:54'),
(426, 29, 'family_status', 'Lower Middle Class', '2026-09-03 23:32:27'),
(427, 29, 'physical_status', 'Normal', '2026-09-03 23:32:27'),
(428, 29, 'location_radius', 'Within 10 km', '2026-09-03 23:32:27'),
(429, 30, 'marital_status', 'divorced', '2026-09-04 00:55:36'),
(430, 30, 'marital_status', 'never_married', '2026-09-04 00:55:36'),
(431, 30, 'marital_status', 'nikah_divorce', '2026-09-04 00:55:36'),
(432, 30, 'marital_status', 'widowed', '2026-09-04 00:55:36'),
(433, 30, 'marital_status', 'awaiting_divorce', '2026-09-04 00:55:36'),
(434, 30, 'marital_status', 'separated', '2026-09-04 00:55:36'),
(435, 30, 'sect', 'Sunni', '2026-09-04 00:55:36'),
(436, 30, 'sect', 'Salafi', '2026-09-04 00:55:36'),
(437, 30, 'sect', 'Hanafi', '2026-09-04 00:55:36'),
(438, 30, 'sect', 'Jamat Islami', '2026-09-04 00:55:36'),
(439, 30, 'sect', 'Shafi', '2026-09-04 00:55:36'),
(440, 30, 'sect', 'Other', '2026-09-04 00:55:36'),
(441, 30, 'sunni_group', 'AP-Sunni', '2026-09-04 00:55:36'),
(442, 30, 'sunni_group', 'EK-Sunni', '2026-09-04 00:55:36'),
(443, 30, 'sunni_group', 'Sunni', '2026-09-04 00:55:36'),
(444, 30, 'salafi_group', 'KNM (Mainstream)', '2026-09-04 00:55:36'),
(445, 30, 'salafi_group', 'KNM Markazu Dawa', '2026-09-04 00:55:36'),
(446, 30, 'salafi_group', 'Salafi Independent', '2026-09-04 00:55:36'),
(447, 30, 'salafi_group', 'Wisdom', '2026-09-04 00:55:36'),
(448, 30, 'salafi_group', 'Other Salafi / Mujahid', '2026-09-04 00:55:36'),
(449, 30, 'education', 'PhD / Doctorate', '2026-09-04 00:55:36'),
(450, 30, 'education', 'Master\'s Degree', '2026-09-04 00:55:36'),
(451, 30, 'education', 'Bachelor\'s Degree', '2026-09-04 00:55:36'),
(452, 30, 'education', 'Professional Degree', '2026-09-04 00:55:36'),
(453, 30, 'education', 'Diploma', '2026-09-04 00:55:36'),
(454, 30, 'education', 'ITI / Technical Certificate', '2026-09-04 00:55:36'),
(455, 30, 'education', 'Plus Two / Higher Secondary', '2026-09-04 00:55:36'),
(456, 30, 'education', 'Religious / Islamic Education', '2026-09-04 00:55:36'),
(457, 30, 'education', 'Others / Below 10th', '2026-09-04 00:55:36'),
(458, 30, 'education_specific', 'MBBS', '2026-09-04 00:55:36'),
(459, 30, 'education_specific', 'BDS', '2026-09-04 00:55:36'),
(460, 30, 'education_specific', 'BHMS', '2026-09-04 00:55:36'),
(461, 30, 'education_specific', 'BAMS', '2026-09-04 00:55:36'),
(462, 30, 'education_specific', 'BUMS', '2026-09-04 00:55:36'),
(463, 30, 'education_specific', 'BE / BTech', '2026-09-04 00:55:36'),
(464, 30, 'education_specific', 'CA', '2026-09-04 00:55:36'),
(465, 30, 'education_specific', 'LLB', '2026-09-04 00:55:36'),
(466, 30, 'education_specific', 'CMA', '2026-09-04 00:55:36'),
(467, 30, 'education_specific', 'CS', '2026-09-04 00:55:36'),
(468, 30, 'education_specific', 'Other Professional', '2026-09-04 00:55:36'),
(469, 30, 'education_specific', 'PharmD', '2026-09-04 00:55:36'),
(470, 30, 'career_sector', 'Business / Self Employed', '2026-09-04 00:55:36'),
(471, 30, 'career_sector', 'Private', '2026-09-04 00:55:36'),
(472, 30, 'career_sector', 'Freelance', '2026-09-04 00:55:36'),
(473, 30, 'career_sector', 'Government', '2026-09-04 00:55:36'),
(474, 30, 'location', 'Malappuram', '2026-09-04 00:55:36'),
(475, 30, 'location', 'Alappuzha', '2026-09-04 00:55:36'),
(476, 30, 'location', 'Ernakulam', '2026-09-04 00:55:36'),
(477, 30, 'location', 'Idukki', '2026-09-04 00:55:36'),
(478, 30, 'location', 'Kannur', '2026-09-04 00:55:36'),
(479, 30, 'location', 'Kasaragod', '2026-09-04 00:55:36'),
(480, 30, 'location', 'Kollam', '2026-09-04 00:55:36'),
(481, 30, 'location', 'Kottayam', '2026-09-04 00:55:36'),
(482, 30, 'location', 'Kozhikode', '2026-09-04 00:55:36'),
(483, 30, 'location', 'Palakkad', '2026-09-04 00:55:36'),
(484, 30, 'location', 'Pathanamthitta', '2026-09-04 00:55:36'),
(485, 30, 'location', 'Thiruvananthapuram', '2026-09-04 00:55:36'),
(486, 30, 'location', 'Thrissur', '2026-09-04 00:55:36'),
(487, 30, 'location', 'Wayanad', '2026-09-04 00:55:36'),
(488, 31, 'marital_status', 'never_married', '2026-09-04 02:11:23'),
(489, 31, 'marital_status', 'divorced', '2026-09-04 02:11:23'),
(490, 31, 'marital_status', 'widowed', '2026-09-04 02:11:23'),
(491, 31, 'marital_status', 'awaiting_divorce', '2026-09-04 02:11:23'),
(492, 31, 'marital_status', 'separated', '2026-09-04 02:11:23'),
(493, 31, 'marital_status', 'nikah_divorce', '2026-09-04 02:11:23'),
(494, 31, 'sect', 'Sunni', '2026-09-04 02:11:23'),
(495, 31, 'sect', 'Salafi', '2026-09-04 02:11:23'),
(496, 31, 'sect', 'Hanafi', '2026-09-04 02:11:23'),
(497, 31, 'sect', 'Jamat Islami', '2026-09-04 02:11:23'),
(498, 31, 'sect', 'Shafi', '2026-09-04 02:11:23'),
(499, 31, 'sect', 'Other', '2026-09-04 02:11:23'),
(500, 31, 'sunni_group', 'AP-Sunni', '2026-09-04 02:11:23'),
(501, 31, 'sunni_group', 'EK-Sunni', '2026-09-04 02:11:23'),
(502, 31, 'sunni_group', 'Sunni', '2026-09-04 02:11:23'),
(503, 31, 'salafi_group', 'KNM (Mainstream)', '2026-09-04 02:11:23'),
(504, 31, 'salafi_group', 'KNM Markazu Dawa', '2026-09-04 02:11:23'),
(505, 31, 'salafi_group', 'Salafi Independent', '2026-09-04 02:11:23'),
(506, 31, 'salafi_group', 'Wisdom', '2026-09-04 02:11:23'),
(507, 31, 'salafi_group', 'Other Salafi / Mujahid', '2026-09-04 02:11:23'),
(508, 31, 'education', 'PhD / Doctorate', '2026-09-04 02:11:23'),
(509, 31, 'education', 'Master\'s Degree', '2026-09-04 02:11:23'),
(510, 31, 'education', 'Bachelor\'s Degree', '2026-09-04 02:11:23'),
(511, 31, 'education', 'Professional Degree', '2026-09-04 02:11:23'),
(512, 31, 'education', 'Diploma', '2026-09-04 02:11:23'),
(513, 31, 'education', 'ITI / Technical Certificate', '2026-09-04 02:11:23'),
(514, 31, 'education', 'Religious / Islamic Education', '2026-09-04 02:11:23'),
(515, 31, 'education', 'Plus Two / Higher Secondary', '2026-09-04 02:11:23'),
(516, 31, 'education', 'Others / Below 10th', '2026-09-04 02:11:23'),
(517, 31, 'career_sector', 'Any', '2026-09-04 02:11:23'),
(518, 31, 'location', 'Malappuram', '2026-09-04 02:11:23'),
(519, 31, 'location', 'Alappuzha', '2026-09-04 02:11:23'),
(520, 31, 'location', 'Ernakulam', '2026-09-04 02:11:23'),
(521, 31, 'location', 'Idukki', '2026-09-04 02:11:23'),
(522, 31, 'location', 'Kannur', '2026-09-04 02:11:23'),
(523, 31, 'location', 'Kasaragod', '2026-09-04 02:11:23'),
(524, 31, 'location', 'Kollam', '2026-09-04 02:11:23'),
(525, 31, 'location', 'Kottayam', '2026-09-04 02:11:23'),
(526, 31, 'location', 'Kozhikode', '2026-09-04 02:11:23'),
(527, 31, 'location', 'Palakkad', '2026-09-04 02:11:23'),
(528, 31, 'location', 'Pathanamthitta', '2026-09-04 02:11:23'),
(529, 31, 'location', 'Thiruvananthapuram', '2026-09-04 02:11:23'),
(530, 31, 'location', 'Thrissur', '2026-09-04 02:11:23'),
(531, 31, 'location', 'Wayanad', '2026-09-04 02:11:23'),
(532, 34, 'marital_status', 'separated', '2026-09-05 23:51:02'),
(533, 34, 'marital_status', 'never_married', '2026-09-05 23:51:02'),
(534, 34, 'marital_status', 'divorced', '2026-09-05 23:51:02'),
(535, 34, 'marital_status', 'widowed', '2026-09-05 23:51:02'),
(536, 34, 'marital_status', 'nikah_divorce', '2026-09-05 23:51:02'),
(537, 34, 'marital_status', 'awaiting_divorce', '2026-09-05 23:51:02'),
(538, 34, 'sect', 'Salafi', '2026-09-05 23:51:02'),
(539, 34, 'sect', 'Jamat Islami', '2026-09-05 23:51:02'),
(540, 34, 'sect', 'Sunni', '2026-09-05 23:51:02'),
(541, 34, 'caste', 'Any', '2026-09-05 23:51:02'),
(542, 34, 'education', 'Professional Degree', '2026-09-05 23:51:02'),
(543, 34, 'education', 'Master\'s Degree', '2026-09-05 23:51:02'),
(544, 34, 'education', 'PhD / Doctorate', '2026-09-05 23:51:02'),
(545, 34, 'education', 'Bachelor\'s Degree', '2026-09-05 23:51:02'),
(546, 34, 'education', 'Religious / Islamic Education', '2026-09-05 23:51:02'),
(547, 34, 'education', 'Plus Two / Higher Secondary', '2026-09-05 23:51:02'),
(548, 34, 'education', 'Diploma', '2026-09-05 23:51:02'),
(549, 34, 'education', 'ITI / Technical Certificate', '2026-09-05 23:51:02'),
(550, 34, 'education_specific', 'MBBS', '2026-09-05 23:51:02'),
(551, 34, 'career_sector', 'Any', '2026-09-05 23:51:02'),
(552, 34, 'location', 'Malappuram', '2026-09-05 23:51:02'),
(553, 34, 'location', 'Thiruvananthapuram', '2026-09-05 23:51:02'),
(554, 35, 'marital_status', 'nikah_divorce', '2026-09-05 23:57:09'),
(555, 35, 'caste', 'Any', '2026-09-05 23:57:09'),
(556, 35, 'location', 'Malappuram', '2026-09-05 23:57:09'),
(557, 35, 'location', 'Pathanamthitta', '2026-09-05 23:57:09'),
(558, 35, 'location', 'Ernakulam', '2026-09-05 23:57:09'),
(559, 37, 'marital_status', 'never_married', '2026-09-06 00:37:28'),
(560, 37, 'sect', 'Shafi', '2026-09-06 00:37:28'),
(561, 37, 'location', 'Malappuram', '2026-09-06 00:37:28'),
(562, 39, 'marital_status', 'divorced', '2026-09-06 00:42:32'),
(563, 39, 'sect', 'Salafi', '2026-09-06 00:42:32'),
(564, 39, 'salafi_group', 'Any', '2026-09-06 00:42:32'),
(565, 39, 'location', 'Malappuram', '2026-09-06 00:42:32'),
(566, 39, 'location', 'All Kerala', '2026-09-06 00:42:32'),
(567, 40, 'marital_status', 'never_married', '2026-09-06 00:48:47'),
(568, 40, 'marital_status', 'awaiting_divorce', '2026-09-06 00:48:47'),
(569, 40, 'marital_status', 'nikah_divorce', '2026-09-06 00:48:47'),
(570, 40, 'marital_status', 'widowed', '2026-09-06 00:48:47'),
(571, 40, 'marital_status', 'divorced', '2026-09-06 00:48:47'),
(572, 40, 'marital_status', 'separated', '2026-09-06 00:48:47'),
(573, 40, 'sect', 'Sunni', '2026-09-06 00:48:47'),
(574, 40, 'sunni_group', 'Any', '2026-09-06 00:48:47'),
(575, 40, 'education', 'PhD / Doctorate', '2026-09-06 00:48:47'),
(576, 40, 'education', 'Master\'s Degree', '2026-09-06 00:48:47'),
(577, 40, 'education', 'Bachelor\'s Degree', '2026-09-06 00:48:47'),
(578, 40, 'education', 'Professional Degree', '2026-09-06 00:48:47'),
(579, 40, 'education', 'Diploma', '2026-09-06 00:48:47'),
(580, 40, 'education', 'ITI / Technical Certificate', '2026-09-06 00:48:47'),
(581, 40, 'education', 'Plus Two / Higher Secondary', '2026-09-06 00:48:47'),
(582, 40, 'education', 'Religious / Islamic Education', '2026-09-06 00:48:47'),
(583, 40, 'career_sector', 'Business / Self Employed', '2026-09-06 00:48:47'),
(584, 40, 'career_sector', 'Private', '2026-09-06 00:48:47'),
(585, 40, 'career_sector', 'Freelance', '2026-09-06 00:48:47'),
(586, 40, 'career_sector', 'Government', '2026-09-06 00:48:47'),
(587, 40, 'location', 'Malappuram', '2026-09-06 00:48:47'),
(588, 40, 'location', 'All Kerala', '2026-09-06 00:48:47'),
(589, 41, 'marital_status', 'never_married', '2026-09-06 01:35:58'),
(590, 41, 'marital_status', 'divorced', '2026-09-06 01:35:58'),
(591, 41, 'marital_status', 'widowed', '2026-09-06 01:35:58'),
(592, 41, 'marital_status', 'nikah_divorce', '2026-09-06 01:35:58'),
(593, 41, 'marital_status', 'separated', '2026-09-06 01:35:58'),
(594, 41, 'marital_status', 'awaiting_divorce', '2026-09-06 01:35:58'),
(595, 41, 'sect', 'Any', '2026-09-06 01:35:58'),
(596, 41, 'education', 'PhD / Doctorate', '2026-09-06 01:35:58'),
(597, 41, 'education', 'Master\'s Degree', '2026-09-06 01:35:58'),
(598, 41, 'education', 'Bachelor\'s Degree', '2026-09-06 01:35:58'),
(599, 41, 'education', 'Professional Degree', '2026-09-06 01:35:58'),
(600, 41, 'education', 'Diploma', '2026-09-06 01:35:58'),
(601, 41, 'education', 'ITI / Technical Certificate', '2026-09-06 01:35:58'),
(602, 41, 'education', 'Religious / Islamic Education', '2026-09-06 01:35:58'),
(603, 41, 'education', 'Plus Two / Higher Secondary', '2026-09-06 01:35:58'),
(604, 41, 'education', 'Others / Below 10th', '2026-09-06 01:35:58'),
(605, 41, 'career_sector', 'Business / Self Employed', '2026-09-06 01:35:58'),
(606, 41, 'career_sector', 'Private', '2026-09-06 01:35:58'),
(607, 41, 'career_sector', 'Freelance', '2026-09-06 01:35:58'),
(608, 41, 'career_sector', 'Government', '2026-09-06 01:35:58'),
(609, 41, 'location', 'Malappuram', '2026-09-06 01:35:58'),
(610, 41, 'location', 'All Kerala', '2026-09-06 01:35:58'),
(614, 41, 'family_status', 'any', '2026-09-06 02:35:22'),
(615, 41, 'physical_status', 'Normal', '2026-09-06 02:35:22'),
(616, 41, 'location_radius', 'Within 25 km', '2026-09-06 02:35:22'),
(617, 41, 'income', 'any', '2026-09-06 02:35:22'),
(618, 42, 'marital_status', 'divorced', '2026-09-06 03:02:55'),
(619, 42, 'marital_status', 'never_married', '2026-09-06 03:02:55'),
(620, 42, 'marital_status', 'nikah_divorce', '2026-09-06 03:02:55'),
(621, 42, 'marital_status', 'widowed', '2026-09-06 03:02:55'),
(622, 42, 'marital_status', 'awaiting_divorce', '2026-09-06 03:02:55'),
(623, 42, 'marital_status', 'separated', '2026-09-06 03:02:55'),
(624, 42, 'sect', 'Any', '2026-09-06 03:02:55'),
(625, 42, 'education', 'PhD / Doctorate', '2026-09-06 03:02:55'),
(626, 42, 'education', 'Master\'s Degree', '2026-09-06 03:02:55'),
(627, 42, 'education', 'Bachelor\'s Degree', '2026-09-06 03:02:55'),
(628, 42, 'education', 'Professional Degree', '2026-09-06 03:02:55'),
(629, 42, 'education', 'Diploma', '2026-09-06 03:02:55'),
(630, 42, 'education', 'ITI / Technical Certificate', '2026-09-06 03:02:55'),
(631, 42, 'education', 'Religious / Islamic Education', '2026-09-06 03:02:55'),
(632, 42, 'education', 'Plus Two / Higher Secondary', '2026-09-06 03:02:55'),
(633, 42, 'education', 'Others / Below 10th', '2026-09-06 03:02:55'),
(634, 42, 'education_specific', 'MBBS', '2026-09-06 03:02:55'),
(635, 42, 'education_specific', 'BDS', '2026-09-06 03:02:55'),
(636, 42, 'education_specific', 'BHMS', '2026-09-06 03:02:55'),
(637, 42, 'education_specific', 'BAMS', '2026-09-06 03:02:55'),
(638, 42, 'education_specific', 'BE / BTech', '2026-09-06 03:02:55'),
(639, 42, 'education_specific', 'CS', '2026-09-06 03:02:55'),
(640, 42, 'education_specific', 'LLB', '2026-09-06 03:02:55'),
(641, 42, 'education_specific', 'BUMS', '2026-09-06 03:02:55'),
(642, 42, 'education_specific', 'CMA', '2026-09-06 03:02:55'),
(643, 42, 'education_specific', 'PharmD', '2026-09-06 03:02:55'),
(644, 42, 'education_specific', 'Other Professional', '2026-09-06 03:02:55'),
(645, 42, 'education_specific', 'CA', '2026-09-06 03:02:55'),
(646, 42, 'career_sector', 'Private', '2026-09-06 03:02:55'),
(647, 42, 'career_sector', 'Government', '2026-09-06 03:02:55'),
(648, 42, 'career_sector', 'Business / Self Employed', '2026-09-06 03:02:55'),
(649, 42, 'location', 'Wayanad', '2026-09-06 03:02:55'),
(650, 42, 'location', 'All Kerala', '2026-09-06 03:02:55'),
(651, 42, 'location', 'Kottayam', '2026-09-06 03:02:55'),
(652, 42, 'family_status', 'any', '2026-09-06 03:37:29'),
(653, 42, 'physical_status', 'any', '2026-09-06 03:37:29'),
(654, 42, 'location_radius', 'Within 25 km', '2026-09-06 03:37:29'),
(655, 43, 'marital_status', 'divorced', '2026-09-06 08:59:37'),
(656, 43, 'sect', 'Shafi', '2026-09-06 08:59:37'),
(657, 43, 'education', 'Bachelor\'s Degree', '2026-09-06 08:59:37'),
(658, 43, 'career_sector', 'Private', '2026-09-06 08:59:37'),
(659, 43, 'career_sector', 'Government', '2026-09-06 08:59:37'),
(660, 43, 'career_sector', 'Business / Self Employed', '2026-09-06 08:59:37'),
(661, 43, 'location', 'Malappuram', '2026-09-06 08:59:37'),
(662, 43, 'location', 'All Kerala', '2026-09-06 08:59:37'),
(663, 44, 'marital_status', 'never_married', '2026-09-06 14:05:40'),
(664, 44, 'sect', 'Sunni', '2026-09-06 14:05:40'),
(665, 44, 'sunni_group', 'Sunni', '2026-09-06 14:05:40'),
(666, 44, 'education', 'Professional Degree', '2026-09-06 14:05:40'),
(667, 44, 'education_specific', 'MBBS (General Medical Doctor)', '2026-09-06 14:05:40'),
(668, 44, 'career_sector', 'Business / Self Employed', '2026-09-06 14:05:40'),
(669, 44, 'location', 'Malappuram', '2026-09-06 14:05:40'),
(670, 44, 'location', 'All Kerala', '2026-09-06 14:05:40'),
(671, 45, 'marital_status', 'never_married', '2026-09-06 15:39:58'),
(672, 45, 'marital_status', 'divorced', '2026-09-06 15:39:58'),
(673, 45, 'marital_status', 'widowed', '2026-09-06 15:39:58'),
(674, 45, 'marital_status', 'nikah_divorce', '2026-09-06 15:39:58'),
(675, 45, 'marital_status', 'separated', '2026-09-06 15:39:58'),
(676, 45, 'marital_status', 'awaiting_divorce', '2026-09-06 15:39:58'),
(677, 45, 'sect', 'Any', '2026-09-06 15:39:58'),
(678, 45, 'education', 'PhD / Doctorate', '2026-09-06 15:39:58'),
(679, 45, 'education', 'Master\'s Degree', '2026-09-06 15:39:58'),
(680, 45, 'education', 'Bachelor\'s Degree', '2026-09-06 15:39:58'),
(681, 45, 'education', 'Professional Degree', '2026-09-06 15:39:58'),
(682, 45, 'education', 'Diploma', '2026-09-06 15:39:58'),
(683, 45, 'education', 'ITI / Technical Certificate', '2026-09-06 15:39:58'),
(684, 45, 'education', 'Plus Two / Higher Secondary', '2026-09-06 15:39:58'),
(685, 45, 'education', 'Religious / Islamic Education', '2026-09-06 15:39:58'),
(686, 45, 'education', 'Others / Below 10th', '2026-09-06 15:39:58'),
(687, 45, 'education_specific', 'MBBS', '2026-09-06 15:39:58'),
(688, 45, 'education_specific', 'MD / MS / DNB', '2026-09-06 15:39:58'),
(689, 45, 'education_specific', 'BAMS / BHMS / BUMS', '2026-09-06 15:39:58'),
(690, 45, 'education_specific', 'BDS / MDS', '2026-09-06 15:39:58'),
(691, 45, 'education_specific', 'BE / B.Tech', '2026-09-06 15:39:58'),
(692, 45, 'education_specific', 'ME / M.Tech', '2026-09-06 15:39:58'),
(693, 45, 'education_specific', 'BPT / MPT', '2026-09-06 15:39:58'),
(694, 45, 'education_specific', 'B.Pharm / Pharm.D', '2026-09-06 15:39:58'),
(695, 45, 'education_specific', 'CA / CMA / CS / ACCA', '2026-09-06 15:39:58'),
(696, 45, 'education_specific', 'LLB / LLM', '2026-09-06 15:39:58'),
(697, 45, 'education_specific', 'Other Professional', '2026-09-06 15:39:58'),
(698, 45, 'career_sector', 'Any', '2026-09-06 15:39:58'),
(699, 45, 'location', 'Malappuram', '2026-09-06 15:39:58'),
(700, 45, 'location', 'All Kerala', '2026-09-06 15:39:58'),
(701, 45, 'family_status', 'any', '2026-09-06 15:40:56'),
(702, 45, 'physical_status', 'any', '2026-09-06 15:40:56'),
(703, 45, 'location_radius', 'Within 25 km', '2026-09-06 15:40:56'),
(704, 45, 'income', 'any', '2026-09-06 15:40:56'),
(705, 45, 'complexion', 'any', '2026-09-06 15:40:56'),
(706, 46, 'marital_status', 'never_married', '2026-09-06 16:25:48'),
(707, 46, 'marital_status', 'divorced', '2026-09-06 16:25:48'),
(708, 46, 'marital_status', 'widowed', '2026-09-06 16:25:48'),
(709, 46, 'marital_status', 'nikah_divorce', '2026-09-06 16:25:48'),
(710, 46, 'marital_status', 'separated', '2026-09-06 16:25:48'),
(711, 46, 'marital_status', 'awaiting_divorce', '2026-09-06 16:25:48'),
(712, 46, 'sect', 'Any', '2026-09-06 16:25:48'),
(713, 46, 'education', 'PhD / Doctorate', '2026-09-06 16:25:48'),
(714, 46, 'education', 'Master\'s Degree', '2026-09-06 16:25:48'),
(715, 46, 'education', 'Bachelor\'s Degree', '2026-09-06 16:25:48'),
(716, 46, 'education', 'Professional Degree', '2026-09-06 16:25:48'),
(717, 46, 'education', 'Diploma', '2026-09-06 16:25:48'),
(718, 46, 'education', 'ITI / Technical Certificate', '2026-09-06 16:25:48'),
(719, 46, 'education', 'Religious / Islamic Education', '2026-09-06 16:25:48'),
(720, 46, 'education', 'Plus Two / Higher Secondary', '2026-09-06 16:25:48'),
(721, 46, 'education', 'Others / Below 10th', '2026-09-06 16:25:48'),
(722, 46, 'education_specific', 'MBBS', '2026-09-06 16:25:48'),
(723, 46, 'education_specific', 'MD / MS / DNB', '2026-09-06 16:25:48'),
(724, 46, 'education_specific', 'BAMS / BHMS / BUMS', '2026-09-06 16:25:48'),
(725, 46, 'education_specific', 'BE / B.Tech', '2026-09-06 16:25:48'),
(726, 46, 'education_specific', 'BDS / MDS', '2026-09-06 16:25:48'),
(727, 46, 'education_specific', 'ME / M.Tech', '2026-09-06 16:25:48'),
(728, 46, 'education_specific', 'B.Pharm / Pharm.D', '2026-09-06 16:25:48'),
(729, 46, 'education_specific', 'BPT / MPT', '2026-09-06 16:25:48'),
(730, 46, 'education_specific', 'CA / CMA / CS / ACCA', '2026-09-06 16:25:48'),
(731, 46, 'education_specific', 'LLB / LLM', '2026-09-06 16:25:48'),
(732, 46, 'education_specific', 'Other Professional', '2026-09-06 16:25:48'),
(733, 46, 'location', 'Malappuram', '2026-09-06 16:25:48'),
(734, 46, 'location', 'All Kerala', '2026-09-06 16:25:48'),
(735, 46, 'family_status', 'any', '2026-09-06 16:27:20'),
(736, 46, 'physical_status', 'any', '2026-09-06 16:27:20'),
(737, 46, 'location_radius', 'Within 10 km', '2026-09-06 16:27:20'),
(738, 46, 'income', 'any', '2026-09-06 16:27:20'),
(739, 46, 'complexion', 'any', '2026-09-06 16:27:20'),
(740, 47, 'marital_status', 'never_married', '2026-09-06 23:51:35'),
(741, 47, 'marital_status', 'divorced', '2026-09-06 23:51:35'),
(742, 47, 'marital_status', 'nikah_divorce', '2026-09-06 23:51:35'),
(743, 47, 'marital_status', 'widowed', '2026-09-06 23:51:35'),
(744, 47, 'marital_status', 'awaiting_divorce', '2026-09-06 23:51:35'),
(745, 47, 'marital_status', 'separated', '2026-09-06 23:51:35'),
(746, 47, 'sect', 'Any', '2026-09-06 23:51:35'),
(747, 47, 'education', 'PhD / Doctorate', '2026-09-06 23:51:35'),
(748, 47, 'education', 'Master\'s Degree', '2026-09-06 23:51:35'),
(749, 47, 'education', 'Bachelor\'s Degree', '2026-09-06 23:51:35'),
(750, 47, 'education', 'Professional Degree', '2026-09-06 23:51:35'),
(751, 47, 'education', 'Diploma', '2026-09-06 23:51:35'),
(752, 47, 'education', 'ITI / Technical Certificate', '2026-09-06 23:51:35'),
(753, 47, 'education', 'Religious / Islamic Education', '2026-09-06 23:51:35'),
(754, 47, 'education', 'Plus Two / Higher Secondary', '2026-09-06 23:51:35'),
(755, 47, 'education', 'Others / Below 10th', '2026-09-06 23:51:35'),
(756, 47, 'education_specific', 'MD / MS / DNB', '2026-09-06 23:51:35'),
(757, 47, 'education_specific', 'BDS / MDS', '2026-09-06 23:51:35'),
(758, 47, 'education_specific', 'MBBS', '2026-09-06 23:51:35'),
(759, 47, 'education_specific', 'BE / B.Tech', '2026-09-06 23:51:35'),
(760, 47, 'education_specific', 'ME / M.Tech', '2026-09-06 23:51:35'),
(761, 47, 'education_specific', 'B.Pharm / Pharm.D', '2026-09-06 23:51:35'),
(762, 47, 'education_specific', 'BAMS / BHMS / BUMS', '2026-09-06 23:51:35'),
(763, 47, 'education_specific', 'CA / CMA / CS / ACCA', '2026-09-06 23:51:35'),
(764, 47, 'education_specific', 'LLB / LLM', '2026-09-06 23:51:35'),
(765, 47, 'education_specific', 'BPT / MPT', '2026-09-06 23:51:35'),
(766, 47, 'education_specific', 'Other Professional', '2026-09-06 23:51:35'),
(767, 47, 'career_sector', 'Any', '2026-09-06 23:51:35'),
(768, 47, 'location', 'Malappuram', '2026-09-06 23:51:35'),
(769, 47, 'location', 'All Kerala', '2026-09-06 23:51:35'),
(770, 48, 'marital_status', 'never_married', '2026-09-06 23:54:39'),
(771, 48, 'marital_status', 'divorced', '2026-09-06 23:54:39'),
(772, 48, 'marital_status', 'widowed', '2026-09-06 23:54:39'),
(773, 48, 'marital_status', 'nikah_divorce', '2026-09-06 23:54:39'),
(774, 48, 'marital_status', 'separated', '2026-09-06 23:54:39'),
(775, 48, 'marital_status', 'awaiting_divorce', '2026-09-06 23:54:39'),
(776, 48, 'sect', 'Any', '2026-09-06 23:54:39'),
(777, 48, 'education', 'Others / Below 10th', '2026-09-06 23:54:39'),
(778, 48, 'education', 'Plus Two / Higher Secondary', '2026-09-06 23:54:39'),
(779, 48, 'education', 'Religious / Islamic Education', '2026-09-06 23:54:39'),
(780, 48, 'education', 'ITI / Technical Certificate', '2026-09-06 23:54:39'),
(781, 48, 'education', 'Diploma', '2026-09-06 23:54:39'),
(782, 48, 'education', 'Professional Degree', '2026-09-06 23:54:39'),
(783, 48, 'education', 'Master\'s Degree', '2026-09-06 23:54:39'),
(784, 48, 'education', 'PhD / Doctorate', '2026-09-06 23:54:39'),
(785, 48, 'education', 'Bachelor\'s Degree', '2026-09-06 23:54:39'),
(786, 48, 'education_specific', 'MBBS', '2026-09-06 23:54:39'),
(787, 48, 'education_specific', 'MD / MS / DNB', '2026-09-06 23:54:39'),
(788, 48, 'education_specific', 'BAMS / BHMS / BUMS', '2026-09-06 23:54:39'),
(789, 48, 'education_specific', 'BDS / MDS', '2026-09-06 23:54:39'),
(790, 48, 'education_specific', 'BE / B.Tech', '2026-09-06 23:54:39'),
(791, 48, 'education_specific', 'ME / M.Tech', '2026-09-06 23:54:39'),
(792, 48, 'education_specific', 'BPT / MPT', '2026-09-06 23:54:39'),
(793, 48, 'education_specific', 'B.Pharm / Pharm.D', '2026-09-06 23:54:39'),
(794, 48, 'education_specific', 'CA / CMA / CS / ACCA', '2026-09-06 23:54:39'),
(795, 48, 'education_specific', 'LLB / LLM', '2026-09-06 23:54:39'),
(796, 48, 'education_specific', 'Other Professional', '2026-09-06 23:54:39'),
(797, 48, 'career_sector', 'Any', '2026-09-06 23:54:39'),
(798, 48, 'location', 'Malappuram', '2026-09-06 23:54:39'),
(799, 48, 'location', 'All Kerala', '2026-09-06 23:54:39'),
(800, 49, 'marital_status', 'never_married', '2026-09-07 00:51:25'),
(801, 49, 'marital_status', 'divorced', '2026-09-07 00:51:25'),
(802, 49, 'marital_status', 'widowed', '2026-09-07 00:51:25'),
(803, 49, 'marital_status', 'nikah_divorce', '2026-09-07 00:51:25'),
(804, 49, 'marital_status', 'separated', '2026-09-07 00:51:25'),
(805, 49, 'marital_status', 'awaiting_divorce', '2026-09-07 00:51:25'),
(806, 49, 'caste', 'Any', '2026-09-07 00:51:25'),
(807, 49, 'career_sector', 'Any', '2026-09-07 00:51:25'),
(808, 49, 'location', 'Malappuram', '2026-09-07 00:51:25'),
(809, 49, 'location', 'All Kerala', '2026-09-07 00:51:25'),
(815, 49, 'family_status', 'Lower Middle Class', '2026-09-07 01:16:03'),
(816, 49, 'family_status', 'Upper Middle Class', '2026-09-07 01:16:03'),
(817, 49, 'family_status', 'Middle Class', '2026-09-07 01:16:03'),
(818, 49, 'family_status', 'Affluent', '2026-09-07 01:16:03'),
(819, 49, 'physical_status', 'Physically Challenged', '2026-09-07 01:16:03'),
(820, 49, 'physical_status', 'Other', '2026-09-07 01:16:03'),
(821, 49, 'physical_status', 'Normal', '2026-09-07 01:16:03'),
(822, 49, 'location_radius', 'Anywhere in Kerala', '2026-09-07 01:16:03'),
(823, 49, 'income', 'Below ₹2 Lakh', '2026-09-07 01:16:03'),
(824, 49, 'income', '₹5 - ₹10 Lakh', '2026-09-07 01:16:03'),
(825, 49, 'income', '₹15 - ₹25 Lakh', '2026-09-07 01:16:03'),
(826, 49, 'income', '₹10 - ₹15 Lakh', '2026-09-07 01:16:03'),
(827, 49, 'income', '₹2 - ₹5 Lakh', '2026-09-07 01:16:03'),
(828, 49, 'income', 'Above ₹25 Lakh', '2026-09-07 01:16:03'),
(829, 49, 'complexion', 'Very Fair', '2026-09-07 01:16:03'),
(830, 49, 'complexion', 'Wheatish', '2026-09-07 01:16:03'),
(831, 49, 'complexion', 'Fair', '2026-09-07 01:16:03'),
(832, 49, 'complexion', 'Medium', '2026-09-07 01:16:03'),
(833, 49, 'complexion', 'Dusky', '2026-09-07 01:16:03'),
(834, 49, 'complexion', 'Dark', '2026-09-07 01:16:03'),
(835, 49, 'star', 'Bharani', '2026-09-07 01:16:03'),
(836, 49, 'star', 'Krittika (Karthika)', '2026-09-07 01:16:03'),
(837, 49, 'star', 'Ashwini (Aswathi)', '2026-09-07 01:16:03'),
(838, 49, 'star', 'Mrigashirsha (Makayiram)', '2026-09-07 01:16:03'),
(839, 49, 'star', 'Ardra (Thiruvathira)', '2026-09-07 01:16:03'),
(840, 49, 'star', 'Punarvasu (Punartham)', '2026-09-07 01:16:03'),
(841, 49, 'star', 'Ashlesha (Ayilyam)', '2026-09-07 01:16:03'),
(842, 49, 'star', 'Pushya (Pooyam)', '2026-09-07 01:16:03'),
(843, 49, 'star', 'Magha (Makam)', '2026-09-07 01:16:03'),
(844, 49, 'star', 'Purva Phalguni (Pooram)', '2026-09-07 01:16:03'),
(845, 49, 'star', 'Uttara Phalguni (Uthram)', '2026-09-07 01:16:03'),
(846, 49, 'star', 'Chitra', '2026-09-07 01:16:03'),
(847, 49, 'star', 'Anuradha (Anizham)', '2026-09-07 01:16:03'),
(848, 49, 'star', 'Vishakha (Vishakam)', '2026-09-07 01:16:03'),
(849, 49, 'star', 'Swati (Chothi)', '2026-09-07 01:16:03'),
(850, 49, 'star', 'Jyeshtha (Thriketta)', '2026-09-07 01:16:03'),
(851, 49, 'star', 'Mula (Moolam)', '2026-09-07 01:16:03'),
(852, 49, 'star', 'Uttara Ashadha (Uthradam)', '2026-09-07 01:16:03'),
(853, 49, 'star', 'Purva Ashadha (Pooradam)', '2026-09-07 01:16:03'),
(854, 49, 'star', 'Shravana (Thiruvonam)', '2026-09-07 01:16:03'),
(855, 49, 'star', 'Dhanishtha (Avittam)', '2026-09-07 01:16:03'),
(856, 49, 'star', 'Shatabhisha (Chathayam)', '2026-09-07 01:16:03'),
(857, 49, 'star', 'Purva Bhadrapada (Pooruruttathi)', '2026-09-07 01:16:03'),
(858, 50, 'marital_status', 'divorced', '2026-09-08 00:31:48'),
(859, 50, 'sect', 'Sunni', '2026-09-08 00:31:48'),
(860, 50, 'sunni_group', 'AP-Sunni', '2026-09-08 00:31:48'),
(861, 50, 'career_sector', 'Any', '2026-09-08 00:31:48'),
(862, 50, 'location', 'All Kerala', '2026-09-08 00:31:48'),
(863, 51, 'marital_status', 'never_married', '2026-09-08 23:52:55'),
(864, 51, 'marital_status', 'nikah_divorce', '2026-09-08 23:52:55'),
(865, 51, 'marital_status', 'widowed', '2026-09-08 23:52:55'),
(866, 51, 'marital_status', 'awaiting_divorce', '2026-09-08 23:52:55'),
(867, 51, 'marital_status', 'separated', '2026-09-08 23:52:55');
INSERT INTO `preference_values` (`id`, `user_id`, `preference_type`, `value`, `created_at`) VALUES
(868, 51, 'marital_status', 'divorced', '2026-09-08 23:52:55'),
(869, 51, 'caste', 'Any', '2026-09-08 23:52:55'),
(870, 51, 'career_sector', 'Any', '2026-09-08 23:52:55'),
(871, 51, 'location', 'Malappuram', '2026-09-08 23:52:55'),
(872, 51, 'location', 'All Kerala', '2026-09-08 23:52:55'),
(919, 51, 'family_status', 'Affluent', '2026-09-09 23:04:58'),
(920, 51, 'family_status', 'Upper Middle Class', '2026-09-09 23:04:58'),
(921, 51, 'family_status', 'Middle Class', '2026-09-09 23:04:58'),
(922, 51, 'family_status', 'Lower Middle Class', '2026-09-09 23:04:58'),
(923, 51, 'physical_status', 'Physically Challenged', '2026-09-09 23:04:58'),
(924, 51, 'physical_status', 'Normal', '2026-09-09 23:04:58'),
(925, 51, 'physical_status', 'Other', '2026-09-09 23:04:58'),
(926, 51, 'location_radius', 'Within 50 km', '2026-09-09 23:04:58'),
(927, 51, 'income', 'Below ₹2 Lakh', '2026-09-09 23:04:58'),
(928, 51, 'income', '₹2 - ₹5 Lakh', '2026-09-09 23:04:58'),
(929, 51, 'income', '₹15 - ₹25 Lakh', '2026-09-09 23:04:58'),
(930, 51, 'income', 'Above ₹25 Lakh', '2026-09-09 23:04:58'),
(931, 51, 'income', '₹10 - ₹15 Lakh', '2026-09-09 23:04:58'),
(932, 51, 'income', '₹5 - ₹10 Lakh', '2026-09-09 23:04:58'),
(933, 51, 'complexion', 'Very Fair', '2026-09-09 23:04:58'),
(934, 51, 'complexion', 'Fair', '2026-09-09 23:04:58'),
(935, 51, 'complexion', 'Wheatish', '2026-09-09 23:04:58'),
(936, 51, 'complexion', 'Medium', '2026-09-09 23:04:58'),
(937, 51, 'complexion', 'Dusky', '2026-09-09 23:04:58'),
(938, 51, 'complexion', 'Dark', '2026-09-09 23:04:58'),
(939, 51, 'star', 'Ashwini (Aswathi)', '2026-09-09 23:04:58'),
(940, 51, 'star', 'Bharani', '2026-09-09 23:04:58'),
(941, 51, 'star', 'Punarvasu (Punartham)', '2026-09-09 23:04:58'),
(942, 51, 'star', 'Krittika (Karthika)', '2026-09-09 23:04:58'),
(943, 51, 'star', 'Rohini', '2026-09-09 23:04:58'),
(944, 51, 'star', 'Mrigashirsha (Makayiram)', '2026-09-09 23:04:58'),
(945, 51, 'star', 'Ardra (Thiruvathira)', '2026-09-09 23:04:58'),
(946, 51, 'star', 'Pushya (Pooyam)', '2026-09-09 23:04:58'),
(947, 51, 'star', 'Ashlesha (Ayilyam)', '2026-09-09 23:04:58'),
(948, 51, 'star', 'Magha (Makam)', '2026-09-09 23:04:58'),
(949, 51, 'star', 'Uttara Phalguni (Uthram)', '2026-09-09 23:04:58'),
(950, 51, 'star', 'Hasta (Atham)', '2026-09-09 23:04:58'),
(951, 51, 'star', 'Purva Phalguni (Pooram)', '2026-09-09 23:04:58'),
(952, 51, 'star', 'Chitra', '2026-09-09 23:04:58'),
(953, 51, 'star', 'Swati (Chothi)', '2026-09-09 23:04:58'),
(954, 51, 'star', 'Jyeshtha (Thriketta)', '2026-09-09 23:04:58'),
(955, 51, 'star', 'Anuradha (Anizham)', '2026-09-09 23:04:58'),
(956, 51, 'star', 'Vishakha (Vishakam)', '2026-09-09 23:04:58'),
(957, 51, 'star', 'Mula (Moolam)', '2026-09-09 23:04:58'),
(958, 51, 'star', 'Purva Ashadha (Pooradam)', '2026-09-09 23:04:58'),
(959, 51, 'star', 'Uttara Ashadha (Uthradam)', '2026-09-09 23:04:58'),
(960, 51, 'star', 'Shravana (Thiruvonam)', '2026-09-09 23:04:58'),
(961, 51, 'star', 'Dhanishtha (Avittam)', '2026-09-09 23:04:58'),
(962, 51, 'star', 'Shatabhisha (Chathayam)', '2026-09-09 23:04:58'),
(963, 51, 'star', 'Purva Bhadrapada (Pooruruttathi)', '2026-09-09 23:04:58'),
(964, 51, 'star', 'Uttara Bhadrapada (Uthrattathi)', '2026-09-09 23:04:58'),
(965, 51, 'star', 'Revati', '2026-09-09 23:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `profile_for` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `marital_status` varchar(50) NOT NULL,
  `has_kids` varchar(20) DEFAULT NULL,
  `number_of_kids` smallint(5) UNSIGNED DEFAULT NULL,
  `kids_living_status` varchar(50) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `district` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL DEFAULT 'Kerala',
  `pincode` varchar(10) NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `place` varchar(150) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_source` enum('current','map','manual') DEFAULT NULL,
  `religion` varchar(50) NOT NULL,
  `sect` varchar(100) DEFAULT NULL,
  `muslim_group` varchar(100) DEFAULT NULL,
  `salafi_group` varchar(100) DEFAULT NULL,
  `caste` varchar(100) DEFAULT NULL,
  `sub_caste` varchar(100) DEFAULT NULL,
  `nakshatra` varchar(100) DEFAULT NULL,
  `rashi` varchar(100) DEFAULT NULL,
  `dosham` varchar(100) DEFAULT NULL,
  `denomination` varchar(100) DEFAULT NULL,
  `christian_sub_group` varchar(100) DEFAULT NULL,
  `parish_name` varchar(150) DEFAULT NULL,
  `highest_education` varchar(150) NOT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `job_title` varchar(150) NOT NULL,
  `job_sector` varchar(100) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `complexion` varchar(50) DEFAULT NULL,
  `physical_status` varchar(100) DEFAULT NULL,
  `secondary_mobile` varchar(20) DEFAULT NULL,
  `whatsapp_country_code` varchar(10) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `college_university` varchar(200) DEFAULT NULL,
  `annual_income` varchar(100) DEFAULT NULL,
  `work_location` varchar(200) DEFAULT NULL,
  `work_location_type` varchar(50) DEFAULT NULL,
  `work_state` varchar(100) DEFAULT NULL,
  `work_district` varchar(100) DEFAULT NULL,
  `work_country` varchar(100) DEFAULT NULL,
  `work_city` varchar(100) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `father_name` varchar(150) DEFAULT NULL,
  `father_occupation` varchar(150) DEFAULT NULL,
  `father_status` varchar(50) DEFAULT NULL,
  `mother_name` varchar(150) DEFAULT NULL,
  `mother_occupation` varchar(150) DEFAULT NULL,
  `mother_status` varchar(50) DEFAULT NULL,
  `brothers` smallint(5) UNSIGNED DEFAULT NULL,
  `sisters` smallint(5) UNSIGNED DEFAULT NULL,
  `married_brothers` smallint(5) UNSIGNED DEFAULT NULL,
  `married_sisters` smallint(5) UNSIGNED DEFAULT NULL,
  `family_status` varchar(100) DEFAULT NULL,
  `home_type` varchar(100) DEFAULT NULL,
  `expectations` text DEFAULT NULL,
  `registration_completed` tinyint(1) NOT NULL DEFAULT 0,
  `profile_status` enum('draft','new','pending_verification','verified','rejected','blocked') NOT NULL DEFAULT 'draft',
  `completion_percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `home_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `profile_for`, `gender`, `full_name`, `marital_status`, `has_kids`, `number_of_kids`, `kids_living_status`, `date_of_birth`, `height`, `district`, `state`, `pincode`, `house_name`, `place`, `latitude`, `longitude`, `location_source`, `religion`, `sect`, `muslim_group`, `salafi_group`, `caste`, `sub_caste`, `nakshatra`, `rashi`, `dosham`, `denomination`, `christian_sub_group`, `parish_name`, `highest_education`, `specialization`, `job_title`, `job_sector`, `weight`, `body_type`, `complexion`, `physical_status`, `secondary_mobile`, `whatsapp_country_code`, `whatsapp_number`, `email`, `college_university`, `annual_income`, `work_location`, `work_location_type`, `work_state`, `work_district`, `work_country`, `work_city`, `company_name`, `father_name`, `father_occupation`, `father_status`, `mother_name`, `mother_occupation`, `mother_status`, `brothers`, `sisters`, `married_brothers`, `married_sisters`, `family_status`, `home_type`, `expectations`, `registration_completed`, `profile_status`, `completion_percentage`, `home_verified`, `created_at`, `updated_at`) VALUES
(1, 11, 'self', 'female', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '2007-01-01', 61.00, 'Kozhikode', 'Kerala', '673525', '434343', 'Perambra', 11.5580000, 75.7775000, 'map', 'Muslim', 'Sunni', 'Sunni', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MSc', 'Business Analyst', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 02:30:49', '2026-08-30 02:31:25'),
(2, 12, 'self', 'male', 'Ayesha Fathima', 'awaiting_divorce', 'no', NULL, NULL, '1997-12-12', 62.00, 'Kozhikode', 'Kerala', '673525', 'se Name', 'Perambra', 11.5580000, 75.7775000, 'map', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Education', 'Accounts Executive', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 02:39:43', '2026-08-30 02:40:13'),
(3, 13, 'sister', 'female', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '2003-10-15', 59.00, 'Kozhikode', 'Kerala', '670109', 'use Name', 'Vadakara', 11.5974000, 75.6054000, 'map', 'Muslim', 'Other', '', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'Other', 'Fashion Designer', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 16:04:28', '2026-08-30 16:04:56'),
(4, 14, 'self', 'female', 'Ayesha Fathima gpt', 'never_married', NULL, NULL, NULL, '1998-06-15', 63.00, 'Malappuram', 'Kerala', '676503', 'House Name', 'Kottakkal', NULL, NULL, 'manual', 'Muslim', 'Sunni', 'EK-Sunni', '', '', '', '', '', '', '', '', '', 'General Degree (Bachelors)', 'BCA', 'Software Engineer', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 16:15:56', '2026-08-30 16:19:21'),
(5, 15, 'sister', 'female', 'Ayesha Fathima', 'divorced', 'yes', 1, 'with_me', '1999-11-11', 61.00, 'Palakkad', 'Kerala', '679104', 'House Name', 'Manisseri', 10.7619594, 76.3488516, 'map', 'Hindu', '', '', '', 'Nair', 'Menon', 'Magha (Makam)', 'Kumbham (Aquarius)', 'no', '', '', '', 'PhD / Doctorate', 'Education', 'Accounts Executive', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 17:49:26', '2026-08-30 17:50:06'),
(6, 16, 'sister', 'female', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '1995-12-12', 62.00, 'Kozhikode', 'Kerala', '670109', ' Name', 'Vadakara', 11.5974000, 75.6054000, 'map', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Engineering', 'Administrative Assistant', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'verified', 100, 1, '2026-08-30 18:04:09', '2026-09-06 09:13:28'),
(7, 17, 'self', 'male', 'ijas', 'divorced', 'yes', 1, 'with_me', '1996-09-10', 64.00, 'Kozhikode', 'Kerala', '670109', 'ij', 'Vadakara', 11.5974000, 75.6054000, 'map', 'Muslim', 'Jamat Islami', '', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MCA', 'Accountant', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 18:19:02', '2026-08-30 18:23:45'),
(8, 18, 'self', 'male', 'Ayesha Fathima', 'separated', 'yes', 3, 'with_me', '1983-11-12', 61.00, 'Kozhikode', 'Kerala', '670109', '670109 House Name', 'Vadakara', 11.5974000, 75.6054000, 'map', 'Christian', '', '', '', '', '', '', '', '', 'Catholic', 'Syro-Malabar Catholic', 'h / Church Nam', 'PhD / Doctorate', 'Social Science', 'Architect', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 20:46:15', '2026-08-30 20:47:00'),
(9, 19, 'self', 'male', 'ijas', 'divorced', 'yes', 3, 'with_me', '1996-11-12', 65.00, 'Malappuram', 'Kerala', '676317', 'padikkal', 'padikkal', NULL, NULL, 'manual', 'Muslim', 'Sunni', 'Sunni', '', '', '', '', '', '', '', '', '', 'General Degree (Bachelors)', 'BA', 'Job Title / Role', 'Student', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-08-30 23:32:00', '2026-08-30 23:32:46'),
(10, 20, 'self', 'male', 'TEST USER 5', 'never_married', NULL, NULL, NULL, '1982-11-11', 86.00, 'Kozhikode', 'Kerala', '670109', 'Name', 'Vadakara', 11.5974000, 75.6054000, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MTech', 'Accounts Executive', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending_verification', 100, 0, '2026-08-31 05:15:42', '2026-09-02 21:14:44'),
(11, 21, 'self', 'male', 'TEST USER', 'never_married', NULL, NULL, NULL, '1995-11-12', 72.00, 'Kannur', 'Kerala', '670592', 'House Name', 'Nagavalavu', 11.9318000, 75.5723000, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'General Degree (Bachelors)', 'BCom', 'Student', 'Student', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'verified', 100, 1, '2026-08-31 23:06:32', '2026-09-02 22:06:25'),
(12, 22, 'self', 'female', 'test', 'never_married', NULL, NULL, NULL, '2007-11-12', 60.00, 'Kozhikode', 'Kerala', '673522', 'House Name', 'Payyoli', 11.5154000, 75.6193000, 'map', 'Muslim', 'Sunni', 'EK-Sunni', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Computer Science / IT', 'Job Title', 'Freelance', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-01 09:10:45', '2026-09-01 09:11:19'),
(13, 24, 'self', 'female', 'Ijas', 'never_married', NULL, NULL, NULL, '1993-01-01', 75.00, 'Malappuram', 'Kerala', '673642', 'use Na', 'Malappuram', 11.1078000, 76.0549000, 'map', 'Muslim', 'Hanafi', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Other', 'Accountant', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'verified', 100, 1, '2026-09-02 22:34:11', '2026-09-06 15:33:48'),
(14, 25, 'self', 'male', 'Ijas', 'never_married', NULL, NULL, NULL, '1996-01-01', 48.00, 'Alappuzha', 'Kerala', '676317', 'Thakidiyil house, Padikkal velimukku po', 'Malappuram', NULL, NULL, 'manual', 'Muslim', 'Jamat Islami', '', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'LLM', 'Accounts Executive', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-02 22:53:13', '2026-09-02 22:53:33'),
(15, 26, 'self', 'male', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '1997-01-02', 62.00, 'Alappuzha', 'Kerala', '324324', 'ij', 'Manisseri', NULL, NULL, 'manual', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'Religious / Islamic Education', 'Madrasa', '3424', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-03 01:30:26', '2026-09-03 01:31:24'),
(16, 27, 'sister', 'female', 'Ayesha', 'never_married', NULL, NULL, NULL, '1999-01-02', 65.00, 'Malappuram', 'Kerala', '673642', 'r', 'Malappuram', 11.1078000, 76.0549000, 'map', 'Muslim', 'Sunni', 'EK-Sunni', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MSc', 'Business Person', 'Private', 56.00, 'Slim', 'Very Fair', 'Normal', '9745553598', '+91', '+919745553598', 'ijascontact@gmail.com', 'calicut univercity', '₹15 - ₹25 Lakh', NULL, 'india_same_state', 'Kerala', 'ity / District', '', '', 'Company / Organizat', 'ther\'s Name', 'ccupation', 'living', 'other\'s Name', 'other\'s Name Occupat', 'living', 1, 1, 1, 1, 'Middle Class', 'Family House', 'Your Expectations', 1, 'new', 100, 0, '2026-09-03 01:47:01', '2026-09-03 02:45:56'),
(17, 28, 'self', 'female', 'Fathima', 'divorced', 'yes', 3, 'with_me', '2000-02-03', 64.00, '', 'Kerala', '', '', '', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 20, 0, '2026-09-03 22:08:43', '2026-09-03 22:08:43'),
(19, 29, 'self', 'female', 'Asiya', 'nikah_divorce', NULL, NULL, NULL, '2004-02-01', 68.00, 'Malappuram', 'Kerala', '676556', 'House Name', 'Vairankode', 10.8899963, 75.9889523, 'map', 'Muslim', 'Sunni', 'Sunni', '', '', '', '', '', '', '', '', '', 'Professional Degree', 'BDS', 'Accounts Manager', 'Business / Self Employed', 51.00, 'Slim', 'Very Fair', 'Normal', '', '+91', '+919745553598', '', '15a63e4f65sd1f', 'Below ₹2 Lakh', NULL, 'india_same_state', 'Kerala', 'asd', '', '', '536af41sd65f146a5', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 'Lower Middle Class', '', 'sorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gjsorkto erjtgie jgdfg krokgtepo gj', 1, 'verified', 100, 1, '2026-09-03 22:14:28', '2026-09-06 09:21:02'),
(20, 30, 'brother', 'male', 'Rahman', 'divorced', 'yes', 3, 'with_me', '1994-02-06', 53.00, 'Malappuram', 'Kerala', '673642', 'Palapetty House', 'Malappuram', 11.1078000, 76.0549000, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'Professional Degree', 'BAMS', 'Accounts Executive', 'Business / Self Employed', 56.00, 'Slim', 'Very Fair', 'Normal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-04 00:53:24', '2026-09-04 01:20:07'),
(21, 31, 'sister', 'female', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '1987-01-01', 67.00, 'Malappuram', 'Kerala', '673642', 'as', 'Malappuram', 11.1078000, 76.0549000, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MTech', 'Accounts Manager', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-04 02:10:25', '2026-09-04 02:11:23'),
(22, 32, 'self', 'male', 'Nimesh', 'never_married', NULL, NULL, NULL, '1991-12-10', 60.00, 'Wayanad', 'Kerala', '151616', 'e Name', 'Sultan Bathery', 11.6986000, 76.2608000, 'map', 'Hindu', '', '', '', 'Thiyya / Ezhava', '', 'Punarvasu (Punartham)', 'Makaram (Capricorn)', 'yes', '', '', '', 'PhD / Doctorate', 'Engineering', 'tle / Role', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 80, 0, '2026-09-05 23:18:58', '2026-09-05 23:20:13'),
(23, 33, 'self', 'male', 'sa', 'never_married', NULL, NULL, NULL, '1995-01-01', 63.00, 'Wayanad', 'Kerala', '435435', '435435 House Name', 'Sultan Bathery', 11.6986000, 76.2608000, 'map', 'Hindu', '', '', '', 'Namboothiri', '', 'Rohini', 'Vrischikam (Scorpio)', 'no', '', '', '', 'Master\'s Degree', 'MTech', 'Accounts Manager', 'Freelance', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 80, 0, '2026-09-05 23:28:07', '2026-09-05 23:29:33'),
(24, 34, 'sister', 'female', 'TEST USER', 'separated', 'yes', 2, 'with_me', '1996-11-12', 62.00, 'Malappuram', 'Kerala', '676551', 'House Name', 'Kadungathukundu', 10.9292057, 75.9900087, 'map', 'Hindu', '', '', '', 'Namboothiri', '', 'Uttara Phalguni (Uthram)', 'Makaram (Capricorn)', 'yes', '', '', '', 'Professional Degree', 'CS', 'Accounts Manager', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-05 23:42:17', '2026-09-05 23:51:03'),
(25, 35, 'self', 'male', 'Ayesha Fathima', 'nikah_divorce', NULL, NULL, NULL, '1996-11-18', 54.00, 'Malappuram', 'Kerala', '676552', 's', 'Puthanathani', 10.9453859, 76.0009947, 'map', 'Hindu', '', '', '', 'Viswakarma', 'Asari (Carpenters)', 'Magha (Makam)', 'Dhanu (Sagittarius)', 'yes', '', '', '', 'Master\'s Degree', 'LLM', 'Accounts Manager', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-05 23:54:35', '2026-09-05 23:57:09'),
(26, 36, 'self', 'male', 'RQ Health Care', 'never_married', NULL, NULL, NULL, '1998-12-13', 60.00, 'Malappuram', 'Kerala', '676552', 'House Name', 'Valanchery', 10.9130247, 76.0778972, 'map', 'Muslim', 'Salafi', '', 'KNM Markazu Dawa', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Other', '/ Role', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 80, 0, '2026-09-06 00:09:04', '2026-09-06 00:09:38'),
(27, 37, 'self', 'male', 'TEST USER 5', 'never_married', NULL, NULL, NULL, '1994-01-01', 63.00, 'Malappuram', 'Kerala', '676500', 's', 'Kootilangadi', 11.0424482, 76.1053624, 'map', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Social Science', 'Accounts Manager', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-06 00:37:10', '2026-09-06 00:37:28'),
(28, 38, 'self', 'male', 'TEST USER 5', 'separated', 'no', NULL, NULL, '2002-11-11', 61.00, 'Malappuram', 'Kerala', '676556', 's', 'Vairankode', 10.8968428, 75.9735296, 'map', 'Muslim', 'Other', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Engineering', 'Accounts Executive', 'Government', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 80, 0, '2026-09-06 00:38:29', '2026-09-06 00:38:47'),
(29, 39, 'brother', 'male', 'Ayesha Fathima', 'divorced', 'yes', 1, 'with_me', '1997-11-11', 59.00, 'Malappuram', 'Kerala', '676501', 'Name', 'College Padi', 11.0100976, 75.9845156, 'map', 'Muslim', 'Other', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Social Science', '/ Role', 'Student', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-06 00:41:33', '2026-09-06 00:42:32'),
(30, 40, 'self', 'male', 'Ayesha Fathima', 'never_married', NULL, NULL, NULL, '1992-12-12', 62.00, 'Malappuram', 'Kerala', '676102', 'House Name', 'Malappuram', 10.8536867, 75.9350784, 'map', 'Muslim', 'Sunni', 'Sunni', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Other', 'Designer', 'Business / Self Employed', 50.00, 'Slim', 'Very Fair', 'Normal', '', '+91', '+911234567890', '', '', '', NULL, 'india_same_state', 'Kerala', 'a', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 'Middle Class', '', 'Your Expectations O', 1, 'new', 100, 0, '2026-09-06 00:48:04', '2026-09-06 01:02:12'),
(31, 41, 'self', 'female', 'Raabiya', 'never_married', NULL, NULL, NULL, '1981-12-13', 74.00, 'Malappuram', 'Kerala', '676301', 'House Name', 'Vettichira', 10.9184185, 76.0119808, 'map', 'Muslim', 'Other', '', '', '', '', '', '', '', '', '', '', 'Others / Below 10th', 'SSLC', 'Accounts Manager', 'Business / Self Employed', 50.00, 'Slim', 'Very Fair', 'Normal', '9749865456', '+91', '+919749865456', 'ijascontact@gmail.com', 'ollege / University Op', '₹15 - ₹25 Lakh', NULL, 'outside_india', '', '', 'Other', 'City', 'Company / Organization Optional', 'Father\'s Details', 'Father\'s Details Optional Father\'s Name', 'passed_away', 'other\'s Details Optional Mother\'s Name', 'er\'s Details Optional Mother\'s Name', 'living', 3, 2, 1, 1, 'Upper Middle Class', 'Rented House', 'Your ExpectationsTell us what you are looking for in your preferred match.Your Expectations Optional', 1, 'verified', 100, 1, '2026-09-06 01:35:07', '2026-09-06 14:43:28'),
(32, 42, 'brother', 'male', 'TEST USER 1', 'divorced', 'no', 2, 'with_me', '1995-12-12', 67.00, 'Wayanad', 'Kerala', '446464', 'ouse Nam', 'Sultan Bathery', 11.6986000, 76.2608000, 'map', 'Muslim', 'Salafi', '', 'KNM (Mainstream)', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Computer Science / IT', 'ob Title / Role', 'Student', NULL, '', '', 'Normal', '', '+91', '+919451465110', '', '', '', NULL, 'india_same_state', 'Kerala', 'City / Dist', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 'Middle Class', '', NULL, 1, 'verified', 100, 1, '2026-09-06 03:01:45', '2026-09-06 14:33:56'),
(33, 43, 'self', 'male', 'User', 'divorced', 'yes', 2, 'with_me', '1996-12-12', 68.00, 'Malappuram', 'Kerala', '676306', 'House Name', 'Karumbil', 11.0262733, 75.9405714, 'map', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'Professional Degree', 'MBBS', 'Administrative Assistant', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-06 08:54:35', '2026-09-06 08:59:37'),
(34, 44, 'brother', 'male', 'ihmasiya', 'never_married', NULL, NULL, NULL, '1999-01-01', 67.00, 'Malappuram', 'Kerala', '676103', 'House Name', 'Kadungathukundu', 10.9238121, 75.9570505, 'map', 'Muslim', 'Sunni', 'Sunni', '', '', '', '', '', '', '', '', '', 'Professional Degree', 'MD/MS/ DNB (Medical Specialist / Surgeon)', 'Accountant', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-06 14:04:20', '2026-09-06 14:05:40'),
(35, 45, 'sister', 'female', 'Fathima', 'never_married', NULL, NULL, NULL, '1994-12-12', 65.00, 'Malappuram', 'Kerala', '676528', 'e Name', 'Othukungal', 11.0316650, 76.0349037, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'Professional Degree', 'MD / MS / DNB', 'Doctor', 'Private', 33.00, '', '', 'Normal', '', '+91', '+911234567890', '', 's', '₹15 - ₹25 Lakh', NULL, 'india_same_state', 'Kerala', 'strict', '', '', 's', '', '', '', '', '', '', NULL, NULL, NULL, NULL, 'Middle Class', '', NULL, 1, 'verified', 100, 1, '2026-09-06 15:38:10', '2026-09-06 15:42:32'),
(36, 46, 'daughter', 'female', 'Ayesima', 'never_married', NULL, NULL, NULL, '2003-11-12', 62.00, 'Malappuram', 'Kerala', '676510', 'House Name', 'Perambra', 10.9723508, 76.0019455, 'map', 'Muslim', 'Jamat Islami', '', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Engineering', 'Accounts Executive', 'Business / Self Employed', 20.00, '', '', 'Normal', '', '+91', '+911512364789', 'ijascontact@gmail.com', '', '', NULL, 'outside_india', '', '', 'Germany', 'City', '', '', '', '', '', '', '', 1, 1, 1, 1, 'Middle Class', '', NULL, 1, 'verified', 100, 1, '2026-09-06 16:24:52', '2026-09-06 19:08:06'),
(37, 47, 'self', 'male', 'Raszq', 'never_married', NULL, NULL, NULL, '1982-12-12', 62.00, 'Malappuram', 'Kerala', '676306', 'use Na', 'Kolappuram', 11.0630801, 75.9258881, 'map', 'Muslim', 'Shafi', '', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MTech', 'Business Person', 'Student', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-06 23:50:45', '2026-09-06 23:51:35'),
(38, 48, 'sister', 'female', 'Rasiya', 'never_married', NULL, NULL, NULL, '1983-11-12', 60.00, 'Malappuram', 'Kerala', '676304', 'House Name', 'Vengara', 11.0370567, 75.9914876, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'PhD / Doctorate', 'Computer Science / IT', 'Accounts Executive', 'Business / Self Employed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'verified', 100, 1, '2026-09-06 23:53:48', '2026-09-07 00:06:59'),
(39, 49, 'sister', 'female', 'aa', 'never_married', NULL, NULL, NULL, '2006-01-01', 51.00, 'Malappuram', 'Kerala', '676508', 'se Name', 'Karumbil', 11.0307318, 75.9423672, 'map', 'Hindu', '', '', '', 'Namboothiri', '', 'Chitra', 'Kumbham (Aquarius)', '', '', '', '', 'PhD / Doctorate', 'Other', 'Administrative Assistant', 'Freelance', 43.00, 'Slim', 'Very Fair', 'Normal', NULL, NULL, NULL, NULL, 'sadf', '₹15 - ₹25 Lakh', NULL, 'india_same_state', 'Kerala', 'sdad', '', '', 'dsd', 'sda', 'sad', 'living', 'das', 'sad', 'living', 2, 2, 2, 2, 'Lower Middle Class', 'Rented House', NULL, 1, 'new', 100, 0, '2026-09-07 00:50:38', '2026-09-10 00:35:42'),
(40, 50, 'self', 'male', 'Rameshan', 'divorced', 'yes', 1, 'with_me', '1995-12-13', 80.00, 'Kozhikode', 'Kerala', '673008', 'House Name', 'Cherooppa', 11.2622164, 75.8988404, 'map', 'Muslim', 'Sunni', 'AP-Sunni', '', '', '', '', '', '', '', '', '', 'Master\'s Degree', 'MSc', 'Accounts Manager', 'Private', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'new', 100, 0, '2026-09-08 00:30:59', '2026-09-08 00:31:48'),
(41, 51, 'self', 'male', 'Rakesh', 'never_married', NULL, NULL, NULL, '1990-12-14', 75.00, 'Malappuram', 'Kerala', '676553', '434343', 'Kadampuzha', 10.9453859, 76.0646564, 'map', 'Hindu', '', '', '', 'SC', 'Cheruman', 'Magha (Makam)', 'Dhanu (Sagittarius)', 'no', '', '', '', 'Master\'s Degree', 'LLM', 'Accounts Executive', 'Government', 34.00, 'Slim', 'Fair', 'Normal', '1321321321', '+91', '+911321321321', 'ijascontact@gmail.com', 'Calicut univercity', '₹15 - ₹25 Lakh', NULL, 'india_same_state', 'Kerala', '43434', '', '', 'Company / Organization Option', 'Muhammad Ijas', 'ssssssssss', 'living', 'sssssssss', 'ssssssss', 'living', 2, 2, 2, 2, 'Lower Middle Class', 'Family House', 'our Expectations OptionalLE COMPLETIONYour Expe', 1, 'verified', 100, 1, '2026-09-08 23:52:07', '2026-09-10 00:33:40'),
(43, 53, 'sister', 'female', 'rose', 'divorced', 'yes', 1, 'with_me', '1987-12-10', 81.00, 'Malappuram', 'Kerala', '676309', 'se Name', 'Cheruppara', 11.0262733, 75.9217399, 'map', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'new', 40, 0, '2026-09-09 22:08:04', '2026-09-09 22:08:11');

-- --------------------------------------------------------

--
-- Table structure for table `profile_photos`
--

CREATE TABLE `profile_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','pending','rejected','deleted') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_photos`
--

INSERT INTO `profile_photos` (`id`, `user_id`, `file_path`, `is_primary`, `display_order`, `status`, `created_at`) VALUES
(1, 27, 'uploads/profile-photos/8364ccb43faf4d9a8b0d7a94c6af2665.jpg', 1, 0, 'deleted', '2026-09-03 02:58:49'),
(2, 27, 'uploads/profile-photos/8552c95b6028d020015ecea1bb605c87.png', 1, 0, 'active', '2026-09-03 03:00:11'),
(3, 27, 'uploads/profile-photos/243eb8edb177c2b3a3ebd7f27703612b.png', 0, 1, 'active', '2026-09-03 03:00:11'),
(4, 27, 'uploads/profile-photos/3e6b574b9efc76040d203e90bdc9917c.png', 0, 2, 'active', '2026-09-03 03:00:11'),
(5, 27, 'uploads/profile-photos/dad045c07613d8abc1aa373d893517b8.png', 0, 3, 'active', '2026-09-03 03:13:15'),
(6, 29, 'uploads/profile-photos/6ba3aac2d5247b9cc0149bf9a3d55b06.png', 1, 0, 'active', '2026-09-03 23:30:55'),
(7, 41, 'uploads/profile-photos/f01250f59cb06be7bccea17f6b55cfba.webp', 1, 0, 'active', '2026-09-06 02:40:39'),
(8, 41, 'uploads/profile-photos/818330ae4c4211fea6710e1db3f4c3df.jpg', 0, 1, 'active', '2026-09-06 02:45:10'),
(9, 41, 'uploads/profile-photos/de7dcd8298df74514286d1067ee87063.webp', 0, 2, 'active', '2026-09-06 02:45:15'),
(10, 41, 'uploads/profile-photos/bfcf1401b1f34f63f4123a9ec917c02e.png', 0, 3, 'active', '2026-09-06 02:45:18'),
(11, 42, 'uploads/profile-photos/a938d1a430f85f68b567eb2894d73f46.png', 1, 0, 'deleted', '2026-09-06 03:37:42'),
(12, 42, 'uploads/profile-photos/076f7a15fb6058752e8a446a70db37cb.jpg', 1, 0, 'active', '2026-09-06 09:24:28'),
(13, 45, 'uploads/profile-photos/66ee5d0af0a5b46a33f76edd2d145125.jpg', 1, 0, 'active', '2026-09-06 15:41:19'),
(14, 46, 'uploads/profile-photos/8857d2852aa028bb903115dee388be59.jpg', 1, 0, 'active', '2026-09-06 16:27:36'),
(15, 44, 'uploads/profile-photos/da8dc1c0b496b0f66e7207ea9e6453ee.jpg', 1, 0, 'active', '2026-09-06 23:47:27'),
(16, 47, 'uploads/profile-photos/fd32481f98611e872226f4def5bb7130.jpg', 1, 0, 'deleted', '2026-09-06 23:52:32'),
(17, 47, 'uploads/profile-photos/f09bc3ee902cbd1b9b37b236f7cbccf5.webp', 1, 0, 'deleted', '2026-09-06 23:56:17'),
(18, 47, 'uploads/profile-photos/b7c138619c133764e5f3eb43fd2a7b7c.webp', 1, 0, 'deleted', '2026-09-06 23:57:13'),
(19, 47, 'uploads/profile-photos/5b7510b9506ae022c93cc2517ad31917.webp', 1, 0, 'deleted', '2026-09-06 23:58:48'),
(20, 47, 'uploads/profile-photos/4aa683818acdd0fb02e6141dc54706c3.webp', 1, 0, 'active', '2026-09-07 00:00:43'),
(21, 47, 'uploads/profile-photos/0f2405e00471e385e33f8de244edd540.webp', 0, 1, 'active', '2026-09-07 00:00:47'),
(22, 47, 'uploads/profile-photos/f22e1bab69869ff8b16995d88dd40e21.webp', 0, 2, 'active', '2026-09-07 00:00:51'),
(23, 47, 'uploads/profile-photos/a2de66869b5ef4b02cb141d31185f5c1.webp', 0, 3, 'active', '2026-09-07 00:00:54'),
(24, 34, 'uploads/profile-photos/51a0533796a4395d6373b40dc7dd7efc.jpg', 1, 0, 'active', '2026-09-09 00:16:19'),
(26, 51, 'uploads/profile-photos/6147bc6df529cf14ce0aff7462b60cee.jpg', 1, 0, 'active', '2026-09-09 23:05:38'),
(27, 51, 'uploads/profile-photos/391a78f233eeda7b740b9d8a6c79731b.png', 0, 1, 'active', '2026-09-09 23:05:42'),
(28, 51, 'uploads/profile-photos/f05f4b99627be759e0c20ad1e98759c5.jpg', 0, 2, 'active', '2026-09-09 23:05:46'),
(29, 51, 'uploads/profile-photos/e9bb8d04b959c96d2d778f728f2baa31.webp', 0, 3, 'active', '2026-09-09 23:05:50'),
(30, 49, 'uploads/profile-photos/ed3b5f18565b2da03539d2797a3be289.jpg', 1, 0, 'active', '2026-09-10 00:36:09'),
(31, 49, 'uploads/profile-photos/e2d17b5d28f25924a0a54034eefac115.png', 0, 1, 'active', '2026-09-10 00:36:13');

-- --------------------------------------------------------

--
-- Table structure for table `profile_preferences`
--

CREATE TABLE `profile_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `age_min` tinyint(3) UNSIGNED DEFAULT NULL,
  `age_max` tinyint(3) UNSIGNED DEFAULT NULL,
  `height_min` decimal(5,2) DEFAULT NULL,
  `height_max` decimal(5,2) DEFAULT NULL,
  `preferred_religion` varchar(50) DEFAULT NULL,
  `acceptance_of_kids` varchar(50) DEFAULT NULL,
  `horoscope_required` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_preferences`
--

INSERT INTO `profile_preferences` (`id`, `user_id`, `age_min`, `age_max`, `height_min`, `height_max`, `preferred_religion`, `acceptance_of_kids`, `horoscope_required`, `created_at`, `updated_at`) VALUES
(1, 11, 19, 27, 61.00, 87.00, 'Muslim', '', NULL, '2026-08-30 02:31:25', '2026-08-30 02:31:25'),
(2, 12, 20, 28, 48.00, 62.00, 'Muslim', 'no', NULL, '2026-08-30 02:40:13', '2026-08-30 02:40:13'),
(3, 13, 22, 30, 59.00, 87.00, 'Muslim', '', NULL, '2026-08-30 16:04:56', '2026-08-30 16:04:56'),
(4, 14, 26, 32, 64.00, 72.00, 'Muslim', '', NULL, '2026-08-30 16:19:21', '2026-08-30 16:19:21'),
(5, 15, 26, 34, 61.00, 73.00, 'Hindu', 'yes', NULL, '2026-08-30 17:50:06', '2026-08-30 17:50:06'),
(6, 16, 30, 38, 62.00, 87.00, 'Muslim', '', NULL, '2026-08-30 18:04:34', '2026-08-30 18:04:34'),
(7, 17, 21, 29, 48.00, 64.00, 'Muslim', 'yes', NULL, '2026-08-30 18:23:45', '2026-08-30 18:23:45'),
(8, 18, 34, 42, 48.00, 61.00, 'Christian', 'yes', NULL, '2026-08-30 20:47:00', '2026-08-30 20:47:00'),
(9, 19, 21, 29, 48.00, 65.00, 'Muslim', 'yes', NULL, '2026-08-30 23:32:46', '2026-08-30 23:32:46'),
(10, 20, 18, 60, 48.00, 86.00, 'Muslim', 'yes', NULL, '2026-08-31 05:16:43', '2026-08-31 05:16:43'),
(11, 21, 18, 60, 48.00, 72.00, 'Muslim', 'yes_not_living', NULL, '2026-08-31 23:08:09', '2026-08-31 23:08:09'),
(12, 22, 18, 26, 60.00, 87.00, 'Muslim', '', NULL, '2026-09-01 09:11:19', '2026-09-01 09:11:19'),
(13, 24, 18, 41, 49.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-02 22:35:13', '2026-09-02 22:35:13'),
(14, 25, 22, 30, 48.00, 48.00, 'Muslim', '', NULL, '2026-09-02 22:53:33', '2026-09-02 22:53:33'),
(15, 26, 18, 60, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-03 01:31:24', '2026-09-03 01:31:24'),
(16, 27, 18, 48, 65.00, 87.00, 'Muslim', 'yes_not_living', NULL, '2026-09-03 01:48:07', '2026-09-03 01:48:07'),
(17, 29, 19, 54, 50.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-03 22:15:54', '2026-09-03 22:15:54'),
(18, 30, 18, 56, 48.00, 81.00, 'Muslim', 'yes', NULL, '2026-09-04 00:55:36', '2026-09-04 00:55:36'),
(19, 31, 18, 60, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-04 02:11:23', '2026-09-04 02:11:23'),
(20, 34, 18, 60, 48.00, 87.00, 'Hindu', 'yes', NULL, '2026-09-05 23:51:02', '2026-09-05 23:51:02'),
(21, 35, 21, 29, 48.00, 54.00, 'Hindu', 'yes', NULL, '2026-09-05 23:57:09', '2026-09-05 23:57:09'),
(22, 37, 24, 32, 48.00, 63.00, 'Muslim', '', NULL, '2026-09-06 00:37:28', '2026-09-06 00:37:28'),
(23, 39, 20, 28, 48.00, 59.00, 'Muslim', 'yes', NULL, '2026-09-06 00:42:32', '2026-09-06 00:42:32'),
(24, 40, 18, 60, 48.00, 62.00, 'Muslim', 'yes', NULL, '2026-09-06 00:48:47', '2026-09-06 00:48:47'),
(25, 41, 18, 60, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 01:35:58', '2026-09-06 01:35:58'),
(26, 42, 22, 58, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 03:02:55', '2026-09-06 03:02:55'),
(27, 43, 21, 29, 48.00, 68.00, 'Muslim', 'yes', NULL, '2026-09-06 08:59:37', '2026-09-06 08:59:37'),
(28, 44, 19, 27, 48.00, 67.00, 'Muslim', '', NULL, '2026-09-06 14:05:40', '2026-09-06 14:05:40'),
(29, 45, 18, 58, 52.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 15:39:58', '2026-09-06 15:39:58'),
(30, 46, 18, 60, 51.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 16:25:48', '2026-09-06 16:25:48'),
(31, 47, 18, 60, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 23:51:35', '2026-09-06 23:51:35'),
(32, 48, 18, 60, 48.00, 87.00, 'Muslim', 'yes', NULL, '2026-09-06 23:54:39', '2026-09-06 23:54:39'),
(33, 49, 18, 60, 51.00, 87.00, 'Hindu', 'yes', 'yes', '2026-09-07 00:51:25', '2026-09-07 01:15:27'),
(34, 50, 22, 30, 48.00, 80.00, 'Muslim', 'yes_living', NULL, '2026-09-08 00:31:48', '2026-09-08 00:31:48'),
(35, 51, 18, 59, 48.00, 75.00, 'Hindu', 'no', 'yes', '2026-09-08 23:52:55', '2026-09-09 23:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `shortlists`
--

CREATE TABLE `shortlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `shortlisted_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `shortlists`
--

INSERT INTO `shortlists` (`id`, `user_id`, `shortlisted_user_id`, `created_at`) VALUES
(1, 29, 25, '2026-09-04 02:17:25'),
(2, 29, 20, '2026-09-04 02:17:27'),
(3, 40, 31, '2026-09-06 01:20:24'),
(4, 41, 40, '2026-09-06 01:36:05'),
(6, 41, 39, '2026-09-06 02:24:19'),
(13, 42, 29, '2026-09-07 23:30:37'),
(14, 42, 27, '2026-09-07 23:30:40'),
(15, 42, 48, '2026-09-07 23:30:41'),
(16, 42, 24, '2026-09-07 23:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `account_status` enum('active','inactive','blocked','pending') NOT NULL DEFAULT 'pending',
  `otp_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` datetime DEFAULT NULL,
  `photo_privacy_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `strict_matching_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `member_id`, `phone`, `password_hash`, `role`, `account_status`, `otp_verified`, `created_at`, `updated_at`, `last_login_at`, `photo_privacy_enabled`, `strict_matching_enabled`) VALUES
(1, 'MWI000001', '3432432434', '$2y$10$iOipxGjuEApASTQ.egdCGuQaZZ0vGeDhYmlaqTyywYEobVNJw.4mS', 'user', 'pending', 1, '2026-08-29 01:40:32', '2026-08-29 01:40:32', NULL, 0, 0),
(2, 'MWI000002', '9876543211', '$2y$10$1B2zEssRkX/x0il6bx8mmu2qBsASDpePKA.ggDps4wV2xWISEB9dS', 'user', 'pending', 1, '2026-08-29 01:42:49', '2026-08-29 02:02:35', '2026-08-29 02:02:35', 0, 0),
(3, 'MWI000003', '9854343211', '$2y$10$G7b4MhIadqGDQz/2CnjaqeYzIaEuJEQnp1QAqvf/CWicj7ZOlEDd6', 'user', 'pending', 1, '2026-08-29 20:22:46', '2026-08-29 20:22:46', NULL, 0, 0),
(4, 'MWI000004', '9745554548', '$2y$10$Ez0O2uJimJ62CLgUAWcsoun7ICYH05HnirB86CCn0GVnfodjQkgAG', 'user', 'pending', 1, '2026-08-29 20:28:42', '2026-08-29 20:28:42', NULL, 0, 0),
(5, 'MWI000005', '9843543211', '$2y$10$XAsJSCveykWb8PrUmJtQ6.rpN.1t6jsPPfhMp3y7wlGExx.q1DUC2', 'user', 'pending', 1, '2026-08-29 20:32:03', '2026-08-29 20:32:03', NULL, 0, 0),
(6, 'MWI000006', '9854378211', '$2y$10$mfLWppUYvzo7KOq8e5k0Ju.s37HcwtNnkPvLQVn00KghNvJi0Px46', 'user', 'pending', 1, '2026-08-29 20:37:53', '2026-08-29 20:37:53', NULL, 0, 0),
(7, 'MWI000007', '9749443598', '$2y$10$mMR9yz2lAWEbLSFMheQDGuf17QbyjEX.cz2BAGlL4E0YkRzoJLumG', 'user', 'pending', 1, '2026-08-29 21:20:31', '2026-08-29 21:20:31', NULL, 0, 0),
(8, 'MWI000008', '9999999999', '$2y$10$Ki99j/5.erHjUKlkidGSkOmwenIRzK.JQyyxDYk.EQoXTiNPChljG', 'user', 'pending', 1, '2026-08-29 23:49:37', '2026-08-29 23:49:37', NULL, 0, 0),
(9, 'MWI000009', '9999988888', '$2y$10$DM021ye1yqy8m/zeJSs9v.mDwjM8zYABPl4MKMtsqyf/5b9B.ALzm', 'user', 'pending', 1, '2026-08-30 00:18:56', '2026-08-30 00:18:56', NULL, 0, 0),
(10, 'MWI000010', '8989898989', '$2y$10$H/0vppg2bH9g3De4EAo93OFTy9EdNp1l2O2TNO53Ndnylv3Ww8F8W', 'user', 'pending', 1, '2026-08-30 00:35:07', '2026-08-30 00:35:07', NULL, 0, 0),
(11, 'MWI000011', '9749443458', '$2y$10$RLkv8YUAdnqYnaH3wIvpUeQieYOuSSvSJOz/SK5tJ86qm6sS9x.Yu', 'user', 'active', 1, '2026-08-30 02:30:34', '2026-08-30 02:31:25', NULL, 0, 0),
(12, 'MWI000012', '1111111118', '$2y$10$tOOhmKzLmkpbBF5MUyuh5OLViUeM6Tamuoy.fCRTFV8JTcfm/0mU6', 'user', 'active', 1, '2026-08-30 02:39:29', '2026-08-30 02:40:13', NULL, 0, 0),
(13, 'MWI000013', '4555555558', '$2y$10$02MMy89QCE5BnDlEruASgeKG7TctZYvV5.xCaVxolDF4wPdPrM3/G', 'user', 'active', 1, '2026-08-30 16:04:13', '2026-08-30 16:04:56', NULL, 0, 0),
(14, 'MWI000014', '9876543210', '$2y$10$qQlYa/0O6snlZBPPYy6yZ.aDDroxG.02ItEslrWHN3GuwH4RGa.pa', 'user', 'active', 1, '2026-08-30 16:14:54', '2026-08-31 23:34:48', '2026-08-31 23:34:48', 0, 0),
(15, 'MWI000015', '3433343458', '$2y$10$6xqaqbxDXILpFNXTiTKAp.LcEQIl1NEPceLfOlvSlk.zAnXENtb96', 'user', 'active', 1, '2026-08-30 17:49:08', '2026-08-30 17:50:06', NULL, 0, 0),
(16, 'MWI000016', '3454354354', '$2y$10$JpYcrDyTbp7bGGGwiKZtCe6p3WFVq3lMH7sUjLWrLYBovu5anCP4S', 'user', 'active', 1, '2026-08-30 18:03:54', '2026-08-30 18:04:34', NULL, 0, 0),
(17, 'MWI000017', '2222222222', '$2y$10$CmTmjHa8DmDC/J59kB0UPeuZG1vul30quQnjl6GzoVybWxxekzLd2', 'user', 'active', 1, '2026-08-30 18:16:43', '2026-09-05 23:14:55', '2026-09-05 23:14:55', 0, 0),
(18, 'MWI000018', '9876451410', '$2y$10$bmfzQ.GPdZtnOPqu1DSh7u7QsObDMMCCi7lHzLaORrGChyj52Qj/G', 'user', 'active', 1, '2026-08-30 20:45:58', '2026-08-30 20:47:00', NULL, 0, 0),
(19, 'MWI000019', '9749422222', '$2y$10$mhiHSFIoFt.InbHx1LebD.QvRoALvP2B9v2gqR6LHZKw0qNu/42S.', 'user', 'active', 1, '2026-08-30 23:31:26', '2026-08-30 23:32:46', NULL, 0, 0),
(20, 'MWI000020', '2323232323', '$2y$10$EfcUCywmbRJAw3jHAQkjM.EfvPkkbopZACnRogFRVUbhe9eXTYOyi', 'user', 'active', 1, '2026-08-31 05:15:27', '2026-08-31 05:16:43', NULL, 0, 0),
(21, 'MWI000021', '4354354354', '$2y$10$.6drGsovrzKWuK25gwP1meSFftoPz/.ooPKC3CG9CmDO9ILK4W1gy', 'user', 'active', 1, '2026-08-31 23:06:05', '2026-09-02 22:07:23', '2026-09-02 22:07:23', 0, 0),
(22, 'MWI000022', '1465456498', '$2y$10$oBZpmQcdGCZDAuxVywUMce.XFOKe9t4oDtIhQYdDQasQxnGzsdnPi', 'user', 'active', 1, '2026-08-31 23:38:54', '2026-09-02 22:31:35', '2026-09-02 22:31:35', 0, 0),
(23, 'admin', '9000000000', '$2y$12$J6YdkfWjwUAOdVnqs/ggt.1YyJ0BTu539QXsR0Oa.RqJMCDpBRX66', 'admin', 'active', 0, '2026-09-02 00:57:49', '2026-09-10 00:44:10', '2026-09-10 00:44:10', 0, 0),
(24, 'MWI000024', '9711153598', '$2y$10$53leS8c0/EOlYSQE5ovOvOvUZNKHu3q95lu6miqYbikQNZAmBp.c.', 'user', 'active', 1, '2026-09-02 22:33:40', '2026-09-06 15:34:13', '2026-09-06 15:34:13', 0, 0),
(25, 'MWI000025', '9745553598', '$2y$10$8Xqw3RxBbdV/lYx.tUixOeliA1noMqAnao3Dd6zOR4ODOZqwD00dW', 'user', 'active', 1, '2026-09-02 22:52:59', '2026-09-02 22:53:33', NULL, 0, 0),
(26, 'MWI000026', '4353454354', '$2y$10$0d3GMKdXSJDl8UuuptEhy.yq0UGLpzGP5Lzjy1os52S/jPY8OBNsC', 'user', 'active', 1, '2026-09-03 01:30:13', '2026-09-03 01:31:24', NULL, 0, 0),
(27, 'MWI000027', '6456546546', '$2y$10$rD0gxE/kmvqqRORt5xr/FuqeVyfEeX461nHMpO9YcyR3jpcUCTYX6', 'user', 'active', 1, '2026-09-03 01:46:42', '2026-09-03 01:48:07', NULL, 0, 0),
(28, 'MWI000028', '2222222227', '$2y$10$hjUzJDP2PoIAtPxT/mJ0ieo9rBtbNNgA38Fve1lGrp6SL2NWnnHm6', 'user', 'pending', 1, '2026-09-03 21:12:34', '2026-09-03 21:12:34', NULL, 0, 0),
(29, 'MWI000029', '9876543218', '$2y$10$njpTJ23OvF.8/8eBop/yMePzWlC15Yz0qNIx7VE1dixUJx6qX8j2C', 'user', 'active', 1, '2026-09-03 22:14:01', '2026-09-06 14:51:15', '2026-09-06 14:51:15', 0, 0),
(30, 'MWI000030', '4658146416', '$2y$10$iBPZkhNUO4.6DmNNlNMC6OwdXId5vtNe15IcDgMVH0hevrkClM8oK', 'user', 'active', 1, '2026-09-04 00:52:53', '2026-09-04 00:55:36', NULL, 0, 0),
(31, 'MWI000031', '1654465645', '$2y$10$61.comozwFpdMuVYKZvNEO/NGgvxN6JdALrSSSF.qzmXz.lSdKHvi', 'user', 'active', 1, '2026-09-04 02:10:12', '2026-09-04 02:11:23', NULL, 0, 0),
(32, 'MWI000032', '9849849658', '$2y$10$ghwDmn6MrlI9Mco5AsCR8u.YxMIBCQtA.CFOVej/S9GiLEeiI2h3G', 'user', 'pending', 1, '2026-09-05 23:18:39', '2026-09-05 23:18:39', NULL, 0, 0),
(33, 'MWI000033', '2665656615', '$2y$10$wt3B4Vs.lBPE5TMcMSit7ucLLGBNN8PG3HA1O1EIisamVGCyqe7qa', 'user', 'pending', 1, '2026-09-05 23:27:50', '2026-09-05 23:27:50', NULL, 0, 0),
(34, 'MWI000034', '6511651656', '$2y$10$l.10mz1K4Mz4cnlLknIPjuefP0MsFkiZ1PLyMu52xzyyA9pEMPzhe', 'user', 'active', 1, '2026-09-05 23:41:58', '2026-09-09 00:15:59', '2026-09-09 00:15:59', 0, 0),
(35, 'MWI000035', '1113123213', '$2y$10$0b6xKVu2s5fnlAHXkE.Me.e6BuGufUthtTTIrWDUaTma/u4DQJJ.O', 'user', 'active', 1, '2026-09-05 23:54:20', '2026-09-05 23:57:09', NULL, 0, 0),
(36, 'MWI000036', '8979777867', '$2y$10$sTcDxXZ6pCZ1BhxXi8Gcierp.83UPOcsZqhetQAH94PW5yUbydjTK', 'user', 'pending', 1, '2026-09-06 00:08:45', '2026-09-06 00:08:45', NULL, 0, 0),
(37, 'MWI000037', '2266662222', '$2y$10$EfN9ZzSLFJV.X7Dn6ahkjue2uR3S9KMfg3eSVJnPFPNvdzthnC1ia', 'user', 'active', 1, '2026-09-06 00:36:59', '2026-09-06 00:37:28', NULL, 0, 0),
(38, 'MWI000038', '5466465465', '$2y$10$U.Al.Pm2RJgKAQiR9VnX8e5z8Hx2foukQ0NEM8hM05v8.FKzXN2Gi', 'user', 'pending', 1, '2026-09-06 00:38:13', '2026-09-06 00:38:13', NULL, 0, 0),
(39, 'MWI000039', '9555543210', '$2y$10$KCbzJgTPtP1jrKct.kSL8uq84V4p0lIwnuodTXl4A6LCyXL2596pO', 'user', 'active', 1, '2026-09-06 00:41:12', '2026-09-06 00:42:32', NULL, 0, 0),
(40, 'MWI000040', '3322222222', '$2y$10$pdM6K62P2GhZ37G7CTNFBOERC0nCwz6W5b8bpoSW26KnUwxWwlbki', 'user', 'active', 1, '2026-09-06 00:47:48', '2026-09-06 01:20:11', '2026-09-06 01:20:11', 0, 0),
(41, 'MWI000041', '9749865456', '$2y$10$/euNdvdFxqAGNICFQY9fpeh841h3O9YsWARarSJky1xAMMFrNEFPS', 'user', 'active', 1, '2026-09-06 01:34:50', '2026-09-06 14:38:38', '2026-09-06 14:38:38', 0, 0),
(42, 'MWI000042', '9451465110', '$2y$10$c7p37vA5zgaxwEAzHsSqkekXyz9Ja65GsjXAStcC9OKZlEhgGU2m.', 'user', 'active', 1, '2026-09-06 03:01:17', '2026-09-07 21:39:59', '2026-09-07 21:39:59', 0, 0),
(43, 'MWI000043', '2222254354', '$2y$10$9tole/ED2fdw4159EAk7i.QUTESHT95alnf4vHNP6Cbzwimm4725S', 'user', 'active', 1, '2026-09-06 08:54:07', '2026-09-06 08:59:37', NULL, 0, 0),
(44, 'MWI000044', '3453453454', '$2y$10$7T3QqeZgACNLiGRg5igI8edmnomawXg1oFg78GvigcF5.rvWUupDC', 'user', 'active', 1, '2026-09-06 14:03:26', '2026-09-06 23:45:50', '2026-09-06 23:45:50', 0, 0),
(45, 'MWI000045', '6844445465', '$2y$10$f2Dv7SburQXzTw33l6qq/.GHwnsYOxUQSBldBFjDSv2f7lmJadtmO', 'user', 'active', 1, '2026-09-06 15:37:29', '2026-09-06 15:39:58', NULL, 0, 0),
(46, 'MWI000046', '3444543210', '$2y$10$Xg3RkWXGm4FY0YdujZQSZeU0nAIKa8Hdu4kVGHk72vxCucjWr7eSa', 'user', 'active', 1, '2026-09-06 16:24:22', '2026-09-07 00:14:23', '2026-09-07 00:14:23', 0, 0),
(47, 'MWI000047', '5161661546', '$2y$10$2v3nI3IJ.e8DnAD3nCjBCeZUVdqmQWqimqZUFKEcl4tG7EKFx.3aq', 'user', 'active', 1, '2026-09-06 23:50:28', '2026-09-06 23:51:35', NULL, 0, 0),
(48, 'MWI000048', '6666456498', '$2y$10$CHPq0DH4lLmmvSmuU/Ta3OTaqNUwAc57RHgIiQDMKDzDdLjyREBi6', 'user', 'active', 1, '2026-09-06 23:53:29', '2026-09-09 22:32:13', '2026-09-09 22:32:13', 0, 0),
(49, 'MWI000049', '3244444444', '$2y$10$I8tbJ40YE1egM975z4dUUOeM0wxaMzfdsdL/.iLHnrVuJddiqRV76', 'user', 'active', 1, '2026-09-07 00:50:20', '2026-09-10 00:34:57', '2026-09-10 00:34:57', 0, 0),
(50, 'MWI0050', '9874587458', '$2y$10$.BhkNQHKsGvUb3TcwBnFd.9wvR58LfPwevawHg/CMq.7BNe3tY7PG', 'user', 'active', 1, '2026-09-08 00:30:35', '2026-09-08 00:31:48', NULL, 0, 0),
(51, 'MWI0051', '1321321321', '$2y$10$Xu1yPIv1tPyxzNrtjiEhPu05ruPQ22Au1sdElOzf98ITGRVtgelX.', 'user', 'active', 1, '2026-09-08 23:51:30', '2026-09-09 23:01:14', '2026-09-09 23:01:14', 0, 0),
(53, 'MWI0053', '3565454646', '$2y$10$kir4SiQ5FCrFlb.Dmi8koO3zDVkxTua7jXqWTyupdtGMqQ36EEDsu', 'user', 'pending', 1, '2026-09-09 22:07:34', '2026-09-09 22:07:34', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `photo_privacy_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `strict_matching_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_requests`
--

CREATE TABLE `verification_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','in_progress','verified','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_accuracy` decimal(10,2) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `location_place` varchar(150) DEFAULT NULL,
  `location_district` varchar(100) DEFAULT NULL,
  `location_state` varchar(100) DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `verification_photo_path` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_requests`
--

INSERT INTO `verification_requests` (`id`, `user_id`, `payment_id`, `status`, `requested_at`, `started_at`, `completed_at`, `verified_by`, `latitude`, `longitude`, `location_accuracy`, `location_name`, `location_place`, `location_district`, `location_state`, `verification_notes`, `verification_photo_path`, `created_at`, `updated_at`) VALUES
(1, 21, 2, 'verified', '2026-09-02 21:14:37', '2026-09-02 22:05:46', '2026-09-02 22:06:25', 23, 11.1078000, 76.0549000, 100000.00, NULL, 'Nagavalavu', 'Kannur', 'Kerala', NULL, 'uploads/home-verification/MWI000021_home_1_78c5ff3c200a340a.jpg', '2026-09-02 21:14:37', '2026-09-02 22:06:25'),
(2, 20, 3, 'pending', '2026-09-02 21:14:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-02 21:14:44', '2026-09-02 21:14:44'),
(3, 24, 4, 'verified', '2026-09-02 22:36:48', '2026-09-02 22:37:03', '2026-09-06 15:33:48', 23, 11.8684000, 75.3726000, 100000.00, NULL, 'Malappuram', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000024_home_3_760f3a4b4676beb9.png', '2026-09-02 22:36:48', '2026-09-06 15:33:48'),
(4, 16, 5, 'verified', '2026-09-06 09:12:51', '2026-09-06 09:13:13', '2026-09-06 09:13:28', 23, 11.9318000, 75.5723000, 50000.00, NULL, 'Vadakara', 'Kozhikode', 'Kerala', 'erification Notes (Optional', 'uploads/home-verification/MWI000016_home_4_74f91048f39914fd.png', '2026-09-06 09:12:51', '2026-09-06 09:13:28'),
(5, 29, 6, 'verified', '2026-09-06 09:20:44', '2026-09-06 09:20:50', '2026-09-06 09:21:02', 23, 11.9318000, 75.5723000, 50000.00, NULL, 'Vairankode', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000029_home_5_d72c3d69a7815f9c.png', '2026-09-06 09:20:44', '2026-09-06 09:21:02'),
(6, 42, 7, 'verified', '2026-09-06 14:33:12', '2026-09-06 14:33:18', '2026-09-06 14:33:56', 23, 11.8684000, 75.3726000, 100000.00, NULL, 'Sultan Bathery', 'Wayanad', 'Kerala', 'tes (Optio', 'uploads/home-verification/MWI000042_home_6_84f5f3ef64af1b65.jpg', '2026-09-06 14:33:12', '2026-09-06 14:33:56'),
(7, 41, 8, 'verified', '2026-09-06 14:40:27', '2026-09-06 14:43:20', '2026-09-06 14:43:28', 23, 11.8684000, 75.3726000, 100000.00, NULL, 'Vettichira', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000041_home_7_db65e2ba31763a42.jpg', '2026-09-06 14:40:27', '2026-09-06 14:43:28'),
(8, 45, 9, 'verified', '2026-09-06 15:41:38', '2026-09-06 15:42:23', '2026-09-06 15:42:32', 23, 11.8684000, 75.3726000, 100000.00, NULL, 'Othukungal', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000045_home_8_cdbea4cf849b9627.jpg', '2026-09-06 15:41:38', '2026-09-06 15:42:32'),
(9, 46, 10, 'verified', '2026-09-06 19:04:16', '2026-09-06 19:07:51', '2026-09-06 19:08:06', 23, 11.1544000, 76.1549000, 50000.00, NULL, 'Perambra', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000046_home_9_e7eeabb4cfbd2963.jpg', '2026-09-06 19:04:16', '2026-09-06 19:08:06'),
(10, 48, 11, 'verified', '2026-09-07 00:06:39', '2026-09-07 00:06:43', '2026-09-07 00:06:59', 23, 11.3144000, 75.9252000, 50000.00, NULL, 'Vengara', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI000048_home_10_1730590ba1bf3d52.jpg', '2026-09-07 00:06:39', '2026-09-07 00:06:59'),
(12, 51, 13, 'verified', '2026-09-10 00:33:17', '2026-09-10 00:33:25', '2026-09-10 00:33:40', 23, 11.0504000, 75.9781000, 50000.00, NULL, 'Kadampuzha', 'Malappuram', 'Kerala', NULL, 'uploads/home-verification/MWI0051_home_12_02739a4aa859d966.jpg', '2026-09-10 00:33:17', '2026-09-10 00:33:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_sessions_user` (`user_id`),
  ADD KEY `idx_sessions_expiry` (`expires_at`);

--
-- Indexes for table `deleteddata`
--
ALTER TABLE `deleteddata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deleteddata_member_id` (`member_id`),
  ADD KEY `idx_deleteddata_phone` (`phone`),
  ADD KEY `idx_deleteddata_original_user` (`original_user_id`),
  ADD KEY `idx_deleteddata_deleted_at` (`deleted_at`),
  ADD KEY `idx_deleteddata_admin` (`deleted_by_admin_user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_user` (`user_id`),
  ADD KEY `idx_feedback_type` (`feedback_type`),
  ADD KEY `idx_feedback_status` (`status`),
  ADD KEY `idx_feedback_created` (`created_at`);

--
-- Indexes for table `interests`
--
ALTER TABLE `interests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_interest_pair` (`sender_user_id`,`receiver_user_id`),
  ADD KEY `idx_interest_received` (`receiver_user_id`,`status`),
  ADD KEY `idx_interest_sent` (`sender_user_id`,`status`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_phone_purpose` (`phone`,`purpose`),
  ADD KEY `idx_otp_expiry` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `fk_payments_plan` (`plan_id`),
  ADD KEY `idx_payments_user_status` (`user_id`,`payment_status`),
  ADD KEY `idx_payments_status` (`payment_status`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plans_active` (`is_active`);

--
-- Indexes for table `preference_values`
--
ALTER TABLE `preference_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_preference_value` (`user_id`,`preference_type`,`value`),
  ADD KEY `idx_preference_lookup` (`preference_type`,`value`),
  ADD KEY `idx_preference_user_type` (`user_id`,`preference_type`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_profiles_status` (`profile_status`),
  ADD KEY `idx_profiles_gender` (`gender`),
  ADD KEY `idx_profiles_district` (`district`),
  ADD KEY `idx_profiles_religion` (`religion`),
  ADD KEY `idx_profiles_dob` (`date_of_birth`),
  ADD KEY `idx_profiles_marital` (`marital_status`);

--
-- Indexes for table `profile_photos`
--
ALTER TABLE `profile_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_photos_user` (`user_id`),
  ADD KEY `idx_photos_primary` (`user_id`,`is_primary`);

--
-- Indexes for table `profile_preferences`
--
ALTER TABLE `profile_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `shortlists`
--
ALTER TABLE `shortlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_shortlist_pair` (`user_id`,`shortlisted_user_id`),
  ADD KEY `fk_shortlist_target` (`shortlisted_user_id`),
  ADD KEY `idx_shortlist_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_users_status` (`account_status`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_photo_privacy_enabled` (`photo_privacy_enabled`),
  ADD KEY `idx_users_strict_matching_enabled` (`strict_matching_enabled`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_settings_user_id` (`user_id`),
  ADD KEY `idx_user_settings_photo_privacy` (`photo_privacy_enabled`),
  ADD KEY `idx_user_settings_strict_matching` (`strict_matching_enabled`);

--
-- Indexes for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_verification_payment` (`payment_id`),
  ADD KEY `fk_verification_admin` (`verified_by`),
  ADD KEY `idx_verification_status` (`status`),
  ADD KEY `idx_verification_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `deleteddata`
--
ALTER TABLE `deleteddata`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `interests`
--
ALTER TABLE `interests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `preference_values`
--
ALTER TABLE `preference_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=966;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `profile_photos`
--
ALTER TABLE `profile_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `profile_preferences`
--
ALTER TABLE `profile_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `shortlists`
--
ALTER TABLE `shortlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_requests`
--
ALTER TABLE `verification_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `fk_admin_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `auth_sessions`
--
ALTER TABLE `auth_sessions`
  ADD CONSTRAINT `fk_auth_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `interests`
--
ALTER TABLE `interests`
  ADD CONSTRAINT `fk_interest_receiver` FOREIGN KEY (`receiver_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_interest_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `preference_values`
--
ALTER TABLE `preference_values`
  ADD CONSTRAINT `fk_preference_values_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profile_photos`
--
ALTER TABLE `profile_photos`
  ADD CONSTRAINT `fk_profile_photos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profile_preferences`
--
ALTER TABLE `profile_preferences`
  ADD CONSTRAINT `fk_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shortlists`
--
ALTER TABLE `shortlists`
  ADD CONSTRAINT `fk_shortlist_target` FOREIGN KEY (`shortlisted_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_shortlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `fk_user_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD CONSTRAINT `fk_verification_admin` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_verification_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
