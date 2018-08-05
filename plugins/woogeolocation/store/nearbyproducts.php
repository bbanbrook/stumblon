<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class neoweb_Widget_Product extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'woocommerce_ndbProducts',
            __('Woogeolocation Products Near Me', 'ndb'),
            array('description' => __('This plugin help display near by products on your website.', 'ndb'))
        );
    }
	function get_lat_long($address){
	    $address = str_replace(" ", "+", $address);
	    $url     = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$json = curl_exec($ch);
			curl_close($ch);
		}else if(function_exists('file_get_contents')){
			$json = file_get_contents($url);
		}else{
			die(__('Please to enable curl or file_get_contents function!', 'woogeolocation'));
		}
	    $json = json_decode($json);
	    $lat  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
	    $lon  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
	    return Array('lat' => $lat, 'lon' => $lon);
	}
    public function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $ids   = $instance['totalnoof'] * 1;
        $ids   = ($ids > 0) ? $ids : 20;
        $ids   = $ids ? $ids : 20;

        global $wpdb;
        $tablePrefix      = $wpdb->prefix;
        $admin_latitude   = get_option('woogeo_dokan_default_latitude',true);
        $admin_longitude  = get_option('woogeo_dokan_default_longitude',true);
        $admin_postalcode = get_option('woogeo_dokan_default_postalcode',true);

    	if(isset($_GET['_address'])){
        	$address = $_GET['_address'];
        }else if($atts['search_location_autodetect_address'] == 'yes' && store_get_client_ip_env() != ''){
        	$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.store_get_client_ip_env()));
        	$address = $query['city'].', '.$query['country'];
        }else if($atts['search_location_default'] != ''){
        	$address = $atts['search_location_default'];
        }else{
        	$address = get_option('woogeo_dokan_default_address');
        }
		$array_values               = get_lat_long($address);
		$array_values['lat']        = ($array_values['lat']) ? $array_values['lat'] : $admin_latitude;
		$array_values['lon']        = ($array_values['lon']) ? $array_values['lon'] : $admin_longitude;
		$array_values['postalcode'] = ($_GET['_postalcode']) ? $_GET['_postalcode'] : $admin_postalcode;
        $querystr = "SELECT post_id FROM {$wpdb->postmeta}"; //this is case when there is no radious condition
        if(!isset($_GET['showall'])){
            if ($array_values['lat'] == '' && $array_values['lon'] == '') {
				if ($array_values['postalcode'] != ''){
					$querystr .= "where(meta_value='" . $postalcode . "')";
					$querystr .= "GROUP BY post_id";
				}
            } else {
                $distance = (int) $_GET['radious'];
                $querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
                                   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
                                    HAVING distance < '%s'  ORDER BY distance LIMIT 0 ," . $ids . " ";
                $querystr = sprintf($querystr, $latitude, $longitude, $latitude, ($distance));
            }
        }
        $pageposts = $wpdb->get_results($querystr, OBJECT);
        if($pageposts):$productArray = array();
            echo $before_widget;
            if($title)
                echo $before_title . $title . $after_title;
            echo "<div class='woocommerce widget_product'>";
            $totalFound = 0;
            echo '<ul class="product_list_widget">';
            foreach($pageposts as $prd)
            {
                $product = new WC_product($prd->post_id);
                if(!$product->post) {
                    continue;
                }
                $productArray[] = $prd->post_id;
                ?>
                <li>
                    <a href="<?php echo esc_url(get_permalink($product->id));?>" title="<?php echo esc_attr($product->get_title());?>">
                        <?php echo $product->get_image(); ?>
                        <?php $str = mb_strtoupper($product->get_title(), 'UTF-8');
                        echo '<b>' . $str . '</b></a><br/>' ?>
                        <?php echo $product->get_rating_html(); ?>
                        <?php echo '<p style="color: #85c94f; text-align: left"> <b>'.$product->get_price_html().'</b></p>' ?>
                        <?php if(!$product->is_purchasable()){
                            return;
                        }
                        // Availability
                        $availability      = $product->get_availability();
                        $availability_html = empty($availability['availability'])?'':'<p class="stock '.esc_attr($availability['class']).'">'.esc_html($availability['availability']).'</p>';
                        echo apply_filters('woocommerce_stock_html', $availability_html, $availability['availability'], $product); ?>
                        <?php if($product->is_in_stock()):?>
                            <?php $terms = get_the_terms($product->id, 'product_cat');
                            foreach($terms as $term) {
                                $product_cat = $term->name;
                                break;
                            }
                            $author              = get_user_by('id', $product->post->post_author);
                            $store_info          = dokan_get_store_info($author->ID);
                            $pv_cerified_enabled = isset($store_info['pv_certified_enabled'])? esc_attr($store_info['pv_certified_enabled']):'no';
                            if($product_cat == 'Médicaments' && $pv_cerified_enabled == 'no'){
                                printf('<br><a class="button ywctm-custom-button ", href="'.get_permalink($product->id).'"><span class="ywctm-inquiry-title"><b>'._e('Booking only', 'woogeolocation').'</b></span></a>');
                            }else{
//                                do_action('woocommerce_before_add_to_cart_form'); ?>
<!--                                <form class="cart" method="post" enctype='multipart/form-data'>-->
<!--                                    <input type="hidden" name="add-to-cart" value="--><?php //echo esc_attr($product->id); ?><!--"/>-->
<!--                                    <button type="submit" class="single_add_to_cart_button button alt">--><?php //echo esc_html($product->single_add_to_cart_text()); ?><!--</button>-->
<!--                                </form>-->
<!--                                --><?php //do_action('woocommerce_after_add_to_cart_form');
                            }
                        endif; ?>
                </li>
                <?php
                global $product;
                $author     = get_user_by('id', $product->post->post_author);
                $store_info = dokan_get_store_info($author->ID);
                ?>
                <li>
                    <?php
                    if(!empty($store_info['store_name'])) {
                        ?>
                        <span class="details">
                            <?php echo $store_info['price']; ?>
                            <?php printf('<a href="%s">%s</a>', dokan_get_store_url($author->ID), $author->display_name);?>
                        </span>
                        <?php
                    }
                    ?>
                </li>
                <br>
                <?php
                $totalFound++;
            }
            if($totalFound == 0) {
                echo "<li>".__('Aucun produit à proximité', 'woogeolocation')."</li>";
            }
            echo '</ul>';
            echo '</div>';
            echo $after_widget;
        endif;
        wp_reset_postdata();
        //	echo $content;
    }

    // Widget Backend
    public function form($instance)
    {
        $title     = (isset($instance['title']))?$instance['title'] : __('Products Near Me', 'woogeolocation');
        $totalnoof = $instance['totalnoof'];?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('totalnoof'); ?>"><?php _e('Total No of products :', 'woogeolocation'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('totalnoof'); ?>"
                   name="<?php echo $this->get_field_name('totalnoof'); ?>" type="text"
                   value="<?php echo esc_attr($totalnoof); ?>"/>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['totalnoof'] = (!empty($new_instance['totalnoof'])) ? strip_tags($new_instance['totalnoof']) : '';
        return $instance;
    }
}

function neoweb_Widget_Product_register_widgets()
{
    register_widget('neoweb_Widget_Product');
}
//add_action('widgets_init', 'neoweb_Widget_Product_register_widgets');