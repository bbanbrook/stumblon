<?php

/**
 * WOO installer class
 *
 * @author Internet 2.0
 */
class GEO_Installer {

    private $theme_version = 1.0;

    function do_install() {
        $this->create_tables();
    }
	
	function do_uninstall() {
        $this->drop_tables();
    }

    function create_tables() {
        $this->create_geo_location_table();
    }
	
	function drop_tables() {
		$this->drop_geo_location_table();
    }

    function create_geo_location_table() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->geo_location} (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `latitude` float(11) NOT NULL,
               `longitude` float(11) NOT NULL,
               `postalcode` varchar(50) NOT NULL,
               `productid` int(14) NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $wpdb->query($sql);
    }
	
	function drop_geo_location_table() {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS {$wpdb->geo_location}";
        $wpdb->query($sql);
    }
}



