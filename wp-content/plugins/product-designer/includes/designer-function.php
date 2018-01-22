<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access




function Tdesigner_google_fonts(){
	
	$fonts = array();
	
	$fonts[] = array('name'=>'Bungee');
	$fonts[] = array('name'=>'Bungee Inline');
	$fonts[] = array('name'=>'Sumana');
	$fonts[] = array('name'=>'Montserrat');
	$fonts[] = array('name'=>'Indie Flower');
	$fonts[] = array('name'=>'Muli');
	$fonts[] = array('name'=>'Tillana');
	$fonts[] = array('name'=>'Lobster');
	$fonts[] = array('name'=>'Delius Unicase');
	$fonts[] = array('name'=>'Gloria Hallelujah');	
	$fonts[] = array('name'=>'Anton');	
	$fonts[] = array('name'=>'Pacifico');		
	$fonts[] = array('name'=>'Abril Fatface');	
	$fonts[] = array('name'=>'Ranga');	
	$fonts[] = array('name'=>'Dancing Script');	
	$fonts[] = array('name'=>'Shadows Into Light');		
	$fonts[] = array('name'=>'Amatic SC');	
	$fonts[] = array('name'=>'Poiret One');	
	$fonts[] = array('name'=>'Rock Salt');		
	$fonts[] = array('name'=>'Covered By Your Grace');	
	$fonts[] = array('name'=>'Tangerine');		
	$fonts[] = array('name'=>'Freckle Face');		
	$fonts[] = array('name'=>'Nothing You Could Do');	
	$fonts[] = array('name'=>'Ravi Prakash');		
	$fonts[] = array('name'=>'Prata');		
	$fonts[] = array('name'=>'Nixie One');			
	$fonts[] = array('name'=>'Press Start 2P');			
	$fonts[] = array('name'=>'Sigmar One');			
	$fonts[] = array('name'=>'Reenie Beanie');		
	$fonts[] = array('name'=>'Crafty Girls');		
	$fonts[] = array('name'=>'Cabin Sketch');		
	$fonts[] = array('name'=>'Bungee Shade');			
	$fonts[] = array('name'=>'Aclonica');		
	$fonts[] = array('name'=>'Ewert');		
	$fonts[] = array('name'=>'Monoton');	
	$fonts[] = array('name'=>'Fredericka the Great');	
	$fonts[] = array('name'=>'Holtwood One SC');	
	
	$fonts[] = array('name'=>'Rammetto One');	
	$fonts[] = array('name'=>'Bowlby One SC');		
	$fonts[] = array('name'=>'Coiny');		
	$fonts[] = array('name'=>'Bungee Outline');		
	$fonts[] = array('name'=>'Kumar One Outline');		
	
	$fonts[] = array('name'=>'Shojumaru');		
	$fonts[] = array('name'=>'Raleway Dots');	
	$fonts[] = array('name'=>'Frijole');		
	$fonts[] = array('name'=>'Bonbon');	
	$fonts[] = array('name'=>'Megrim');		
	$fonts[] = array('name'=>'Codystar');		
	$fonts[] = array('name'=>'Rye');		
	$fonts[] = array('name'=>'Nosifer');				
	
	$fonts[] = array('name'=>'myFirstFont', 'src'=>'http://www.w3schools.com/cssref/sansation_light.woff');	
	
	$fonts = apply_filters('product_designer_filter_fonts', $fonts);
	return $fonts;
	
	}





