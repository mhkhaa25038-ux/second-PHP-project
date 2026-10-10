-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2026 at 05:15 AM
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
-- Database: `cinebook`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `banner_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`banner_id`, `title`, `image_url`, `link_url`, `status`, `created_at`) VALUES
(6, '', '1774841392_xem_phim_truoc_tra_sau_nang_cao_trai_nghiem_dat_ve_xem_phim_banner_7c5313b241.webp', 'https://www.homepaylater.vn/static/5121de881c88984e6aa0243750192a12/c579c/xem_phim_truoc_tra_sau_nang_cao_trai_nghiem_dat_ve_xem_phim_banner_7c5313b241.webp', 'active', '2026-03-30 10:29:52'),
(7, 'Rap', '1774918778_980x500.jpg', 'https://iguov8nhvyobj.vcdn.cloud/media/wysiwyg/2022/092022/980x500.jpg', 'active', '2026-03-31 07:59:38');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `show_id` int(11) NOT NULL,
  `booking_code` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `hold_expires_at` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `show_id`, `booking_code`, `status`, `hold_expires_at`, `subtotal`, `discount_total`, `total`, `created_at`) VALUES
(6, 2, 8, 'BK20260330130713472', 'confirmed', NULL, 210000.00, 0.00, 210000.00, '2026-03-30 13:07:13'),
(7, 1, 12, 'BK20260331072121769', 'confirmed', NULL, 30000.00, 0.00, 30000.00, '2026-03-31 07:21:21'),
(8, 1, 15, 'BK20260331080022709', 'confirmed', NULL, 595000.00, 0.00, 595000.00, '2026-03-31 08:00:22'),
(9, 1, 15, 'BK20260331080045753', 'confirmed', NULL, 595000.00, 0.00, 595000.00, '2026-03-31 08:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `booking_combos`
--

CREATE TABLE `booking_combos` (
  `booking_combo_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_combos`
--

INSERT INTO `booking_combos` (`booking_combo_id`, `booking_id`, `combo_id`, `quantity`, `unit_price`) VALUES
(1, 8, 5, 1, 95000.00),
(2, 9, 5, 1, 95000.00);

-- --------------------------------------------------------

--
-- Table structure for table `booking_seats`
--

CREATE TABLE `booking_seats` (
  `booking_seat_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `show_seat_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_seats`
--

