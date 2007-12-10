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
(1, 1, 'hostname', 'localhost');


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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=9 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `viennacms_node_revisions`
-- 

INSERT INTO `viennacms_node_revisions` (`revision_id`, `node_id`, `revision_number`, `node_content`, `revision_date`) VALUES 
(1, 2, 1, 'The testing revision :)', 1195144352),
(8, 11, 1, 'The content of this nice third child node :D', 1195152668);

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=24 ;


-- 
-- Gegevens worden uitgevoerd voor tabel `viennacms_nodes`
-- 

INSERT INTO `viennacms_nodes` (`node_id`, `title`, title_clean, `description`, `created`, `type`, `parent_id`, `revision_number`) VALUES 
(1, 'The one root node', 'root-node', 'The root of all nodes', 1195133281, 'site', 0, 0),
(2, 'Child node 1', 'child-node-1', 'An node under the root node.', 1195133299, 'page', 1, 1),
(3, 'Child node 2', 'child-node-2', 'Another node under the root node.', 1195133313, 'page', 1, 0),
(4, 'Sub-sub node', 'sub-sub-node', 'An node under a child node', 1195133332, 'page', 3, 0),
(11, 'Third child node', 'third-child-node', 'An third child node :)', 1195152668, 'page', 1, 1);


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
  `time` mediumint(12) NOT NULL,
  PRIMARY KEY  (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;