add_action('wp_ajax_clipart_upload', function(){

		check_ajax_referer('photo-upload');
		
		// you can use WP's wp_handle_upload() function:
		$file = $_FILES['async-upload'];
		
		$status = wp_handle_upload($file, array('action' => 'clipart_upload'));

		$file_loc = $status['file'];
		$file_name = basename($status['name']);
		$file_type = wp_check_filetype($file_name);
	
		$attachment = array(
			'post_mime_type' => $status['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($file['name'])),
			'post_content' => '',
			'post_status' => 'inherit'
		);
	
		$attach_id = wp_insert_attachment($attachment, $file_loc);
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_loc);
		wp_update_attachment_metadata($attach_id, $attach_data);
		//echo $attach_id;
	
	
		$user_id = get_current_user_id();
	
	    $clipart_post = array(
	      'post_title'    => __('Custom clipart', product_designer_textdomain),
	      'post_status'   => 'publish',
	      'post_author'   => $user_id,
	      'post_type'     =>'clipart'
		);
	
		$clipart_ID = wp_insert_post( $clipart_post );
		update_post_meta( $clipart_ID, '_thumbnail_id', $attach_id );
	
	
	
	
	
		$attach_title = get_the_title($attach_id);
	
		$html['attach_url'] = wp_get_attachment_url($attach_id);
		$html['attach_id'] = $attach_id;
		$html['attach_title'] = get_the_title($attach_id);	
	
		$response = array(
							'status'=>'ok',
							'html'=>$html,
							
							
							);
	
		echo json_encode($response);

  exit;
});




	add_filter( 'woocommerce_add_cart_item', 'Tdesigner_add_cart_item' , 10, 1 );
	function Tdesigner_add_cart_item( $cart_item ) {
		if ( (!empty( $cart_item['tdesigner_custom_design'] ))) {

		}

		return $cart_item;
	}






	add_action( 'woocommerce_add_order_item_meta',  'Tdesigner_add_order_item_meta' , 10, 2 );
	function Tdesigner_add_order_item_meta( $item_id, $cart_item ) {
	 
	 //order completed page. & admin
	 
	// var_dump($cart_item);
	 
		if ( (!empty( $cart_item['tdesigner_custom_design'] ))){
			
				 woocommerce_add_order_item_meta( $item_id, 'tdesigner_custom_design', $cart_item['tdesigner_custom_design'] );

			}		 
			 
	}

	add_filter( 'woocommerce_add_cart_item_data', 'Tdesigner_add_cart_item_data', 10, 2 );
	function Tdesigner_add_cart_item_data( $cart_item_meta, $product_id ) {
		global $woocommerce;

		if ( (!empty( $_POST['tdesigner_custom_design'] ))  ){
			
			$cart_item_meta['tdesigner_custom_design'] = stripslashes($_POST['tdesigner_custom_design']);
			
			}
	//var_dump($cart_item_meta);
		//var_dump($_POST);

		return $cart_item_meta;
	}






	add_filter( 'woocommerce_get_cart_item_from_session', 'Tdesigner_get_cart_item_from_session' , 10, 2 );
	function Tdesigner_get_cart_item_from_session( $cart_item, $values ) {


	
		if ( (!empty( $values['tdesigner_custom_design'] )) ) {
			
			$cart_item['tdesigner_custom_design'] = $values['tdesigner_custom_design'];
		}
		

		return $cart_item;
	}



	
	add_filter( 'woocommerce_get_item_data',  'Tdesigner_get_item_data' , 10, 2 );
	function Tdesigner_get_item_data( $item_data, $cart_item ) {
		
	// at cart page, checkout page
		if ( (!empty( $cart_item['tdesigner_custom_design']) )){
			
			$tdesigner_custom_design = $cart_item['tdesigner_custom_design'];
			
			$tdesigner_custom_design = unserialize($tdesigner_custom_design);
			
			//var_dump($cart_item);
			
			$product_id = $cart_item['product_id'];
			
			$side_data = get_post_meta($product_id, 'side_data', true);
			
			//var_dump($side_data);
			//var_dump($cart_item);
			$html = '<br />';
			foreach($tdesigner_custom_design as $side=>$design_id){
				
				$attach_url = wp_get_attachment_url( $design_id );
				
				$html.= '<img class="tooltip" title='.$side_data[$side]['name'].' width="30" src='.$attach_url.' /> ';
				}
			
			//var_dump($val);
			
			$item_data[] = array(
				'name'    => __( 'Custom Design', product_designer_textdomain ),
				'value'   => $tdesigner_custom_design,
				'display' => $html
			);
			
		}
			
			
		

			
	

		return $item_data;
	}
	
	
	function product_designer_page_list_ids()
		{	
			$wp_query = new WP_Query(
				array (
					'post_type' => 'page',
					'posts_per_page' => -1,
					) );
					
			$pages_ids = array();
					
			if ( $wp_query->have_posts() ) :
			
	
			while ( $wp_query->have_posts() ) : $wp_query->the_post();
			
			$pages_ids[get_the_ID()] = get_the_title();
			
			
			endwhile;
			wp_reset_query();
			endif;
			
			
			return $pages_ids;
		
		}
	
	
	
	
	
	
