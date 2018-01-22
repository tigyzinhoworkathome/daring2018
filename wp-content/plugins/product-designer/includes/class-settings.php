<?php

/*
* @Author 		ParaTheme
* Copyright: 	2015 ParaTheme
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 	


class product_designer_class_settings  {
	
	
    public function __construct(){

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );

    }
	
	
	public function admin_menu() {
		
		add_menu_page(__('Product Designer', product_designer_textdomain), __('Product Designer', product_designer_textdomain), 'manage_options', 'product_designer', array( $this, 'settings' ));
		add_submenu_page('product_designer', __('Help', product_designer_textdomain), __('Help', product_designer_textdomain), 'manage_options', 'help', array( $this, 'help' ));	

	}
	
	public function settings(){
		
		include( 'menu/settings.php' );	
		
		}
	

	public function help(){
		
		include( 'menu/help.php' );	
		
		}
	
	
	
	
	
	

}


new product_designer_class_settings();

