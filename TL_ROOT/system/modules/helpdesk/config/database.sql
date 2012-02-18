-- Contao Helpdesk :: Database setup file
--
-- NOTE: this file was edited with tabs set to 4.
-- Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
-- See accompaning file LICENSE.txt
--
-- NOTE: this file was edited with tabs set to 4.
-- 
-- **********************************************************
-- *      ! ! !   I M P O R T A N T  N O T E   ! ! !        *
-- *                                                        *
-- * Do not import this file manually! Use the Contao    *
-- * install tool to create and maintain database tables:   *
-- * - Point your browser to                                *
-- *   http://www.yourdomain.com/Contao/install.php      *
-- * - Enter the installation password and click "Login"    *
-- * - Scroll down and click button "Update Database"       *
-- **********************************************************

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_settings`
-- 

CREATE TABLE `tl_helpdesk_settings` (
  `id` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `version` int(9) NOT NULL default '0',
  `tpage` int(6) NOT NULL default '30',
  `mpage` int(6) NOT NULL default '15',
  `spage` int(6) NOT NULL default '10',
  `pagenavctl` char(1) NOT NULL default '1',
  `pagenavsize` int(6) NOT NULL default '7',
  `edits` char(1) NOT NULL default '2',
  `editswait` int(6) NOT NULL default '300',
  `postdelay` int(6) NOT NULL default '15',
  `searchdelay` int(6) NOT NULL default '30',
  `searchmax` int(6) NOT NULL default '100',
  `tlsearch` char(1) NOT NULL default '',
  `recenthours` int(6) NOT NULL default '24',
  `images` varchar(255) NOT NULL default '',
  `feeds` char(1) NOT NULL default '',
  `feedmax` int(6) NOT NULL default '10',
  `feedlimit` int(6) NOT NULL default '150',
  `feedlink` varchar(64) NOT NULL default 'helpdesk',
  `feedtitle` varchar(64) NOT NULL default 'Contao Syndication',
  `feeddescription` varchar(255) NOT NULL default 'RSS feeds from the forums and helpdesks',
  `logging` char(1) NOT NULL default '0',
  `tot_tickets` int(10) unsigned NOT NULL default '0',
  `tot_messages` int(10) unsigned NOT NULL default '0',
  `tot_members` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_categories`
-- 

CREATE TABLE `tl_helpdesk_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `header` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `description` text NULL,
  `buttons` text NULL,
  `access` char(1) NOT NULL default '0',
  `replyonly` char(1) NOT NULL default '',
  `published` char(1) NOT NULL default '',
  `be_clients` blob NULL,
  `fe_clients` blob NULL,
  `be_supporters` blob NULL,
  `fe_supporters` blob NULL,
  `feed` char(1) NOT NULL default '',
  `atch` char(1) NOT NULL default '',
  `atch_dir` varchar(100) NOT NULL default 'tl_files/helpdesk',
  `atch_size` int(10) unsigned NOT NULL default '100000',
  `atch_types` varchar(255) NOT NULL default 'txt,zip,rar,pdf,jpg,png,gif',
  `notify` char(1) NOT NULL default '',
  `notify_atch` char(1) NOT NULL default '1',
  `notify_name` varchar(100) NOT NULL default '',
  `notify_sender` varchar(100) NOT NULL default '',
  `notify_fe_url` varchar(100) NOT NULL default '',
  `notify_be_url` varchar(100) NOT NULL default '',
  `notify_astext` char(1) NOT NULL default '',
  `notify_newsubj` varchar(100) NOT NULL default '',
  `notify_newtext` text NULL,
  `notify_replysubj` varchar(100) NOT NULL default '',
  `notify_replytext` text NULL,
  `import` char(1) NOT NULL default '',
  `import_atch` char(1) NOT NULL default '1',
  `import_server` varchar(100) NOT NULL default '',
  `import_port` varchar(5) NOT NULL default '',
  `import_type` char(1) NOT NULL default '0',
  `import_tls` char(1) NOT NULL default '0',
  `import_username` varchar(100) NOT NULL default '',
  `import_password` varchar(100) NOT NULL default '',
  `import_email` varchar(100) NOT NULL default '',
  `pub_tickets` int(10) unsigned NOT NULL default '0',
  `pub_replies` int(10) unsigned NOT NULL default '0',
  `pub_latest` int(10) unsigned NOT NULL default '0',
  `all_tickets` int(10) unsigned NOT NULL default '0',
  `all_replies` int(10) unsigned NOT NULL default '0',
  `all_latest` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_tickets`