function product_designer_create_order( $post_data ) {
	
	$userid = get_current_user_id();
	
	$response =array();
	
	$tdesigner_custom_design = sanitize_text_field($post_data['tdesigner_custom_design']);
	$quantity = sanitize_text_field($post_data['quantity']);
	$address = sanitize_text_field($post_data['address']);	
	$customer_name = sanitize_text_field($post_data['customer_name']);	
	$product_id = sanitize_text_field($post_data['product_id']);	
	
	$post_order = array(
	  'post_title'    => 'Order - '.date('d-m-y'),
	  'post_status'   => 'publish',
	  'post_type'   => 'pd_order',
	  'post_author'   => $userid,
	);
	
	$order_ID = wp_insert_post($post_order);
	
	update_post_meta( $order_ID, 'tdesigner_custom_design', $tdesigner_custom_design );	
	update_post_meta( $order_ID, 'address', $address );
	update_post_meta( $order_ID, 'customer_name', $customer_name );
	update_post_meta( $order_ID, 'quantity', $quantity );
	update_post_meta( $order_ID, 'product_id', $product_id );

	$response['order_created'] = 'yes';
	
	return $response;
	
	}	
	
	
	
	
function product_designer_wc_edit_link( $html ) {
	
	$product_designer_page_id = get_option( 'product_designer_page_id' );	
	$product_designer_page_url = get_permalink($product_designer_page_id);	
	
	if(is_shop() || is_singular('product')){
		$html .= '<br /><a class="TDesigner-edit-link button " href="'.$product_designer_page_url.'?product_id='.get_the_ID().'"><i class="fa fa-crop" ></i> '.__('Customize', product_designer_textdomain).'</a>';
		}
	
	
	return $html;
}

add_filter( 'woocommerce_get_price_html', 'product_designer_wc_edit_link' );
add_filter( 'woocommerce_cart_item_price', 'product_designer_wc_edit_link' );	
	
	
	
function product_designer_edit_link( $html ) {
	
	$product_designer_page_id = get_option( 'product_designer_page_id' );	
	$product_designer_page_url = get_permalink($product_designer_page_id);	
	

	$html .= '<a class="TDesigner-edit-link button " href="'.$product_designer_page_url.'?product_id='.get_the_ID().'"><i class="fa fa-crop" ></i> '.__('Customize', product_designer_textdomain).'</a>';

	return $html;
}	
	
// add_filter( 'the_content', 'product_designer_edit_link' );	
	
	
	
	
	
	
	
	
	
	
	
function product_designer_ajax_save_template(){
	
	$json = $_POST['json'];
	$current_side = $_POST['current_side'];	
	$product_id = $_POST['product_id'];
	
	
	$templates = get_post_meta($product_id, 'templates', true);
	
	if(!empty($templates)){
		
		$templates[$current_side][time()]['name'] = time();
		$templates[$current_side][time()]['content'] = $json;	
		
		}
	else{
		
		$templates[$current_side][time()]['name'] = time();
		$templates[$current_side][time()]['content'] = $json;		
		
		}
	
	update_post_meta( $product_id, 'templates', $templates );
	//update_post_meta($product_id, 'templates', $templates);
	
	//echo $templates;
	echo ($templates);
	
	die();
	}
	
