-- --------------------------------------------------------

--
-- Table structure for table `ibm_revenuelineitems_products`
--

CREATE TABLE IF NOT EXISTS `ibm_revenuelineitems_products` (
  `id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` int(10) NOT NULL,
  `parent_id` varchar(32) NOT NULL,
  `code` varchar(128) NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
