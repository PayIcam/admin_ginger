-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 29 Mai 2014 à 15:11
-- Version du serveur: 5.6.12-log
-- Version de PHP: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `payicam_ginger`
--
CREATE DATABASE IF NOT EXISTS `payicam_ginger` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `payicam_ginger`;

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

DROP TABLE IF EXISTS `administrateurs`;
CREATE TABLE IF NOT EXISTS `administrateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(55) NOT NULL,
  `prenom` varchar(55) NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int(2) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `app_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id interne de l''application',
  `app_url` varchar(255) DEFAULT NULL COMMENT 'url du service autorisé',
  `app_key` char(32) NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `app_desc` text,
  `app_creator` varchar(8) NOT NULL COMMENT 'Login de l''utilisateur ayant crÃ©e la clef.',
  `app_lastuse` datetime NOT NULL COMMENT 'Date de la dernière utilisation de cette clef.',
  `app_created` datetime NOT NULL COMMENT 'Date de création de la clef',
  `app_removed` datetime DEFAULT NULL COMMENT 'Est ce que la clef est supprimée.',
  PRIMARY KEY (`app_id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Liste des applications et leurs clefs.' AUTO_INCREMENT=2 ;

--
-- Contenu de la table `applications`
--

INSERT INTO `applications` (`app_id`, `app_url`, `app_key`, `app_name`, `app_desc`, `app_creator`, `app_lastuse`, `app_created`, `app_removed`) VALUES
(1, 'http://localhost/icam/payicam/test_ginger', 'test_ginger', 'test_ginger', 'test de ginger', 'Antoine', '2014-05-24 00:00:00', '2014-05-24 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `configs`
--

DROP TABLE IF EXISTS `configs`;
CREATE TABLE IF NOT EXISTS `configs` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `configs`
--

INSERT INTO `configs` (`name`, `value`) VALUES
('authentification', '1'),
('contact', 'contact@payicam.fr'),
('inscriptions', ''),
('maintenance', '0'),
('websitename', 'Admin Ginger');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `level` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `level`) VALUES
(1, 'Administrateur', 'admin', 2),
(2, 'Membre', 'member', 1),
(3, 'Non inscrit', 'non-inscrit', 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(105) NOT NULL DEFAULT '',
  `nom` varchar(55) DEFAULT NULL,
  `prenom` varchar(55) DEFAULT NULL,
  `mail` varchar(105) DEFAULT NULL,
  `badge_uid` varchar(8) DEFAULT NULL,
  `expiration_badge` date DEFAULT NULL,
  PRIMARY KEY (`login`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