add_action('wp_ajax_product_designer_ajax_save_template', 'product_designer_ajax_save_template');
add_action('wp_ajax_nopriv_product_designer_ajax_save_template', 'product_designer_ajax_save_template');	
	
	
	
	
function product_designer_ajax_update_template(){
	
	$json = $_POST['json'];
	$current_side = $_POST['current_side'];	
	$t_id = $_POST['t_id'];		
	$product_id = $_POST['product_id'];
	
	
	$templates = get_post_meta($product_id, 'templates', true);
	
	if(!empty($templates)){
		
		$templates[$current_side][$t_id]['name'] = $t_id;
		$templates[$current_side][$t_id]['content'] = $json;	
		
		}
	else{
		
		$templates[$current_side][time()]['name'] = time();
		$templates[$current_side][time()]['content'] = $json;		
		
		}
	
	update_post_meta( $product_id, 'templates', $templates );
	//update_post_meta($product_id, 'templates', $templates);
	
	//echo $templates;
	echo ($templates);
	
	die();
	}
	
add_action('wp_ajax_product_designer_ajax_update_template', 'product_designer_ajax_update_template');
add_action('wp_ajax_nopriv_product_designer_ajax_update_template', 'product_designer_ajax_update_template');	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
function product_designer_ajax_load_template(){
	
	$t_id = $_POST['t_id'];
	$side_id = $_POST['side_id'];	
	$product_id = $_POST['product_id'];
	
	
	$templates = get_post_meta($product_id, 'templates', true);
	
	$template = $templates[$side_id][$t_id]['content'];

	echo $template;
	
	die();
	}
	
add_action('wp_ajax_product_designer_ajax_load_template', 'product_designer_ajax_load_template');
add_action('wp_ajax_nopriv_product_designer_ajax_load_template', 'product_designer_ajax_load_template');	
	
	
	
	
	
	
	
	
	
	
	
	
function product_designer_ajax_base64_uplaod(){
	
	$base_64 = $_POST['base_64'];
	$current_side = (string)$_POST['current_side'];	
	$product_id = (string)$_POST['product_id'];


	$title = "Tattoo : ";



	$upload_dir       = wp_upload_dir();

	// @new
	$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

	//$img = $_POST['imageData'];
	$img = str_replace('data:image/png;base64,', '', $base_64);
	$img = str_replace(' ', '+', $img);

	$decoded          = base64_decode($img);

	$filename         = 'attachment.png';

	$hashed_filename  = time() . '_' . $filename;

	// @new
	$image_upload     = file_put_contents( $upload_path . $hashed_filename, $decoded );

	//HANDLE UPLOADED FILE
	if( !function_exists( 'wp_handle_sideload' ) ) {

	  require_once( ABSPATH . 'wp-admin/includes/file.php' );

	}

	// Without that I'm getting a debug error!?
	if( !function_exists( 'wp_get_current_user' ) ) {

	  require_once( ABSPATH . 'wp-includes/pluggable.php' );

	}

	// @new
	$file             = array();
	$file['error']    = '';
	$file['tmp_name'] = $upload_path . $hashed_filename;
	$file['name']     = $hashed_filename;
	$file['type']     = 'image/png';
	$file['size']     = filesize( $upload_path . $hashed_filename );

	// upload file to server
	// @new use $file instead of $image_upload
	$file_return      = wp_handle_sideload( $file, array( 'test_form' => false ) );

	$filename = $file_return['file'];
	$attachment = array(
	 'post_mime_type' => $file_return['type'],
	 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
	 'post_content' => '',
	 'post_status' => 'inherit',
	 'guid' => $wp_upload_dir['url'] . '/' . basename($filename)
	 );
		
	$attach_id = wp_insert_attachment( $attachment, $filename );
	$attach_url = wp_get_attachment_url( $attach_id );
	
	$attach_data = wp_generate_attachment_metadata($attach_id, $attach_url);
	wp_update_attachment_metadata($attach_id, $attach_data);
	
	$response = array();
	$response['attach_id'] = $attach_id;
	$response['attachment_url'] = $attach_url;		
	
	//echo json_encode($response);
	
	$cookie_name = "side_customized";
	
	$cook_data = $_COOKIE[$cookie_name];
	$cook_data = unserialize(stripslashes($cook_data));	
	//$cook_data = array();
	$cook_data[$product_id][$current_side] = $attach_id;
	
	
	//$cook_data = serialize($cook_data);

	
	
	$cookie_value = serialize($cook_data);
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
	
	
	
	//$attach_data = wp_generate_attachment_metadata($attach_id, $attach_url);
	//wp_update_attachment_metadata($attach_id, $attach_data);
	
	//echo $response;
	//echo json_encode($response);
	//var_dump($attach_id);
	
	die();
	
	
	
	}
	