-- 

CREATE TABLE `tl_helpdesk_tickets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `client` varchar(64) NOT NULL default '',
  `client_be` char(1) NOT NULL default '',
  `supporter` varchar(64) NOT NULL default '',
  `supporter_be` char(1) NOT NULL default '1',
  `subject` varchar(100) NOT NULL default '',
  `status` char(1) NOT NULL default '0',
  `published` char(1) NOT NULL default '',
  `pub_replies` int(10) unsigned NOT NULL default '0',
  `pub_latest` int(10) unsigned NOT NULL default '0',
  `all_replies` int(10) unsigned NOT NULL default '0',
  `all_latest` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `client` (`client`),
  KEY `supporter` (`supporter`),
  FULLTEXT KEY `subject` (`subject`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_messages`
-- 

CREATE TABLE `tl_helpdesk_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `reply` char(1) NOT NULL default '',
  `by_email` char(1) NOT NULL default '',
  `poster` varchar(64) NOT NULL default '',
  `poster_cd` char(1) NOT NULL default '0',
  `message` text NULL,
  `atch1name` varchar(64) NOT NULL default '',
  `atch2name` varchar(64) NOT NULL default '',
  `atch3name` varchar(64) NOT NULL default '',
  `atch4name` varchar(64) NOT NULL default '',
  `atch5name` varchar(64) NOT NULL default '',
  `published` char(1) NOT NULL default '',
  `edited` int(10) unsigned NOT NULL default '0',
  `editor` varchar(64) NOT NULL default '',
  `editor_cd` char(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  FULLTEXT KEY `message` (`message`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_notifys`
-- 

CREATE TABLE `tl_helpdesk_notifys` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_notifieds`
-- 

CREATE TABLE `tl_helpdesk_notifieds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `email` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_categorymarks`
-- 

CREATE TABLE `tl_helpdesk_categorymarks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `backend` char(1) NOT NULL default '',
  `message` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_ticketmarks`
-- 

CREATE TABLE `tl_helpdesk_ticketmarks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `backend` char(1) NOT NULL default '',
  `message` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_comments`
-- 

CREATE TABLE `tl_helpdesk_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `item_type` varchar(32) NOT NULL default '',
  `item_id` int(10) unsigned NOT NULL default '0',
  `ticket` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `item_type` (`item_type`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_helpdesk_floodcontrol`
-- 

CREATE TABLE `tl_helpdesk_floodcontrol` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` varchar(64) NOT NULL default '',
  `action` varchar(10) NOT NULL default '',
  `tstamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_member`
-- 

CREATE TABLE `tl_member` (
  `helpdesk_timezone` varchar(64) NOT NULL default '',
  `helpdesk_role` varchar(64) NOT NULL default '',
  `helpdesk_signature` text NULL,
  `helpdesk_showrealname` char(1) NOT NULL default '',
  `helpdesk_showlocation` char(1) NOT NULL default '',
  `helpdesk_subscriptions` blob NULL,
  `helpdesk_postcount` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_user`
-- 

CREATE TABLE `tl_user` (
  `helpdesk_timezone` varchar(64) NOT NULL default '',
  `helpdesk_role` varchar(64) NOT NULL default '',
  `helpdesk_location` varchar(64) NOT NULL default '',
  `helpdesk_signature` text NULL,
  `helpdesk_showrealname` char(1) NOT NULL default '',
  `helpdesk_showlocation` char(1) NOT NULL default '',
  `helpdesk_subscriptions` blob NULL,
  `helpdesk_postcount` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_module`
-- 

CREATE TABLE `tl_module` (
  `helpdesk_text` text NULL,
  `helpdesk_links` char(1) NOT NULL default '1',
  `helpdesk_categories` blob NULL,
  `helpdesk_hideempty` char(1) NOT NULL default '',
  `helpdesk_profmode` char(1) NOT NULL default '',
  `helpdesk_profpage` varchar(100) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `helpdesk_reference` varchar(32) NOT NULL default 'article',
  `helpdesk_category` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
