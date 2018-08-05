<?php
global $categories_checked;
$categories_checked = $_GET['product_cat'];
$taxonomy     = 'product_cat';
$post_type    = 'product';
$orderby      = 'name';  
$show_count   = 0;      // 1 for yes, 0 for no
$pad_counts   = 0;      // 1 for yes, 0 for no
$hierarchical = 1;      // 1 for yes, 0 for no  
$title        = '';  
$empty        = 0;
$args         = array(
     	'taxonomy'     => $taxonomy,
	 	'post_type'    => $post_type,
     	'orderby'      => $orderby,
     	'show_count'   => $show_count,
     	'pad_counts'   => $pad_counts,
     	'hierarchical' => $hierarchical,
     	'title_li'     => $title,
     	'hide_empty'   => $empty
 );
 
 function burn_categories($args)
 { 	
 	global $categories_checked;
 	$HTML           = '';
 	$all_categories = get_categories($args);
 	if(count($all_categories) > 0){
	 	foreach($all_categories as $key => $cat){
	 		if(in_array($cat->term_id, $categories_checked)){
	 			$HTML .= '<li><input type="checkbox" class="category-choice" id="cat-'.$cat->term_id.'" name="product_cat[]" value="'.$cat->term_id.'" checked="checked" /><label for="cat-'.$cat->term_id.'">'.$cat->name.'</label>';
	 		}else{
	 			$HTML .= '<li><input type="checkbox" class="category-choice" id="cat-'.$cat->term_id.'" name="product_cat[]" value="'.$cat->term_id.'"/><label for="cat-'.$cat->term_id.'">'.$cat->name.'</label>';
	 		}
	 		$HTML .= burn_categories(Array(
	 				'taxonomy'     => 'product_cat',
	 				'post_type'    => 'product',
   		            'child_of'     => 0,
		            'parent'       => $cat->term_id,
		            'orderby'      => 'name',
		            'show_count'   => 0,
		            'pad_counts'   => 0,
		            'hierarchical' => 1,
		            'title_li'     => '',
		            'hide_empty'   => 0
	 			)
	 		);
 			$HTML .= '</li>';
	 	}
 	}
 	if($HTML != ''){
 		if(isset($args['class-main'])){
 			return '<ul class="'.$args['class-main'].'">'.$HTML.'</ul>';
 		}else{
 			return '<ul>'.$HTML.'</ul>';
 		}
 	}
 	return ''; 	
 }
 $categories = Array();
 $categories = get_categories($args); 
 foreach($categories as $key => $cat){
 	if($cat->parent > 0){
 		unset($categories[$key]);
 	}
 }
 $HTML = Array();
 $HTML = '<ul class="woogeolocation-categories">';
 foreach($categories as $key => $cat)
 {
 	if(in_array($cat->term_id, $categories_checked)){
 		$HTML .= '<li><input type="checkbox" class="category-choice" id="cat-'.$cat->term_id.'" name="product_cat[]" value="'.$cat->term_id.'" checked="checked"/><label for="cat-'.$cat->term_id.'">'.$cat->name.'</label>';
 	}else{
 		$HTML .= '<li><input type="checkbox" class="category-choice" id="cat-'.$cat->term_id.'" name="product_cat[]" value="'.$cat->term_id.'"/><label for="cat-'.$cat->term_id.'">'.$cat->name.'</label>';
 	}
 	if($show_subcategory)
 	{
	 	$burn = burn_categories(
	 				Array(
		 				'taxonomy'     => $taxonomy,
		 				'post_type'    => $post_type,
	   		            'child_of'     => 0,
			            'parent'       => $cat->term_id,
			            'orderby'      => $orderby,
			            'show_count'   => $show_count,
			            'pad_counts'   => $pad_counts,
			            'hierarchical' => $hierarchical,
			            'title_li'     => $title,
			            'hide_empty'   => $empty,
	 					'class-main'   => 'child-main'
		 			)
		 		).'</li>';
	 	
		 if($burn != ''){
		 	$HTML .= $burn;
		 }
 	}
 }
 $HTML .= '</ul>';
 return $HTML;