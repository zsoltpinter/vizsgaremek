-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Gép: localhost:3306
-- Létrehozás ideje: 2026. Feb 17. 13:05
-- Kiszolgáló verziója: 10.11.16-MariaDB-ubu2204
-- PHP verzió: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `rh57507_alapszerviz`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `services_requested` text DEFAULT NULL,
  `estimated_duration` int(11) DEFAULT 60,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `booking_date`, `booking_time`, `name`, `phone`, `email`, `message`, `services_requested`, `estimated_duration`, `status`, `created_at`) VALUES
(1, 3, 1, '2025-12-17', '10:00:00', 'Zsolti', '+36 52 789 334', 'pzsolt061023@gmail.com', '', '[\"Fut\\u00f3m\\u0171 jav\\u00edt\\u00e1s\",\"Gumiabroncs csere\",\"Motorjav\\u00edt\\u00e1s\",\"M\\u0171szaki vizsga el\\u0151k\\u00e9sz\\u00edt\\u00e9s\",\"Olajcsere\"]', 525, 'completed', '2025-12-08 17:51:18'),
(2, 4, 1, '2025-12-19', '10:00:00', 'sdddsds', '+360123456', 'sdsdsd@sdsdds.com', 'SÜRGŐS LEROBBANT A KOCSIM', '[\"Akkumul\\u00e1tor csere\",\"Diagnosztika\",\"F\\u00e9kjav\\u00edt\\u00e1s\",\"Fut\\u00f3m\\u0171 jav\\u00edt\\u00e1s\",\"Gumiabroncs csere\",\"Kl\\u00edma szerviz\",\"Motorjav\\u00edt\\u00e1s\",\"M\\u0171szaki vizsga el\\u0151k\\u00e9sz\\u00edt\\u00e9s\",\"Olajcsere\",\"Sz\\u00e9lv\\u00e9d\\u0151 csere\"]', 875, 'completed', '2025-12-09 09:15:23'),
(3, 3, 1, '2025-12-10', '08:00:00', 'Asztra', '+36 20 231 1211', 'pzsolt061023@gmail.com', 'Ezeket szeretném kérni öcsköseim', '[\"Akkumul\\u00e1tor csere\",\"Fut\\u00f3m\\u0171 jav\\u00edt\\u00e1s\",\"Olajcsere\"]', 170, 'completed', '2025-12-09 15:49:32'),
(4, 3, 1, '2025-12-18', '08:00:00', 'Zsolti', '+3654355211', 'pzsolt061023@gmail.com', '', '[\"Akkumul\\u00e1tor csere\",\"Diagnosztika\",\"F\\u00e9kjav\\u00edt\\u00e1s\",\"Fut\\u00f3m\\u0171 jav\\u00edt\\u00e1s\",\"Gumiabroncs csere\",\"Kl\\u00edma szerviz\",\"Motorjav\\u00edt\\u00e1s\",\"M\\u0171szaki vizsga el\\u0151k\\u00e9sz\\u00edt\\u00e9s\",\"Olajcsere\",\"Sz\\u00e9lv\\u00e9d\\u0151 csere\"]', 875, 'cancelled', '2025-12-17 12:55:32'),
(5, 3, 1, '2025-12-18', '08:00:00', 'Zsolti', '+3654355211', 'pzsolt061023@gmail.com', '', '[\"Akkumul\\u00e1tor csere\",\"Diagnosztika\",\"F\\u00e9kjav\\u00edt\\u00e1s\",\"Fut\\u00f3m\\u0171 jav\\u00edt\\u00e1s\",\"Gumiabroncs csere\",\"Motorjav\\u00edt\\u00e1s\",\"M\\u0171szaki vizsga el\\u0151k\\u00e9sz\\u00edt\\u00e9s\",\"Sz\\u00e9lv\\u00e9d\\u0151 csere\"]', 785, 'cancelled', '2025-12-17 12:56:42'),
(6, 3, 1, '2026-01-01', '08:00:00', 'Zsolti', '+36 2020202', 'pzsolt061023@gmail.com', '', '[\"Gumiabroncs csere\",\"Motorjav\\u00edt\\u00e1s\"]', 285, 'completed', '2025-12-18 23:07:18'),
(7, 9, 3, '2026-02-05', '16:00:00', 'Zsolt egy vajda', '06 70 6767672', 'Raromeo2007@gmail.com', 'Nem vagyok magyar', '[\"Olajcsere\"]', 30, 'cancelled', '2026-02-02 13:03:59'),
(8, 7, 3, '2026-02-04', '10:00:00', 'Lőrincz Levente', '06306742699', '0511levi@gmail.com', 'Kúrvák legyenek, mert szétb... a fejeteket a haverjaim, mert én nyúl vagyok', '[\"Akkumul\\u00e1tor csere\",\"F\\u00e9kjav\\u00edt\\u00e1s\",\"Olajcsere\"]', 140, 'completed', '2026-02-03 13:52:32');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `comments`
--

