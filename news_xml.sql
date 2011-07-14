
CREATE TABLE `news_xml` (
  `id` tinyint(4) NOT NULL auto_increment,
  `title` text NOT NULL,
  `link` text NOT NULL,
  `description` text NOT NULL,
  `pubDate` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
