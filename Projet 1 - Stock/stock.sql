-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 13 avr. 2026 à 18:09
-- Version du serveur : 10.11.11-MariaDB
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `stock`
--

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

CREATE TABLE `fournisseurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mouvements`
--

CREATE TABLE `mouvements` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `type` enum('entree','sortie') NOT NULL,
  `quantite` decimal(10,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unite` varchar(20) DEFAULT 'kg',
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `quantite` decimal(10,2) DEFAULT 0.00,
  `seuil_alerte` decimal(10,2) DEFAULT 5.00,
  `fournisseur_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `description`, `unite`, `prix_unitaire`, `quantite`, `seuil_alerte`, `fournisseur_id`, `created_at`) VALUES
(1, 'Tomates', 'Tomates fraîches pour salades et sauces', 'kg', 2.50, 30.00, 10.00, NULL, '2026-02-27 10:17:36'),
(2, 'Mozzarella', 'Mozzarella di bufala', 'kg', 8.00, 12.00, 5.00, NULL, '2026-02-27 10:17:36'),
(3, 'Poulet', 'Blanc de poulet frais', 'kg', 9.50, 25.00, 8.00, NULL, '2026-02-27 10:17:36'),
(4, 'Steak haché', 'Bœuf haché 15% MG', 'kg', 11.00, 18.00, 6.00, NULL, '2026-02-27 10:17:36'),
(5, 'Saumon', 'Filet de saumon frais', 'kg', 16.00, 9.00, 5.00, NULL, '2026-02-27 10:17:36'),
(6, 'Farine', 'Farine type 45', 'kg', 1.20, 50.00, 15.00, NULL, '2026-02-27 10:17:36'),
(7, 'Huile d\'olive', 'Huile d’olive extra vierge', 'L', 7.50, 20.00, 5.00, NULL, '2026-02-27 10:17:36'),
(8, 'Beurre', 'Beurre doux', 'kg', 6.00, 7.00, 5.00, NULL, '2026-02-27 10:17:36'),
(9, 'Œufs', 'Œufs frais calibre M', 'piece', 0.25, 200.00, 50.00, NULL, '2026-02-27 10:17:36'),
(10, 'Pâtes', 'Penne rigate', 'kg', 1.80, 40.00, 10.00, NULL, '2026-02-27 10:17:36'),
(11, 'Riz', 'Riz basmati', 'kg', 2.20, 35.00, 10.00, NULL, '2026-02-27 10:17:36'),
(12, 'Crème fraîche', 'Crème entière 30%', 'L', 3.50, 6.00, 5.00, NULL, '2026-02-27 10:17:36'),
(13, 'Champignons', 'Champignons de Paris', 'kg', 4.00, 14.00, 6.00, NULL, '2026-02-27 10:17:36'),
(14, 'Oignons', 'Oignons jaunes', 'kg', 1.90, 22.00, 8.00, NULL, '2026-02-27 10:17:36'),
(15, 'Fromage râpé', 'Mélange spécial pizza', 'kg', 7.00, 5.00, 5.00, NULL, '2026-02-27 10:17:36'),
(16, 'test', NULL, 'kg', 25.00, 1.00, 2.00, NULL, '2026-02-27 10:22:09');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `lastname`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'alexisit', 'VERDIERE', 'Alexis', 'alexis.verdiere@gmail.com', '$2y$10$M9J/kXdFl7URXDqTn9ixD.vukMgv25vYJJX2xRgnoGtyk6KkBKq4u', 'admin', '2025-10-22 16:06:01'),
(2, 'adminit', 'It', 'Admin', 'admin@admin.fr', '$2y$10$0ZAG19SPIQwkQmzmnCT0H.hPDEImROLMeOIFTZZ37rFOnAof99f/y', 'admin', '2025-10-30 14:42:28'),
(3, 'test', 'test', 'test', 'test@test.fr', '$2y$10$S1FyqGhJ1qMng/aWiBqdb.ZAJC.cIFhX6qSgc3XuYSSv3exph1FjC', NULL, '2026-01-28 14:30:34');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mouvements`
--
ALTER TABLE `mouvements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fournisseur_id` (`fournisseur_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mouvements`
--
ALTER TABLE `mouvements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `mouvements`
--
ALTER TABLE `mouvements`
  ADD CONSTRAINT `mouvements_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
