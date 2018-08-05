<?php
function endereco() {
    global $post;
    $postId     = ($post->ID);
    $_address   = "";
    $_longitude = "";
    $_latitude  = "";
    if($postId){
	    $_address   = get_post_meta($postId,'_address',true);
    	$_longitude = get_post_meta($postId,'_longitude',true);
     	$_latitude  = get_post_meta($postId,'_latitude',true);
    }
?>

<div class="dokan-form-group">
    <div class="dokan-input-group">
    	<span class="dokan-input-group-addon"></span>
    	<input class="dokan-form-control" name="_address" id="_address1" type="text" placeholder="<?php __( 'Address', 'dokan-lite' ); ?>" value="<?php echo $_address; ?>">
    </div>
</div>

<div class="dokan-form-group">
    <div class="dokan-input-group">
         <span class="dokan-input-group-addon"></span>
         <input class="dokan-form-control" name="_latitude" id="_latitude1" type="text" placeholder="<?php __( 'Latitude', 'dokan-lite' ); ?>" value="<?php echo $_latitude; ?>">
    </div>
</div>

<div class="dokan-form-group">
     <div class="dokan-input-group">
          <span class="dokan-input-group-addon"></span>
          <input class="dokan-form-control"  name="_longitude" id="_longitude1" type="text" placeholder="<?php __( 'Longitude', 'dokan-lite' ); ?>" value="<?php echo $_longitude; ?>">
     </div>
</div>


<?php
}

add_filter('dokan_new_product_form', 'endereco', 10, 2);
//add_filter( 'dokan_product_edit_after_main', 'endereco', 10, 2);
function dokanCallBackEditProduct(){
     if((isset($_POST['update_product']) && wp_verify_nonce($_POST['dokan_edit_product_nonce'], 'dokan_edit_product')) || $_POST['dokan_product_id'])
     {
        if(isset($_GET['product_id'])){
            $post_id = intval($_GET['product_id']);
			save_extra_endereco_fields($post_id, "data");
        }else{
            global $post, $product;
            if (!empty($post)){
                $post_id = $post->ID;
                save_extra_endereco_fields($post_id, "data");
            }
        }
     }
}
add_action( 'template_redirect', 'dokanCallBackEditProduct', 10 );
add_action( 'save_post', 'save_extra_endereco_fields', 10, 2 );