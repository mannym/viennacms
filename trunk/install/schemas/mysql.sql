CREATE TABLE `viennacms_config` (
  `config_name` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY  (`config_name`)
);

CREATE TABLE `viennacms_node_options` (
  `option_id` int(11) NOT NULL auto_increment,
  `node_id` int(11) NOT NULL,
  `option_name` varchar(255) collate latin1_general_ci NOT NULL,
  `option_value` text collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;


CREATE TABLE `viennacms_node_revisions` (
  `revision_id` int(11) NOT NULL auto_increment,
  `node_id` int(11) NOT NULL,
  `revision_number` int(11) NOT NULL,
  `node_content` longtext collate latin1_general_ci NOT NULL,
  `revision_date` int(11) NOT NULL,
  PRIMARY KEY  (`revision_id`)
) ENGINE=MyISAM;


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
  `node_order` int(11) NOT NULL,
  PRIMARY KEY  (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;


CREATE TABLE `viennacms_users` (
  `userid` mediumint(10) NOT NULL auto_increment,
  `username` varchar(20) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(50) NOT NULL,
  `lang` varchar(6) NOT NULL,
  `login_attempts` MEDIUMINT NOT NULL, 
   `last_login_attempt` INT(11) NOT NULL,
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

CREATE TABLE `viennacms_downloads` (
  `download_id` int(15) NOT NULL auto_increment,
  `file_id` int(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `forwarded_for` varchar(15) NOT NULL,
  `user_agent` mediumtext NOT NULL,
  `referer` mediumtext NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;