
CREATE TABLE IF NOT EXISTS `catalog` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000000 ;

CREATE TABLE IF NOT EXISTS `catalog_idasc` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `value` int(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20001 ;

CREATE TABLE IF NOT EXISTS `catalog_iddesc` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `value` int(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20001 ;

CREATE TABLE IF NOT EXISTS `catalog_priceasc` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `value` int(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20001 ;

CREATE TABLE IF NOT EXISTS `catalog_pricedesc` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `value` int(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 AUTO_INCREMENT=20001 ;
