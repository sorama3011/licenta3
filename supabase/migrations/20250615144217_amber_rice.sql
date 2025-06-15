-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 02:30 PM
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
-- Database: `gusturi_romanesti`
--
CREATE DATABASE IF NOT EXISTS `gusturi_romanesti` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gusturi_romanesti`;

-- --------------------------------------------------------

--
-- Table structure for table `adrese`
--

CREATE TABLE `adrese` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `tip` enum('livrare','facturare') NOT NULL,
  `adresa` varchar(255) NOT NULL,
  `oras` varchar(100) NOT NULL,
  `judet` varchar(100) NOT NULL,
  `cod_postal` varchar(10) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `implicit` tinyint(1) DEFAULT 0,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_actualizare` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adrese`
--

INSERT INTO `adrese` (`id`, `utilizator_id`, `tip`, `adresa`, `oras`, `judet`, `cod_postal`, `telefon`, `implicit`, `data_creare`, `data_actualizare`) VALUES
(1, 1, 'livrare', 'Strada Exemplului, Nr. 10, Bl. A, Sc. 1, Ap. 5', 'București', 'București', '010001', '0721123456', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(2, 1, 'facturare', 'Bulevardul Unirii, Nr. 20, Bl. B, Sc. 2, Ap. 10', 'București', 'București', '010002', '0721123456', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `categorii`
--

CREATE TABLE `categorii` (
  `id` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descriere` text DEFAULT NULL,
  `activ` tinyint(1) DEFAULT 1,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_actualizare` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorii`
--

