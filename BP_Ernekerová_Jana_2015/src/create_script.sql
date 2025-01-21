-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Verze MySQL: 5.5.41
-- Verze PHP: 5.3.10-1ubuntu3.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `ernekjan`
--

CREATE DATABASE ernekjan;

-- --------------------------------------------------------

--
-- Struktura tabulky `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(256) NOT NULL,
  `user_file_name` varchar(256) NOT NULL,
  `file_name` varchar(256) NOT NULL,
  `has_header` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
