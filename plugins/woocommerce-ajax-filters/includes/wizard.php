<?php
class BeRocket_AAPF_Wizard {
    function __construct() {
        require_once dirname( __FILE__ ) . '/../wizard/setup-wizard.php';
        add_filter('berocket_wizard_steps_br-aapf-setup', array( __CLASS__, 'setup_wizard_steps'));
        add_action( 'before_wizard_run_br-aapf-setup', array( __CLASS__, 'set_wizard_js_css'));
        berocket_add_setup_wizard('br-aapf-setup', array('title' => __( 'AJAX Product Filters Setup Wizard', 'BeRocket_products_label_domain' )));
    }

    public static function set_wizard_js_css() {
        wp_enqueue_script('common');
        do_action('BeRocket_wizard_javascript');
    }

    public static function setup_wizard_steps($steps) {
        $steps = array(
            'wizard_selectors' => array(
                'name'    => __( 'Selectors', 'BeRocket_domain' ),
                'view'    => array( __CLASS__, 'wizard_selectors' ),
                'handler' => array( __CLASS__, 'wizard_selectors_save' ),
                'fa_icon' => 'fa-circle-o',
            ),
            'wizard_permalinks' => array(
                'name'    => __( 'URL', 'BeRocket_domain' ),
                'view'    => array( __CLASS__, 'wizard_permalinks' ),
                'handler' => array( __CLASS__, 'wizard_permalinks_save' ),
                'fa_icon' => 'fa-link',
            ),
            'wizard_count_reload' => array(
                'name'    => __( 'Attribute count', 'BeRocket_domain' ),
                'view'    => array( __CLASS__, 'wizard_count_reload' ),
                'handler' => array( __CLASS__, 'wizard_count_reload_save' ),
                'fa_icon' => 'fa-eye',
            ),
            'wizard_extra' => array(
                'name'    => __( 'Extra', 'BeRocket_domain' ),
                'view'    => array( __CLASS__, 'wizard_extra' ),
                'handler' => array( __CLASS__, 'wizard_extra_save' ),
                'fa_icon' => 'fa-cogs',
            ),
            'wizard_end' => array(
                'name'    => __( 'Ready!', 'BeRocket_domain' ),
                'view'    => array( __CLASS__, 'wizard_ready' ),
                'handler' => array( __CLASS__, 'wizard_ready_save' ),
                'fa_icon' => 'fa-check',
            ),
        );
        return $steps;
    }

