<?php

/*
* @Author 		ParaTheme
* Copyright: 	2015 ParaTheme
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 	


class class_product_designer_functions  {
	
	
    public function __construct(){

		
		//add_action('add_meta_boxes', array( $this, 'clipart_meta_boxes' ));
	
		
    }
	
	public function tutorials(){

		$tutorials[] = array(
							'title'=>__('How to add sides', product_designer_textdomain),
							'video_id'=>'iyngs87-tww',
							'source'=>'youtube',
							);
							
		$tutorials[] = array(
							'title'=>__('How to create update template', product_designer_textdomain),
							'video_id'=>'E28BZDWfFLA',
							'source'=>'youtube',
							);							
							
		$tutorials[] = array(
							'title'=>__('How to design product and submit order', product_designer_textdomain),
							'video_id'=>'sYJ3ErkUn_4',
							'source'=>'youtube',
							);								
							

		
		$tutorials = apply_filters('product_designer_filters_tutorials', $tutorials);		

		return $tutorials;

		}	
	
	
	public function faq(){



		$faq['core'] = array(
							'title'=>__('Core', product_designer_textdomain),
							'items'=>array(
							
/*

											array(
												'question'=>__('Single job page showing 404 error', product_designer_textdomain),
												'answer_url'=>'https://goo.gl/uGLEWq',
					
												),
												
*/


											),

								
							);

					
		
		
		$faq = apply_filters('product_designer_filters_faq', $faq);		

		return $faq;

		}		
	
	

}


new class_product_designer_functions();

