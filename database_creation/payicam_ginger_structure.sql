-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 11 Janvier 2016 à 14:56
-- Version du serveur: 5.5.46
-- Version de PHP: 5.6.17-1~dotdeb+7.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `payicam_ginger`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `applications`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Liste des applications et leurs clefs.' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `configs`
--

CREATE TABLE IF NOT EXISTS `configs` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `level` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(105) NOT NULL DEFAULT '',
  `nom` varchar(55) DEFAULT NULL,
  `prenom` varchar(55) DEFAULT NULL,
  `mail` varchar(105) DEFAULT NULL,
  `promo` int(5) NOT NULL DEFAULT '0',
  `filiere` varchar(55) NOT NULL DEFAULT 'Ingenieur',
  `badge_uid` varchar(20) DEFAULT NULL,
  `expiration_badge` date DEFAULT NULL,
  `sexe` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