add_action('wp_ajax_product_designer_ajax_base64_uplaod', 'product_designer_ajax_base64_uplaod');
add_action('wp_ajax_nopriv_product_designer_ajax_base64_uplaod', 'product_designer_ajax_base64_uplaod');		
	
	
	
function product_designer_ajax_paged_clipart_list(){
	
		$product_designer_posts_per_page = get_option('product_designer_posts_per_page');
		
		
		
		$response = array();
		$cat_id = sanitize_text_field($_POST['cat']);
		$paged = sanitize_text_field($_POST['paged']);		
		
		if($cat_id=='all'){
			$tax_query = array();
			}
		else{
			$tax_query = array(
							array(
							   'taxonomy' => 'clipart_cat',
							   'field' => 'id',
							   'terms' => $cat_id,
							)
						);
			}

		
		
		$args = array(
					'post_type'=>'clipart',
					'posts_per_page'=>$product_designer_posts_per_page,
					'tax_query' => $tax_query,
					'paged' => $paged,

			);
		
		
		$wp_query = new WP_Query($args);
		
		if ( $wp_query->have_posts() ) :
		while ( $wp_query->have_posts() ) : $wp_query->the_post();	
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' );
		$thumb_url = $thumb['0'];
		
		if(!empty($thumb_url))
		$response['clip_list'].= '<img src="'.$thumb_url.'" />';
		
		endwhile;
			
		
		$paged = $paged;
		$big = 999999999; // need an unlikely integer
		$response['paginatioon'].= paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total'              => 3,
			'prev_text'          => '',
			'next_text'          => '',
			'total' => $wp_query->max_num_pages
			) );
		
		//$response['paginatioon'].= '1 > 2 > 3';
		
		wp_reset_query();		
		endif;	
		
		echo json_encode($response);
		
		die();
	
	}	
add_action('wp_ajax_product_designer_ajax_paged_clipart_list', 'product_designer_ajax_paged_clipart_list');
add_action('wp_ajax_nopriv_product_designer_ajax_paged_clipart_list', 'product_designer_ajax_paged_clipart_list');	
	
	
	
	
	
	
function product_designer_ajax_get_clipart_list(){
	
		$product_designer_posts_per_page = get_option('product_designer_posts_per_page');
		
		$response = array();
		$cat_id = sanitize_text_field($_POST['cat']);	
		
		if($cat_id=='all'){
			$tax_query = array();
			}
		else{
			$tax_query = array(
							array(
							   'taxonomy' => 'clipart_cat',
							   'field' => 'id',
							   'terms' => $cat_id,
							)
						);
			}

		
		
		$args = array(
					'post_type'=>'clipart',
					'posts_per_page'=>$product_designer_posts_per_page,
					'tax_query' => $tax_query,

			);
		
		
		$wp_query = new WP_Query($args);
		
		if ( $wp_query->have_posts() ) :
		while ( $wp_query->have_posts() ) : $wp_query->the_post();	
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' );
		$thumb_url = $thumb['0'];
		
		if(!empty($thumb_url))
		$response['clip_list'].= '<img src="'.$thumb_url.'" />';
		
		endwhile;
			
		
		$paged = 1;
		$big = 999999999; // need an unlikely integer
		$response['paginatioon'].= paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $paged ),
			'prev_text'          => '',
			'next_text'          => '',
			'total' => $wp_query->max_num_pages
			) );
		
		//$response['paginatioon'].= '1 > 2 > 3';
		
		wp_reset_query();		
		endif;	
		
		echo json_encode($response);
		
		die();
	
	}	
add_action('wp_ajax_product_designer_ajax_get_clipart_list', 'product_designer_ajax_get_clipart_list');
add_action('wp_ajax_nopriv_product_designer_ajax_get_clipart_list', 'product_designer_ajax_get_clipart_list');
