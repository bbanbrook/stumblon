<?php
///this is used for making the category filters
function neowebsolution_product_subcategories_filter( $args = array() )
{
    //this is to confirm it's category page
    if (is_product_category())
    {
		$cate = get_queried_object();
		$cateID = $cate->term_id;
		echo do_shortcode('[make_filter_html map="no" product_listing="yes" category="yes" category_id="'.$cateID.'"]');
    }
}
add_action( 'woocommerce_before_shop_loop', 'neowebsolution_product_subcategories_filter', 50 );