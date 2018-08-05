<?php
function endereco() {
    global $post;
    $postId = ($post->ID);
    $_address = "";
    $_longitude = "";
    $_latitude = "";
    $_postalcode = "";
    if($postId){
     $_address =   get_post_meta($postId,'_address',true);
     $_longitude =   get_post_meta($postId,'_longitude',true);
     $_latitude =   get_post_meta($postId,'_latitude',true);
     $_postalcode =   get_post_meta($postId,'_postalcode',true);
    }
?>

<div class="dokan-form-group">
    <div class="dokan-input-group">
    	<span class="dokan-input-group-addon"></span>
    	<input class="dokan-form-control" name="_address" id="_address1" type="text" placeholder="<?php _e( 'Address', 'dokan' ); ?>" value="<?php echo $_address; ?>">
    </div>
</div>

<div class="dokan-form-group">
    <div class="dokan-input-group">
         <span class="dokan-input-group-addon"></span>
         <input class="dokan-form-control" name="_latitude" id="_latitude1" type="text" placeholder="<?php _e( 'Latitude', 'dokan' ); ?>" value="<?php echo $_latitude; ?>">
    </div>
</div>

<div class="dokan-form-group">
     <div class="dokan-input-group">
          <span class="dokan-input-group-addon"></span>
          <input class="dokan-form-control"  name="_longitude" id="_longitude1" type="text" placeholder="<?php _e( 'Longitude', 'dokan' ); ?>" value="<?php echo $_longitude; ?>">
     </div>
</div>
<div class="dokan-form-group">
     <div class="dokan-input-group">
          <span class="dokan-input-group-addon"></span>
          <input class="dokan-form-control"  name="_postalcode" id="_postalcode1" type="text" placeholder="<?php _e( 'Postal code', 'dokan' ); ?>" value="<?php echo $_postalcode; ?>">
     </div>
</div>


<?php
}

add_filter( 'dokan_new_product_form', 'endereco', 10, 2);
//add_filter( 'dokan_product_edit_after_main', 'endereco', 10, 2);

function dokanCallBackEditProduct(){
      if (( isset( $_POST['update_product'] ) && wp_verify_nonce( $_POST['dokan_edit_product_nonce'], 'dokan_edit_product' ) ) || isset($_POST['dokan_product_id']) )
      {
        if ( isset( $_GET['product_id'] ) ) {
            $post_id = intval( $_GET['product_id'] );
 save_extra_endereco_fields( $post_id, "data" );
        } else {
            global $post, $product;

            if ( !empty( $post ) ) {
                $post_id = $post->ID;
                save_extra_endereco_fields( $post_id, "data" );
            }
        }
      }
}
add_action( 'template_redirect', 'dokanCallBackEditProduct', 10 );
add_action( 'dokan_new_product_added', 'save_extra_endereco_fields', 10, 2 );