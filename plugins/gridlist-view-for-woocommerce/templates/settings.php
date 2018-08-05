<div class="wrap">
<?php 
$dplugin_name = 'WooCommerce Grid/List View';
$dplugin_link = 'http://berocket.com/product/woocommerce-grid-list-view';
$dplugin_price = 16;
$dplugin_lic   = 3;
$dplugin_desc = '';
@ include 'settings_head.php';
@ include 'discount.php';
?>
<div class="wrap show_premium">  
    <div id="icon-themes" class="icon32"></div>  
    <h2>Grid/List View Settings</h2>  
    <?php settings_errors(); ?>  

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? @ $_GET[ 'tab' ] : 'buttons'; ?>  

    <h2 class="nav-tab-wrapper">  
        <a href="?page=br-list-grid-view&tab=buttons" class="nav-tab <?php echo $active_tab == 'buttons' ? 'nav-tab-active' : ''; ?>"><?php _e('Buttons', 'BeRocket_LGV_domain') ?></a>
        <a href="?page=br-list-grid-view&tab=product_count" class="nav-tab <?php echo $active_tab == 'product_count' ? 'nav-tab-active' : ''; ?>"><?php _e('Product Count', 'BeRocket_LGV_domain') ?></a>
        <a href="?page=br-list-grid-view&tab=javascript" class="nav-tab <?php echo $active_tab == 'javascript' ? 'nav-tab-active' : ''; ?>"><?php _e('JavaScript', 'BeRocket_LGV_domain') ?></a>
    </h2>  

    <form class="lgv_submit_form" method="post" action="options.php">  
        <?php 
        if( $active_tab == 'buttons' ) { 
            settings_fields( 'br_lgv_buttons_page_option' );
            do_settings_sections( 'br_lgv_buttons_page_option' );
            echo '<input type="submit" class="button-primary" value="' . __('Save Changes', 'BeRocket_LGV_domain') . '" />';
        } else if( $active_tab == 'product_count' ) {
            settings_fields( 'br_lgv_product_count_option' );
            do_settings_sections( 'br_lgv_product_count_option' );
            echo '<input type="submit" class="button-primary" value="' . __('Save Changes', 'BeRocket_LGV_domain') . '" />';
        } else if( $active_tab == 'liststyle' ) {
            settings_fields( 'br_lgv_liststyle_option' );
            do_settings_sections( 'br_lgv_liststyle_option' ); 
        } else if( $active_tab == 'javascript' ) {
            settings_fields( 'br_lgv_javascript_option' );
            do_settings_sections( 'br_lgv_javascript_option' );
            echo '<input type="submit" class="button-primary" value="' . __('Save Changes', 'BeRocket_LGV_domain') . '" />';
        }
        ?>
    </form> 
</div>
<?php
$feature_list = array(
    'Customization for Product count switch links',
    'Advanced list style for products',
    'Customization for simple products list style',
    'Customization for advanced products list style',
    'Custom CSS styles',
);
@ include 'settings_footer.php';
?>
</div>
