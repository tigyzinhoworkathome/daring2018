<?php

/*
* @Author 		ParaTheme
* Copyright: 	2015 ParaTheme
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 	


class class_product_designer_post_meta  {
	
	
    public function __construct(){

		
		add_action('add_meta_boxes', array( $this, 'clipart_meta_boxes' ));
		//add_action( 'add_meta_boxes', 'product_designer_add_images' );
		
		add_action( 'save_post', array( $this, 'clipart_meta_save_postdata' ) );
		
		
		add_action('add_meta_boxes', array( $this, 'tdesigner_wc_order_meta_boxes' ));
		//add_action( 'add_meta_boxes', 'product_designer_add_images' );
		
		add_action( 'save_post', array( $this, 'tdesigner_wc_meta_save_postdata' ) );		
		
    }
	
	

	    public function clipart_meta_boxes(){
			
				$screens = array( 'clipart');
			
				foreach ( $screens as $screen ) {
			
					add_meta_box(
						'clipart_meta_box',
						__( 'Clip Art Options', product_designer_textdomain ),
						array( $this, 'clipart_meta_custom_box' ),
						$screen
					);
				}

			}
			
			
	    public function tdesigner_wc_order_meta_boxes(){
			
				$screens = array( 'shop_order');
			
				foreach ( $screens as $screen ) {
			
					add_meta_box(
						'tdesigner_wc_order_meta_box',
						__( 'Tdesigner data', product_designer_textdomain ),
						array( $this, 'tdesigner_wc_order_meta_custom_box' ),
						$screen
					);
				}

			}			
			
			
			
	
function clipart_meta_custom_box( $post ) {


		wp_nonce_field( 'clipart_meta_custom_box', 'clipart_meta_nonce' );
		
		$clipart_price = get_post_meta( $post->ID, 'clipart_price', true );
		
		
		?>
        <div class="">
            <p>
            Price:<br>
            <input type="text" placeholder="2" name="clipart_price" value="<?php echo $clipart_price; ?>" />
            </p>
        </div>
        <?php
		
	}
	
	
	
function tdesigner_wc_order_meta_custom_box( $post ) {


		wp_nonce_field( 'tdesigner_wc_order_meta_custom_box', 'tdesigner_wc_order_meta_nonce' );
		
		$tdesigner_custom_design = get_post_meta( $post->ID, 'tdesigner_custom_design', true );
		
		$order = wc_get_order( $post->ID );
		$order_items = $order->get_items();
		
		foreach ($order_items as $order_item_id => $order_item) { 
			
			$product_id = $order_item['product_id'];
			$side_data = get_post_meta( $product_id, 'side_data', true );

			$item_meta = wc_get_order_item_meta($order_item_id, 'tdesigner_custom_design');
			$item_meta = unserialize($item_meta);
			
			
			//echo '<pre>'.var_export($side_data, true).'</pre>';
		
			foreach($item_meta as $side_id=>$attach_id){
				
				$attach_url = wp_get_attachment_url( $attach_id );
				
				echo '<div class="item">'.$side_data[$side_id]['name'].'<a href="'.$attach_url.'"><img width="50" src="'.$attach_url.'" /></a></div>';
				 
				
				//echo '<pre>'.var_export($attach_id, true).'</pre>';
				
				}
			
			
		}
		
		
		//echo '<pre>'.var_export($items, true).'</pre>';


		
	}	
	
	
	
	
	
	
	
function clipart_meta_save_postdata( $post_id ) {



		if ( ! isset( $_POST['clipart_meta_nonce'] ) )
		return $post_id;
		
		$nonce = $_POST['clipart_meta_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'clipart_meta_custom_box' ) )
		  return $post_id;
		
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
		
		
		if ( 'page' == $_POST['post_type'] ) {
		
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		
		} else {
		
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		}
		
		$clipart_price =  sanitize_text_field($_POST['clipart_price']);
		update_post_meta( $post_id, 'clipart_price', $clipart_price );
	 
	}
	
	
function tdesigner_wc_meta_save_postdata( $post_id ) {



		if ( ! isset( $_POST['tdesigner_wc_order_meta_nonce'] ) )
		return $post_id;
		
		$nonce = $_POST['tdesigner_wc_order_meta_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'tdesigner_wc_order_meta_custom_box' ) )
		  return $post_id;
		
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
		
		
		if ( 'page' == $_POST['post_type'] ) {
		
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		
		} else {
		
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		}
		
		$clipart_price =  sanitize_text_field($_POST['clipart_price']);
		update_post_meta( $post_id, 'clipart_price', $clipart_price );
	 
	}
	
	

}


new class_product_designer_post_meta();