INSERT INTO `booking_seats` (`booking_seat_id`, `booking_id`, `show_seat_id`, `unit_price`) VALUES
(10, 6, 590, 210000.00),
(11, 7, 1502, 30000.00),
(12, 8, 1662, 500000.00),
(13, 9, 1665, 500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `combo_products`
--

CREATE TABLE `combo_products` (
  `combo_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combo_products`
--

INSERT INTO `combo_products` (`combo_id`, `name`, `description`, `price`, `image`, `status`, `created_at`, `updated_at`) VALUES
(3, 'MILO COMBO 2023', '1 large milo + 1 super soft drink + 1 large sweet corn\r\n- Receive on movie day.\r\n- Free change of Caramel corn flavor\r\n- Changing the cheese flavor will incur an additional charge', 30000.00, 'https://iguov8nhvyobj.vcdn.cloud/media/concession/web/64d0e5082f836_1691411720.png', 'active', '2026-03-30 10:01:31', '2026-03-30 10:01:31'),
(4, 'PREMIUM COMBO1', 'Large Sweet Corn + 2 Super Large Drinks + 1 Snack\r\n- Apply Holiday and New Year prices to corn products for transactions with screenings on Holidays and New Year\r\n- Receive goods on the same day as the movie (when purchased with tickets) or on the selected date (when purchased at Store)', 120000.00, 'https://iguov8nhvyobj.vcdn.cloud/media/concession/web/6839856ba1c4e_1748600172.png', 'active', '2026-03-30 10:04:02', '2026-03-30 10:04:02'),
(5, 'MY COMBO 1 Large Sweet Corn + 1 Super Large Drink', 'MY COMBO 1 Large Sweet Corn + 1 Super Large Drink\r\n- Apply Holiday and New Year prices to corn products for transactions with screenings on Holidays and New Year\r\n- Receive goods on the same day as the movie (when purchased with tickets) or on the selected date (when purchased at Store)', 95000.00, 'https://iguov8nhvyobj.vcdn.cloud/media/concession/web/6644731d5a8f5_1715761949.png', 'active', '2026-03-30 10:06:09', '2026-03-30 10:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `start_at`, `end_at`, `image`, `status`) VALUES
(6, 'PRICE AT THEATER PrimeScreen Binh Thuy', '1. Target Audience:\r\n\r\nApplies to CGV members purchasing movie tickets directly at or online via the CGV Cinemas app or website cgv.vn.\r\n\r\n2. Re-download the app:\r\n\r\n- CGV Hoang Van Thu\r\n\r\n3. Application Period:\r\n\r\nApplies from June 10, 2025 until further notice.\r\n\r\n4. Ticket Price:\r\n\r\nSTANDARD 2D SCREENING TICKET PRICE: Only 79,000 VND/ticket\r\n\r\n5. Instructions for Purchasing Tickets:\r\n\r\nPurchase tickets directly at the store:\r\n\r\nStep 1: Customers present their membership card/CGV app after logging in with their member account/registered CGV member phone number to receive the CGV member price.\r\n\r\n(For customers who are not yet CGV members but are going to watch movies at participating CGV Membership Tickets, please register for membership quickly at tinhdau. Details can be found here.)\r\nStep 2: Choose a movie, suitable showtime, and desired seating location.\r\n\r\nStep 3: Check transaction information and inform CGV staff of the appropriate payment method, then pay and receive your ticket.\r\n\r\nOnline Ticket Purchase\r\n\r\nStep 1: Log in to your member account on the CGV app or website cgv.vn\r\n\r\nStep 2: Select a theater participating in the CGV Membership Ticket program\r\n\r\nStep 3: Choose a movie, suitable showtime, and desired seating location.\r\n\r\nStep 4: Check transaction information and choose a suitable payment method, then pay and receive your ticket code.\r\n\r\n5. Terms and Conditions:\r\n\r\n- Applicable to all shows from Monday to Sunday. - Applicable to CGV members.\r\n\r\n- When making a purchase, each CGV member is limited to a maximum of 2 tickets per show.\r\n\r\n- No limit on the number of tickets purchased per day.\r\n\r\n- Applies only to tickets for regular or VIP 2D films; does not apply to Sweetbox seats, special viewing rooms such as Gold Class, L’amour, Premium, Cine&Foret, Cine&Living Room, IMAX, 4DX, ULTRA 4DX, Starium, and ScreenX.\r\n\r\n- - The surcharge for upgrading from 2D to 3D or upgrading to Sweetbox is not applicable.\r\n\r\n- CGV members purchasing tickets directly at the theater must present their Member Card/CGV App after logging in to their member account/registered CGV member phone number during the transaction process, including at the ticket control area before entering the viewing room.\r\n\r\n- Ticket prices do not apply to the \"North Wind\" and \"Festival\" categories, as well as compensatory holidays as stipulated by law.\r\n\r\n- In all cases, customers are not allowed to use ticket purchases for resale purposes in any form. CGV reserves the right to refuse any transaction showing signs of fraud.\r\n\r\n- CGV reserves the right to change the Terms and Conditions for CGV members and the official ticket prices.\r\n\r\n- Transactions are conducted and spending is in accordance with CGV\'s policies.\r\n\r\nBy purchasing a CGV member ticket, customers automatically accept all the terms and conditions of the main ticket price list listed here.', '2026-03-30 10:31:00', '2026-04-30 10:31:00', 'https://iguov8nhvyobj.vcdn.cloud/media/wysiwyg/2025/102025/350x495_13_.jpg', 'active'),
(7, 'CGV MEMBERSHIP PROGRAM 2026', NULL, '2026-05-31 07:40:00', '2026-06-30 07:40:00', 'https://iguov8nhvyobj.vcdn.cloud/media/wysiwyg/2026/022026/1.jpg', 'active'),
(8, 'GET A 30,000 VND DISCOUNT AT CGV De Photo', '1. PROGRAM DETAILS\r\n\r\n- Customers who purchase any merchandise combo will receive a 30,000 VND discount code for one photo shoot/print.\r\n\r\n2. PROGRAM DURATION:\r\n\r\n- From August 6th, 2025 until further notice from CGV.\r\n\r\n3. APPLICABLE LOCATIONS:\r\n\r\nApplicable to the following cinema complexes:\r\n\r\n- CGV Van Hanh Mall\r\n\r\n- CGV Vivo City\r\n\r\n4. TERMS AND CONDITIONS:\r\n\r\n- Applicable to customers who purchase any merchandise combo.\r\n\r\n- Applicable to transactions made from August 6th, 2025.\r\n\r\n- Customers will receive a 30,000 VND discount code. One photo shoot or print at CGV de Photo. Discount code valid for one day.\r\n\r\n- Customers must use the discount code at the cinema where they purchase the product.\r\n\r\n- Promotion applies to customers making transactions at all counters (ticket counter, popcorn counter).\r\n\r\n- Applicable to transactions made directly at the cinema or online via the CGV website/app.\r\n\r\n- Discounts can be applied cumulatively within a single transaction, for example, buy 1 merchandise combo and receive 1 discount code, buy 2 merchandise combos and receive 2 discount codes, etc. Tickets cannot be combined to apply the program.\r\n\r\n- Each valid transaction can only receive the gift once.\r\n\r\n- There is no limit to the number of entries per customer if the transaction meets the conditions.\r\n\r\n- The number of gifts is limited; the program may end early if all gifts are distributed.\r\n\r\n- Cannot be combined with other promotions.\r\n\r\n5. NOT APPLICABLE:\r\n\r\n- Tickets redeemed from vouchers purchased during livestream programs.\r\n\r\n- 0 VND tickets/vouchers.\r\n\r\n- CGVian, CJ Membership.\r\n\r\n- Using CGV reward points to redeem for 0 VND tickets.\r\n\r\n- Cannot be combined with other promotions.\r\n\r\n6. INSTRUCTIONS FOR USING COUPONS AT PHOTOBOOTH – CGV de Photo.\r\n\r\nOn the payment screen:\r\n\r\n- Select payment method: Coupon\r\n\r\n- Enter the code/Scan the coupon number below\r\n\r\n- Select Pay and continue taking/printing photos', '2026-06-30 07:41:00', '2026-08-31 07:41:00', 'https://iguov8nhvyobj.vcdn.cloud/media/wysiwyg/2025/082025/PTB_30K_N_O_350x495.png', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(6, 'Action'),
(10, 'Animation'),
(9, 'Comedy'),
(7, 'Horror'),
(8, 'Romance');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `name`, `address`, `city`, `phone`, `status`) VALUES
(4, 'PrimeScreen Ninh Kieu', '128 April 30 Street, Ninh Kieu Ward, Ninh Kieu District, City. Can Tho', 'Can Tho City', NULL, 'active'),
(5, 'PrimeScreen Cai Rang', '2 Vo Nguyen Giap Street, Hung Phu Ward, Cai Rang District, City. Can Tho', 'Can Tho City', NULL, 'active'),
(6, 'PrimeScreen Binh Thuy', '215 Cach Mang Thang 8 Street, Binh Thuy Ward, Binh Thuy District, City. Can Tho', 'Can Tho City', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `synopsis` text DEFAULT NULL,
  `language` varchar(50) NOT NULL,
  `duration_min` int(11) NOT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'coming_soon',
  `age_rating` varchar(10) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `synopsis`, `language`, `duration_min`, `trailer_url`, `poster_url`, `status`, `age_rating`, `release_date`, `created_at`, `updated_at`) VALUES
(8, 'SEE YOU ON THE DAY OF THE SOLAR ECLIPSE', 'In 1995, while facing an important life decision, An was suddenly pulled back to the past by love letters that had never been exchanged. The journey to find Thien - her first love once engraved in her heart - takes her back to the old village of Tra May, where sweet memories mixed with hurt have never gone away. In a fateful moment when two people suddenly meet, secrets hidden for many years are gradually revealed, forcing An to face the truth and choose his own path. “See You on the Eclipse Day” is an emotional love story about things left unsaid, about eternal love and the tormenting question: if I had the chance, would I dare to believe in my heart again?', 'Vietnamese - English subtitles', 118, 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/4/7/470wx700h-nhatthuc.jpg', 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/4/7/470wx700h-nhatthuc.jpg', 'now_showing', 'T16', '2026-03-30', '2026-03-30 09:44:37', '2026-03-30 09:44:37'),
(9, 'ESCAPE FROM THE Apocalypse', 'Ryland Grace, a science teacher, realizes he is Earth\'s last hope. His mission: save the Sun from a mysterious creature that is draining light energy, pushing the entire solar system into eternal darkness. If they fail, life on Earth will die with the last light of the sun. Amidst the loneliness of outer space and the pressure of time running out, all his calculations and all his decisions carry the fate of all humanity on his shoulders. But on that seemingly alone journey in endless space, an unexpected friendship with an alien creature appeared. And perhaps, to save the Earth, he won\'t have to fight alone.', 'English - Vietnamese subtitles', 157, 'https://youtu.be/LQ9KHDpA9vI', 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/3/5/350x495-mary.jpg', 'now_showing', 'T13', '2026-03-30', '2026-03-30 09:48:28', '2026-03-30 09:48:28'),
(10, 'DEMON ENTRANCE 2', 'The Devil Enters the Trang 2 is a prequel of the character Minh Nhu, returning to his family\'s dyeing factory after many years of being chased away. Here, she must face ghostly phenomena and the cruel truth about her mother\'s death and the bloody contract of the past. Evil will be rewarded with evil, will Minh Nhu be able to escape the clutches of evil?', 'Vietnamese - English subtitles', 126, 'https://youtu.be/VCI9XTxlQxk', 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/4/7/470wx700h-qnt.jpg', 'now_showing', 'T18', '2026-03-30', '2026-03-30 09:51:04', '2026-03-30 10:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `movie_genres`
--

CREATE TABLE `movie_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(8, 6),
(8, 9),
(9, 8),
(10, 7);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `transaction_code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `method`, `amount`, `status`, `paid_at`, `transaction_code`) VALUES
(6, 6, 'cash', 210000.00, 'paid', '2026-03-30 13:07:13', 'TXN20260330130713148'),
(7, 7, 'cash', 30000.00, 'paid', '2026-03-31 07:21:21', 'TXN20260331072121807'),
(8, 8, 'cash', 595000.00, 'paid', '2026-03-31 08:00:22', 'TXN20260331080022946'),
(9, 9, 'cash', 595000.00, 'paid', '2026-03-31 08:00:45', 'TXN20260331080045766');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `location_id`, `room_name`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
(5, 6, 'Room 1', 96, 'active', '2026-03-30 09:27:54', '2026-03-30 09:33:30'),
(6, 6, 'Room 2', 96, 'active', '2026-03-30 09:28:14', '2026-03-30 09:28:14'),
(7, 5, 'Room 1', 96, 'active', '2026-03-30 09:28:47', '2026-03-30 09:38:39'),
(8, 5, 'Room 2', 96, 'active', '2026-03-30 09:29:07', '2026-03-30 09:38:52'),
(9, 4, 'Room 1', 96, 'active', '2026-03-30 09:29:43', '2026-03-30 09:39:23'),
(11, 4, 'Room 2', 96, 'active', '2026-03-30 09:30:14', '2026-03-30 09:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `seat_row` varchar(10) NOT NULL,
  `is_aisle` tinyint(1) NOT NULL DEFAULT 0,
  `is_accessible` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_id`, `room_id`, `category_id`, `seat_number`, `seat_row`, `is_aisle`, `is_accessible`) VALUES
(293, 5, 7, '1', 'A', 0, 1),
(294, 5, 7, '2', 'A', 0, 1),
(295, 5, 7, '3', 'A', 0, 0),
(296, 5, 7, '4', 'A', 1, 0),
(297, 5, 7, '5', 'A', 1, 0),
(298, 5, 7, '6', 'A', 0, 0),
(299, 5, 7, '7', 'A', 0, 0),
(300, 5, 7, '8', 'A', 1, 0),
(301, 5, 7, '9', 'A', 1, 0),
(302, 5, 7, '10', 'A', 0, 0),
(303, 5, 7, '11', 'A', 0, 0),
(304, 5, 7, '12', 'A', 0, 0),
(305, 5, 7, '1', 'B', 0, 0),
(306, 5, 7, '2', 'B', 0, 0),
(307, 5, 7, '3', 'B', 0, 0),
(308, 5, 7, '4', 'B', 1, 0),
(309, 5, 7, '5', 'B', 1, 0),
(310, 5, 7, '6', 'B', 0, 0),
(311, 5, 7, '7', 'B', 0, 0),
(312, 5, 7, '8', 'B', 1, 0),
(313, 5, 7, '9', 'B', 1, 0),
(314, 5, 7, '10', 'B', 0, 0),
(315, 5, 7, '11', 'B', 0, 0),
(316, 5, 7, '12', 'B', 0, 0),
(317, 5, 7, '1', 'C', 0, 0),
(318, 5, 7, '2', 'C', 0, 0),
(319, 5, 7, '3', 'C', 0, 0),
(320, 5, 7, '4', 'C', 1, 0),
(321, 5, 7, '5', 'C', 1, 0),
(322, 5, 7, '6', 'C', 0, 0),
(323, 5, 7, '7', 'C', 0, 0),
(324, 5, 7, '8', 'C', 1, 0),
(325, 5, 7, '9', 'C', 1, 0),
(326, 5, 7, '10', 'C', 0, 0),
(327, 5, 7, '11', 'C', 0, 0),
(328, 5, 7, '12', 'C', 0, 0),
(329, 5, 7, '1', 'D', 0, 0),
(330, 5, 7, '2', 'D', 0, 0),
(331, 5, 7, '3', 'D', 0, 0),
(332, 5, 7, '4', 'D', 1, 0),
(333, 5, 7, '5', 'D', 1, 0),
(334, 5, 7, '6', 'D', 0, 0),
(335, 5, 7, '7', 'D', 0, 0),
(336, 5, 7, '8', 'D', 1, 0),
(337, 5, 7, '9', 'D', 1, 0),
(338, 5, 7, '10', 'D', 0, 0),
(339, 5, 7, '11', 'D', 0, 0),
(340, 5, 7, '12', 'D', 0, 0),
(341, 5, 7, '1', 'E', 0, 0),
(342, 5, 7, '2', 'E', 0, 0),
(343, 5, 7, '3', 'E', 0, 0),
(344, 5, 7, '4', 'E', 1, 0),
(345, 5, 7, '5', 'E', 1, 0),
(346, 5, 7, '6', 'E', 0, 0),
(347, 5, 7, '7', 'E', 0, 0),
(348, 5, 7, '8', 'E', 1, 0),
(349, 5, 7, '9', 'E', 1, 0),
(350, 5, 7, '10', 'E', 0, 0),
(351, 5, 7, '11', 'E', 0, 0),
(352, 5, 7, '12', 'E', 0, 0),
(353, 5, 7, '1', 'F', 0, 0),
(354, 5, 7, '2', 'F', 0, 0),
(355, 5, 7, '3', 'F', 0, 0),
(356, 5, 7, '4', 'F', 1, 0),
(357, 5, 7, '5', 'F', 1, 0),
(358, 5, 7, '6', 'F', 0, 0),
(359, 5, 7, '7', 'F', 0, 0),
(360, 5, 7, '8', 'F', 1, 0),
(361, 5, 7, '9', 'F', 1, 0),
(362, 5, 7, '10', 'F', 0, 0),
(363, 5, 7, '11', 'F', 0, 0),
(364, 5, 7, '12', 'F', 0, 0),
(365, 5, 7, '1', 'G', 0, 0),
(366, 5, 7, '2', 'G', 0, 0),
(367, 5, 7, '3', 'G', 0, 0),
(368, 5, 7, '4', 'G', 1, 0),
(369, 5, 7, '5', 'G', 1, 0),
(370, 5, 7, '6', 'G', 0, 0),
(371, 5, 7, '7', 'G', 0, 0),
(372, 5, 7, '8', 'G', 1, 0),
(373, 5, 7, '9', 'G', 1, 0),
(374, 5, 7, '10', 'G', 0, 0),
(375, 5, 7, '11', 'G', 0, 0),
(376, 5, 7, '12', 'G', 0, 0),
(377, 5, 7, '1', 'H', 0, 0),
(378, 5, 7, '2', 'H', 0, 0),
(379, 5, 7, '3', 'H', 0, 0),
(380, 5, 7, '4', 'H', 1, 0),
(381, 5, 7, '5', 'H', 1, 0),
(382, 5, 7, '6', 'H', 0, 0),
(383, 5, 7, '7', 'H', 0, 0),
(384, 5, 7, '8', 'H', 1, 0),
(385, 5, 7, '9', 'H', 1, 0),
(386, 5, 7, '10', 'H', 0, 0),
(387, 5, 7, '11', 'H', 0, 0),
(388, 5, 7, '12', 'H', 0, 0),
(389, 6, 7, '1', 'A', 0, 1),
(390, 6, 7, '2', 'A', 0, 1),
(391, 6, 7, '3', 'A', 0, 0),
(392, 6, 7, '4', 'A', 1, 0),
(393, 6, 7, '5', 'A', 1, 0),
(394, 6, 7, '6', 'A', 0, 0),
(395, 6, 7, '7', 'A', 0, 0),
(396, 6, 7, '8', 'A', 1, 0),
(397, 6, 7, '9', 'A', 1, 0),
(398, 6, 7, '10', 'A', 0, 0),
(399, 6, 7, '11', 'A', 0, 0),
(400, 6, 7, '12', 'A', 0, 0),
(401, 6, 7, '1', 'B', 0, 0),
(402, 6, 7, '2', 'B', 0, 0),
(403, 6, 7, '3', 'B', 0, 0),
(404, 6, 7, '4', 'B', 1, 0),
(405, 6, 7, '5', 'B', 1, 0),
(406, 6, 7, '6', 'B', 0, 0),
(407, 6, 7, '7', 'B', 0, 0),
(408, 6, 7, '8', 'B', 1, 0),
(409, 6, 7, '9', 'B', 1, 0),
(410, 6, 7, '10', 'B', 0, 0),
(411, 6, 7, '11', 'B', 0, 0),
(412, 6, 7, '12', 'B', 0, 0),
(413, 6, 7, '1', 'C', 0, 0),
(414, 6, 7, '2', 'C', 0, 0),
(415, 6, 7, '3', 'C', 0, 0),
(416, 6, 7, '4', 'C', 1, 0),
(417, 6, 7, '5', 'C', 1, 0),
(418, 6, 7, '6', 'C', 0, 0),
(419, 6, 7, '7', 'C', 0, 0),
(420, 6, 7, '8', 'C', 1, 0),
(421, 6, 7, '9', 'C', 1, 0),
(422, 6, 7, '10', 'C', 0, 0),
(423, 6, 7, '11', 'C', 0, 0),
(424, 6, 7, '12', 'C', 0, 0),
(425, 6, 7, '1', 'D', 0, 0),
(426, 6, 7, '2', 'D', 0, 0),
(427, 6, 7, '3', 'D', 0, 0),
(428, 6, 7, '4', 'D', 1, 0),
(429, 6, 7, '5', 'D', 1, 0),
(430, 6, 7, '6', 'D', 0, 0),
(431, 6, 7, '7', 'D', 0, 0),
(432, 6, 7, '8', 'D', 1, 0),
(433, 6, 7, '9', 'D', 1, 0),
(434, 6, 7, '10', 'D', 0, 0),
(435, 6, 7, '11', 'D', 0, 0),
(436, 6, 7, '12', 'D', 0, 0),
(437, 6, 7, '1', 'E', 0, 0),
(438, 6, 7, '2', 'E', 0, 0),
(439, 6, 7, '3', 'E', 0, 0),
(440, 6, 7, '4', 'E', 1, 0),
(441, 6, 7, '5', 'E', 1, 0),
(442, 6, 7, '6', 'E', 0, 0),
(443, 6, 7, '7', 'E', 0, 0),
(444, 6, 7, '8', 'E', 1, 0),
(445, 6, 7, '9', 'E', 1, 0),
(446, 6, 7, '10', 'E', 0, 0),
(447, 6, 7, '11', 'E', 0, 0),
(448, 6, 7, '12', 'E', 0, 0),
(449, 6, 6, '1', 'F', 0, 0),
(450, 6, 6, '2', 'F', 0, 0),
(451, 6, 6, '3', 'F', 0, 0),
(452, 6, 6, '4', 'F', 1, 0),
(453, 6, 6, '5', 'F', 1, 0),
(454, 6, 6, '6', 'F', 0, 0),
(455, 6, 6, '7', 'F', 0, 0),
(456, 6, 6, '8', 'F', 1, 0),
(457, 6, 6, '9', 'F', 1, 0),
(458, 6, 6, '10', 'F', 0, 0),
(459, 6, 6, '11', 'F', 0, 0),
(460, 6, 6, '12', 'F', 0, 0),
(461, 6, 6, '1', 'G', 0, 0),
(462, 6, 6, '2', 'G', 0, 0),
(463, 6, 6, '3', 'G', 0, 0),
(464, 6, 6, '4', 'G', 1, 0),
(465, 6, 6, '5', 'G', 1, 0),
(466, 6, 6, '6', 'G', 0, 0),
(467, 6, 6, '7', 'G', 0, 0),
(468, 6, 6, '8', 'G', 1, 0),
(469, 6, 6, '9', 'G', 1, 0),
(470, 6, 6, '10', 'G', 0, 0),
(471, 6, 6, '11', 'G', 0, 0),
(472, 6, 6, '12', 'G', 0, 0),
(473, 6, 6, '1', 'H', 0, 0),
(474, 6, 6, '2', 'H', 0, 0),
(475, 6, 6, '3', 'H', 0, 0),
(476, 6, 6, '4', 'H', 1, 0),
(477, 6, 6, '5', 'H', 1, 0),
(478, 6, 6, '6', 'H', 0, 0),
(479, 6, 6, '7', 'H', 0, 0),
(480, 6, 6, '8', 'H', 1, 0),
(481, 6, 6, '9', 'H', 1, 0),
(482, 6, 6, '10', 'H', 0, 0),
(483, 6, 6, '11', 'H', 0, 0),
(484, 6, 6, '12', 'H', 0, 0),
(485, 7, 7, '1', 'A', 0, 1),
(486, 7, 7, '2', 'A', 0, 1),
(487, 7, 7, '3', 'A', 0, 0),
(488, 7, 7, '4', 'A', 1, 0),
(489, 7, 7, '5', 'A', 1, 0),
(490, 7, 7, '6', 'A', 0, 0),
(491, 7, 7, '7', 'A', 0, 0),
(492, 7, 7, '8', 'A', 1, 0),
(493, 7, 7, '9', 'A', 1, 0),
(494, 7, 7, '10', 'A', 0, 0),
(495, 7, 7, '11', 'A', 0, 0),
(496, 7, 7, '12', 'A', 0, 0),
(497, 7, 7, '1', 'B', 0, 0),
(498, 7, 7, '2', 'B', 0, 0),
(499, 7, 7, '3', 'B', 0, 0),
(500, 7, 7, '4', 'B', 1, 0),
(501, 7, 7, '5', 'B', 1, 0),
(502, 7, 7, '6', 'B', 0, 0),
(503, 7, 7, '7', 'B', 0, 0),
(504, 7, 7, '8', 'B', 1, 0),
(505, 7, 7, '9', 'B', 1, 0),
(506, 7, 7, '10', 'B', 0, 0),
(507, 7, 7, '11', 'B', 0, 0),
(508, 7, 7, '12', 'B', 0, 0),
(509, 7, 7, '1', 'C', 0, 0),
(510, 7, 7, '2', 'C', 0, 0),
(511, 7, 7, '3', 'C', 0, 0),
(512, 7, 7, '4', 'C', 1, 0),
(513, 7, 7, '5', 'C', 1, 0),
(514, 7, 7, '6', 'C', 0, 0),
(515, 7, 7, '7', 'C', 0, 0),
(516, 7, 7, '8', 'C', 1, 0),
(517, 7, 7, '9', 'C', 1, 0),
(518, 7, 7, '10', 'C', 0, 0),
(519, 7, 7, '11', 'C', 0, 0),
(520, 7, 7, '12', 'C', 0, 0),
(521, 7, 7, '1', 'D', 0, 0),
(522, 7, 7, '2', 'D', 0, 0),
(523, 7, 7, '3', 'D', 0, 0),
(524, 7, 7, '4', 'D', 1, 0),
(525, 7, 7, '5', 'D', 1, 0),
(526, 7, 7, '6', 'D', 0, 0),
(527, 7, 7, '7', 'D', 0, 0),
(528, 7, 7, '8', 'D', 1, 0),
(529, 7, 7, '9', 'D', 1, 0),
(530, 7, 7, '10', 'D', 0, 0),
(531, 7, 7, '11', 'D', 0, 0),
(532, 7, 7, '12', 'D', 0, 0),
(533, 7, 7, '1', 'E', 0, 0),
(534, 7, 7, '2', 'E', 0, 0),
(535, 7, 7, '3', 'E', 0, 0),
(536, 7, 7, '4', 'E', 1, 0),
(537, 7, 7, '5', 'E', 1, 0),
(538, 7, 7, '6', 'E', 0, 0),
(539, 7, 7, '7', 'E', 0, 0),
(540, 7, 7, '8', 'E', 1, 0),
(541, 7, 7, '9', 'E', 1, 0),
(542, 7, 7, '10', 'E', 0, 0),
(543, 7, 7, '11', 'E', 0, 0),
(544, 7, 7, '12', 'E', 0, 0),
(545, 7, 6, '1', 'F', 0, 0),
(546, 7, 6, '2', 'F', 0, 0),
(547, 7, 6, '3', 'F', 0, 0),
(548, 7, 6, '4', 'F', 1, 0),
(549, 7, 6, '5', 'F', 1, 0),
(550, 7, 6, '6', 'F', 0, 0),
(551, 7, 6, '7', 'F', 0, 0),
(552, 7, 6, '8', 'F', 1, 0),
(553, 7, 6, '9', 'F', 1, 0),
(554, 7, 6, '10', 'F', 0, 0),
(555, 7, 6, '11', 'F', 0, 0),
(556, 7, 6, '12', 'F', 0, 0),
(557, 7, 6, '1', 'G', 0, 0),
(558, 7, 6, '2', 'G', 0, 0),
(559, 7, 6, '3', 'G', 0, 0),
(560, 7, 6, '4', 'G', 1, 0),
(561, 7, 6, '5', 'G', 1, 0),
(562, 7, 6, '6', 'G', 0, 0),
(563, 7, 6, '7', 'G', 0, 0),
(564, 7, 6, '8', 'G', 1, 0),
(565, 7, 6, '9', 'G', 1, 0),
(566, 7, 6, '10', 'G', 0, 0),
(567, 7, 6, '11', 'G', 0, 0),
(568, 7, 6, '12', 'G', 0, 0),
(569, 7, 6, '1', 'H', 0, 0),
(570, 7, 6, '2', 'H', 0, 0),
(571, 7, 6, '3', 'H', 0, 0),
(572, 7, 6, '4', 'H', 1, 0),
(573, 7, 6, '5', 'H', 1, 0),
(574, 7, 6, '6', 'H', 0, 0),
(575, 7, 6, '7', 'H', 0, 0),
(576, 7, 6, '8', 'H', 1, 0),
(577, 7, 6, '9', 'H', 1, 0),
(578, 7, 6, '10', 'H', 0, 0),
(579, 7, 6, '11', 'H', 0, 0),
(580, 7, 6, '12', 'H', 0, 0),
(581, 8, 6, '1', 'A', 0, 1),
(582, 8, 6, '2', 'A', 0, 1),
(583, 8, 6, '3', 'A', 0, 0),
(584, 8, 6, '4', 'A', 1, 0),
(585, 8, 6, '5', 'A', 1, 0),
(586, 8, 6, '6', 'A', 0, 0),
(587, 8, 6, '7', 'A', 0, 0),
(588, 8, 6, '8', 'A', 1, 0),
(589, 8, 6, '9', 'A', 1, 0),
(590, 8, 6, '10', 'A', 0, 0),
(591, 8, 6, '11', 'A', 0, 0),
(592, 8, 6, '12', 'A', 0, 0),
(593, 8, 6, '1', 'B', 0, 0),
(594, 8, 6, '2', 'B', 0, 0),
(595, 8, 6, '3', 'B', 0, 0),
(596, 8, 6, '4', 'B', 1, 0),
(597, 8, 6, '5', 'B', 1, 0),
(598, 8, 6, '6', 'B', 0, 0),
(599, 8, 6, '7', 'B', 0, 0),
(600, 8, 6, '8', 'B', 1, 0),
(601, 8, 6, '9', 'B', 1, 0),
(602, 8, 6, '10', 'B', 0, 0),
(603, 8, 6, '11', 'B', 0, 0),
(604, 8, 6, '12', 'B', 0, 0),
(605, 8, 6, '1', 'C', 0, 0),
(606, 8, 6, '2', 'C', 0, 0),
(607, 8, 6, '3', 'C', 0, 0),
(608, 8, 6, '4', 'C', 1, 0),
(609, 8, 6, '5', 'C', 1, 0),
(610, 8, 6, '6', 'C', 0, 0),
(611, 8, 6, '7', 'C', 0, 0),
(612, 8, 6, '8', 'C', 1, 0),
(613, 8, 6, '9', 'C', 1, 0),
(614, 8, 6, '10', 'C', 0, 0),
(615, 8, 6, '11', 'C', 0, 0),
(616, 8, 6, '12', 'C', 0, 0),
(617, 8, 6, '1', 'D', 0, 0),
(618, 8, 6, '2', 'D', 0, 0),
(619, 8, 6, '3', 'D', 0, 0),
(620, 8, 6, '4', 'D', 1, 0),
(621, 8, 6, '5', 'D', 1, 0),
(622, 8, 6, '6', 'D', 0, 0),
(623, 8, 6, '7', 'D', 0, 0),
(624, 8, 6, '8', 'D', 1, 0),
(625, 8, 6, '9', 'D', 1, 0),
(626, 8, 6, '10', 'D', 0, 0),
(627, 8, 6, '11', 'D', 0, 0),
(628, 8, 6, '12', 'D', 0, 0),
(629, 8, 6, '1', 'E', 0, 0),
(630, 8, 6, '2', 'E', 0, 0),
(631, 8, 6, '3', 'E', 0, 0),
(632, 8, 6, '4', 'E', 1, 0),
(633, 8, 6, '5', 'E', 1, 0),
(634, 8, 6, '6', 'E', 0, 0),
(635, 8, 6, '7', 'E', 0, 0),
(636, 8, 6, '8', 'E', 1, 0),
(637, 8, 6, '9', 'E', 1, 0),
(638, 8, 6, '10', 'E', 0, 0),
(639, 8, 6, '11', 'E', 0, 0),
(640, 8, 6, '12', 'E', 0, 0),
(641, 8, 7, '1', 'F', 0, 0),
(642, 8, 7, '2', 'F', 0, 0),
(643, 8, 7, '3', 'F', 0, 0),
(644, 8, 7, '4', 'F', 1, 0),
(645, 8, 7, '5', 'F', 1, 0),
(646, 8, 7, '6', 'F', 0, 0),
(647, 8, 7, '7', 'F', 0, 0),
(648, 8, 7, '8', 'F', 1, 0),
(649, 8, 7, '9', 'F', 1, 0),
(650, 8, 7, '10', 'F', 0, 0),
(651, 8, 7, '11', 'F', 0, 0),
(652, 8, 7, '12', 'F', 0, 0),
(653, 8, 7, '1', 'G', 0, 0),
(654, 8, 7, '2', 'G', 0, 0),
(655, 8, 7, '3', 'G', 0, 0),
(656, 8, 7, '4', 'G', 1, 0),
(657, 8, 7, '5', 'G', 1, 0),
(658, 8, 7, '6', 'G', 0, 0),
(659, 8, 7, '7', 'G', 0, 0),
(660, 8, 7, '8', 'G', 1, 0),
(661, 8, 7, '9', 'G', 1, 0),
(662, 8, 7, '10', 'G', 0, 0),
(663, 8, 7, '11', 'G', 0, 0),
(664, 8, 7, '12', 'G', 0, 0),
(665, 8, 7, '1', 'H', 0, 0),
(666, 8, 7, '2', 'H', 0, 0),
(667, 8, 7, '3', 'H', 0, 0),
(668, 8, 7, '4', 'H', 1, 0),
(669, 8, 7, '5', 'H', 1, 0),
(670, 8, 7, '6', 'H', 0, 0),
(671, 8, 7, '7', 'H', 0, 0),
(672, 8, 7, '8', 'H', 1, 0),
(673, 8, 7, '9', 'H', 1, 0),
(674, 8, 7, '10', 'H', 0, 0),
(675, 8, 7, '11', 'H', 0, 0),
(676, 8, 7, '12', 'H', 0, 0),
(773, 9, 7, '1', 'A', 0, 1),
(774, 9, 7, '2', 'A', 0, 1),
(775, 9, 7, '3', 'A', 0, 0),
(776, 9, 7, '4', 'A', 1, 0),
(777, 9, 7, '5', 'A', 1, 0),
(778, 9, 7, '6', 'A', 0, 0),
(779, 9, 7, '7', 'A', 0, 0),
(780, 9, 7, '8', 'A', 1, 0),
(781, 9, 7, '9', 'A', 1, 0),
(782, 9, 7, '10', 'A', 0, 0),
(783, 9, 7, '11', 'A', 0, 0),
(784, 9, 7, '12', 'A', 0, 0),
(785, 9, 7, '1', 'B', 0, 0),
(786, 9, 7, '2', 'B', 0, 0),
(787, 9, 7, '3', 'B', 0, 0),
(788, 9, 7, '4', 'B', 1, 0),
(789, 9, 7, '5', 'B', 1, 0),
(790, 9, 7, '6', 'B', 0, 0),
(791, 9, 7, '7', 'B', 0, 0),
(792, 9, 7, '8', 'B', 1, 0),
(793, 9, 7, '9', 'B', 1, 0),
(794, 9, 7, '10', 'B', 0, 0),
(795, 9, 7, '11', 'B', 0, 0),
(796, 9, 7, '12', 'B', 0, 0),
(797, 9, 7, '1', 'C', 0, 0),
(798, 9, 7, '2', 'C', 0, 0),
(799, 9, 7, '3', 'C', 0, 0),
(800, 9, 7, '4', 'C', 1, 0),
(801, 9, 7, '5', 'C', 1, 0),
(802, 9, 7, '6', 'C', 0, 0),
(803, 9, 7, '7', 'C', 0, 0),
(804, 9, 7, '8', 'C', 1, 0),
(805, 9, 7, '9', 'C', 1, 0),
(806, 9, 7, '10', 'C', 0, 0),
(807, 9, 7, '11', 'C', 0, 0),
(808, 9, 7, '12', 'C', 0, 0),
(809, 9, 7, '1', 'D', 0, 0),
(810, 9, 7, '2', 'D', 0, 0),
(811, 9, 7, '3', 'D', 0, 0),
(812, 9, 7, '4', 'D', 1, 0),
(813, 9, 7, '5', 'D', 1, 0),
(814, 9, 7, '6', 'D', 0, 0),
(815, 9, 7, '7', 'D', 0, 0),
(816, 9, 7, '8', 'D', 1, 0),
(817, 9, 7, '9', 'D', 1, 0),
(818, 9, 7, '10', 'D', 0, 0),
(819, 9, 7, '11', 'D', 0, 0),
(820, 9, 7, '12', 'D', 0, 0),
(821, 9, 7, '1', 'E', 0, 0),
(822, 9, 7, '2', 'E', 0, 0),
(823, 9, 7, '3', 'E', 0, 0),
(824, 9, 7, '4', 'E', 1, 0),
(825, 9, 7, '5', 'E', 1, 0),
(826, 9, 7, '6', 'E', 0, 0),
(827, 9, 7, '7', 'E', 0, 0),
(828, 9, 7, '8', 'E', 1, 0),
(829, 9, 7, '9', 'E', 1, 0),
(830, 9, 7, '10', 'E', 0, 0),
(831, 9, 7, '11', 'E', 0, 0),
(832, 9, 7, '12', 'E', 0, 0),
(833, 9, 7, '1', 'F', 0, 0),
(834, 9, 7, '2', 'F', 0, 0),
(835, 9, 7, '3', 'F', 0, 0),
(836, 9, 7, '4', 'F', 1, 0),
(837, 9, 7, '5', 'F', 1, 0),
(838, 9, 7, '6', 'F', 0, 0),
(839, 9, 7, '7', 'F', 0, 0),
(840, 9, 7, '8', 'F', 1, 0),
(841, 9, 7, '9', 'F', 1, 0),
(842, 9, 7, '10', 'F', 0, 0),
(843, 9, 7, '11', 'F', 0, 0),
(844, 9, 7, '12', 'F', 0, 0),
(845, 9, 7, '1', 'G', 0, 0),
(846, 9, 7, '2', 'G', 0, 0),
(847, 9, 7, '3', 'G', 0, 0),
(848, 9, 7, '4', 'G', 1, 0),
(849, 9, 7, '5', 'G', 1, 0),
(850, 9, 7, '6', 'G', 0, 0),
(851, 9, 7, '7', 'G', 0, 0),
(852, 9, 7, '8', 'G', 1, 0),
(853, 9, 7, '9', 'G', 1, 0),
(854, 9, 7, '10', 'G', 0, 0),
(855, 9, 7, '11', 'G', 0, 0),
(856, 9, 7, '12', 'G', 0, 0),
(857, 9, 7, '1', 'H', 0, 0),
(858, 9, 7, '2', 'H', 0, 0),
(859, 9, 7, '3', 'H', 0, 0),
(860, 9, 7, '4', 'H', 1, 0),
(861, 9, 7, '5', 'H', 1, 0),
(862, 9, 7, '6', 'H', 0, 0),
(863, 9, 7, '7', 'H', 0, 0),
(864, 9, 7, '8', 'H', 1, 0),
(865, 9, 7, '9', 'H', 1, 0),
(866, 9, 7, '10', 'H', 0, 0),
(867, 9, 7, '11', 'H', 0, 0),
(868, 9, 7, '12', 'H', 0, 0),
(869, 11, 6, '1', 'A', 0, 1),
(870, 11, 6, '2', 'A', 0, 1),
(871, 11, 6, '3', 'A', 0, 0),
(872, 11, 6, '4', 'A', 1, 0),
(873, 11, 6, '5', 'A', 1, 0),
(874, 11, 6, '6', 'A', 0, 0),
(875, 11, 6, '7', 'A', 0, 0),
(876, 11, 6, '8', 'A', 1, 0),
(877, 11, 6, '9', 'A', 1, 0),
(878, 11, 6, '10', 'A', 0, 0),
(879, 11, 6, '11', 'A', 0, 0),
(880, 11, 6, '12', 'A', 0, 0),
(881, 11, 6, '1', 'B', 0, 0),
(882, 11, 6, '2', 'B', 0, 0),
(883, 11, 6, '3', 'B', 0, 0),
(884, 11, 6, '4', 'B', 1, 0),
(885, 11, 6, '5', 'B', 1, 0),
(886, 11, 6, '6', 'B', 0, 0),
(887, 11, 6, '7', 'B', 0, 0),
(888, 11, 6, '8', 'B', 1, 0),
(889, 11, 6, '9', 'B', 1, 0),
(890, 11, 6, '10', 'B', 0, 0),
(891, 11, 6, '11', 'B', 0, 0),
(892, 11, 6, '12', 'B', 0, 0),
(893, 11, 6, '1', 'C', 0, 0),
(894, 11, 6, '2', 'C', 0, 0),
(895, 11, 6, '3', 'C', 0, 0),
(896, 11, 6, '4', 'C', 1, 0),
(897, 11, 6, '5', 'C', 1, 0),
(898, 11, 6, '6', 'C', 0, 0),
(899, 11, 6, '7', 'C', 0, 0),
(900, 11, 6, '8', 'C', 1, 0),
(901, 11, 6, '9', 'C', 1, 0),
(902, 11, 6, '10', 'C', 0, 0),
(903, 11, 6, '11', 'C', 0, 0),
(904, 11, 6, '12', 'C', 0, 0),
(905, 11, 6, '1', 'D', 0, 0),
(906, 11, 6, '2', 'D', 0, 0),
(907, 11, 6, '3', 'D', 0, 0),
(908, 11, 6, '4', 'D', 1, 0),
(909, 11, 6, '5', 'D', 1, 0),
(910, 11, 6, '6', 'D', 0, 0),
(911, 11, 6, '7', 'D', 0, 0),
(912, 11, 6, '8', 'D', 1, 0),
(913, 11, 6, '9', 'D', 1, 0),
(914, 11, 6, '10', 'D', 0, 0),
(915, 11, 6, '11', 'D', 0, 0),
(916, 11, 6, '12', 'D', 0, 0),
(917, 11, 6, '1', 'E', 0, 0),
(918, 11, 6, '2', 'E', 0, 0),
(919, 11, 6, '3', 'E', 0, 0),
(920, 11, 6, '4', 'E', 1, 0),
(921, 11, 6, '5', 'E', 1, 0),
(922, 11, 6, '6', 'E', 0, 0),
(923, 11, 6, '7', 'E', 0, 0),
(924, 11, 6, '8', 'E', 1, 0),
(925, 11, 6, '9', 'E', 1, 0),
(926, 11, 6, '10', 'E', 0, 0),
(927, 11, 6, '11', 'E', 0, 0),
(928, 11, 6, '12', 'E', 0, 0),
(929, 11, 6, '1', 'F', 0, 0),
(930, 11, 6, '2', 'F', 0, 0),
(931, 11, 6, '3', 'F', 0, 0),
(932, 11, 6, '4', 'F', 1, 0),
(933, 11, 6, '5', 'F', 1, 0),
(934, 11, 6, '6', 'F', 0, 0),
(935, 11, 6, '7', 'F', 0, 0),
(936, 11, 6, '8', 'F', 1, 0),
(937, 11, 6, '9', 'F', 1, 0),
(938, 11, 6, '10', 'F', 0, 0),
(939, 11, 6, '11', 'F', 0, 0),
(940, 11, 6, '12', 'F', 0, 0),
(941, 11, 6, '1', 'G', 0, 0),
(942, 11, 6, '2', 'G', 0, 0),
(943, 11, 6, '3', 'G', 0, 0),
(944, 11, 6, '4', 'G', 1, 0),
(945, 11, 6, '5', 'G', 1, 0),
(946, 11, 6, '6', 'G', 0, 0),
(947, 11, 6, '7', 'G', 0, 0),
(948, 11, 6, '8', 'G', 1, 0),
(949, 11, 6, '9', 'G', 1, 0),
(950, 11, 6, '10', 'G', 0, 0),
(951, 11, 6, '11', 'G', 0, 0),
(952, 11, 6, '12', 'G', 0, 0),
(953, 11, 6, '1', 'H', 0, 0),
(954, 11, 6, '2', 'H', 0, 0),
(955, 11, 6, '3', 'H', 0, 0),
(956, 11, 6, '4', 'H', 1, 0),
(957, 11, 6, '5', 'H', 1, 0),
(958, 11, 6, '6', 'H', 0, 0),
(959, 11, 6, '7', 'H', 0, 0),
(960, 11, 6, '8', 'H', 1, 0),
(961, 11, 6, '9', 'H', 1, 0),
(962, 11, 6, '10', 'H', 0, 0),
(963, 11, 6, '11', 'H', 0, 0),
(964, 11, 6, '12', 'H', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `seat_categories`
--

CREATE TABLE `seat_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seat_categories`
--

INSERT INTO `seat_categories` (`category_id`, `name`, `price_multiplier`) VALUES
(6, 'VIP', 5.00),
(7, 'Standard', 3.00);

-- --------------------------------------------------------

--
-- Table structure for table `showtimes`
--

CREATE TABLE `showtimes` (
  `show_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `screen_format` varchar(50) DEFAULT '2D',
  `subtitle_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `showtimes`
--

INSERT INTO `showtimes` (`show_id`, `movie_id`, `room_id`, `start_at`, `end_at`, `base_price`, `screen_format`, `subtitle_type`, `status`, `created_at`, `updated_at`) VALUES
(8, 10, 5, '2026-03-30 00:53:00', '2026-03-30 03:14:00', 70000.00, '2D', NULL, 'scheduled', '2026-03-30 09:54:29', '2026-03-30 10:24:00'),
(9, 9, 9, '2026-04-30 02:06:00', '2026-05-30 04:58:00', 10000.00, '2D', NULL, 'scheduled', '2026-03-30 10:07:25', '2026-03-31 07:28:04'),
(10, 8, 7, '2026-03-30 10:07:00', '2026-05-23 12:20:00', 85000.00, '2D', NULL, 'scheduled', '2026-03-30 10:08:42', '2026-03-30 10:08:42'),
(12, 8, 6, '2026-05-20 10:37:00', '2026-10-30 12:50:00', 10000.00, '2D', NULL, 'scheduled', '2026-03-30 10:10:30', '2026-03-31 07:20:42'),
(13, 10, 11, '2026-03-30 10:44:00', '2026-03-30 13:05:00', 76000.00, '2D', NULL, 'scheduled', '2026-03-30 10:11:24', '2026-03-30 10:23:25'),
(14, 10, 5, '2026-04-16 07:17:00', '2026-07-04 09:38:00', 70000.00, '2D', NULL, 'scheduled', '2026-03-31 07:18:03', '2026-03-31 07:18:03'),
(15, 10, 8, '2026-03-31 07:36:00', '2026-04-30 09:57:00', 100000.00, '2D', NULL, 'scheduled', '2026-03-31 07:37:19', '2026-03-31 07:37:19'),
(17, 9, 11, '2026-03-31 08:06:00', '2026-03-31 11:43:00', 80000.00, '2D', NULL, 'scheduled', '2026-03-31 08:07:06', '2026-03-31 08:07:06'),
(18, 9, 9, '2026-03-31 08:07:00', '2026-03-31 19:44:00', 100000.00, '2D', NULL, 'scheduled', '2026-03-31 08:08:13', '2026-03-31 08:08:13');

-- --------------------------------------------------------

--
-- Table structure for table `show_seats`
--

CREATE TABLE `show_seats` (
  `show_seat_id` int(11) NOT NULL,
  `show_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `hold_until` datetime DEFAULT NULL,
  `hold_token` varchar(100) DEFAULT NULL,
  `final_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `show_seats`
--

INSERT INTO `show_seats` (`show_seat_id`, `show_id`, `seat_id`, `status`, `hold_until`, `hold_token`, `final_price`) VALUES
(575, 8, 293, 'available', NULL, NULL, 210000.00),
(576, 8, 294, 'available', NULL, NULL, 210000.00),
(577, 8, 295, 'available', NULL, NULL, 210000.00),
(578, 8, 296, 'available', NULL, NULL, 210000.00),
(579, 8, 297, 'available', NULL, NULL, 210000.00),
(580, 8, 298, 'available', NULL, NULL, 210000.00),
(581, 8, 299, 'available', NULL, NULL, 210000.00),
(582, 8, 300, 'available', NULL, NULL, 210000.00),
(583, 8, 301, 'available', NULL, NULL, 210000.00),
(584, 8, 302, 'available', NULL, NULL, 210000.00),
(585, 8, 303, 'available', NULL, NULL, 210000.00),
(586, 8, 304, 'available', NULL, NULL, 210000.00),
(587, 8, 305, 'available', NULL, NULL, 210000.00),
(588, 8, 306, 'available', NULL, NULL, 210000.00),
(589, 8, 307, 'available', NULL, NULL, 210000.00),
(590, 8, 308, 'booked', NULL, NULL, 210000.00),
(591, 8, 309, 'available', NULL, NULL, 210000.00),
(592, 8, 310, 'available', NULL, NULL, 210000.00),
(593, 8, 311, 'available', NULL, NULL, 210000.00),
(594, 8, 312, 'available', NULL, NULL, 210000.00),
(595, 8, 313, 'available', NULL, NULL, 210000.00),
(596, 8, 314, 'available', NULL, NULL, 210000.00),
(597, 8, 315, 'available', NULL, NULL, 210000.00),
(598, 8, 316, 'available', NULL, NULL, 210000.00),
(599, 8, 317, 'available', NULL, NULL, 210000.00),
(600, 8, 318, 'available', NULL, NULL, 210000.00),
(601, 8, 319, 'available', NULL, NULL, 210000.00),
(602, 8, 320, 'available', NULL, NULL, 210000.00),
(603, 8, 321, 'available', NULL, NULL, 210000.00),
(604, 8, 322, 'available', NULL, NULL, 210000.00),
(605, 8, 323, 'available', NULL, NULL, 210000.00),
(606, 8, 324, 'available', NULL, NULL, 210000.00),
(607, 8, 325, 'available', NULL, NULL, 210000.00),
(608, 8, 326, 'available', NULL, NULL, 210000.00),
(609, 8, 327, 'available', NULL, NULL, 210000.00),
(610, 8, 328, 'available', NULL, NULL, 210000.00),
(611, 8, 329, 'available', NULL, NULL, 210000.00),
(612, 8, 330, 'available', NULL, NULL, 210000.00),
(613, 8, 331, 'available', NULL, NULL, 210000.00),
(614, 8, 332, 'available', NULL, NULL, 210000.00),
(615, 8, 333, 'available', NULL, NULL, 210000.00),
(616, 8, 334, 'available', NULL, NULL, 210000.00),
(617, 8, 335, 'available', NULL, NULL, 210000.00),
(618, 8, 336, 'available', NULL, NULL, 210000.00),
(619, 8, 337, 'available', NULL, NULL, 210000.00),
(620, 8, 338, 'available', NULL, NULL, 210000.00),
(621, 8, 339, 'available', NULL, NULL, 210000.00),
(622, 8, 340, 'available', NULL, NULL, 210000.00),
(623, 8, 341, 'available', NULL, NULL, 210000.00),
(624, 8, 342, 'available', NULL, NULL, 210000.00),
(625, 8, 343, 'available', NULL, NULL, 210000.00),
(626, 8, 344, 'available', NULL, NULL, 210000.00),
(627, 8, 345, 'available', NULL, NULL, 210000.00),
(628, 8, 346, 'available', NULL, NULL, 210000.00),
(629, 8, 347, 'available', NULL, NULL, 210000.00),
(630, 8, 348, 'available', NULL, NULL, 210000.00),
(631, 8, 349, 'available', NULL, NULL, 210000.00),
(632, 8, 350, 'available', NULL, NULL, 210000.00),
(633, 8, 351, 'available', NULL, NULL, 210000.00),
(634, 8, 352, 'available', NULL, NULL, 210000.00),
(635, 8, 353, 'available', NULL, NULL, 210000.00),
(636, 8, 354, 'available', NULL, NULL, 210000.00),
(637, 8, 355, 'available', NULL, NULL, 210000.00),
(638, 8, 356, 'available', NULL, NULL, 210000.00),
(639, 8, 357, 'available', NULL, NULL, 210000.00),
(640, 8, 358, 'available', NULL, NULL, 210000.00),
(641, 8, 359, 'available', NULL, NULL, 210000.00),
(642, 8, 360, 'available', NULL, NULL, 210000.00),
(643, 8, 361, 'available', NULL, NULL, 210000.00),
(644, 8, 362, 'available', NULL, NULL, 210000.00),
(645, 8, 363, 'available', NULL, NULL, 210000.00),
(646, 8, 364, 'available', NULL, NULL, 210000.00),
(647, 8, 365, 'available', NULL, NULL, 210000.00),
(648, 8, 366, 'available', NULL, NULL, 210000.00),
(649, 8, 367, 'available', NULL, NULL, 210000.00),
(650, 8, 368, 'available', NULL, NULL, 210000.00),
(651, 8, 369, 'available', NULL, NULL, 210000.00),
(652, 8, 370, 'available', NULL, NULL, 210000.00),
(653, 8, 371, 'available', NULL, NULL, 210000.00),
(654, 8, 372, 'available', NULL, NULL, 210000.00),
(655, 8, 373, 'available', NULL, NULL, 210000.00),
(656, 8, 374, 'available', NULL, NULL, 210000.00),
(657, 8, 375, 'available', NULL, NULL, 210000.00),
(658, 8, 376, 'available', NULL, NULL, 210000.00),
(659, 8, 377, 'available', NULL, NULL, 210000.00),
(660, 8, 378, 'available', NULL, NULL, 210000.00),
(661, 8, 379, 'available', NULL, NULL, 210000.00),
(662, 8, 380, 'available', NULL, NULL, 210000.00),
(663, 8, 381, 'available', NULL, NULL, 210000.00),
(664, 8, 382, 'available', NULL, NULL, 210000.00),
(665, 8, 383, 'available', NULL, NULL, 210000.00),
(666, 8, 384, 'available', NULL, NULL, 210000.00),
(667, 8, 385, 'available', NULL, NULL, 210000.00),
(668, 8, 386, 'available', NULL, NULL, 210000.00),
(669, 8, 387, 'available', NULL, NULL, 210000.00),
(670, 8, 388, 'available', NULL, NULL, 210000.00),
(829, 10, 545, 'available', NULL, NULL, 425000.00),
(830, 10, 546, 'available', NULL, NULL, 425000.00),
(831, 10, 547, 'available', NULL, NULL, 425000.00),
(832, 10, 548, 'available', NULL, NULL, 425000.00),
(833, 10, 549, 'available', NULL, NULL, 425000.00),
(834, 10, 550, 'available', NULL, NULL, 425000.00),
(835, 10, 551, 'available', NULL, NULL, 425000.00),
(836, 10, 552, 'available', NULL, NULL, 425000.00),
(837, 10, 553, 'available', NULL, NULL, 425000.00),
(838, 10, 554, 'available', NULL, NULL, 425000.00),
(839, 10, 555, 'available', NULL, NULL, 425000.00),
(840, 10, 556, 'available', NULL, NULL, 425000.00),
(841, 10, 557, 'available', NULL, NULL, 425000.00),
(842, 10, 558, 'available', NULL, NULL, 425000.00),
(843, 10, 559, 'available', NULL, NULL, 425000.00),
(844, 10, 560, 'available', NULL, NULL, 425000.00),
(845, 10, 561, 'available', NULL, NULL, 425000.00),
(846, 10, 562, 'available', NULL, NULL, 425000.00),
(847, 10, 563, 'available', NULL, NULL, 425000.00),
(848, 10, 564, 'available', NULL, NULL, 425000.00),
(849, 10, 565, 'available', NULL, NULL, 425000.00),
(850, 10, 566, 'available', NULL, NULL, 425000.00),
(851, 10, 567, 'available', NULL, NULL, 425000.00),
(852, 10, 568, 'available', NULL, NULL, 425000.00),
(853, 10, 569, 'available', NULL, NULL, 425000.00),
(854, 10, 570, 'available', NULL, NULL, 425000.00),
(855, 10, 571, 'available', NULL, NULL, 425000.00),
(856, 10, 572, 'available', NULL, NULL, 425000.00),
(857, 10, 573, 'available', NULL, NULL, 425000.00),
(858, 10, 574, 'available', NULL, NULL, 425000.00),
(859, 10, 575, 'available', NULL, NULL, 425000.00),
(860, 10, 576, 'available', NULL, NULL, 425000.00),
(861, 10, 577, 'available', NULL, NULL, 425000.00),
(862, 10, 578, 'available', NULL, NULL, 425000.00),
(863, 10, 579, 'available', NULL, NULL, 425000.00),
(864, 10, 580, 'available', NULL, NULL, 425000.00),
(865, 10, 485, 'available', NULL, NULL, 255000.00),
(866, 10, 486, 'available', NULL, NULL, 255000.00),
(867, 10, 487, 'available', NULL, NULL, 255000.00),
(868, 10, 488, 'available', NULL, NULL, 255000.00),
(869, 10, 489, 'available', NULL, NULL, 255000.00),
(870, 10, 490, 'available', NULL, NULL, 255000.00),
(871, 10, 491, 'available', NULL, NULL, 255000.00),
(872, 10, 492, 'available', NULL, NULL, 255000.00),
(873, 10, 493, 'available', NULL, NULL, 255000.00),
(874, 10, 494, 'available', NULL, NULL, 255000.00),
(875, 10, 495, 'available', NULL, NULL, 255000.00),
(876, 10, 496, 'available', NULL, NULL, 255000.00),
(877, 10, 497, 'available', NULL, NULL, 255000.00),
(878, 10, 498, 'available', NULL, NULL, 255000.00),
(879, 10, 499, 'available', NULL, NULL, 255000.00),
(880, 10, 500, 'available', NULL, NULL, 255000.00),
(881, 10, 501, 'available', NULL, NULL, 255000.00),
(882, 10, 502, 'available', NULL, NULL, 255000.00),
(883, 10, 503, 'available', NULL, NULL, 255000.00),
(884, 10, 504, 'available', NULL, NULL, 255000.00),
(885, 10, 505, 'available', NULL, NULL, 255000.00),
(886, 10, 506, 'available', NULL, NULL, 255000.00),
(887, 10, 507, 'available', NULL, NULL, 255000.00),
(888, 10, 508, 'available', NULL, NULL, 255000.00),
(889, 10, 509, 'available', NULL, NULL, 255000.00),
(890, 10, 510, 'available', NULL, NULL, 255000.00),
(891, 10, 511, 'available', NULL, NULL, 255000.00),
(892, 10, 512, 'available', NULL, NULL, 255000.00),
(893, 10, 513, 'available', NULL, NULL, 255000.00),
(894, 10, 514, 'available', NULL, NULL, 255000.00),
(895, 10, 515, 'available', NULL, NULL, 255000.00),
(896, 10, 516, 'available', NULL, NULL, 255000.00),
(897, 10, 517, 'available', NULL, NULL, 255000.00),
(898, 10, 518, 'available', NULL, NULL, 255000.00),
(899, 10, 519, 'available', NULL, NULL, 255000.00),
(900, 10, 520, 'available', NULL, NULL, 255000.00),
(901, 10, 521, 'available', NULL, NULL, 255000.00),
(902, 10, 522, 'available', NULL, NULL, 255000.00),
(903, 10, 523, 'available', NULL, NULL, 255000.00),
(904, 10, 524, 'available', NULL, NULL, 255000.00),
(905, 10, 525, 'available', NULL, NULL, 255000.00),
(906, 10, 526, 'available', NULL, NULL, 255000.00),
(907, 10, 527, 'available', NULL, NULL, 255000.00),
(908, 10, 528, 'available', NULL, NULL, 255000.00),
(909, 10, 529, 'available', NULL, NULL, 255000.00),
(910, 10, 530, 'available', NULL, NULL, 255000.00),
(911, 10, 531, 'available', NULL, NULL, 255000.00),
(912, 10, 532, 'available', NULL, NULL, 255000.00),
(913, 10, 533, 'available', NULL, NULL, 255000.00),
(914, 10, 534, 'available', NULL, NULL, 255000.00),
(915, 10, 535, 'available', NULL, NULL, 255000.00),
(916, 10, 536, 'available', NULL, NULL, 255000.00),
(917, 10, 537, 'available', NULL, NULL, 255000.00),
(918, 10, 538, 'available', NULL, NULL, 255000.00),
(919, 10, 539, 'available', NULL, NULL, 255000.00),
(920, 10, 540, 'available', NULL, NULL, 255000.00),
(921, 10, 541, 'available', NULL, NULL, 255000.00),
(922, 10, 542, 'available', NULL, NULL, 255000.00),
(923, 10, 543, 'available', NULL, NULL, 255000.00),
(924, 10, 544, 'available', NULL, NULL, 255000.00),
(1210, 13, 869, 'available', NULL, NULL, 380000.00),
(1211, 13, 870, 'available', NULL, NULL, 380000.00),
(1212, 13, 871, 'available', NULL, NULL, 380000.00),
(1213, 13, 872, 'available', NULL, NULL, 380000.00),
(1214, 13, 873, 'available', NULL, NULL, 380000.00),
(1215, 13, 874, 'available', NULL, NULL, 380000.00),
(1216, 13, 875, 'available', NULL, NULL, 380000.00),
(1217, 13, 876, 'available', NULL, NULL, 380000.00),
(1218, 13, 877, 'available', NULL, NULL, 380000.00),
(1219, 13, 878, 'available', NULL, NULL, 380000.00),
(1220, 13, 879, 'available', NULL, NULL, 380000.00),
(1221, 13, 880, 'available', NULL, NULL, 380000.00),
(1222, 13, 881, 'available', NULL, NULL, 380000.00),
(1223, 13, 882, 'available', NULL, NULL, 380000.00),
(1224, 13, 883, 'available', NULL, NULL, 380000.00),
(1225, 13, 884, 'available', NULL, NULL, 380000.00),
(1226, 13, 885, 'available', NULL, NULL, 380000.00),
(1227, 13, 886, 'available', NULL, NULL, 380000.00),
(1228, 13, 887, 'available', NULL, NULL, 380000.00),
(1229, 13, 888, 'available', NULL, NULL, 380000.00),
(1230, 13, 889, 'available', NULL, NULL, 380000.00),
(1231, 13, 890, 'available', NULL, NULL, 380000.00),
(1232, 13, 891, 'available', NULL, NULL, 380000.00),
(1233, 13, 892, 'available', NULL, NULL, 380000.00),
(1234, 13, 893, 'available', NULL, NULL, 380000.00),
(1235, 13, 894, 'available', NULL, NULL, 380000.00),
(1236, 13, 895, 'available', NULL, NULL, 380000.00),
(1237, 13, 896, 'available', NULL, NULL, 380000.00),
(1238, 13, 897, 'available', NULL, NULL, 380000.00),
(1239, 13, 898, 'available', NULL, NULL, 380000.00),
(1240, 13, 899, 'available', NULL, NULL, 380000.00),
(1241, 13, 900, 'available', NULL, NULL, 380000.00),
(1242, 13, 901, 'available', NULL, NULL, 380000.00),
(1243, 13, 902, 'available', NULL, NULL, 380000.00),
(1244, 13, 903, 'available', NULL, NULL, 380000.00),
(1245, 13, 904, 'available', NULL, NULL, 380000.00),
(1246, 13, 905, 'available', NULL, NULL, 380000.00),
(1247, 13, 906, 'available', NULL, NULL, 380000.00),
(1248, 13, 907, 'available', NULL, NULL, 380000.00),
(1249, 13, 908, 'available', NULL, NULL, 380000.00),
(1250, 13, 909, 'available', NULL, NULL, 380000.00),
(1251, 13, 910, 'available', NULL, NULL, 380000.00),
(1252, 13, 911, 'available', NULL, NULL, 380000.00),
(1253, 13, 912, 'available', NULL, NULL, 380000.00),
(1254, 13, 913, 'available', NULL, NULL, 380000.00),
(1255, 13, 914, 'available', NULL, NULL, 380000.00),
(1256, 13, 915, 'available', NULL, NULL, 380000.00),
(1257, 13, 916, 'available', NULL, NULL, 380000.00),
(1258, 13, 917, 'available', NULL, NULL, 380000.00),
(1259, 13, 918, 'available', NULL, NULL, 380000.00),
(1260, 13, 919, 'available', NULL, NULL, 380000.00),
(1261, 13, 920, 'available', NULL, NULL, 380000.00),
(1262, 13, 921, 'available', NULL, NULL, 380000.00),
(1263, 13, 922, 'available', NULL, NULL, 380000.00),
(1264, 13, 923, 'available', NULL, NULL, 380000.00),
(1265, 13, 924, 'available', NULL, NULL, 380000.00),
(1266, 13, 925, 'available', NULL, NULL, 380000.00),
(1267, 13, 926, 'available', NULL, NULL, 380000.00),
(1268, 13, 927, 'available', NULL, NULL, 380000.00),
(1269, 13, 928, 'available', NULL, NULL, 380000.00),
(1270, 13, 929, 'available', NULL, NULL, 380000.00),
(1271, 13, 930, 'available', NULL, NULL, 380000.00),
(1272, 13, 931, 'available', NULL, NULL, 380000.00),
(1273, 13, 932, 'available', NULL, NULL, 380000.00),
(1274, 13, 933, 'available', NULL, NULL, 380000.00),
(1275, 13, 934, 'available', NULL, NULL, 380000.00),
(1276, 13, 935, 'available', NULL, NULL, 380000.00),
(1277, 13, 936, 'available', NULL, NULL, 380000.00),
(1278, 13, 937, 'available', NULL, NULL, 380000.00),
(1279, 13, 938, 'available', NULL, NULL, 380000.00),
(1280, 13, 939, 'available', NULL, NULL, 380000.00),
(1281, 13, 940, 'available', NULL, NULL, 380000.00),
(1282, 13, 941, 'available', NULL, NULL, 380000.00),
(1283, 13, 942, 'available', NULL, NULL, 380000.00),
(1284, 13, 943, 'available', NULL, NULL, 380000.00),
(1285, 13, 944, 'available', NULL, NULL, 380000.00),
(1286, 13, 945, 'available', NULL, NULL, 380000.00),
(1287, 13, 946, 'available', NULL, NULL, 380000.00),
(1288, 13, 947, 'available', NULL, NULL, 380000.00),
(1289, 13, 948, 'available', NULL, NULL, 380000.00),
(1290, 13, 949, 'available', NULL, NULL, 380000.00),
(1291, 13, 950, 'available', NULL, NULL, 380000.00),
(1292, 13, 951, 'available', NULL, NULL, 380000.00),
(1293, 13, 952, 'available', NULL, NULL, 380000.00),
(1294, 13, 953, 'available', NULL, NULL, 380000.00),
(1295, 13, 954, 'available', NULL, NULL, 380000.00),
(1296, 13, 955, 'available', NULL, NULL, 380000.00),
(1297, 13, 956, 'available', NULL, NULL, 380000.00),
(1298, 13, 957, 'available', NULL, NULL, 380000.00),
(1299, 13, 958, 'available', NULL, NULL, 380000.00),
(1300, 13, 959, 'available', NULL, NULL, 380000.00),
(1301, 13, 960, 'available', NULL, NULL, 380000.00),
(1302, 13, 961, 'available', NULL, NULL, 380000.00),
(1303, 13, 962, 'available', NULL, NULL, 380000.00),
(1304, 13, 963, 'available', NULL, NULL, 380000.00),
(1305, 13, 964, 'available', NULL, NULL, 380000.00),
(1306, 14, 293, 'available', NULL, NULL, 210000.00),
(1307, 14, 302, 'available', NULL, NULL, 210000.00),
(1308, 14, 303, 'available', NULL, NULL, 210000.00),
(1309, 14, 304, 'available', NULL, NULL, 210000.00),
(1310, 14, 294, 'available', NULL, NULL, 210000.00),
(1311, 14, 295, 'available', NULL, NULL, 210000.00),
(1312, 14, 296, 'available', NULL, NULL, 210000.00),
(1313, 14, 297, 'available', NULL, NULL, 210000.00),
(1314, 14, 298, 'available', NULL, NULL, 210000.00),
(1315, 14, 299, 'available', NULL, NULL, 210000.00),
(1316, 14, 300, 'available', NULL, NULL, 210000.00),
(1317, 14, 301, 'available', NULL, NULL, 210000.00),
(1318, 14, 305, 'available', NULL, NULL, 210000.00),
(1319, 14, 314, 'available', NULL, NULL, 210000.00),
(1320, 14, 315, 'available', NULL, NULL, 210000.00),
(1321, 14, 316, 'available', NULL, NULL, 210000.00),
(1322, 14, 306, 'available', NULL, NULL, 210000.00),
(1323, 14, 307, 'available', NULL, NULL, 210000.00),
(1324, 14, 308, 'available', NULL, NULL, 210000.00),
(1325, 14, 309, 'available', NULL, NULL, 210000.00),
(1326, 14, 310, 'available', NULL, NULL, 210000.00),
(1327, 14, 311, 'available', NULL, NULL, 210000.00),
(1328, 14, 312, 'available', NULL, NULL, 210000.00),
(1329, 14, 313, 'available', NULL, NULL, 210000.00),
(1330, 14, 317, 'available', NULL, NULL, 210000.00),
(1331, 14, 326, 'available', NULL, NULL, 210000.00),
(1332, 14, 327, 'available', NULL, NULL, 210000.00),
(1333, 14, 328, 'available', NULL, NULL, 210000.00),
(1334, 14, 318, 'available', NULL, NULL, 210000.00),
(1335, 14, 319, 'available', NULL, NULL, 210000.00),
(1336, 14, 320, 'available', NULL, NULL, 210000.00),
(1337, 14, 321, 'available', NULL, NULL, 210000.00),
(1338, 14, 322, 'available', NULL, NULL, 210000.00),
(1339, 14, 323, 'available', NULL, NULL, 210000.00),
(1340, 14, 324, 'available', NULL, NULL, 210000.00),
(1341, 14, 325, 'available', NULL, NULL, 210000.00),
(1342, 14, 329, 'available', NULL, NULL, 210000.00),
(1343, 14, 338, 'available', NULL, NULL, 210000.00),
(1344, 14, 339, 'available', NULL, NULL, 210000.00),
(1345, 14, 340, 'available', NULL, NULL, 210000.00),
(1346, 14, 330, 'available', NULL, NULL, 210000.00),
(1347, 14, 331, 'available', NULL, NULL, 210000.00),
(1348, 14, 332, 'available', NULL, NULL, 210000.00),
(1349, 14, 333, 'available', NULL, NULL, 210000.00),
(1350, 14, 334, 'available', NULL, NULL, 210000.00),
(1351, 14, 335, 'available', NULL, NULL, 210000.00),
(1352, 14, 336, 'available', NULL, NULL, 210000.00),
(1353, 14, 337, 'available', NULL, NULL, 210000.00),
(1354, 14, 341, 'available', NULL, NULL, 210000.00),
(1355, 14, 350, 'available', NULL, NULL, 210000.00),
(1356, 14, 351, 'available', NULL, NULL, 210000.00),
(1357, 14, 352, 'available', NULL, NULL, 210000.00),
(1358, 14, 342, 'available', NULL, NULL, 210000.00),
(1359, 14, 343, 'available', NULL, NULL, 210000.00),
(1360, 14, 344, 'available', NULL, NULL, 210000.00),
(1361, 14, 345, 'available', NULL, NULL, 210000.00),
(1362, 14, 346, 'available', NULL, NULL, 210000.00),
(1363, 14, 347, 'available', NULL, NULL, 210000.00),
(1364, 14, 348, 'available', NULL, NULL, 210000.00),
(1365, 14, 349, 'available', NULL, NULL, 210000.00),
(1366, 14, 353, 'available', NULL, NULL, 210000.00),
(1367, 14, 362, 'available', NULL, NULL, 210000.00),
(1368, 14, 363, 'available', NULL, NULL, 210000.00),
(1369, 14, 364, 'available', NULL, NULL, 210000.00),
(1370, 14, 354, 'available', NULL, NULL, 210000.00),
(1371, 14, 355, 'available', NULL, NULL, 210000.00),
(1372, 14, 356, 'available', NULL, NULL, 210000.00),
(1373, 14, 357, 'available', NULL, NULL, 210000.00),
(1374, 14, 358, 'available', NULL, NULL, 210000.00),
(1375, 14, 359, 'available', NULL, NULL, 210000.00),
(1376, 14, 360, 'available', NULL, NULL, 210000.00),
(1377, 14, 361, 'available', NULL, NULL, 210000.00),
(1378, 14, 365, 'available', NULL, NULL, 210000.00),
(1379, 14, 374, 'available', NULL, NULL, 210000.00),
(1380, 14, 375, 'available', NULL, NULL, 210000.00),
(1381, 14, 376, 'available', NULL, NULL, 210000.00),
(1382, 14, 366, 'available', NULL, NULL, 210000.00),
(1383, 14, 367, 'available', NULL, NULL, 210000.00),
(1384, 14, 368, 'available', NULL, NULL, 210000.00),
(1385, 14, 369, 'available', NULL, NULL, 210000.00),
(1386, 14, 370, 'available', NULL, NULL, 210000.00),
(1387, 14, 371, 'available', NULL, NULL, 210000.00),
(1388, 14, 372, 'available', NULL, NULL, 210000.00),
(1389, 14, 373, 'available', NULL, NULL, 210000.00),
(1390, 14, 377, 'available', NULL, NULL, 210000.00),
(1391, 14, 386, 'available', NULL, NULL, 210000.00),
(1392, 14, 387, 'available', NULL, NULL, 210000.00),
(1393, 14, 388, 'available', NULL, NULL, 210000.00),
(1394, 14, 378, 'available', NULL, NULL, 210000.00),
(1395, 14, 379, 'available', NULL, NULL, 210000.00),
(1396, 14, 380, 'available', NULL, NULL, 210000.00),
(1397, 14, 381, 'available', NULL, NULL, 210000.00),
(1398, 14, 382, 'available', NULL, NULL, 210000.00),
(1399, 14, 383, 'available', NULL, NULL, 210000.00),
(1400, 14, 384, 'available', NULL, NULL, 210000.00),
(1401, 14, 385, 'available', NULL, NULL, 210000.00),
(1433, 12, 449, 'available', NULL, NULL, 50000.00),
(1434, 12, 458, 'available', NULL, NULL, 50000.00),
(1435, 12, 459, 'available', NULL, NULL, 50000.00),
(1436, 12, 460, 'available', NULL, NULL, 50000.00),
(1437, 12, 450, 'available', NULL, NULL, 50000.00),
(1438, 12, 451, 'available', NULL, NULL, 50000.00),
(1439, 12, 452, 'available', NULL, NULL, 50000.00),
(1440, 12, 453, 'available', NULL, NULL, 50000.00),
(1441, 12, 454, 'available', NULL, NULL, 50000.00),
(1442, 12, 455, 'available', NULL, NULL, 50000.00),
(1443, 12, 456, 'available', NULL, NULL, 50000.00),
(1444, 12, 457, 'available', NULL, NULL, 50000.00),
(1445, 12, 461, 'available', NULL, NULL, 50000.00),
(1446, 12, 470, 'available', NULL, NULL, 50000.00),
(1447, 12, 471, 'available', NULL, NULL, 50000.00),
(1448, 12, 472, 'available', NULL, NULL, 50000.00),
(1449, 12, 462, 'available', NULL, NULL, 50000.00),
(1450, 12, 463, 'available', NULL, NULL, 50000.00),
(1451, 12, 464, 'available', NULL, NULL, 50000.00),
(1452, 12, 465, 'available', NULL, NULL, 50000.00),
(1453, 12, 466, 'available', NULL, NULL, 50000.00),
(1454, 12, 467, 'available', NULL, NULL, 50000.00),
(1455, 12, 468, 'available', NULL, NULL, 50000.00),
(1456, 12, 469, 'available', NULL, NULL, 50000.00),
(1457, 12, 473, 'available', NULL, NULL, 50000.00),
(1458, 12, 482, 'available', NULL, NULL, 50000.00),
(1459, 12, 483, 'available', NULL, NULL, 50000.00),
(1460, 12, 484, 'available', NULL, NULL, 50000.00),
(1461, 12, 474, 'available', NULL, NULL, 50000.00),
(1462, 12, 475, 'available', NULL, NULL, 50000.00),
(1463, 12, 476, 'available', NULL, NULL, 50000.00),
(1464, 12, 477, 'available', NULL, NULL, 50000.00),
(1465, 12, 478, 'available', NULL, NULL, 50000.00),
(1466, 12, 479, 'available', NULL, NULL, 50000.00),
(1467, 12, 480, 'available', NULL, NULL, 50000.00),
(1468, 12, 481, 'available', NULL, NULL, 50000.00),
(1469, 12, 389, 'available', NULL, NULL, 30000.00),
(1470, 12, 398, 'available', NULL, NULL, 30000.00),
(1471, 12, 399, 'available', NULL, NULL, 30000.00),
(1472, 12, 400, 'available', NULL, NULL, 30000.00),
(1473, 12, 390, 'available', NULL, NULL, 30000.00),
(1474, 12, 391, 'available', NULL, NULL, 30000.00),
(1475, 12, 392, 'available', NULL, NULL, 30000.00),
(1476, 12, 393, 'available', NULL, NULL, 30000.00),
(1477, 12, 394, 'available', NULL, NULL, 30000.00),
(1478, 12, 395, 'available', NULL, NULL, 30000.00),
(1479, 12, 396, 'available', NULL, NULL, 30000.00),
(1480, 12, 397, 'available', NULL, NULL, 30000.00),
(1481, 12, 401, 'available', NULL, NULL, 30000.00),
(1482, 12, 410, 'available', NULL, NULL, 30000.00),
(1483, 12, 411, 'available', NULL, NULL, 30000.00),
(1484, 12, 412, 'available', NULL, NULL, 30000.00),
(1485, 12, 402, 'available', NULL, NULL, 30000.00),
(1486, 12, 403, 'available', NULL, NULL, 30000.00),
(1487, 12, 404, 'available', NULL, NULL, 30000.00),
(1488, 12, 405, 'available', NULL, NULL, 30000.00),
(1489, 12, 406, 'available', NULL, NULL, 30000.00),
(1490, 12, 407, 'available', NULL, NULL, 30000.00),
(1491, 12, 408, 'available', NULL, NULL, 30000.00),
(1492, 12, 409, 'available', NULL, NULL, 30000.00),
(1493, 12, 413, 'available', NULL, NULL, 30000.00),
(1494, 12, 422, 'available', NULL, NULL, 30000.00),
(1495, 12, 423, 'available', NULL, NULL, 30000.00),
(1496, 12, 424, 'available', NULL, NULL, 30000.00),
(1497, 12, 414, 'available', NULL, NULL, 30000.00),
(1498, 12, 415, 'available', NULL, NULL, 30000.00),
(1499, 12, 416, 'available', NULL, NULL, 30000.00),
(1500, 12, 417, 'available', NULL, NULL, 30000.00),
(1501, 12, 418, 'available', NULL, NULL, 30000.00),
(1502, 12, 419, 'booked', NULL, NULL, 30000.00),
(1503, 12, 420, 'available', NULL, NULL, 30000.00),
(1504, 12, 421, 'available', NULL, NULL, 30000.00),
(1505, 12, 425, 'available', NULL, NULL, 30000.00),
(1506, 12, 434, 'available', NULL, NULL, 30000.00),
(1507, 12, 435, 'available', NULL, NULL, 30000.00),
(1508, 12, 436, 'available', NULL, NULL, 30000.00),
(1509, 12, 426, 'available', NULL, NULL, 30000.00),
(1510, 12, 427, 'available', NULL, NULL, 30000.00),
(1511, 12, 428, 'available', NULL, NULL, 30000.00),
(1512, 12, 429, 'available', NULL, NULL, 30000.00),
(1513, 12, 430, 'available', NULL, NULL, 30000.00),
(1514, 12, 431, 'available', NULL, NULL, 30000.00),
(1515, 12, 432, 'available', NULL, NULL, 30000.00),
(1516, 12, 433, 'available', NULL, NULL, 30000.00),
(1517, 12, 437, 'available', NULL, NULL, 30000.00),
(1518, 12, 446, 'available', NULL, NULL, 30000.00),
(1519, 12, 447, 'available', NULL, NULL, 30000.00),
(1520, 12, 448, 'available', NULL, NULL, 30000.00),
(1521, 12, 438, 'available', NULL, NULL, 30000.00),
(1522, 12, 439, 'available', NULL, NULL, 30000.00),
(1523, 12, 440, 'available', NULL, NULL, 30000.00),
(1524, 12, 441, 'available', NULL, NULL, 30000.00),
(1525, 12, 442, 'available', NULL, NULL, 30000.00),
(1526, 12, 443, 'available', NULL, NULL, 30000.00),
(1527, 12, 444, 'available', NULL, NULL, 30000.00),
(1528, 12, 445, 'available', NULL, NULL, 30000.00),
(1529, 9, 773, 'available', NULL, NULL, 30000.00),
(1530, 9, 782, 'available', NULL, NULL, 30000.00),
(1531, 9, 783, 'available', NULL, NULL, 30000.00),
(1532, 9, 784, 'available', NULL, NULL, 30000.00),
(1533, 9, 774, 'available', NULL, NULL, 30000.00),
(1534, 9, 775, 'available', NULL, NULL, 30000.00),
(1535, 9, 776, 'available', NULL, NULL, 30000.00),
(1536, 9, 777, 'available', NULL, NULL, 30000.00),
(1537, 9, 778, 'available', NULL, NULL, 30000.00),
(1538, 9, 779, 'available', NULL, NULL, 30000.00),
(1539, 9, 780, 'available', NULL, NULL, 30000.00),
(1540, 9, 781, 'available', NULL, NULL, 30000.00),
(1541, 9, 785, 'available', NULL, NULL, 30000.00),
(1542, 9, 794, 'available', NULL, NULL, 30000.00),
(1543, 9, 795, 'available', NULL, NULL, 30000.00),
(1544, 9, 796, 'available', NULL, NULL, 30000.00),
(1545, 9, 786, 'available', NULL, NULL, 30000.00),
(1546, 9, 787, 'available', NULL, NULL, 30000.00),
(1547, 9, 788, 'available', NULL, NULL, 30000.00),
(1548, 9, 789, 'available', NULL, NULL, 30000.00),
(1549, 9, 790, 'available', NULL, NULL, 30000.00),
(1550, 9, 791, 'available', NULL, NULL, 30000.00),
(1551, 9, 792, 'available', NULL, NULL, 30000.00),
(1552, 9, 793, 'available', NULL, NULL, 30000.00),
(1553, 9, 797, 'available', NULL, NULL, 30000.00),
(1554, 9, 806, 'available', NULL, NULL, 30000.00),
(1555, 9, 807, 'available', NULL, NULL, 30000.00),
(1556, 9, 808, 'available', NULL, NULL, 30000.00),
(1557, 9, 798, 'available', NULL, NULL, 30000.00),
(1558, 9, 799, 'available', NULL, NULL, 30000.00),
(1559, 9, 800, 'available', NULL, NULL, 30000.00),
(1560, 9, 801, 'available', NULL, NULL, 30000.00),
(1561, 9, 802, 'available', NULL, NULL, 30000.00),
(1562, 9, 803, 'available', NULL, NULL, 30000.00),
(1563, 9, 804, 'available', NULL, NULL, 30000.00),
(1564, 9, 805, 'available', NULL, NULL, 30000.00),
(1565, 9, 809, 'available', NULL, NULL, 30000.00),
(1566, 9, 818, 'available', NULL, NULL, 30000.00),
(1567, 9, 819, 'available', NULL, NULL, 30000.00),
(1568, 9, 820, 'available', NULL, NULL, 30000.00),
(1569, 9, 810, 'available', NULL, NULL, 30000.00),
(1570, 9, 811, 'available', NULL, NULL, 30000.00),
(1571, 9, 812, 'available', NULL, NULL, 30000.00),
(1572, 9, 813, 'available', NULL, NULL, 30000.00),
(1573, 9, 814, 'available', NULL, NULL, 30000.00),
(1574, 9, 815, 'available', NULL, NULL, 30000.00),
(1575, 9, 816, 'available', NULL, NULL, 30000.00),
(1576, 9, 817, 'available', NULL, NULL, 30000.00),
(1577, 9, 821, 'available', NULL, NULL, 30000.00),
(1578, 9, 830, 'available', NULL, NULL, 30000.00),
(1579, 9, 831, 'available', NULL, NULL, 30000.00),
(1580, 9, 832, 'available', NULL, NULL, 30000.00),
(1581, 9, 822, 'available', NULL, NULL, 30000.00),
(1582, 9, 823, 'available', NULL, NULL, 30000.00),
(1583, 9, 824, 'available', NULL, NULL, 30000.00),
(1584, 9, 825, 'available', NULL, NULL, 30000.00),
(1585, 9, 826, 'available', NULL, NULL, 30000.00),
(1586, 9, 827, 'available', NULL, NULL, 30000.00),
(1587, 9, 828, 'available', NULL, NULL, 30000.00),
(1588, 9, 829, 'available', NULL, NULL, 30000.00),
(1589, 9, 833, 'available', NULL, NULL, 30000.00),
(1590, 9, 842, 'available', NULL, NULL, 30000.00),
(1591, 9, 843, 'available', NULL, NULL, 30000.00),
(1592, 9, 844, 'available', NULL, NULL, 30000.00),
(1593, 9, 834, 'available', NULL, NULL, 30000.00),
(1594, 9, 835, 'available', NULL, NULL, 30000.00),
(1595, 9, 836, 'available', NULL, NULL, 30000.00),
(1596, 9, 837, 'available', NULL, NULL, 30000.00),
(1597, 9, 838, 'available', NULL, NULL, 30000.00),
(1598, 9, 839, 'available', NULL, NULL, 30000.00),
(1599, 9, 840, 'available', NULL, NULL, 30000.00),
(1600, 9, 841, 'available', NULL, NULL, 30000.00),
(1601, 9, 845, 'available', NULL, NULL, 30000.00),
(1602, 9, 854, 'available', NULL, NULL, 30000.00),
(1603, 9, 855, 'available', NULL, NULL, 30000.00),
(1604, 9, 856, 'available', NULL, NULL, 30000.00),
(1605, 9, 846, 'available', NULL, NULL, 30000.00),
(1606, 9, 847, 'available', NULL, NULL, 30000.00),
(1607, 9, 848, 'available', NULL, NULL, 30000.00),
(1608, 9, 849, 'available', NULL, NULL, 30000.00),
(1609, 9, 850, 'available', NULL, NULL, 30000.00),
(1610, 9, 851, 'available', NULL, NULL, 30000.00),
(1611, 9, 852, 'available', NULL, NULL, 30000.00),
(1612, 9, 853, 'available', NULL, NULL, 30000.00),
(1613, 9, 857, 'available', NULL, NULL, 30000.00),
(1614, 9, 866, 'available', NULL, NULL, 30000.00),
(1615, 9, 867, 'available', NULL, NULL, 30000.00),
(1616, 9, 868, 'available', NULL, NULL, 30000.00),
(1617, 9, 858, 'available', NULL, NULL, 30000.00),
(1618, 9, 859, 'available', NULL, NULL, 30000.00),
(1619, 9, 860, 'available', NULL, NULL, 30000.00),
(1620, 9, 861, 'available', NULL, NULL, 30000.00),
(1621, 9, 862, 'available', NULL, NULL, 30000.00),
(1622, 9, 863, 'available', NULL, NULL, 30000.00),
(1623, 9, 864, 'available', NULL, NULL, 30000.00),
(1624, 9, 865, 'available', NULL, NULL, 30000.00),
(1656, 15, 581, 'available', NULL, NULL, 500000.00),
(1657, 15, 590, 'available', NULL, NULL, 500000.00),
(1658, 15, 591, 'available', NULL, NULL, 500000.00),
(1659, 15, 592, 'available', NULL, NULL, 500000.00),
(1660, 15, 582, 'available', NULL, NULL, 500000.00),
(1661, 15, 583, 'available', NULL, NULL, 500000.00),
(1662, 15, 584, 'booked', NULL, NULL, 500000.00),
(1663, 15, 585, 'available', NULL, NULL, 500000.00),
(1664, 15, 586, 'available', NULL, NULL, 500000.00),
(1665, 15, 587, 'booked', NULL, NULL, 500000.00),
(1666, 15, 588, 'available', NULL, NULL, 500000.00),
(1667, 15, 589, 'available', NULL, NULL, 500000.00),
(1668, 15, 593, 'available', NULL, NULL, 500000.00),
(1669, 15, 602, 'available', NULL, NULL, 500000.00),
(1670, 15, 603, 'available', NULL, NULL, 500000.00),
(1671, 15, 604, 'available', NULL, NULL, 500000.00),
(1672, 15, 594, 'available', NULL, NULL, 500000.00),
(1673, 15, 595, 'available', NULL, NULL, 500000.00),
(1674, 15, 596, 'available', NULL, NULL, 500000.00),
(1675, 15, 597, 'available', NULL, NULL, 500000.00),
(1676, 15, 598, 'available', NULL, NULL, 500000.00),
(1677, 15, 599, 'available', NULL, NULL, 500000.00),
(1678, 15, 600, 'available', NULL, NULL, 500000.00),
(1679, 15, 601, 'available', NULL, NULL, 500000.00),
(1680, 15, 605, 'available', NULL, NULL, 500000.00),
(1681, 15, 614, 'available', NULL, NULL, 500000.00),
(1682, 15, 615, 'available', NULL, NULL, 500000.00),
(1683, 15, 616, 'available', NULL, NULL, 500000.00),
(1684, 15, 606, 'available', NULL, NULL, 500000.00),
(1685, 15, 607, 'available', NULL, NULL, 500000.00),
(1686, 15, 608, 'available', NULL, NULL, 500000.00),
(1687, 15, 609, 'available', NULL, NULL, 500000.00),
(1688, 15, 610, 'held', '2026-03-31 09:22:23', '60168b1d118b15c202fe318940c3747e', 500000.00),
(1689, 15, 611, 'available', NULL, NULL, 500000.00),
(1690, 15, 612, 'available', NULL, NULL, 500000.00),
(1691, 15, 613, 'available', NULL, NULL, 500000.00),
(1692, 15, 617, 'available', NULL, NULL, 500000.00),
(1693, 15, 626, 'available', NULL, NULL, 500000.00),
(1694, 15, 627, 'available', NULL, NULL, 500000.00),
(1695, 15, 628, 'available', NULL, NULL, 500000.00),
(1696, 15, 618, 'available', NULL, NULL, 500000.00),
(1697, 15, 619, 'available', NULL, NULL, 500000.00),
(1698, 15, 620, 'available', NULL, NULL, 500000.00),
(1699, 15, 621, 'available', NULL, NULL, 500000.00),
(1700, 15, 622, 'available', NULL, NULL, 500000.00),
(1701, 15, 623, 'available', NULL, NULL, 500000.00),
(1702, 15, 624, 'available', NULL, NULL, 500000.00),
(1703, 15, 625, 'available', NULL, NULL, 500000.00),
(1704, 15, 629, 'available', NULL, NULL, 500000.00),
(1705, 15, 638, 'available', NULL, NULL, 500000.00),
(1706, 15, 639, 'available', NULL, NULL, 500000.00),
(1707, 15, 640, 'available', NULL, NULL, 500000.00),
(1708, 15, 630, 'available', NULL, NULL, 500000.00),
(1709, 15, 631, 'available', NULL, NULL, 500000.00),
(1710, 15, 632, 'available', NULL, NULL, 500000.00),
(1711, 15, 633, 'available', NULL, NULL, 500000.00),
(1712, 15, 634, 'available', NULL, NULL, 500000.00),
(1713, 15, 635, 'available', NULL, NULL, 500000.00),
(1714, 15, 636, 'available', NULL, NULL, 500000.00),
(1715, 15, 637, 'available', NULL, NULL, 500000.00),
(1716, 15, 641, 'available', NULL, NULL, 300000.00),
(1717, 15, 650, 'available', NULL, NULL, 300000.00),
(1718, 15, 651, 'available', NULL, NULL, 300000.00),
(1719, 15, 652, 'available', NULL, NULL, 300000.00),
(1720, 15, 642, 'available', NULL, NULL, 300000.00),
(1721, 15, 643, 'available', NULL, NULL, 300000.00),
(1722, 15, 644, 'available', NULL, NULL, 300000.00),
(1723, 15, 645, 'available', NULL, NULL, 300000.00),
(1724, 15, 646, 'available', NULL, NULL, 300000.00),
(1725, 15, 647, 'available', NULL, NULL, 300000.00),
(1726, 15, 648, 'available', NULL, NULL, 300000.00),
(1727, 15, 649, 'available', NULL, NULL, 300000.00),
(1728, 15, 653, 'available', NULL, NULL, 300000.00),
(1729, 15, 662, 'available', NULL, NULL, 300000.00),
(1730, 15, 663, 'available', NULL, NULL, 300000.00),
(1731, 15, 664, 'available', NULL, NULL, 300000.00),
(1732, 15, 654, 'available', NULL, NULL, 300000.00),
(1733, 15, 655, 'available', NULL, NULL, 300000.00),
(1734, 15, 656, 'available', NULL, NULL, 300000.00),
(1735, 15, 657, 'available', NULL, NULL, 300000.00),
(1736, 15, 658, 'available', NULL, NULL, 300000.00),
(1737, 15, 659, 'available', NULL, NULL, 300000.00),
(1738, 15, 660, 'available', NULL, NULL, 300000.00),
(1739, 15, 661, 'available', NULL, NULL, 300000.00),
(1740, 15, 665, 'available', NULL, NULL, 300000.00),
(1741, 15, 674, 'available', NULL, NULL, 300000.00),
(1742, 15, 675, 'available', NULL, NULL, 300000.00),
(1743, 15, 676, 'available', NULL, NULL, 300000.00),
(1744, 15, 666, 'available', NULL, NULL, 300000.00),
(1745, 15, 667, 'available', NULL, NULL, 300000.00),
(1746, 15, 668, 'available', NULL, NULL, 300000.00),
(1747, 15, 669, 'available', NULL, NULL, 300000.00),
(1748, 15, 670, 'available', NULL, NULL, 300000.00),
(1749, 15, 671, 'available', NULL, NULL, 300000.00),
(1750, 15, 672, 'available', NULL, NULL, 300000.00),
(1751, 15, 673, 'available', NULL, NULL, 300000.00),
(1910, 17, 869, 'available', NULL, NULL, 400000.00),
(1911, 17, 878, 'available', NULL, NULL, 400000.00),
(1912, 17, 879, 'available', NULL, NULL, 400000.00),
(1913, 17, 880, 'available', NULL, NULL, 400000.00),
(1914, 17, 870, 'available', NULL, NULL, 400000.00),
(1915, 17, 871, 'available', NULL, NULL, 400000.00),
(1916, 17, 872, 'available', NULL, NULL, 400000.00),
(1917, 17, 873, 'available', NULL, NULL, 400000.00),
(1918, 17, 874, 'available', NULL, NULL, 400000.00),
(1919, 17, 875, 'available', NULL, NULL, 400000.00),
(1920, 17, 876, 'available', NULL, NULL, 400000.00),
(1921, 17, 877, 'available', NULL, NULL, 400000.00),
(1922, 17, 881, 'available', NULL, NULL, 400000.00),
(1923, 17, 890, 'available', NULL, NULL, 400000.00),
(1924, 17, 891, 'available', NULL, NULL, 400000.00),
(1925, 17, 892, 'available', NULL, NULL, 400000.00),
(1926, 17, 882, 'available', NULL, NULL, 400000.00),
(1927, 17, 883, 'available', NULL, NULL, 400000.00),
(1928, 17, 884, 'available', NULL, NULL, 400000.00),
(1929, 17, 885, 'available', NULL, NULL, 400000.00),
(1930, 17, 886, 'available', NULL, NULL, 400000.00),
(1931, 17, 887, 'available', NULL, NULL, 400000.00),
(1932, 17, 888, 'available', NULL, NULL, 400000.00),
(1933, 17, 889, 'available', NULL, NULL, 400000.00),
(1934, 17, 893, 'available', NULL, NULL, 400000.00),
(1935, 17, 902, 'available', NULL, NULL, 400000.00),
(1936, 17, 903, 'available', NULL, NULL, 400000.00),
(1937, 17, 904, 'available', NULL, NULL, 400000.00),
(1938, 17, 894, 'available', NULL, NULL, 400000.00),
(1939, 17, 895, 'available', NULL, NULL, 400000.00),
(1940, 17, 896, 'available', NULL, NULL, 400000.00),
(1941, 17, 897, 'available', NULL, NULL, 400000.00),
(1942, 17, 898, 'available', NULL, NULL, 400000.00),
(1943, 17, 899, 'available', NULL, NULL, 400000.00),
(1944, 17, 900, 'available', NULL, NULL, 400000.00),
(1945, 17, 901, 'available', NULL, NULL, 400000.00),
(1946, 17, 905, 'available', NULL, NULL, 400000.00),
(1947, 17, 914, 'available', NULL, NULL, 400000.00),
(1948, 17, 915, 'available', NULL, NULL, 400000.00),
(1949, 17, 916, 'available', NULL, NULL, 400000.00),
(1950, 17, 906, 'available', NULL, NULL, 400000.00),
(1951, 17, 907, 'available', NULL, NULL, 400000.00),
(1952, 17, 908, 'available', NULL, NULL, 400000.00),
(1953, 17, 909, 'available', NULL, NULL, 400000.00),
(1954, 17, 910, 'available', NULL, NULL, 400000.00),
(1955, 17, 911, 'available', NULL, NULL, 400000.00),
(1956, 17, 912, 'available', NULL, NULL, 400000.00),
(1957, 17, 913, 'available', NULL, NULL, 400000.00),
(1958, 17, 917, 'available', NULL, NULL, 400000.00),
(1959, 17, 926, 'available', NULL, NULL, 400000.00),
(1960, 17, 927, 'available', NULL, NULL, 400000.00),
(1961, 17, 928, 'available', NULL, NULL, 400000.00),
(1962, 17, 918, 'available', NULL, NULL, 400000.00),
(1963, 17, 919, 'available', NULL, NULL, 400000.00),
(1964, 17, 920, 'available', NULL, NULL, 400000.00),
(1965, 17, 921, 'available', NULL, NULL, 400000.00),
(1966, 17, 922, 'available', NULL, NULL, 400000.00),
(1967, 17, 923, 'available', NULL, NULL, 400000.00),
(1968, 17, 924, 'available', NULL, NULL, 400000.00),
(1969, 17, 925, 'available', NULL, NULL, 400000.00),
(1970, 17, 929, 'available', NULL, NULL, 400000.00),
(1971, 17, 938, 'available', NULL, NULL, 400000.00),
(1972, 17, 939, 'available', NULL, NULL, 400000.00),
(1973, 17, 940, 'available', NULL, NULL, 400000.00),
(1974, 17, 930, 'available', NULL, NULL, 400000.00),
(1975, 17, 931, 'available', NULL, NULL, 400000.00),
(1976, 17, 932, 'available', NULL, NULL, 400000.00),
(1977, 17, 933, 'available', NULL, NULL, 400000.00),
(1978, 17, 934, 'available', NULL, NULL, 400000.00),
(1979, 17, 935, 'available', NULL, NULL, 400000.00),
(1980, 17, 936, 'available', NULL, NULL, 400000.00),
(1981, 17, 937, 'available', NULL, NULL, 400000.00),
(1982, 17, 941, 'available', NULL, NULL, 400000.00),
(1983, 17, 950, 'available', NULL, NULL, 400000.00),
(1984, 17, 951, 'available', NULL, NULL, 400000.00),
(1985, 17, 952, 'available', NULL, NULL, 400000.00),
(1986, 17, 942, 'available', NULL, NULL, 400000.00),
(1987, 17, 943, 'available', NULL, NULL, 400000.00),
(1988, 17, 944, 'available', NULL, NULL, 400000.00),
(1989, 17, 945, 'available', NULL, NULL, 400000.00),
(1990, 17, 946, 'available', NULL, NULL, 400000.00),
(1991, 17, 947, 'available', NULL, NULL, 400000.00),
(1992, 17, 948, 'available', NULL, NULL, 400000.00),
(1993, 17, 949, 'available', NULL, NULL, 400000.00),
(1994, 17, 953, 'available', NULL, NULL, 400000.00),
(1995, 17, 962, 'available', NULL, NULL, 400000.00),
(1996, 17, 963, 'available', NULL, NULL, 400000.00),
(1997, 17, 964, 'available', NULL, NULL, 400000.00),
(1998, 17, 954, 'available', NULL, NULL, 400000.00),
(1999, 17, 955, 'available', NULL, NULL, 400000.00),
(2000, 17, 956, 'available', NULL, NULL, 400000.00),
(2001, 17, 957, 'available', NULL, NULL, 400000.00),
(2002, 17, 958, 'available', NULL, NULL, 400000.00),
(2003, 17, 959, 'available', NULL, NULL, 400000.00),
(2004, 17, 960, 'available', NULL, NULL, 400000.00),
(2005, 17, 961, 'available', NULL, NULL, 400000.00),
(2037, 18, 773, 'available', NULL, NULL, 300000.00),
(2038, 18, 782, 'available', NULL, NULL, 300000.00),
(2039, 18, 783, 'available', NULL, NULL, 300000.00),
(2040, 18, 784, 'available', NULL, NULL, 300000.00),
(2041, 18, 774, 'available', NULL, NULL, 300000.00),
(2042, 18, 775, 'available', NULL, NULL, 300000.00),
(2043, 18, 776, 'available', NULL, NULL, 300000.00),
(2044, 18, 777, 'available', NULL, NULL, 300000.00),
(2045, 18, 778, 'available', NULL, NULL, 300000.00),
(2046, 18, 779, 'available', NULL, NULL, 300000.00),
(2047, 18, 780, 'available', NULL, NULL, 300000.00),
(2048, 18, 781, 'available', NULL, NULL, 300000.00),
(2049, 18, 785, 'available', NULL, NULL, 300000.00),
(2050, 18, 794, 'available', NULL, NULL, 300000.00),
(2051, 18, 795, 'available', NULL, NULL, 300000.00),
(2052, 18, 796, 'available', NULL, NULL, 300000.00),
(2053, 18, 786, 'available', NULL, NULL, 300000.00),
(2054, 18, 787, 'available', NULL, NULL, 300000.00),
(2055, 18, 788, 'available', NULL, NULL, 300000.00),
(2056, 18, 789, 'available', NULL, NULL, 300000.00),
(2057, 18, 790, 'available', NULL, NULL, 300000.00),
(2058, 18, 791, 'available', NULL, NULL, 300000.00),
(2059, 18, 792, 'available', NULL, NULL, 300000.00),
(2060, 18, 793, 'available', NULL, NULL, 300000.00),
(2061, 18, 797, 'available', NULL, NULL, 300000.00),
(2062, 18, 806, 'available', NULL, NULL, 300000.00),
(2063, 18, 807, 'available', NULL, NULL, 300000.00),
(2064, 18, 808, 'available', NULL, NULL, 300000.00),
(2065, 18, 798, 'available', NULL, NULL, 300000.00),
(2066, 18, 799, 'available', NULL, NULL, 300000.00),
(2067, 18, 800, 'available', NULL, NULL, 300000.00),
(2068, 18, 801, 'available', NULL, NULL, 300000.00),
(2069, 18, 802, 'available', NULL, NULL, 300000.00),
(2070, 18, 803, 'available', NULL, NULL, 300000.00),
(2071, 18, 804, 'available', NULL, NULL, 300000.00),
(2072, 18, 805, 'available', NULL, NULL, 300000.00),
(2073, 18, 809, 'available', NULL, NULL, 300000.00),
(2074, 18, 818, 'available', NULL, NULL, 300000.00),
(2075, 18, 819, 'available', NULL, NULL, 300000.00),
(2076, 18, 820, 'available', NULL, NULL, 300000.00),
(2077, 18, 810, 'available', NULL, NULL, 300000.00),
(2078, 18, 811, 'available', NULL, NULL, 300000.00),
(2079, 18, 812, 'available', NULL, NULL, 300000.00),
(2080, 18, 813, 'available', NULL, NULL, 300000.00),
(2081, 18, 814, 'available', NULL, NULL, 300000.00),
(2082, 18, 815, 'available', NULL, NULL, 300000.00),
(2083, 18, 816, 'available', NULL, NULL, 300000.00),
(2084, 18, 817, 'available', NULL, NULL, 300000.00),
(2085, 18, 821, 'available', NULL, NULL, 300000.00),
(2086, 18, 830, 'available', NULL, NULL, 300000.00),
(2087, 18, 831, 'available', NULL, NULL, 300000.00),
(2088, 18, 832, 'available', NULL, NULL, 300000.00),
(2089, 18, 822, 'available', NULL, NULL, 300000.00),
(2090, 18, 823, 'available', NULL, NULL, 300000.00),
(2091, 18, 824, 'available', NULL, NULL, 300000.00),
(2092, 18, 825, 'available', NULL, NULL, 300000.00),
(2093, 18, 826, 'available', NULL, NULL, 300000.00),
(2094, 18, 827, 'available', NULL, NULL, 300000.00),
(2095, 18, 828, 'available', NULL, NULL, 300000.00),
(2096, 18, 829, 'available', NULL, NULL, 300000.00),
(2097, 18, 833, 'available', NULL, NULL, 300000.00),
(2098, 18, 842, 'available', NULL, NULL, 300000.00),
(2099, 18, 843, 'available', NULL, NULL, 300000.00),
(2100, 18, 844, 'available', NULL, NULL, 300000.00),
(2101, 18, 834, 'available', NULL, NULL, 300000.00),
(2102, 18, 835, 'available', NULL, NULL, 300000.00),
(2103, 18, 836, 'available', NULL, NULL, 300000.00),
(2104, 18, 837, 'available', NULL, NULL, 300000.00),
(2105, 18, 838, 'available', NULL, NULL, 300000.00),
(2106, 18, 839, 'available', NULL, NULL, 300000.00),
(2107, 18, 840, 'available', NULL, NULL, 300000.00),
(2108, 18, 841, 'available', NULL, NULL, 300000.00),
(2109, 18, 845, 'available', NULL, NULL, 300000.00),
(2110, 18, 854, 'available', NULL, NULL, 300000.00),
(2111, 18, 855, 'available', NULL, NULL, 300000.00),
(2112, 18, 856, 'available', NULL, NULL, 300000.00),
(2113, 18, 846, 'available', NULL, NULL, 300000.00),
(2114, 18, 847, 'available', NULL, NULL, 300000.00),
(2115, 18, 848, 'available', NULL, NULL, 300000.00),
(2116, 18, 849, 'available', NULL, NULL, 300000.00),
(2117, 18, 850, 'available', NULL, NULL, 300000.00),
(2118, 18, 851, 'available', NULL, NULL, 300000.00),
(2119, 18, 852, 'available', NULL, NULL, 300000.00),
(2120, 18, 853, 'available', NULL, NULL, 300000.00),
(2121, 18, 857, 'available', NULL, NULL, 300000.00),
(2122, 18, 866, 'available', NULL, NULL, 300000.00),
(2123, 18, 867, 'available', NULL, NULL, 300000.00),
(2124, 18, 868, 'available', NULL, NULL, 300000.00),
(2125, 18, 858, 'available', NULL, NULL, 300000.00),
(2126, 18, 859, 'available', NULL, NULL, 300000.00),
(2127, 18, 860, 'available', NULL, NULL, 300000.00),
(2128, 18, 861, 'available', NULL, NULL, 300000.00),
(2129, 18, 862, 'available', NULL, NULL, 300000.00),
(2130, 18, 863, 'available', NULL, NULL, 300000.00),
(2131, 18, 864, 'available', NULL, NULL, 300000.00),
(2132, 18, 865, 'available', NULL, NULL, 300000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `preferred_language` varchar(20) DEFAULT 'vi',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `loyalty_points` int(11) NOT NULL DEFAULT 0,
  `member_tier` varchar(50) DEFAULT 'Standard',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password_hash`, `preferred_language`, `status`, `role`, `loyalty_points`, `member_tier`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'Mai Hữu Kha', 'maihuukha180407@gmail.com', '0384729080', '$2y$10$.L8J9Ff13T6wJqT0cERZs.S/9DhdIe1X744JG4sZkM9NwKRJZK1iq', 'vi', 'active', 'admin', 0, 'Standard', 0, '2026-03-12 15:46:29', '2026-03-23 14:17:35'),
(2, 'Cũng Quang Long', 'ql@gamil.com', '123456789', '$2y$10$ZQYmk.fRM/ECOvREiKBXJu770pOHThrlmbpBmBhhiaU.v8iveBICG', 'vi', 'active', 'user', 0, 'Standard', 0, '2026-03-12 15:49:33', '2026-03-12 15:49:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_bookings_user` (`user_id`),
  ADD KEY `fk_bookings_showtime` (`show_id`);

--
-- Indexes for table `booking_combos`
--
ALTER TABLE `booking_combos`
  ADD PRIMARY KEY (`booking_combo_id`),
  ADD UNIQUE KEY `uq_booking_combo` (`booking_id`,`combo_id`),
  ADD KEY `fk_booking_combos_combo` (`combo_id`);

--
-- Indexes for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`booking_seat_id`),
  ADD UNIQUE KEY `uq_booking_show_seat` (`booking_id`,`show_seat_id`),
  ADD KEY `fk_booking_seats_show_seat` (`show_seat_id`);

--
-- Indexes for table `combo_products`
--
ALTER TABLE `combo_products`
  ADD PRIMARY KEY (`combo_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`,`genre_id`),
  ADD KEY `fk_movie_genres_genre` (`genre_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `fk_payments_booking` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `uq_room_name_per_location` (`location_id`,`room_name`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `uq_seat_per_room` (`room_id`,`seat_row`,`seat_number`),
  ADD KEY `fk_seats_category` (`category_id`);

--
-- Indexes for table `seat_categories`
--
ALTER TABLE `seat_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD PRIMARY KEY (`show_id`),
  ADD KEY `fk_showtimes_movie` (`movie_id`),
  ADD KEY `fk_showtimes_room` (`room_id`);

--
-- Indexes for table `show_seats`
--
ALTER TABLE `show_seats`
  ADD PRIMARY KEY (`show_seat_id`),
  ADD UNIQUE KEY `uq_show_seat` (`show_id`,`seat_id`),
  ADD KEY `fk_show_seats_seat` (`seat_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `booking_combos`
--
ALTER TABLE `booking_combos`
  MODIFY `booking_combo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `booking_seats`
--
ALTER TABLE `booking_seats`
  MODIFY `booking_seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `combo_products`
--
ALTER TABLE `combo_products`
  MODIFY `combo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=965;

--
-- AUTO_INCREMENT for table `seat_categories`
--
ALTER TABLE `seat_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `showtimes`
--
ALTER TABLE `showtimes`
  MODIFY `show_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `show_seats`
--
ALTER TABLE `show_seats`
  MODIFY `show_seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2164;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_showtime` FOREIGN KEY (`show_id`) REFERENCES `showtimes` (`show_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_combos`
--
ALTER TABLE `booking_combos`
  ADD CONSTRAINT `fk_booking_combos_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_combos_combo` FOREIGN KEY (`combo_id`) REFERENCES `combo_products` (`combo_id`) ON UPDATE CASCADE;

--
-- Constraints for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD CONSTRAINT `fk_booking_seats_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_seats_show_seat` FOREIGN KEY (`show_seat_id`) REFERENCES `show_seats` (`show_seat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD CONSTRAINT `fk_movie_genres_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movie_genres_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `fk_seats_category` FOREIGN KEY (`category_id`) REFERENCES `seat_categories` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_seats_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD CONSTRAINT `fk_showtimes_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_showtimes_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `show_seats`
--
ALTER TABLE `show_seats`
  ADD CONSTRAINT `fk_show_seats_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_show_seats_showtime` FOREIGN KEY (`show_id`) REFERENCES `showtimes` (`show_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
