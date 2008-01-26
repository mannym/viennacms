-- phpMyAdmin SQL Dump
-- version 2.9.1.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generatie Tijd: 16 Nov 2007 om 09:38
-- Server versie: 5.0.27
-- PHP Versie: 5.2.0
-- 
-- Database: `viennacms_dev`
-- 

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `viennacms_node_options`
-- 

CREATE TABLE `viennacms_node_options` (
  `option_id` int(11) NOT NULL auto_increment,
  `node_id` int(11) NOT NULL,
  `option_name` varchar(255) collate latin1_general_ci NOT NULL,
  `option_value` text collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `viennacms_node_options`
-- 

INSERT INTO `viennacms_node_options` (`option_id`, `node_id`, `option_name`, `option_value`) VALUES 
(1, 1, 'hostname', '');


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `viennacms_node_revisions`
-- 

CREATE TABLE `viennacms_node_revisions` (
  `revision_id` int(11) NOT NULL auto_increment,
  `node_id` int(11) NOT NULL,
  `revision_number` int(11) NOT NULL,
  `node_content` longtext collate latin1_general_ci NOT NULL,
  `revision_date` int(11) NOT NULL,
  PRIMARY KEY  (`revision_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `viennacms_nodes`
-- 

CREATE TABLE `viennacms_nodes` (
  `node_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate latin1_general_ci NOT NULL,
  `title_clean` varchar(255) collate latin1_general_ci NOT NULL,
  `parentdir` text collate latin1_general_ci NOT NULL,
  `extension` varchar(20) collate latin1_general_ci NOT NULL,
  `description` varchar(255) collate latin1_general_ci NOT NULL,
  `created` int(11) NOT NULL,
  `type` varchar(40) collate latin1_general_ci NOT NULL,
  `parent_id` int(11) NOT NULL,
  `revision_number` int(11) NOT NULL,
  PRIMARY KEY  (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;


-- 
-- Gegevens worden uitgevoerd voor tabel `viennacms_nodes`
-- 

INSERT INTO `viennacms_nodes` (`node_id`, `title`, title_clean, `description`, `created`, `type`, `parent_id`, `revision_number`) VALUES 
(1, 'viennaCMS default site name', 'root-node', 'The root of all nodes', 1195133281, 'site', 0, 0);


-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `viennacms_users`
--

CREATE TABLE `viennacms_users` (
  `userid` mediumint(10) NOT NULL auto_increment,
  `username` varchar(20) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(50) NOT NULL,
  `lang` varchar(6) NOT NULL,
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Gegevens worden uitgevoerd voor tabel `viennacms_users`
--

INSERT INTO `viennacms_users` (`userid`, `username`, `password`, `email`) VALUES
(1, 'admin', '200ceb26807d6bf99fd6f4f0d1ca54d4', 'foo@bar.com');
-- Standaard gebruiker, gebruikersnaam: admin, wachtwoord: administrator

--
-- Tabel structuur voor tabel `viennacms_uploads`
--


CREATE TABLE `viennacms_uploads` (
  `upload_id` bigint(20) NOT NULL auto_increment,
  `filename` varchar(120) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `type` varchar(50) NOT NULL,
  `time` bigint(20) NOT NULL,
  `downloaded` mediumint(10) NOT NULL,
  PRIMARY KEY  (`upload_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


--
-- Tabel structuur voor tabel `viennacms_downloads`
--

CREATE TABLE `viennacms_downloads` (
  `download_id` tinyint(15) NOT NULL auto_increment,
  `file_id` tinyint(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `forwarded_for` varchar(15) NOT NULL,
  `user_agent` mediumtext NOT NULL,
  `referer` mediumtext NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;