    public static function wizard_selectors($wizard) {
        $option = BeRocket_AAPF::get_aapf_option();
        ?>
        <form method="post" class="br_framework_submit_form">
            <div class="nav-block berocket_framework_menu_general-block nav-block-active">
                <div>
                    <h3><?php _e('IMPORTANT', 'BeRocket_AJAX_domain') ?></h3>
                    <p><?php _e('Selectors can be different for each theme. Please setup correct selectors, otherwise plugin can doesn\'t work or some features can work incorrect', 'BeRocket_AJAX_domain') ?></p>
                    <p><?php _e('You can try to setup it via "Auto-selectors" and plugin will try get selectors for your theme, this take a while.', 'BeRocket_AJAX_domain') ?></p>
                    <p><?php _e('Manually you can check selectors on your shop page or contact <strong>theme author</strong> with question about it.', 'BeRocket_AJAX_domain') ?></p>
                    <p><?php _e('Also theme with Isotope/Masonry or any type of the image Lazy-Load required custom JavaScript. Please contact your <strong>theme author</strong> to get correct JavaScript code for it', 'BeRocket_AJAX_domain') ?></p>
                    <p><?php _e('JavaScript for some theme you can find in <a href="http://berocket.com/docs/plugin/woocommerce-ajax-products-filter#theme_setup" target="_blank">BeRocket Documentation</a>', 'BeRocket_AJAX_domain') ?></p>
                </div>
                <table class="framework-form-table berocket_framework_menu_selectors">
                    <tbody>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Get selectors automatically (BETA)', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <?php echo BeRocket_wizard_generate_autoselectors(array(
                                    'products' => '.berocket_aapf_products_selector',
                                    'pagination' => '.berocket_aapf_pagination_selector',
                                    'result_count' => '.berocket_aapf_product_count_selector')); ?>
                                <div>
                                    <?php _e('Please do not use it on live sites. If something went wrong write us.', 'BeRocket_AJAX_domain') ?>
                                </div>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row">Products Container Selector</th>
                            <td><label>
                                <input type="text" name="berocket_aapf_wizard_settings[products_holder_id]" 
                                    value="<?php if( ! empty($option['products_holder_id']) ) echo $option['products_holder_id']; ?>" 
                                    class="berocket_aapf_products_selector">
                            </label></td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row">Pagination Selector</th>
                            <td><label>
                                <input type="text" name="berocket_aapf_wizard_settings[woocommerce_pagination_class]" 
                                    value="<?php if( ! empty($option['woocommerce_pagination_class']) ) echo $option['woocommerce_pagination_class']; ?>" 
                                    class="berocket_aapf_pagination_selector">
                            </label></td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Product count selector', 'BeRocket_AJAX_domain') ?></th>
                            <td><label>
                                <input type="text" name="berocket_aapf_wizard_settings[woocommerce_result_count_class]" 
                                    value="<?php if( ! empty($option['woocommerce_result_count_class']) ) echo $option['woocommerce_result_count_class']; ?>" 
                                    class="berocket_aapf_product_count_selector">
                            </label></td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Product order by selector', 'BeRocket_AJAX_domain') ?></th>
                            <td><label>
                                <input type="text" name="berocket_aapf_wizard_settings[woocommerce_ordering_class]" 
                                    value="<?php if( ! empty($option['woocommerce_ordering_class']) ) echo $option['woocommerce_ordering_class']; ?>" 
                                    class="">
                            </label></td>
                        </tr>
                        <tr style="display: table-row;">
                            <td colspan="2">
                                <a href="#custom-js-css" class="wizard_custom_js_css_open"><?php _e('You need some custom JavaScript/CSS code?', 'BeRocket_AJAX_domain') ?></a>
                                <div class="wizard_custom_js_css" style="display: none;">
                                    <h3><?php _e('User custom CSS style', 'BeRocket_AJAX_domain') ?></h3>
                                    <textarea name="berocket_aapf_wizard_settings[user_custom_css]"><?php echo br_get_value_from_array($option, array('user_custom_css')) ?></textarea>
                                    <h3><?php _e('JavaScript Before Products Update', 'BeRocket_AJAX_domain') ?></h3>
                                    <textarea name="berocket_aapf_wizard_settings[user_func][before_update]"><?php echo br_get_value_from_array($option, array('user_func', 'before_update')) ?></textarea>
                                    <h3><?php _e('JavaScript On Products Update', 'BeRocket_AJAX_domain') ?></h3>
                                    <textarea name="berocket_aapf_wizard_settings[user_func][on_update]"><?php echo br_get_value_from_array($option, array('user_func', 'on_update')) ?></textarea>
                                    <h3><?php _e('JavaScript After Products Update', 'BeRocket_AJAX_domain') ?></h3>
                                    <textarea name="berocket_aapf_wizard_settings[user_func][after_update]"><?php echo br_get_value_from_array($option, array('user_func', 'after_update')) ?></textarea>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <script>
            jQuery(document).on('click', '.wizard_custom_js_css_open', function(event) {
                event.preventDefault();
                jQuery(this).hide();
                jQuery('.wizard_custom_js_css').show();
            });
            </script>
            <?php wp_nonce_field( $wizard->page_id ); ?>
            <p class="next-step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Next Step", 'BeRocket_AJAX_domain' ); ?>" name="save_step" />
            </p>
        </form>
        <?php
    }

    public static function wizard_selectors_save($wizard) {
        check_admin_referer( $wizard->page_id );
        $option = BeRocket_AAPF::get_aapf_option();
        if( ! empty($_POST['berocket_aapf_wizard_settings']) && is_array($_POST['berocket_aapf_wizard_settings']) ) {
            $new_option = array_merge(
                array('woocommerce_removes' => array('pagination' => '', 'result_count' => '', 'ordering' => '')), 
                $_POST['berocket_aapf_wizard_settings']
            );
            $option = array_merge($option, $new_option);
        }
        $option = BeRocket_AAPF::sanitize_aapf_option($option);
        update_option( 'br_filters_options', $option );
        $wizard->redirect_to_next_step();
    }

    public static function wizard_permalinks($wizard) {
        $option = BeRocket_AAPF::get_aapf_option();
        ?>
        <form method="post" class="br_framework_submit_form">
            <div class="nav-block berocket_framework_menu_general-block nav-block-active">
                <table class="framework-form-table berocket_framework_menu_selectors">
                    <tbody>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('SEO friendly URLs', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <input class="berocket_wizard_seo_friendly" name="berocket_aapf_wizard_settings[seo_friendly_urls]" type="checkbox" value="1"<?php if( ! empty($option['seo_friendly_urls']) ) echo ' checked'?>>
                                <?php _e('Page URL will be changed after filtering.', 'BeRocket_AJAX_domain') ?>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <td colspan="2">
                                <p><?php _e('Without this option after page reload filter will be unselected', 'BeRocket_AJAX_domain') ?></p>
                                <p><?php _e('Also back button doesn\'t load previous selected filters', 'BeRocket_AJAX_domain') ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php wp_nonce_field( $wizard->page_id ); ?>
            <p class="next-step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Next Step", 'BeRocket_AJAX_domain' ); ?>" name="save_step" />
            </p>
        </form>
        <?php
    }

    public static function wizard_permalinks_save($wizard) {
        check_admin_referer( $wizard->page_id );
        $option = BeRocket_AAPF::get_aapf_option();
        if( empty($_POST['berocket_aapf_wizard_settings']) || ! is_array($_POST['berocket_aapf_wizard_settings']) ) {
            $_POST['berocket_aapf_wizard_settings'] = array();
        }
        $option_new = array_merge(array('seo_friendly_urls' => ''), $_POST['berocket_aapf_wizard_settings']);
        $option = array_merge($option, $option_new);
        $option = BeRocket_AAPF::sanitize_aapf_option($option);
        update_option( 'br_filters_options', $option );
        $wizard->redirect_to_next_step();
    }

    public static function wizard_count_reload($wizard) {
        $option = BeRocket_AAPF::get_aapf_option();
        ?>
        <form method="post" class="br_framework_submit_form">
            <div class="nav-block berocket_framework_menu_general-block nav-block-active">
                <table class="framework-form-table berocket_framework_menu_selectors">
                    <tbody>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Presets', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <p><?php _e('You can select some preset or setup each settings manually', 'BeRocket_AJAX_domain') ?></p>
                                <select class="attribute_count_preset">
                                    <option value="custom"><?php _e('Custom', 'BeRocket_AJAX_domain') ?></option>
                                    <option value="show_all"><?php _e('Show all attributes (very fast)', 'BeRocket_AJAX_domain') ?></option>
                                    <option value="hide_page"><?php _e('Hide empty attributes by page (fast)', 'BeRocket_AJAX_domain') ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="attribute_count_preset_info attribute_count_preset_info_show_all" style="display: none;" data-count="1">
                            <td colspan="2">
                                <h4><?php _e('Show all attributes', 'BeRocket_AJAX_domain') ?></h4>
                                <p><?php _e('Display all attribute values, including attribute values without products, but work realy fast. If you have a lot of products(2000 and more) then it will be better solution. But check that all attribute values in your shop has attribute', 'BeRocket_AJAX_domain') ?></p>
                            </td>
                        </tr>
                        <tr class="attribute_count_preset_info attribute_count_preset_info_hide_page" style="display: none;" data-count="26">
                            <td colspan="2">
                                <h4><?php _e('Hide empty attributes by page', 'BeRocket_AJAX_domain') ?></h4>
                                <p><?php _e('Display only attribute values with products, but do not check selected filters. Any first selected filter will return products, but next filters can return "no products" message', 'BeRocket_AJAX_domain') ?></p>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Show all values', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <label><input name="berocket_aapf_wizard_settings[show_all_values]" class="attribute_count_preset_1" type="checkbox" value="1"
                                <?php if( ! empty($option['show_all_values']) ) echo " checked"; ?>>
                                <?php _e('Check if you want to show not used attribute values too', 'BeRocket_AJAX_domain') ?>
                                </label>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <td colspan="2">
                                <?php _e('Uses all attribute values in filters, uses also values without products for your shop. Can fix some problems with displaying filters on pages', 'BeRocket_AJAX_domain') ?>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Hide values', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <div><label><input name="berocket_aapf_wizard_settings[hide_value][o]" class="attribute_count_preset_2" type="checkbox" value="1"
                                <?php if( ! empty($option['hide_value']['o']) ) echo " checked"; ?>>
                                <?php _e('Hide values without products', 'BeRocket_AJAX_domain') ?>
                                </label></div>
                                <div><label><input name="berocket_aapf_wizard_settings[hide_value][sel]" class="attribute_count_preset_4" type="checkbox" value="1"
                                <?php if( ! empty($option['hide_value']['sel']) ) echo " checked"; ?>>
                                <?php _e('Hide selected values', 'BeRocket_AJAX_domain') ?>
                                </label></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <script>
                jQuery(document).on('change', '.attribute_count_preset', function() {
                    jQuery('.attribute_count_preset_info').hide();
                    jQuery('.attribute_count_preset_info_'+jQuery(this).val()).show();
                    var data_count = jQuery('.attribute_count_preset_info_'+jQuery(this).val()).data('count');
                    if( data_count ) {
                        var data_counts = [32,16,8,4,2,1];
                        data_counts.forEach(function( item, i, arr) {
                            if( data_count >= item ) {
                                jQuery('.attribute_count_preset_'+item).prop('checked', true);
                                data_count = data_count - item;
                            } else {
                                jQuery('.attribute_count_preset_'+item).prop('checked', false);
                            }
                        });
                    }
                });
                jQuery(document).on('change', '.attribute_count_preset_1, .attribute_count_preset_2, .attribute_count_preset_4, .attribute_count_preset_8, .attribute_count_preset_16, .attribute_count_preset_32', function() {
                    jQuery('.attribute_count_preset').val('custom').trigger('change');
                });
            </script>
            <?php wp_nonce_field( $wizard->page_id ); ?>
            <p class="next-step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Next Step", 'BeRocket_AJAX_domain' ); ?>" name="save_step" />
            </p>
        </form>
        <?php
    }

    public static function wizard_count_reload_save($wizard) {
        check_admin_referer( $wizard->page_id );
        $option = BeRocket_AAPF::get_aapf_option();
        if( empty($_POST['berocket_aapf_wizard_settings']) || ! is_array($_POST['berocket_aapf_wizard_settings']) ) {
            $_POST['berocket_aapf_wizard_settings'] = array();
        }
        $option_new = array_merge(array('show_all_values' => '', 'hide_value' => array('o' => '', 'sel' => '')), $_POST['berocket_aapf_wizard_settings']);
        $option = array_merge($option, $option_new);
        $option = BeRocket_AAPF::sanitize_aapf_option($option);
        update_option( 'br_filters_options', $option );
        $wizard->redirect_to_next_step();
    }

    public static function wizard_extra($wizard) {
        $option = BeRocket_AAPF::get_aapf_option();
        ?>
        <form method="post" class="br_framework_submit_form">
            <div class="nav-block berocket_framework_menu_general-block nav-block-active">
                <table class="framework-form-table berocket_framework_menu_selectors">
                    <tbody>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Jump to first page', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <label><input name="berocket_aapf_wizard_settings[first_page_jump]" type="checkbox" value="1"
                                <?php if( ! empty($option['first_page_jump']) ) echo " checked"; ?>>
                                <?php _e('Load first page after any filter changes. Can fix some problem with "no products" message after filtering', 'BeRocket_AJAX_domain') ?>
                                </label>
                            </td>
                        </tr>
                        <tr style="display: table-row;">
                            <th scope="row"><?php _e('Scroll page to the top', 'BeRocket_AJAX_domain') ?></th>
                            <td>
                                <label><input name="berocket_aapf_wizard_settings[scroll_shop_top]" type="checkbox" value="1"
                                <?php if( ! empty($option['scroll_shop_top']) ) echo " checked"; ?>>
                                <?php _e('Check if you want scroll page to the top of shop after filters change', 'BeRocket_AJAX_domain') ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <script>
            </script>
            <?php wp_nonce_field( $wizard->page_id ); ?>
            <p class="next-step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Next Step", 'BeRocket_AJAX_domain' ); ?>" name="save_step" />
            </p>
        </form>
        <?php
    }

    public static function wizard_extra_save($wizard) {
        check_admin_referer( $wizard->page_id );
        $option = BeRocket_AAPF::get_aapf_option();
        if( empty($_POST['berocket_aapf_wizard_settings']) || ! is_array($_POST['berocket_aapf_wizard_settings']) ) {
            $_POST['berocket_aapf_wizard_settings'] = array();
        }
        $option_new = array_merge(array('first_page_jump' => '', 'scroll_shop_top' => ''), $_POST['berocket_aapf_wizard_settings']);
        $option = array_merge($option, $option_new);
        $option = BeRocket_AAPF::sanitize_aapf_option($option);
        update_option( 'br_filters_options', $option );
        $wizard->redirect_to_next_step();
    }

    public static function wizard_ready($wizard) {
        $option = BeRocket_AAPF::get_aapf_option();
        ?>
        <form method="post" class="br_framework_submit_form">
            <div class="nav-block berocket_framework_menu_general-block nav-block-active">
                <h2><?php _e('Plugin ready to use', 'BeRocket_AJAX_domain') ?></h2>
                <div><iframe width="560" height="315" src="https://www.youtube.com/embed/8gaMj-IxUj0?rel=0&amp;showinfo=0" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe></div>
                <h4><?php _e('Widget', 'BeRocket_AJAX_domain') ?></h4>
                <p><?php _e('Now you can add widgets AJAX Product Filters to your side bar', 'BeRocket_AJAX_domain') ?></p>
                <p><?php _e('More information about widget options you can get on <a target="_blank" href="http://berocket.com/docs/plugin/woocommerce-ajax-products-filter#widget">BeRocket Documentation</a>', 'BeRocket_AJAX_domain') ?></p>
            </div>
            <?php wp_nonce_field( $wizard->page_id ); ?>
            <p class="next-step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Open plugin settings", 'BeRocket_AJAX_domain' ); ?>" name="save_step" />
            </p>
        </form>
        <?php
    }

    public static function wizard_ready_save($wizard) {
        check_admin_referer( $wizard->page_id );
        wp_redirect( admin_url( 'admin.php?page='.BeRocket_AAPF::$values['option_page'] ) );
    }
}
new BeRocket_AAPF_Wizard();
