CREATE TABLE IF NOT EXISTS #__geo_location (
   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `latitude` float(11) NOT NULL,
   `longitude` float(11) NOT NULL,
   `postalcode` varchar(50) NOT NULL,
   `sale_price` varchar(150) NOT NULL,
   `productid` int(14) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;