INSERT INTO `categorii` (`id`, `nume`, `slug`, `descriere`, `activ`, `data_creare`, `data_actualizare`) VALUES
(1, 'Dulcețuri & Miere', 'dulceturi', 'Dulcețuri, gemuri, miere și alte produse dulci tradiționale.', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(2, 'Conserve & Murături', 'conserve', 'Conserve de legume, zacuscă, murături și alte preparate conservate.', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(3, 'Mezeluri', 'mezeluri', 'Mezeluri tradiționale, afumături și produse din carne.', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(4, 'Brânzeturi', 'branza', 'Brânzeturi și produse lactate tradiționale.', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(5, 'Băuturi', 'bauturi', 'Băuturi alcoolice și non-alcoolice tradiționale.', 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `comenzi`
--

CREATE TABLE `comenzi` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `numar_comanda` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `transport` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `metoda_plata` varchar(50) NOT NULL,
  `status_plata` enum('in_asteptare','platita','rambursata','anulata') DEFAULT 'in_asteptare',
  `status` enum('in_asteptare','confirmata','in_procesare','expediata','livrata','anulata') DEFAULT 'in_asteptare',
  `nume_livrare` varchar(255) NOT NULL,
  `adresa_livrare` varchar(255) NOT NULL,
  `oras_livrare` varchar(100) NOT NULL,
  `judet_livrare` varchar(100) NOT NULL,
  `cod_postal_livrare` varchar(10) NOT NULL,
  `telefon_livrare` varchar(20) DEFAULT NULL,
  `nume_facturare` varchar(255) NOT NULL,
  `adresa_facturare` varchar(255) NOT NULL,
  `oras_facturare` varchar(100) NOT NULL,
  `judet_facturare` varchar(100) NOT NULL,
  `cod_postal_facturare` varchar(10) NOT NULL,
  `telefon_facturare` varchar(20) DEFAULT NULL,
  `observatii` text DEFAULT NULL,
  `data_plasare` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_confirmare` datetime DEFAULT NULL,
  `data_expediere` datetime DEFAULT NULL,
  `data_livrare` datetime DEFAULT NULL,
  `numar_awb` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacte`
--

CREATE TABLE `contacte` (
  `id` int(11) NOT NULL,
  `prenume` varchar(100) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `subiect` varchar(255) NOT NULL,
  `mesaj` text NOT NULL,
  `data_trimitere` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cos_cumparaturi`
--

CREATE TABLE `cos_cumparaturi` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) DEFAULT NULL,
  `sesiune_id` varchar(255) DEFAULT NULL,
  `produs_id` int(11) NOT NULL,
  `cantitate` int(11) NOT NULL,
  `data_adaugare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detalii_comenzi`
--

CREATE TABLE `detalii_comenzi` (
  `id` int(11) NOT NULL,
  `comanda_id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `nume_produs` varchar(255) NOT NULL,
  `pret_unitar` decimal(10,2) NOT NULL,
  `cantitate` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `etichete`
--

CREATE TABLE `etichete` (
  `id` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descriere` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etichete`
--

INSERT INTO `etichete` (`id`, `nume`, `slug`, `descriere`) VALUES
(1, 'Produs de post', 'produs-de-post', 'Produse potrivite pentru perioadele de post.'),
(2, 'Fără zahăr', 'fara-zahar', 'Produse fără zahăr adăugat.'),
(3, 'Artizanal', 'artizanal', 'Produse realizate manual sau în cantități mici, cu metode tradiționale.'),
(4, 'Fără aditivi', 'fara-aditivi', 'Produse fără aditivi artificiali, conservanți sau coloranți.'),
(5, 'Ambalat manual', 'ambalat-manual', 'Produse ambalate manual.');

-- --------------------------------------------------------

--
-- Table structure for table `favorite`
--

CREATE TABLE `favorite` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `data_adaugare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imagini_produse`
--

CREATE TABLE `imagini_produse` (
  `id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `descriere` varchar(255) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagini_produse`
--

INSERT INTO `imagini_produse` (`id`, `produs_id`, `url`, `descriere`, `principal`) VALUES
(1, 1, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Dulceata+Capsuni+Arges', 'Dulceață de Căpșuni de Argeș', 1),
(2, 2, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Zacusca+Buzau', 'Zacuscă de Buzău', 1),
(3, 3, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Branza+Burduf+Maramures', 'Brânză de Burduf din Maramureș', 1),
(4, 4, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Tuica+Prune+Hunedoara', 'Țuică de Prune Hunedoara', 1),
(5, 5, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Miere+Salcam+Transilvania', 'Miere de Salcâm Transilvania', 1),
(6, 6, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Carnati+Plescoi', 'Cârnați de Pleșcoi', 1),
(7, 7, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Telemea+Ibanesti', 'Telemea de Ibănești', 1),
(8, 8, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Palinca+Pere', 'Pălincă de Pere Maramureș', 1),
(9, 9, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Gem+Caise', 'Gem de Caise Banat', 1),
(10, 10, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Slanina+Afumata', 'Slănină Afumată Oltenia', 1),
(11, 11, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Miere+Tei', 'Miere de Tei Bucovina', 1),
(12, 12, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Muraturi+Asortate', 'Murături Asortate Crișana', 1),
(13, 13, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Cas+Capra', 'Caș de Capră Maramureș', 1),
(14, 14, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Dulceata+Trandafiri', 'Dulceață de Trandafiri Dobrogea', 1),
(15, 15, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Horinca+Maramures', 'Horincă Maramureș', 1),
(16, 16, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Miere+Tei', 'Miere de Tei Transilvania', 1),
(17, 17, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Compot+Visine', 'Compot de Vișine Oltenia', 1),
(18, 18, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Sunca+Tara', 'Șuncă de Țară Banat', 1),
(19, 19, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Branza+Vaci', 'Brânză de Vaci Transilvania', 1),
(20, 20, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Vin+Tara', 'Vin de Țară Dobrogea', 1),
(21, 21, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Dulceata+Gutui', 'Dulceață de Gutui Dobrogea', 1),
(22, 22, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Ciuperci+Murate', 'Ciuperci Murate Bucovina', 1),
(23, 23, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Lebar+Porc', 'Lebăr de Porc Crișana', 1),
(24, 24, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Urda+Proaspata', 'Urdă Proaspătă Maramureș', 1),
(25, 25, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Sirop+Catina', 'Sirop de Cătină Bucovina', 1),
(26, 26, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Miere+Rapita', 'Miere de Rapiță Oltenia', 1),
(27, 27, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Fasole+Batuta', 'Fasole Bătută Muntenia', 1),
(28, 28, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Jumari+Porc', 'Jumări de Porc Banat', 1),
(29, 29, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Smantana+Tara', 'Smântână de Țară Transilvania', 1),
(30, 30, 'https://via.placeholder.com/600x400/8B0000/FFFFFF?text=Rachiu+Prune', 'Rachiu de Prune Crișana', 1);

-- --------------------------------------------------------

--
-- Table structure for table `informatii_nutritionale`
--

CREATE TABLE `informatii_nutritionale` (
  `id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `valoare_energetica_kcal` decimal(10,2) DEFAULT NULL,
  `valoare_energetica_kj` decimal(10,2) DEFAULT NULL,
  `grasimi` decimal(10,2) DEFAULT NULL,
  `grasimi_saturate` decimal(10,2) DEFAULT NULL,
  `glucide` decimal(10,2) DEFAULT NULL,
  `zaharuri` decimal(10,2) DEFAULT NULL,
  `fibre` decimal(10,2) DEFAULT NULL,
  `proteine` decimal(10,2) DEFAULT NULL,
  `sare` decimal(10,2) DEFAULT NULL,
  `vitamina_c` decimal(10,2) DEFAULT NULL,
  `calciu` decimal(10,2) DEFAULT NULL,
  `fier` decimal(10,2) DEFAULT NULL,
  `potasiu` decimal(10,2) DEFAULT NULL,
  `magneziu` decimal(10,2) DEFAULT NULL,
  `vitamina_a` decimal(10,2) DEFAULT NULL,
  `fosfor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informatii_nutritionale`
--

INSERT INTO `informatii_nutritionale` (`id`, `produs_id`, `valoare_energetica_kcal`, `valoare_energetica_kj`, `grasimi`, `grasimi_saturate`, `glucide`, `zaharuri`, `fibre`, `proteine`, `sare`, `vitamina_c`, `calciu`, `fier`, `potasiu`, `magneziu`, `vitamina_a`, `fosfor`) VALUES
(1, 1, 245.00, 1025.00, 0.20, 0.10, 60.00, 58.00, 1.20, 0.40, 0.02, 25.00, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 2, 85.00, 356.00, 4.20, 0.60, 8.50, 6.20, 3.80, 1.80, 1.20, 15.00, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 3, 298.00, 1247.00, 24.50, 16.80, 1.20, 1.20, 0.00, 18.50, 2.80, NULL, 485.00, NULL, NULL, NULL, NULL, NULL),
(4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 5, 304.00, 1272.00, 0.00, NULL, 82.40, 82.10, NULL, 0.30, 0.00, 0.50, NULL, NULL, 52.00, 2.00, NULL, NULL),
(6, 6, 412.00, 1724.00, 35.20, 13.80, 1.20, 0.80, 0.00, 22.50, 2.80, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 7, 264.00, 1105.00, 21.30, 14.20, 1.50, 1.50, NULL, 17.10, 3.20, NULL, 493.00, NULL, NULL, NULL, NULL, NULL),
(8, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 9, 278.00, 1163.00, 0.10, 0.02, 68.50, 66.20, 1.80, 0.60, 0.01, 4.00, NULL, NULL, NULL, NULL, 96.00, NULL),
(10, 10, 518.00, 2168.00, 53.20, 19.80, 0.00, 0.00, NULL, 11.20, 1.80, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 11, 304.00, 1272.00, 0.00, NULL, 82.40, 82.10, NULL, 0.30, 0.00, 0.50, NULL, NULL, 52.00, 2.00, NULL, NULL),
(12, 12, 19.00, 79.00, 0.10, 0.02, 3.90, 2.60, 1.20, 0.80, 1.50, 8.00, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 13, 103.00, 431.00, 4.10, 2.90, 4.10, 4.10, NULL, 11.00, 0.10, NULL, 134.00, NULL, NULL, NULL, NULL, 111.00),
(14, 14, 260.00, 1088.00, 0.10, 0.02, 64.20, 62.80, 0.80, 0.30, 0.01, 12.00, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 16, 304.00, 1272.00, 0.00, NULL, 82.40, 82.10, NULL, 0.30, 0.00, 0.50, NULL, NULL, 52.00, 2.00, NULL, NULL),
(17, 17, 46.00, 193.00, 0.10, 0.02, 11.20, 10.80, 0.80, 0.40, 0.01, 8.00, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 18, 145.00, 607.00, 5.10, 1.80, 0.50, 0.20, NULL, 24.20, 2.10, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 19, 98.00, 410.00, 4.30, 2.70, 3.40, 3.40, NULL, 11.00, 0.10, NULL, 113.00, NULL, NULL, NULL, NULL, 95.00),
(20, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 21, 238.00, 996.00, 0.10, 0.02, 58.50, 56.80, 1.50, 0.30, 0.01, 15.00, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 22, 22.00, 92.00, 0.30, 0.10, 3.30, 1.90, 2.50, 3.10, 1.80, 2.50, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 23, 319.00, 1335.00, 28.50, 10.20, 1.40, 0.80, NULL, 13.20, 1.50, NULL, NULL, 18.00, NULL, NULL, NULL, NULL),
(24, 24, 166.00, 695.00, 11.20, 7.80, 3.20, 3.20, NULL, 11.10, 0.10, NULL, 159.00, NULL, NULL, NULL, NULL, 158.00),
(25, 25, 82.00, 343.00, 0.70, 0.10, 19.20, 18.80, 2.10, 0.90, 0.01, 200.00, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 26, 304.00, 1272.00, 0.00, NULL, 82.40, 82.10, NULL, 0.30, 0.00, 0.50, NULL, NULL, 52.00, 2.00, NULL, NULL),
(27, 27, 142.00, 594.00, 5.20, 0.70, 18.50, 1.80, 6.30, 8.20, 0.80, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 28, 642.00, 2688.00, 65.80, 23.50, 0.00, 0.00, NULL, 12.50, 1.20, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 29, 325.00, 1360.00, 35.00, 22.50, 2.80, 2.80, NULL, 2.50, 0.10, NULL, 87.00, NULL, NULL, NULL, 350.00, NULL),
(30, 30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ingrediente`
--

CREATE TABLE `ingrediente` (
  `id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `lista_ingrediente` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingrediente`
--

INSERT INTO `ingrediente` (`id`, `produs_id`, `lista_ingrediente`) VALUES
(1, 1, 'Căpșuni (65%), zahăr, acid citric, pectină naturală'),
(2, 2, 'Vinete (45%), ardei roșii (25%), ceapă, ulei de floarea-soarelui, pastă de tomate, sare, piper negru'),
(3, 3, 'Lapte de oaie pasteurizat, sare, culturi de fermentare lactică, cheag'),
(4, 4, 'Prune fermentate natural, apă de izvor'),
(5, 5, 'Miere pură de salcâm 100%'),
(6, 6, 'Carne de porc (85%), grăsime de porc, sare, condimente (piper negru, cimbru, usturoi), conservant: nitrit de sodiu'),
(7, 7, 'Lapte de oaie pasteurizat, sare, culturi de fermentare lactică, cheag'),
(8, 8, 'Pere Williams fermentate natural, apă de izvor'),
(9, 9, 'Caise (60%), zahăr, acid citric, pectină naturală'),
(10, 10, 'Slănină de porc, sare grunjoasă, condimente naturale'),
(11, 11, 'Miere pură de tei 100%'),
(12, 12, 'Castraveți, gogonele, conopidă, morcovi, oțet de vin, sare, condimente naturale (dafin, coriandru, mărar)'),
(13, 13, 'Lapte de capră pasteurizat, culturi de fermentare lactică, cheag'),
(14, 14, 'Petale de trandafiri Damasc (30%), zahăr, acid citric, pectină naturală'),
(15, 15, 'Prune fermentate natural, apă de izvor din Maramureș'),
(16, 16, 'Miere pură de tei 100%'),
(17, 17, 'Vișine (65%), apă, acid citric natural'),
(18, 18, 'Carne de porc (95%), sare grunjoasă, condimente naturale (piper negru, cimbru, dafin), conservant: nitrit de sodiu'),
(19, 19, 'Lapte de vacă pasteurizat, culturi de fermentare lactică, cheag'),
(20, 20, 'Struguri fermentați natural (Fetească Neagră, Băbească Neagră)'),
(21, 21, 'Gutui (60%), zahăr, acid citric, pectină naturală'),
(22, 22, 'Ciuperci de pădure, oțet de vin, sare, condimente naturale (dafin, coriandru, piper negru)'),
(23, 23, 'Ficat de porc (75%), grăsime de porc, ceapă, usturoi, sare, condimente naturale (piper negru, cimbru)'),
(24, 24, 'Zer de oaie, sare'),
(25, 25, 'Cătină sălbatică (100%), acid citric natural'),
(26, 26, 'Miere pură de rapiță 100%'),
(27, 27, 'Fasole albă (75%), ceapă, ulei de floarea-soarelui, sare, usturoi, piper negru'),
(28, 28, 'Slănină de porc, sare grunjoasă'),
(29, 29, 'Smântână din lapte de vacă (conținut de grăsime 35%)'),
(30, 30, 'Prune fermentate natural, apă de izvor');

-- --------------------------------------------------------

--
-- Table structure for table `istoric_puncte`
--

CREATE TABLE `istoric_puncte` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `puncte` int(11) NOT NULL,
  `tip` enum('adaugare','utilizare') NOT NULL,
  `descriere` varchar(255) DEFAULT NULL,
  `referinta_id` int(11) DEFAULT NULL,
  `data_tranzactie` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_actiuni`
--

CREATE TABLE `log_actiuni` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) DEFAULT NULL,
  `actiune` varchar(100) NOT NULL,
  `descriere` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `data_actiune` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nume` varchar(255) DEFAULT NULL,
  `cod_confirmare` varchar(255) DEFAULT NULL,
  `confirmat` tinyint(1) DEFAULT 0,
  `activ` tinyint(1) DEFAULT 1,
  `data_inscriere` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produse`
--

CREATE TABLE `produse` (
  `id` int(11) NOT NULL,
  `nume` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `descriere_scurta` varchar(500) DEFAULT NULL,
  `descriere` text DEFAULT NULL,
  `pret` decimal(10,2) NOT NULL,
  `pret_redus` decimal(10,2) DEFAULT NULL,
  `cantitate` varchar(50) DEFAULT NULL,
  `stoc` int(11) DEFAULT 100,
  `categorie_id` int(11) DEFAULT NULL,
  `regiune_id` int(11) DEFAULT NULL,
  `imagine` varchar(255) DEFAULT NULL,
  `recomandat` tinyint(1) DEFAULT 0,
  `activ` tinyint(1) DEFAULT 1,
  `data_adaugare` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_actualizare` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produse`
--

INSERT INTO `produse` (`id`, `nume`, `slug`, `descriere_scurta`, `descriere`, `pret`, `pret_redus`, `cantitate`, `stoc`, `categorie_id`, `regiune_id`, `imagine`, `recomandat`, `activ`, `data_adaugare`, `data_actualizare`) VALUES
(1, 'Dulceață de Căpșuni de Argeș', 'dulceata-capsuni-arges', 'Dulceață tradițională din căpșuni proaspete cultivate în dealurile pitorești ale Argeșului. Preparată după rețete străvechi, fără conservanți artificiali, păstrând gustul autentic al căpșunilor de vară.', 'Această dulceață este perfectă pentru micul dejun, servită pe pâine proaspătă sau ca ingredient în deserturi tradiționale românești. Căpșunile sunt culese la maturitate optimă și procesate în aceeași zi pentru a păstra toate vitaminele și aroma naturală.', 18.99, NULL, '350g', 100, 1, 2, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Dulceata+Capsuni', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(2, 'Zacuscă de Buzău', 'zacusca-buzau', 'Zacuscă tradițională preparată din vinete și ardei copți pe foc de lemne, după rețeta autentică din zona Buzăului. Un produs 100% natural, fără conservanți artificiali, care păstrează gustul autentic al legumelor de vară.', 'Vinetele și ardeii sunt copți manual pe foc de lemne pentru a obține aroma specifică, apoi sunt procesați cu grijă pentru a păstra textura și gustul tradițional. Perfectă ca aperitiv sau ca garnitură pentru preparate tradiționale românești.', 15.50, NULL, '450g', 100, 2, 2, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Zacusca+Buzau', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(3, 'Brânză de Burduf', 'branza-burduf-maramures', 'Brânză tradițională de oaie maturată în burduf de brad, preparată după rețete străvechi din Maramureș. Un produs autentic cu gust intens și aromat, specific zonei montane.', 'Această brânză este preparată din lapte proaspăt de oaie, fermentată natural și maturată în burduf de brad pentru a obține gustul și aroma caracteristică. Procesul de maturare durează minimum 3 luni, timp în care brânza își dezvoltă textura cremoasă și gustul puternic, specific acestui produs tradițional maramureșean.', 32.00, NULL, '500g', 100, 4, 3, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Branza+Burduf', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(4, 'Țuică de Prune Hunedoara', 'tuica-prune-hunedoara', 'Țuică tradițională de prune din Hunedoara, distilată după rețete străvechi transmise din generație în generație. Cu o concentrație de 52% alcool, această țuică oferă un gust autentic și o aromă intensă specifică prunelor de Transilvania.', 'Produsă în cantități limitate folosind doar prune selectate din livezile din zona Hunedoara, această țuică este distilată de două ori pentru a obține puritatea și gustul perfect. Procesul de fermentare naturală durează minimum 6 luni, iar distilarea se face în alambicuri tradiționale de cupru.', 45.00, NULL, '500ml', 100, 5, 1, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Tuica+Prune', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(5, 'Miere de Salcâm Transilvania', 'miere-salcam-transilvania', 'Miere pură de salcâm din Munții Apuseni, Transilvania. Această miere cristalizată natural are un gust delicat și o aromă florală specifică, fiind considerată una dintre cele mai fine soiuri de miere din România.', 'Recoltată din stupinele amplasate în pădurile de salcâm din Transilvania, această miere păstrează toate proprietățile nutritive și terapeutice naturale. Procesul de cristalizare naturală conferă mierii o textură cremoasă și un gust rafinat, perfect pentru consumul zilnic sau pentru prepararea de deserturi tradiționale.', 28.50, NULL, '500g', 100, 1, 1, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Miere+Salcam', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(6, 'Cârnați de Pleșcoi', 'carnati-plescoi-muntenia', 'Cârnați afumați tradițional din Pleșcoi, Muntenia, preparați după rețete străvechi transmise din generație în generație. Acești cârnați sunt afumați cu lemn de fag și au un gust intens și aromat specific zonei.', 'Preparați din carne de porc selectată și condimentați cu un amestec secret de mirodenii, acești cârnați sunt afumați natural timp de 48 de ore. Procesul tradițional de preparare conferă produsului o textură perfectă și un gust autentic care îți va aminti de gusturile copilăriei.', 24.99, NULL, '400g', 100, 3, 2, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Carnati+Plescoi', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(7, 'Telemea de Ibănești', 'telemea-ibanesti-muntenia', 'Telemea tradițională din lapte de oaie de la Ibănești, Muntenia. Această brânză sărată are un gust intens și o textură cremoasă, fiind preparată după rețete străvechi transmise din generație în generație.', 'Produsă din lapte proaspăt de oaie, această telemea este maturată în saramură naturală timp de minimum 30 de zile. Procesul tradițional de preparare conferă brânzei gustul caracteristic și textura perfectă pentru consumul direct sau pentru prepararea de specialități culinare românești.', 19.50, NULL, '300g', 100, 4, 2, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Telemea+Ibanesti', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(8, 'Pălincă de Pere Maramureș', 'palinca-pere-maramures', 'Pălincă dublă distilare din pere Williams din Maramureș. Această băutură tradițională cu 65% alcool este produsă în cantități limitate folosind doar pere selectate din livezile maramureșene, distilată după rețete străvechi.', 'Procesul de dublă distilare în alambicuri tradiționale de cupru conferă acestei pălinci puritatea și gustul excepțional. Perele Williams sunt fermentate natural timp de 8 luni, iar distilarea se face în două etape pentru a obține concentrația perfectă și aroma intensă specifică acestui produs premium.', 55.00, NULL, '500ml', 100, 5, 3, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Palinca+Pere', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(9, 'Gem de Caise Banat', 'gem-caise-banat', 'Gem tradițional din caise de Banat, preparat după rețete străvechi transmise din generație în generație. Acest gem păstrează bucățile de caise și are un gust intens și aromat specific fructelor coapte la soare din Banat.', 'Caisele sunt culese la maturitate optimă din livezile bănățene și procesate în aceeași zi pentru a păstra toate vitaminele și aroma naturală. Gemul este fiert în cantități mici, cu adaos minim de zahăr, pentru a evidenția gustul autentic al caiselor.', 21.50, NULL, '350g', 100, 1, 4, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Gem+Caise', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(10, 'Slănină Afumată Oltenia', 'slanina-afumata-oltenia', 'Slănină afumată tradițional cu lemn de fag din Oltenia. Această slănină este preparată după rețete străvechi, fiind afumată natural timp de 72 de ore pentru a obține gustul și aroma specifică produselor tradiționale oltenești.', 'Procesul de afumare se face exclusiv cu lemn de fag, fără aditivi chimici, conferind slăninii o aromă intensă și un gust autentic. Carnea este selectată cu grijă și sărată cu sare grunjoasă naturală, apoi afumată la temperaturi controlate pentru a păstra toate proprietățile nutritive.', 35.00, NULL, '600g', 100, 3, 5, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Slanina+Afumata', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(11, 'Miere de Tei Bucovina', 'miere-tei-bucovina', 'Miere cristalizată de tei din pădurile Bucovinei. Această miere are proprietăți terapeutice excepționale și un gust delicat, fiind recoltată din stupinele amplasate în pădurile seculare de tei din nordul României.', 'Mierea de tei este cunoscută pentru efectele sale calmante și proprietățile antibacteriene. Cristalizarea naturală conferă mierii o textură cremoasă și un gust rafinat, cu note florale specifice florilor de tei. Este ideală pentru tratarea răcelilor și pentru relaxare.', 32.00, NULL, '500g', 100, 1, 7, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Miere+Tei', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(12, 'Murături Asortate Crișana', 'muraturi-asortate-crisana', 'Murături tradiționale în oțet de vin din Crișana. Acest amestec de legume murate conține castraveți, gogonele, conopidă și morcovi, toate preparate după rețete străvechi transmise din generație în generație.', 'Legumele sunt selectate cu grijă și murate în oțet de vin natural, cu adaos de condimente tradiționale. Procesul de murare durează minimum 30 de zile, timp în care legumele își dezvoltă gustul caracteristic și textura crocantă. Ideal ca aperitiv sau garnitură.', 16.99, NULL, '720ml', 100, 2, 6, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Muraturi+Asortate', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(13, 'Caș de Capră Maramureș', 'cas-capra-maramures', 'Caș proaspăt din lapte de capră din Maramureș. Această brânză proaspătă are un gust delicat și o textură cremoasă, fiind preparată după metode tradiționale maramureșene din lapte proaspăt de capră.', 'Cașul este preparat zilnic din lapte de capră proaspăt, fără aditivi sau conservanți. Procesul tradițional de coagulare și scurgere conferă produsului gustul caracteristic și textura perfectă. Este ideal pentru consumul direct, în salate sau pentru prepararea de deserturi tradiționale.', 26.50, NULL, '400g', 100, 4, 3, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Cas+Capra', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(14, 'Dulceață de Trandafiri Dobrogea', 'dulceata-trandafiri-dobrogea', 'Dulceață delicată din petale de trandafiri din Dobrogea. Această dulceață premium este preparată din petale de trandafiri Damasc, culese manual în zori de zi și procesate în aceeași zi pentru a păstra aroma și proprietățile naturale.', 'Petalele de trandafiri sunt selectate cu grijă din grădinile tradiționale din Dobrogea și procesate după rețete străvechi. Dulceața are un gust rafinat și o aromă intensă, fiind considerată o delicatesă în bucătăria românească. Perfectă pentru deserturi fine sau ca ingredient în prăjituri tradiționale.', 42.50, NULL, '250g', 100, 1, 8, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Dulceata+Trandafiri', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(15, 'Horincă Maramureș', 'horinca-maramures', 'Horincă tradițională din prune din Maramureș, cu 65% alcool. Această băutură spirituoasă premium este distilată după rețete străvechi maramureșene, fiind considerată una dintre cele mai fine băuturi tradiționale românești.', 'Horinca este produsă din prune selectate, fermentate natural timp de 10 luni, apoi distilată de trei ori în alambicuri tradiționale de cupru. Procesul complex de distilare conferă băuturii puritatea excepțională și gustul rafinat, specific acestui produs de lux din Maramureș.', 65.00, NULL, '500ml', 100, 5, 3, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Horinca+Maramures', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(16, 'Miere de Tei', 'miere-tei-transilvania', 'Miere naturală de tei din Transilvania. Această miere are proprietăți terapeutice excepționale și un gust delicat, fiind recoltată din stupinele amplasate în pădurile de tei din Transilvania.', 'Mierea de tei este cunoscută pentru efectele sale calmante și proprietățile antibacteriene. Are o textură cremoasă și un gust rafinat, cu note florale specifice florilor de tei. Este ideală pentru tratarea răcelilor și pentru relaxare.', 25.50, NULL, '500g', 100, 1, 1, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Miere+Tei', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(17, 'Compot de Vișine', 'compot-visine-oltenia', 'Compot natural din vișine de Oltenia, fără zahăr adăugat. Acest compot este preparat din vișine proaspete, culese la maturitate optimă din livezile oltenești și conservate în propriul suc natural.', 'Vișinele sunt procesate imediat după culegere pentru a păstra toate vitaminele și aroma naturală. Compotul nu conține conservanți artificiali sau zahăr adăugat, fiind îndulcit natural de fructe. Ideal pentru copii și persoane care urmează o dietă sănătoasă.', 16.99, NULL, '720ml', 100, 2, 5, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Compot+Visine', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(18, 'Șuncă de Țară Banat', 'sunca-tara-banat', 'Șuncă afumată tradițional în Banat, preparată din carne de porc selectată și afumată cu lemn de fag. Această șuncă de țară este preparată după rețete străvechi bănățene, fiind considerată una dintre cele mai fine specialități din regiune.', 'Carnea este sărată cu sare grunjoasă naturală și condimentată cu un amestec secret de mirodenii, apoi afumată timp de 5 zile la temperaturi controlate. Procesul tradițional de preparare conferă șuncii gustul autentic și textura perfectă, specifică produselor artizanale din Banat.', 48.00, NULL, '500g', 100, 3, 4, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Sunca+Tara', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(19, 'Brânză de Vaci Transilvania', 'branza-vaci-transilvania', 'Brânză tradițională din lapte de vacă din Transilvania. Această brânză proaspătă este preparată din lapte de vacă de la ferme locale din Transilvania, după metode tradiționale transmise din generație în generație.', 'Laptele este colectat zilnic de la ferme certificate din zona montană a Transilvaniei și procesat în aceeași zi pentru a păstra prospețimea și gustul autentic. Brânza are o textură cremoasă și un gust delicat, fiind ideală pentru consumul direct sau pentru prepararea de specialități culinare.', 22.00, NULL, '400g', 100, 4, 1, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Branza+Vaci', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(20, 'Vin de Țară Dobrogea', 'vin-tara-dobrogea', 'Vin roșu sec din soiuri autohtone din Dobrogea. Acest vin de țară este produs din struguri cultivați în podgoriile tradiționale din Dobrogea, fiind vinificat după metode artizanale pentru a păstra caracteristicile unice ale teroirului dobrogean.', 'Vinul este elaborat din soiuri autohtone românești, fermentat în butoaie de stejar și maturizat timp de 12 luni pentru a dezvolta complexitatea aromelor. Are un gust echilibrat, cu note de fructe roșii și un final persistent, fiind perfect pentru mesele tradiționale românești.', 38.50, NULL, '750ml', 100, 5, 8, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Vin+Tara', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(21, 'Dulceață de Gutui', 'dulceata-gutui-dobrogea', 'Dulceață aromată din gutui de Dobrogea, preparată după rețete străvechi transmise din generație în generație. Această dulceață are o aromă intensă și un gust delicat, specific gutuilor coapte din clima blândă a Dobrogei.', 'Gutuile sunt culese la maturitate optimă din livezile tradiționale din Dobrogea și procesate în aceeași zi pentru a păstra toate proprietățile nutritive și aroma naturală. Dulceața este fiartă în cantități mici, cu adaos minim de zahăr, pentru a evidenția gustul autentic al gutuilor.', 23.50, NULL, '350g', 100, 1, 8, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Dulceata+Gutui', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(22, 'Ciuperci Murate Bucovina', 'ciuperci-murate-bucovina', 'Ciuperci de pădure murate tradițional din Bucovina. Aceste ciuperci sunt culese manual din pădurile seculare ale Bucovinei și murate după rețete străvechi, păstrând gustul autentic și textura perfectă.', 'Ciupercile sunt selectate cu grijă, curățate și murate în oțet natural cu condimente tradiționale. Procesul de murare durează minimum 21 de zile, timp în care ciupercile își dezvoltă gustul caracteristic. Sunt ideale ca aperitiv sau garnitură pentru preparate tradiționale.', 18.50, NULL, '500g', 100, 2, 7, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Ciuperci+Murate', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(23, 'Lebăr de Porc Crișana', 'lebar-porc-crisana', 'Lebăr tradițional din Crișana, preparat din ficat de porc proaspăt și condimentat cu un amestec secret de mirodenii. Acest lebăr este preparat după rețete străvechi transmise din generație în generație în zona Crișanei.', 'Ficatul de porc este selectat cu grijă și procesat în aceeași zi pentru a păstra prospețimea și gustul autentic. Lebărul este condimentat cu ceapă, usturoi și condimente naturale, apoi ambalat manual. Are o textură cremoasă și un gust intens, fiind ideal pentru aperitive sau sandvișuri.', 29.99, NULL, '300g', 100, 3, 6, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Lebar+Porc', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(24, 'Urdă Proaspătă Maramureș', 'urda-proaspata-maramures', 'Urdă proaspătă din zer de oaie din Maramureș. Această urdă tradițională este preparată din zerul rămas după fabricarea brânzei de oaie, fiind fiartă și scursă după metode străvechi maramureșene.', 'Urda este preparată zilnic din zer proaspăt de oaie, fără aditivi sau conservanți. Are o textură fină și cremoasă și un gust delicat, fiind bogată în proteine și minerale. Este ideală pentru consumul direct, în salate sau pentru prepararea de deserturi tradiționale.', 15.50, NULL, '300g', 100, 4, 3, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Urda+Proaspata', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(25, 'Sirop de Cătină Bucovina', 'sirop-catina-bucovina', 'Sirop natural din cătină de munte din Bucovina, fără zahăr adăugat. Acest sirop este preparat din cătină sălbatică culeasă manual din zonele montane ale Bucovinei, fiind bogat în vitamina C și antioxidanți naturali.', 'Cătina este procesată la rece pentru a păstra toate vitaminele și proprietățile nutritive. Siropul nu conține zahăr adăugat, fiind îndulcit natural de fructe. Are un gust intens și o aromă specifică, fiind ideal pentru întărirea sistemului imunitar și ca supliment natural de vitamine.', 27.50, NULL, '500ml', 100, 5, 7, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Sirop+Catina', 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(26, 'Miere de Rapiță Oltenia', 'miere-rapita-oltenia', 'Miere cristalizată de rapiță din Oltenia. Această miere are o culoare alb-gălbuie și o textură cremoasă, fiind recoltată din stupinele amplasate în câmpurile de rapiță din Oltenia.', 'Mierea de rapiță este cunoscută pentru cristalizarea sa rapidă și textura fină, asemănătoare untului. Are un gust delicat, ușor dulce și o aromă subtilă. Este bogată în glucoză și fructoză, fiind o sursă excelentă de energie rapidă. Ideală pentru consumul zilnic, în ceai sau ca ingredient în deserturi.', 20.00, NULL, '500g', 100, 1, 5, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Miere+Rapita', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(27, 'Fasole Bătută Muntenia', 'fasole-batuta-muntenia', 'Fasole bătută tradițională cu ceapă din Muntenia. Această specialitate este preparată după rețete străvechi din fasole albă de calitate superioară și ceapă caramelizată, având o textură cremoasă și un gust autentic.', 'Fasolea este fiartă lent, apoi bătută manual pentru a obține textura perfectă. Ceapa este călită în ulei de floarea-soarelui până devine aurie și aromată. Produsul este 100% natural, fără conservanți sau aditivi artificiali, fiind ideal pentru perioadele de post sau ca aperitiv tradițional.', 12.50, NULL, '400g', 100, 2, 2, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Fasole+Batuta', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(28, 'Jumări de Porc Banat', 'jumari-porc-banat', 'Jumări crocante din Banat, preparate tradițional din slănină de porc de calitate superioară. Aceste jumări sunt prăjite lent pentru a obține textura perfectă - crocante la exterior și suculente la interior.', 'Slănina este tăiată manual în bucăți mici, apoi prăjită la temperatură controlată până devine aurie și crocantă. Jumările sunt condimentate doar cu sare grunjoasă naturală, păstrând gustul autentic al preparatelor tradiționale bănățene. Ideale ca aperitiv sau ca ingredient în diverse preparate culinare.', 31.00, NULL, '250g', 100, 3, 4, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Jumari+Porc', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(29, 'Smântână de Țară Transilvania', 'smantana-tara-transilvania', 'Smântână grasă de țară 35% din Transilvania. Această smântână tradițională este obținută din lapte de vacă proaspăt de la ferme locale din Transilvania, fiind procesată după metode străvechi.', 'Smântâna este separată natural din laptele crud, fără adaos de stabilizatori sau conservanți. Are o textură densă, cremoasă și un gust bogat, specific produselor tradiționale. Este ideală pentru gătit, pentru prepararea sosurilor sau pentru a fi servită cu mămăligă și alte preparate tradiționale românești.', 14.50, NULL, '200ml', 100, 4, 1, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Smantana+Tara', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00'),
(30, 'Rachiu de Prune Crișana', 'rachiu-prune-crisana', 'Rachiu tradițional din prune de Crișana, cu 48% alcool. Această băutură spirituoasă este distilată după rețete străvechi transmise din generație în generație în zona Crișanei.', 'Prunele sunt selectate cu grijă din livezile locale, fermentate natural și distilate în alambicuri tradiționale de cupru. Rachiul are o aromă intensă de prune și un gust echilibrat, fiind maturizat pentru a dezvolta complexitatea aromelor. Este servit tradițional la temperatura camerei, ca aperitiv sau digestiv.', 52.00, NULL, '500ml', 100, 5, 6, 'https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Rachiu+Prune', 0, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `produse_etichete`
--

CREATE TABLE `produse_etichete` (
  `produs_id` int(11) NOT NULL,
  `eticheta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produse_etichete`
--

INSERT INTO `produse_etichete` (`produs_id`, `eticheta_id`) VALUES
(1, 3),
(1, 4),
(2, 1),
(2, 3),
(2, 4),
(3, 3),
(3, 5),
(4, 3),
(4, 4),
(5, 3),
(5, 4),
(6, 3),
(6, 5),
(7, 3),
(7, 4),
(8, 3),
(8, 4),
(9, 3),
(9, 4),
(10, 3),
(10, 5),
(11, 3),
(11, 4),
(12, 1),
(12, 3),
(12, 4),
(13, 3),
(13, 4),
(14, 3),
(14, 4),
(14, 5),
(15, 3),
(15, 4),
(16, 3),
(16, 4),
(17, 1),
(17, 2),
(17, 4),
(18, 3),
(18, 5),
(19, 3),
(19, 4),
(20, 3),
(20, 4),
(21, 3),
(21, 4),
(22, 1),
(22, 3),
(22, 4),
(23, 3),
(23, 5),
(24, 3),
(24, 4),
(25, 2),
(25, 3),
(25, 4),
(26, 3),
(26, 4),
(27, 1),
(27, 3),
(27, 4),
(28, 3),
(28, 5),
(29, 3),
(29, 4),
(30, 3),
(30, 4);

-- --------------------------------------------------------

--
-- Table structure for table `recenzii`
--

CREATE TABLE `recenzii` (
  `id` int(11) NOT NULL,
  `produs_id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `titlu` varchar(255) NOT NULL,
  `comentariu` text NOT NULL,
  `aprobat` tinyint(1) DEFAULT 0,
  `data_adaugare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regiuni`
--

CREATE TABLE `regiuni` (
  `id` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descriere` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regiuni`
--

INSERT INTO `regiuni` (`id`, `nume`, `slug`, `descriere`) VALUES
(1, 'Transilvania', 'transilvania', 'Produse tradiționale din regiunea istorică a Transilvaniei.'),
(2, 'Muntenia', 'muntenia', 'Produse tradiționale din regiunea istorică a Munteniei.'),
(3, 'Maramureș', 'maramures', 'Produse tradiționale din regiunea istorică a Maramureșului.'),
(4, 'Banat', 'banat', 'Produse tradiționale din regiunea istorică a Banatului.'),
(5, 'Oltenia', 'oltenia', 'Produse tradiționale din regiunea istorică a Olteniei.'),
(6, 'Crișana', 'crisana', 'Produse tradiționale din regiunea istorică a Crișanei.'),
(7, 'Bucovina', 'bucovina', 'Produse tradiționale din regiunea istorică a Bucovinei.'),
(8, 'Dobrogea', 'dobrogea', 'Produse tradiționale din regiunea istorică a Dobrogei.');

-- --------------------------------------------------------

--
-- Table structure for table `resetare_parola`
--

CREATE TABLE `resetare_parola` (
  `id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `data_expirare` datetime NOT NULL,
  `folosit` tinyint(1) DEFAULT 0,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sesiuni`
--

CREATE TABLE `sesiuni` (
  `id` varchar(255) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `data_expirare` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilizatori`
--

CREATE TABLE `utilizatori` (
  `id` int(11) NOT NULL,
  `prenume` varchar(100) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `puncte_fidelitate` int(11) DEFAULT 0,
  `newsletter` tinyint(1) DEFAULT 0,
  `activ` tinyint(1) DEFAULT 1,
  `data_inregistrare` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_autentificare` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilizatori`
--

INSERT INTO `utilizatori` (`id`, `prenume`, `nume`, `email`, `parola`, `telefon`, `puncte_fidelitate`, `newsletter`, `activ`, `data_inregistrare`, `ultima_autentificare`) VALUES
(1, 'Maria', 'Popescu', 'maria.popescu@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0721234567', 320, 1, 1, '2024-06-15 12:00:00', '2024-06-15 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `vouchere`
--

CREATE TABLE `vouchere` (
  `id` int(11) NOT NULL,
  `cod` varchar(50) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `tip_discount` enum('procent','valoare') NOT NULL,
  `valoare_minima_comanda` decimal(10,2) DEFAULT NULL,
  `data_inceput` date NOT NULL,
  `data_expirare` date NOT NULL,
  `utilizari_maxime` int(11) DEFAULT NULL,
  `utilizari_curente` int(11) DEFAULT 0,
  `activ` tinyint(1) DEFAULT 1,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchere`
--

INSERT INTO `vouchere` (`id`, `cod`, `discount`, `tip_discount`, `valoare_minima_comanda`, `data_inceput`, `data_expirare`, `utilizari_maxime`, `utilizari_curente`, `activ`, `data_creare`) VALUES
(1, 'BINE10', 10.00, 'procent', 50.00, '2024-01-01', '2024-12-31', NULL, 0, 1, '2024-06-15 12:00:00'),
(2, 'VARA20', 20.00, 'procent', 100.00, '2024-06-01', '2024-08-31', 100, 0, 1, '2024-06-15 12:00:00'),
(3, 'LIVRARE', 15.00, 'valoare', 75.00, '2024-01-01', '2024-12-31', NULL, 0, 1, '2024-06-15 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `vouchere_utilizatori`
--

CREATE TABLE `vouchere_utilizatori` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `utilizator_id` int(11) NOT NULL,
  `folosit` tinyint(1) DEFAULT 0,
  `data_folosire` datetime DEFAULT NULL,
  `data_creare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adrese`
--
ALTER TABLE `adrese`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `categorii`
--
ALTER TABLE `categorii`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comenzi`
--
ALTER TABLE `comenzi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numar_comanda` (`numar_comanda`),
  ADD KEY `utilizator_id` (`utilizator_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `contacte`
--
ALTER TABLE `contacte`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizator_id` (`utilizator_id`),
  ADD KEY `produs_id` (`produs_id`),
  ADD KEY `sesiune_id` (`sesiune_id`);

--
-- Indexes for table `detalii_comenzi`
--
ALTER TABLE `detalii_comenzi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comanda_id` (`comanda_id`),
  ADD KEY `produs_id` (`produs_id`);

--
-- Indexes for table `etichete`
--
ALTER TABLE `etichete`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilizator_produs` (`utilizator_id`,`produs_id`),
  ADD KEY `produs_id` (`produs_id`);

--
-- Indexes for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produs_id` (`produs_id`);

--
-- Indexes for table `informatii_nutritionale`
--
ALTER TABLE `informatii_nutritionale`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produs_id` (`produs_id`);

--
-- Indexes for table `ingrediente`
--
ALTER TABLE `ingrediente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produs_id` (`produs_id`);

--
-- Indexes for table `istoric_puncte`
--
ALTER TABLE `istoric_puncte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `log_actiuni`
--
ALTER TABLE `log_actiuni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `produse`
--
ALTER TABLE `produse`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `regiune_id` (`regiune_id`);

--
-- Indexes for table `produse_etichete`
--
ALTER TABLE `produse_etichete`
  ADD PRIMARY KEY (`produs_id`,`eticheta_id`),
  ADD KEY `eticheta_id` (`eticheta_id`);

--
-- Indexes for table `recenzii`
--
ALTER TABLE `recenzii`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produs_utilizator` (`produs_id`,`utilizator_id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `regiuni`
--
ALTER TABLE `regiuni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `resetare_parola`
--
ALTER TABLE `resetare_parola`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `sesiuni`
--
ALTER TABLE `sesiuni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- Indexes for table `utilizatori`
--
ALTER TABLE `utilizatori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchere`
--
ALTER TABLE `vouchere`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cod` (`cod`);

--
-- Indexes for table `vouchere_utilizatori`
--
ALTER TABLE `vouchere_utilizatori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_utilizator` (`voucher_id`,`utilizator_id`),
  ADD KEY `utilizator_id` (`utilizator_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adrese`
--
ALTER TABLE `adrese`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categorii`
--
ALTER TABLE `categorii`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comenzi`
--
ALTER TABLE `comenzi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacte`
--
ALTER TABLE `contacte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalii_comenzi`
--
ALTER TABLE `detalii_comenzi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `etichete`
--
ALTER TABLE `etichete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `informatii_nutritionale`
--
ALTER TABLE `informatii_nutritionale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `ingrediente`
--
ALTER TABLE `ingrediente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `istoric_puncte`
--
ALTER TABLE `istoric_puncte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_actiuni`
--
ALTER TABLE `log_actiuni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produse`
--
ALTER TABLE `produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `recenzii`
--
ALTER TABLE `recenzii`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regiuni`
--
ALTER TABLE `regiuni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `resetare_parola`
--
ALTER TABLE `resetare_parola`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilizatori`
--
ALTER TABLE `utilizatori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vouchere`
--
ALTER TABLE `vouchere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vouchere_utilizatori`
--
ALTER TABLE `vouchere_utilizatori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adrese`
--
ALTER TABLE `adrese`
  ADD CONSTRAINT `adrese_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comenzi`
--
ALTER TABLE `comenzi`
  ADD CONSTRAINT `comenzi_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`),
  ADD CONSTRAINT `comenzi_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchere` (`id`);

--
-- Constraints for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  ADD CONSTRAINT `cos_cumparaturi_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cos_cumparaturi_ibfk_2` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detalii_comenzi`
--
ALTER TABLE `detalii_comenzi`
  ADD CONSTRAINT `detalii_comenzi_ibfk_1` FOREIGN KEY (`comanda_id`) REFERENCES `comenzi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalii_comenzi_ibfk_2` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`);

--
-- Constraints for table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  ADD CONSTRAINT `imagini_produse_ibfk_1` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `informatii_nutritionale`
--
ALTER TABLE `informatii_nutritionale`
  ADD CONSTRAINT `informatii_nutritionale_ibfk_1` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingrediente`
--
ALTER TABLE `ingrediente`
  ADD CONSTRAINT `ingrediente_ibfk_1` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `istoric_puncte`
--
ALTER TABLE `istoric_puncte`
  ADD CONSTRAINT `istoric_puncte_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_actiuni`
--
ALTER TABLE `log_actiuni`
  ADD CONSTRAINT `log_actiuni_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produse`
--
ALTER TABLE `produse`
  ADD CONSTRAINT `produse_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categorii` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `produse_ibfk_2` FOREIGN KEY (`regiune_id`) REFERENCES `regiuni` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produse_etichete`
--
ALTER TABLE `produse_etichete`
  ADD CONSTRAINT `produse_etichete_ibfk_1` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produse_etichete_ibfk_2` FOREIGN KEY (`eticheta_id`) REFERENCES `etichete` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recenzii`
--
ALTER TABLE `recenzii`
  ADD CONSTRAINT `recenzii_ibfk_1` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recenzii_ibfk_2` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resetare_parola`
--
ALTER TABLE `resetare_parola`
  ADD CONSTRAINT `resetare_parola_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sesiuni`
--
ALTER TABLE `sesiuni`
  ADD CONSTRAINT `sesiuni_ibfk_1` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchere_utilizatori`
--
ALTER TABLE `vouchere_utilizatori`
  ADD CONSTRAINT `vouchere_utilizatori_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchere` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vouchere_utilizatori_ibfk_2` FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;