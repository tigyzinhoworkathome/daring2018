<?php

/*
* @Author 		ParaTheme
* Copyright: 	2015 ParaTheme
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 	


class class_product_designer_posttypes  {
	
	
    public function __construct(){

		
		add_action('init', array( $this, 'posttype_clipart' ));
		add_action( 'init', array( $this, 'clipart_taxonomies' ), 0 );
		
		add_action('init', array( $this, 'posttype_pd_order' ), 100);		
		
    }
	
	

	    public function posttype_clipart(){
			
	        $labels = array(
                'name' => _x('Clip Art', product_designer_textdomain),
                'singular_name' => _x('Clip Art', product_designer_textdomain),
                'add_new' => _x('Add Clip Art', product_designer_textdomain),
                'add_new_item' => __('Add Clip Art', product_designer_textdomain),
                'edit_item' => __('Edit Clip Art', product_designer_textdomain),
                'new_item' => __('New Clip Art', product_designer_textdomain),
                'view_item' => __('View Clip Art', product_designer_textdomain),
                'search_items' => __('Search Clip Art', product_designer_textdomain),
                'not_found' =>  __('Nothing found', product_designer_textdomain),
                'not_found_in_trash' => __('Nothing found in Trash', product_designer_textdomain),
                'parent_item_colon' => ''
        );

        $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'query_var' => true,
                'menu_icon' => 'dashicons-nametag',
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title','thumbnail'),
				

          );
 
        register_post_type( 'clipart' , $args );
			
			
			}
	
	
	    public function clipart_taxonomies(){
			
			
				register_taxonomy('clipart_cat', 'clipart', array(
						// Hierarchical taxonomy (like categories)
						'hierarchical' => true,
						'show_admin_column' => true,
						// This array of options controls the labels displayed in the WordPress Admin UI
						'labels' => array(
								'name' => _x( 'Clip Art Categories', product_designer_textdomain ),
								'singular_name' => _x( 'Clip Art Categories', product_designer_textdomain ),
								'search_items' =>  __( 'Search Clip Art Categories', product_designer_textdomain ),
								'all_items' => __( 'All Clip Art Categories', product_designer_textdomain ),
								'parent_item' => __( 'Parent Clip Art Categories', product_designer_textdomain ),
								'parent_item_colon' => __( 'Parent Clip Art Categories:', product_designer_textdomain ),
								'edit_item' => __( 'Edit Clip Art Categories', product_designer_textdomain ),
								'update_item' => __( 'Update Clip Art Categories', product_designer_textdomain ),
								'add_new_item' => __( 'Add Clip Art Categories', product_designer_textdomain ),
								'new_item_name' => __( 'New Clip Art Categories Name', product_designer_textdomain ),
								'menu_name' => __( 'Clip Art Categories' ),
								
						),
						// Control the slugs used for this taxonomy
						'rewrite' => array(
								'slug' => 'clipart_cat', // This controls the base slug that will display before each term
								'with_front' => false, // Don't display the category base before "/locations/"
								'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
						),
				));
			
			
			}
	
	

	    public function posttype_pd_order(){
			
	        $labels = array(
                'name' => _x('Order', product_designer_textdomain),
                'singular_name' => _x('Order', product_designer_textdomain),
                'add_new' => _x('Add Order', product_designer_textdomain),
                'add_new_item' => __('Add Order', product_designer_textdomain),
                'edit_item' => __('Edit Order', product_designer_textdomain),
                'new_item' => __('New Order', product_designer_textdomain),
                'view_item' => __('View Order', product_designer_textdomain),
                'search_items' => __('Search Order', product_designer_textdomain),
                'not_found' =>  __('Nothing found', product_designer_textdomain),
                'not_found_in_trash' => __('Nothing found in Trash', product_designer_textdomain),
                'parent_item_colon' => ''
        );

        $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'query_var' => true,
                'menu_icon' => 'dashicons-nametag',
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => null,
				'show_in_menu' 	=> 'product_designer',	
                'supports' => array('title','thumbnail','custom-fields'),
				

          );
 
        register_post_type( 'pd_order' , $args );
			
			
			}
}


new class_product_designer_posttypes();

