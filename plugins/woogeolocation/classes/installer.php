<?php
/**
 * WOO installer class
 *
 * @author Internet 2.0
 */
class GEO_Installer {
    private $theme_version = 1.0;
	protected function splitQueries($query)
	{
		$buffer    = array();
		$queries   = array();
		$in_string = false;
		// Trim any whitespace.
		$query = trim($query);
		// Remove comment lines.
		$query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);
		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);
		// Find function.
		$funct = explode('CREATE OR REPLACE FUNCTION', $query);
		// Save sql before function and parse it.
		$query = $funct[0];
		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($query) - 1; $i++)
		{
			if ($query[$i] == ';' && !$in_string){
				$queries[] = substr($query, 0, $i);
				$query     = substr($query, $i + 1);
				$i         = 0;
			}
			if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\"){
				$in_string = false;
			}elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")){
				$in_string = $query[$i];
			}
			if (isset ($buffer[1])){
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $query[$i];
		}
		// If the is anything left over, add it to the queries.
		if (!empty($query)){
			$queries[] = $query;
		}
		// Add function part as is.
		for ($f = 1, $fMax = count($funct); $f < $fMax; $f++)
		{
			$queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
		}
		return $queries;
	}
	function installSample($schema)
	{
		global $wpdb;		
		$return = true;
		// Get the contents of the schema file.
		$buffer = file_get_contents($schema);
		// Get an array of queries from the schema and process them.
		$queries = $this->splitQueries($buffer);
		foreach ($queries as $query)
		{
			// Trim any whitespace.
			$query = trim($query);
			// If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
			if (!empty($query) && ($query{0} != '#') && ($query{0} != '-')){
				$query = $this->convertUtf8mb4QueryToUtf8($query);
				$query = str_replace('#__', $wpdb->prefix, $query);
				$wpdb->query($query);
			}
		}
	}
	public function convertUtf8mb4QueryToUtf8($query)
	{
		return str_replace('utf8mb4', 'utf8', $query);
	}
    function do_install() {
        $this->create_geo_location_table();
        /*
         * Import all SQLs file from sql folder
         */
        require_once(dirname(__FILE__).'/files.php');
        $pathPart = dirname(dirname( __FILE__ ))  . '/data/sql/';
    	$files = Woogeolocation_files::files($pathPart, '\.sql$');
		if (empty($files)){
			return false;
		}
		foreach ($files as $file)
		{
        	$this->installSample($pathPart.$file);
		}
    }
	
	function do_uninstall() {
        $this->drop_geo_location_table();
    }
    function create_geo_location_table() {
        global $wpdb;
		$wpdb->geo_location = $wpdb->prefix . 'geo_location';
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->geo_location} (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `latitude` float(11) NOT NULL,
               `longitude` float(11) NOT NULL,
               `postalcode` varchar(50) NOT NULL,
  			   `sale_price` varchar(150) NOT NULL,
               `productid` int(14) NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $wpdb->query($sql);
    }
	function drop_geo_location_table() {
        global $wpdb;
	    $wpdb->geo_location = $wpdb->prefix . 'geo_location';
	    $sql = "DROP TABLE IF EXISTS {$wpdb->geo_location}";
	    $wpdb->query($sql);
    }
}