INSERT INTO `comments` (`id`, `service_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 4, 'not bad🐱‍👤', '2025-12-09 09:13:45');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `service_id`, `created_at`) VALUES
(3, 4, 1, '2025-12-09 09:13:17'),
(4, 3, 1, '2026-01-13 10:48:13');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `ratings`
--

INSERT INTO `ratings` (`id`, `service_id`, `user_id`, `rating`, `created_at`) VALUES
(1, 1, 3, 5, '2025-12-08 17:50:37'),
(8, 1, 4, 4, '2025-12-09 09:13:07'),
(12, 1, 5, 5, '2025-12-11 07:21:19'),
(13, 1, 8, 5, '2025-12-18 23:05:08'),
(16, 3, 7, 5, '2026-01-16 10:24:00'),
(17, 3, 9, 1, '2026-02-02 13:01:45');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `hours` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `is_premium` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`id`, `user_id`, `name`, `city`, `address`, `description`, `phone`, `hours`, `image`, `status`, `is_premium`, `views`, `created_at`) VALUES
(1, 3, 'AutóProfi Műhely', 'Debrecen', '4028 Debrecen, Kassai út 67.', 'Az AutóProfi Műhely teljes körű autójavítási és karbantartási szolgáltatásokat nyújt személy- és kisteherautók számára. Specialitásaink közé tartozik a gyors olaj- és szűrőcsere, futóműállítás, fékjavítás, kuplung- és vezérléscsere, diagnosztika, valamint klímarendszerek töltése és javítása. Modern eszközökkel, tapasztalt szerelőkkel és rugalmas időpontfoglalással várjuk ügyfeleinket. Célunk a megbízható, pontos és átlátható szervizelés, rejtett költségek nélkül.', '+36 52 789 334', 'H–P: 7:30–17:30 Szo: 8:00–13:00 V: Zárva', '1765216210_69370fd2d5f4d.jpg', 'approved', 1, 277, '2025-12-08 17:50:10'),
(3, 7, 'TurboCar Szerviz', 'Csepel', 'Budapest, Tanműhely köz 7, 1211', 'TurboCar Szerviz egy modern, megbízható autószerviz, ahol a szakértelem és a precíz munkavégzés találkozik. Korszerű műhelyünkben a legújabb diagnosztikai eszközökkel dolgozunk, legyen szó általános karbantartásról, olajcseréről vagy komplex javításokról. Tapasztalt szerelőink minden járművet kiemelt figyelemmel kezelnek, hogy ügyfeleink biztonságosan és elégedetten térhessenek vissza az utakra. Nálunk az autód jó kezekben van!!', '+26705485567', 'H-P 8:00 - 19:00; Sz-V : 10:00 - 17:00', 'szerviz.png', 'approved', 0, 46, '2026-01-09 11:04:31');

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `services_with_ratings`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `services_with_ratings` (
`id` int(11)
,`user_id` int(11)
,`name` varchar(200)
,`city` varchar(100)
,`address` varchar(255)
,`description` text
,`phone` varchar(20)
,`hours` varchar(255)
,`image` varchar(255)
,`status` enum('pending','approved','rejected')
,`is_premium` tinyint(1)
,`views` int(11)
,`created_at` timestamp
,`average_rating` decimal(14,4)
,`rating_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `service_types`
--

INSERT INTO `service_types` (`id`, `name`, `duration_minutes`, `description`, `created_at`) VALUES
(1, 'Olajcsere', 30, 'Motor olaj és olajszűrő csere', '2025-12-08 17:45:42'),
(2, 'Fékjavítás', 90, 'Fékbetét, féktárcsa csere és beállítás', '2025-12-08 17:45:42'),
(3, 'Futómű javítás', 120, 'Futómű alkatrészek cseréje és beállítás', '2025-12-08 17:45:42'),
(4, 'Motorjavítás', 240, 'Motor diagnosztika és javítás', '2025-12-08 17:45:42'),
(5, 'Klíma szerviz', 60, 'Klíma tisztítás, töltés', '2025-12-08 17:45:42'),
(6, 'Gumiabroncs csere', 45, 'Négy kerék gumiabroncs cseréje', '2025-12-08 17:45:42'),
(7, 'Műszaki vizsga előkészítés', 90, 'Teljes átvizsgálás műszaki vizsgához', '2025-12-08 17:45:42'),
(8, 'Diagnosztika', 60, 'Elektronikus diagnosztika', '2025-12-08 17:45:42'),
(9, 'Akkumulátor csere', 20, 'Akkumulátor csere és ellenőrzés', '2025-12-08 17:45:42'),
(10, 'Szélvédő csere', 120, 'Szélvédő üveg csere', '2025-12-08 17:45:42');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `support`
--

CREATE TABLE `support` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `from_admin` tinyint(1) DEFAULT 0,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `support`
--

INSERT INTO `support` (`id`, `user_id`, `admin_id`, `message`, `from_admin`, `status`, `created_at`) VALUES
(2, 4, NULL, '⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⣄⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⢀⣠⣤⣴⣶⣶⣶⣶⣶⣤⣤⣄⣀⣀⣀⣀⣀⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡀⢀⣀⣀⣀⣤⣤⣤⣶⣶⣶⣶⣶⣶⣤⣄⡙⠢⣄⡀⠀⠀ ⢀⣤⡠⠖⣡⣾⣿⠿⠿⠛⠛⠛⠛⠛⠛⠛⠿⠿⠿⣿⣿⣿⣿⣿⡷⠀⠀⠀⠀⠀⠀⠀⠘⢿⣿⣿⣿⣿⣿⡿⠿⠿⠿⠛⠛⠛⠛⠛⠛⠻⢿⣷⣦⡙⢾⣷ ⠸⠿⠃⡾⠟⠉⠀⠀⠀⠀⠀⠀⣀⢀⡀⣀⢀⣀⣀⢀⠀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢀⠀⡀⢀⠀⡀⢀⡀⢀⡀⠀⠀⠀⠀⠀⠀⠙⠻⣾⢹ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⣀⣀⣀⣀⣀⣀⣀⣀⣀⠀⠀⢠⣤⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡴⠀⠀⣀⣀⣀⣀⣀⣄⣠⣄⣤⣤⣀⣀⠀⠀⠀⠀⠀⠈⠈ ⠀⠀⠀⠀⠀⠀⢀⣤⠞⠋⠁⢹⣿⣿⣿⣿⣿⣏⠹⣿⡉⠀⠀⢻⠄⠀⠀⠀⠀⠀⠀⠀⠀⢸⠇⠀⠀⢩⡿⠋⢹⣿⣿⣿⣟⣻⡇⠈⠙⠳⢦⡀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠿⣳⢦⣤⣤⣼⣿⣿⣿⣿⣿⣋⣄⣈⣻⣆⠀⠈⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠁⠀⣔⣏⣄⣤⣼⣿⣿⣿⣿⣿⣥⣶⣶⡾⠟⠃⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠈⠉⠉⠀⠀⠀⠀⠀⠀⠀⠛⠁⠉⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠁⠈⠀⠀⠀⠀⠀⠈⠉⠉⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡠⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢠⡞⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⡟⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⡄⠀⠀⠀⠀⡾⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣠⡴⠋⠀⠀⠀⠀⣸⠁⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣼⡟⠉⠀⠀⠀⠀⠀⢠⡇⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢾⣦⣤⣤⣤⣤⣤⣤⣤⣤⣤⣤⣤⣤⣶⡶⠶⠶⠶⠿⠛⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀', 0, 'closed', '2025-12-09 09:23:23'),
(18, 4, 3, 'Fasza', 1, 'closed', '2025-12-09 14:41:08'),
(20, 8, NULL, 'Hi, my withdrawal got rejected, can you help me why?', 0, 'open', '2025-12-18 23:05:27'),
(21, 8, 3, 'Az komoly', 1, 'open', '2025-12-18 23:05:32'),
(22, 8, NULL, 'cigany', 0, 'open', '2025-12-18 23:05:36'),
(23, 3, NULL, 'Hell oszia', 0, 'open', '2025-12-18 23:07:41'),
(24, 3, 8, 'yooooo', 1, 'open', '2025-12-18 23:07:50'),
(26, 7, NULL, 'sziasztok seggbe kerem', 0, 'open', '2026-01-16 10:27:01'),
(27, 7, 3, 'az komoly', 1, 'open', '2026-01-16 10:27:13'),
(28, 3, NULL, 'Helo', 0, 'open', '2026-02-03 13:42:42'),
(29, 3, 7, 'Helló Zsolti', 1, 'open', '2026-02-03 13:44:40'),
(30, 3, NULL, 'jaj de jo', 0, 'open', '2026-02-03 13:44:50'),
(31, 3, 7, 'Helló Zsolti', 1, 'open', '2026-02-03 13:45:38'),
(32, 3, 7, 'Helló Zsolti', 1, 'open', '2026-02-03 13:45:55'),
(33, 3, 7, 'Helló Zsolti', 1, 'open', '2026-02-03 13:46:05'),
(34, 3, 7, 'Ülj le 1-es', 1, 'open', '2026-02-03 13:46:47'),
(35, 3, 3, 'dejo', 1, 'open', '2026-02-10 13:01:02');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `is_admin`, `created_at`) VALUES
(2, 'Teszt User', 'user@alapszerviz.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-12-08 17:45:42'),
(3, 'Zsolti', 'pzsolt061023@gmail.com', '$2y$10$gyy09W3gXgT6hIHTPJuGv.sv95ozgQStrpnIPwrSXWUfqe393xPnK', 1, '2025-12-08 17:47:14'),
(4, 'sdddsds', 'sdsdsd@sdsdds.com', '$2y$10$kJ/QniKZDlSUESc8KKlhzuXutJ4sfOv/yZCDWo9sZMXU8hO2kieAq', 0, '2025-12-09 09:05:21'),
(5, 'bertagyorgy', 'pacekoldal@gmail.com', '$2y$10$rHqaKKU5/Vsu3ZTyEjUe0.baBtKKAy.svIDs56Xhp1Lmxn59eT7X2', 0, '2025-12-11 07:20:49'),
(6, 'Batosu', 'mark20061127@gmail.com', '$2y$10$ni696vmCsaCVuwBMBIzsre3PxlZYzyR1hTJxu7D9yQfY1eYPV8S3W', 0, '2025-12-12 10:37:06'),
(7, 'Lőrincz Levente', '0511levi@gmail.com', '$2y$10$rADJdXlbRTb4nmM6T94PjeY1W4J4oD4xV2IHcCSpVVWw.iI8azkbi', 1, '2025-12-15 12:58:11'),
(8, 'benec', 'bogarbence68@gmail.com', '$2y$10$cpzo0onacZFLJpDmv4bEeeXFm46u6eaoBK.4QSCwQVDpew7vZHds2', 1, '2025-12-18 23:04:44'),
(9, 'Zsolt egy vajda', 'Raromeo2007@gmail.com', '$2y$10$.stiPxU01xhSyqEAeqAL0u48EGGBMQSZyujJcGIHSeJkAdv/cD5um', 0, '2026-02-02 12:57:48');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_status` (`status`);

--
-- A tábla indexei `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- A tábla indexei `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`service_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_service_id` (`service_id`);

--
-- A tábla indexei `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`service_id`,`user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- A tábla indexei `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_premium` (`is_premium`);

--
-- A tábla indexei `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_admin` (`is_admin`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT a táblához `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `support`
--
ALTER TABLE `support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

-- --------------------------------------------------------

--
-- Nézet szerkezete `services_with_ratings`
--
DROP TABLE IF EXISTS `services_with_ratings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`rh57507`@`localhost` SQL SECURITY DEFINER VIEW `services_with_ratings`  AS SELECT `s`.`id` AS `id`, `s`.`user_id` AS `user_id`, `s`.`name` AS `name`, `s`.`city` AS `city`, `s`.`address` AS `address`, `s`.`description` AS `description`, `s`.`phone` AS `phone`, `s`.`hours` AS `hours`, `s`.`image` AS `image`, `s`.`status` AS `status`, `s`.`is_premium` AS `is_premium`, `s`.`views` AS `views`, `s`.`created_at` AS `created_at`, coalesce(avg(`r`.`rating`),0) AS `average_rating`, count(`r`.`id`) AS `rating_count` FROM (`services` `s` left join `ratings` `r` on(`s`.`id` = `r`.`service_id`)) GROUP BY `s`.`id` ;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `support`
--
ALTER TABLE `support`
  ADD CONSTRAINT `support_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
