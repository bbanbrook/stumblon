from bs4 import BeautifulSoup
import urllib2
import unicodedata
import sys  
from time import mktime
import time
from datetime import datetime

pid_list = [129,135,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274]
for x in pid_list:
    print """
    DELETE FROM wp_geo_location WHERE productid=%d;
    DELETE FROM `wp_postmeta` WHERE post_id=%d AND meta_key in ('_latitude','_address','_longitude','_postalcode');
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (%d, '_address', 'Newbury Park');
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (%d, '_latitude', '34.1823703');
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (%d, '_longitude', '-118.9190327');
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (%d, '_postalcode', '91320');
    insert into wp_geo_location (`latitude`, `longitude`, `postalcode`, `productid`) VALUES ('34.1823703','-118.9190327','91320','%d');    
    """ % (x,x,x,x,x,x,x)
    ##############Geolocation insert
    #DELETE FROM wp_geo_location WHERE productid=274
    #INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (274, '_address', 'Newbury Park')    
    #INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (274, '_latitude', '34.1823703')
    #INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (274, '_longitude', '-118.9190327')
    #INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (274, '_postalcode', '91320')
    #insert into wp_geo_location (`latitude`, `longitude`, `postalcode`, `productid`) VALUES ('34.1823703','-118.9190327','91320','274')
    
    
    ##############Category insert
    #UPDATE `wp_term_taxonomy`
    #SELECT tt.term_id, tt.term_taxonomy_id FROM wp_terms AS t INNER JOIN wp_term_taxonomy as tt ON tt.term_id = t.term_id WHERE t.term_id = 39 AND tt.taxonomy = 'pa_time'
    
    #DELETE
    #SELECT t.*, tt.* FROM wp_terms AS t INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ('pa_time') AND tr.object_id IN (274)
    #SELECT tt.term_id FROM wp_term_taxonomy AS tt WHERE tt.taxonomy = 'pa_time' AND tt.term_taxonomy_id IN ('40')
    #SELECT tt.term_id, tt.term_taxonomy_id FROM wp_terms AS t INNER JOIN wp_term_taxonomy as tt ON tt.term_id = t.term_id WHERE t.term_id = 39 AND tt.taxonomy = 'pa_time'
    #DELETE FROM wp_term_relationships WHERE object_id = 274 AND term_taxonomy_id IN ('40')
    #UPDATE `wp_term_taxonomy` SET `count` = 0 WHERE `term_taxonomy_id` = 40
    
    #ADD
    #SELECT t.*, tt.* wp_terms AS t INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.term_id = 39
    #SELECT tt.term_id, tt.term_taxonomy_id FROM wp_terms AS t INNER JOIN wp_term_taxonomy as tt ON tt.term_id = t.term_id WHERE t.term_id = 39 AND tt.taxonomy = 'pa_time'
    #SELECT term_taxonomy_id FROM wp_term_relationships WHERE object_id = 274 AND term_taxonomy_id = 40
    #INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`) VALUES (274, 40)
    #UPDATE `wp_term_taxonomy` SET `count` = 1 WHERE `term_taxonomy_id` = 40
    
    
    #################Image insert
    #INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES (257, '_thumbnail_id', '285')
    
    ## Hot Yoga
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`)
    select p.`ID`, '_thumbnail_id', '341' from wp_posts p
    	left join wp_usermeta m on p.`post_author` = m.`user_id`
    	where  m.meta_key = "dokan_store_name"
    	and m.meta_value = "Hot Yoga 1000"
    	and p.`ID` not in
    		(select `post_id` from `wp_postmeta` where `meta_key` = '_thumbnail_id' group by `post_id` having count(`post_id`) >0)	
		
    # CorePower
    INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`)
    select p.`ID`, '_thumbnail_id', '343' from wp_posts p
    	left join wp_usermeta m on p.`post_author` = m.`user_id`
    	where  m.meta_key = "dokan_store_name"
    	and m.meta_value = "CorePower"
    	and p.`ID` not in
    		(select `post_id` from `wp_postmeta` where `meta_key` = '_thumbnail_id' group by `post_id` having count(`post_id`) >0)	
	
	
    select p.`ID`, pm.`meta_value` from wp_posts p
    	left join wp_usermeta m on p.`post_author` = m.`user_id`
    	left join wp_postmeta pm on p.`ID` = pm.post_id 
    	where  m.meta_key = "dokan_store_name"
    	and m.meta_value = "CorePower"
    	and pm.meta_key = "_thumbnail_id"