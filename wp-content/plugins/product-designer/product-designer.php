<?php
/*
Plugin Name: Product Designer
Plugin URI: http://pickplugins.com
Description: Awesome Product Designer for Woo-Commenrce.
Version: 1.0.4
Author: pickplugins
Author URI: http://pickplugins.com
Text Domain: product-designer
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/




class ProductDesigner{
	
	public function __construct(){
		

		define('product_designer_plugin_url', plugins_url('/', __FILE__) );
		define('product_designer_plugin_dir', plugin_dir_path( __FILE__ ) );
		define('product_designer_wp_url', 'https://wordpress.org/plugins/product-designer' );
		define('product_designer_wp_reviews', 'http://wordpress.org/support/view/plugin-reviews/product-designer' );
		define('product_designer_pro_url', 'http://www.pickplugins.com/item/product-designer/' );
		define('product_designer_demo_url', 'http://www.pickplugins.com/demo/product-designer/' );
		define('product_designer_conatct_url', 'http://pickplugins.com/contact' );
		define('product_designer_qa_url', 'http://pickplugins.com/questions/' );
		define('product_designer_plugin_name', 'Product Designer' );
		define('product_designer_plugin_version', '1.0.4' );
		define('product_designer_customer_type', 'free' );	 // pro & free	
		define('product_designer_share_url', 'http://wordpress.org/plugins/product-designer/' );
		define('product_designer_tutorial_video_url', '//www.youtube.com/embed/8OiNCDavSQg?rel=0' );
		define('product_designer_tutorial_doc_url', 'http://pickplugins.com/docs/documentation/product-designer/' );		
		
		define('product_designer_textdomain', 'product-designer' );
		
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-functions.php');		
		
		//require_once( plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes.php');
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-settings.php');		
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-posttypes.php');			
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-post-meta.php');		
		
		
		require_once( plugin_dir_path( __FILE__ ) . 'includes/tshirt-designer-meta.php');
		//require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php');

		
		require_once( plugin_dir_path( __FILE__ ) . 'includes/designer.php');
		require_once( plugin_dir_path( __FILE__ ) . 'includes/designer-function.php');		

		add_action( 'wp_enqueue_scripts', array( $this, 'product_designer_front_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'product_designer_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ));
		
		add_filter('widget_text', 'do_shortcode');
		
		register_activation_hook( __FILE__, array( $this, 'product_designer_install' ) );



		}

	public function load_textdomain() {
	  load_plugin_textdomain( product_designer_textdomain, false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 
	}
	
		
	public function product_designer_install(){

		do_action( 'product_designer_action_install' );
		
		}		
		
	public function product_designer_uninstall(){
		
		do_action( 'product_designer_action_uninstall' );
		}		
		
	public function product_designer_deactivation(){
		
		do_action( 'product_designer_action_deactivation' );
		}
		
		
	public function product_designer_front_scripts(){
			

		$product_designer_sticker_size = get_option( 'product_designer_sticker_size' );
		if(empty($product_designer_sticker_size))
			{
				$product_designer_sticker_size = intval(2*1000*1000);
			}
		else
			{
				$product_designer_sticker_size = intval($product_designer_sticker_size*1000*1000);
			}

		wp_enqueue_script('jquery');		
    	wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-mouse' );		
    	wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		//wp_enqueue_script( 'jquery-ui-tooltip' );		

		//wp_enqueue_style('jquery-ui.css', product_designer_plugin_url.'css/jquery-ui.css');


		//wp_enqueue_script( 'html2canvas.js', plugins_url( '/js/html2canvas.js', __FILE__ ), array('jquery'), '1.0', false);
		wp_enqueue_script( 'fabric.min.js', plugins_url( '/assets/front/js/fabric.min.js', __FILE__ ), array('jquery'), '1.0', false);	
		


		wp_enqueue_script( 'jscolor.js', plugins_url( '/js/jscolor.js', __FILE__ ), array('jquery'), '1.0', false);
		
		if(is_singular()){
			
			$product_designer_page_id = get_option('product_designer_page_id');
			
			$page_id = get_the_id();
			if($product_designer_page_id == $page_id){
				wp_enqueue_script('TDesigner', plugins_url( '/assets/front/js/TDesigner.js' , __FILE__ ) , array( 'jquery' ));
				}
			
			
			
			}

		
		wp_enqueue_script('product_designer_js', plugins_url( '/js/scripts.js' , __FILE__ ) , array( 'jquery' ));
		
		
		wp_enqueue_script('tooltipster.bundle.min', plugins_url( '/assets/front/js/tooltipster.bundle.min.js' , __FILE__ ) , array( 'jquery' ));		
				
		wp_localize_script( 'product_designer_js', 'product_designer_ajax', array( 'product_designer_ajaxurl' => admin_url( 'admin-ajax.php')));
		//wp_enqueue_style('product_designer_style', product_designer_plugin_url.'css/style.css');
		wp_enqueue_style('TDesigner', product_designer_plugin_url.'assets/front/css/TDesigner.css');
		
		//wp_enqueue_style('icofont', product_designer_plugin_url.'assets/front/css/icofont.css');		
		wp_enqueue_style('FontCPD', product_designer_plugin_url.'assets/front/css/FontCPD/FontCPD.css');			
		
		wp_enqueue_style('font-awesome.min', product_designer_plugin_url.'assets/global/css/font-awesome.min.css');
		wp_enqueue_style('tooltipster.bundle.min', product_designer_plugin_url.'assets/front/css/tooltipster.bundle.min.css');


		wp_enqueue_script('plupload-all');	
		//wp_enqueue_script('plupload_js', plugins_url( '/assets/global/js/scripts-plupload.js' , __FILE__ ) , array( 'jquery' ));
		


		do_action('product_designer_action_front_scripts');
		}		
		
	public function product_designer_admin_scripts(){
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		
		
		wp_enqueue_style('product_designer_admin', product_designer_plugin_url.'assets/admin/css/style.css');
		wp_enqueue_style('product_designer_style-templates', product_designer_plugin_url.'assets/admin/css/style-templates.css');		
		
		//ParaAdmin
		wp_enqueue_style('ParaAdmin', product_designer_plugin_url.'ParaAdmin/css/ParaAdmin.css');
		//wp_enqueue_style('ParaDashboard', product_designer_plugin_url.'ParaAdmin/css/ParaDashboard.css');		
		//wp_enqueue_style('ParaIcons', product_designer_plugin_url.'ParaAdmin/css/ParaIcons.css');		
		wp_enqueue_script('ParaAdmin', plugins_url( 'ParaAdmin/js/ParaAdmin.js' , __FILE__ ) , array( 'jquery' ));
		
		wp_enqueue_style('font-awesome.min', product_designer_plugin_url.'assets/global/css/font-awesome.min.css');
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'product_designer_color_picker', plugins_url('/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );

		do_action('product_designer_action_admin_scripts');
		}		
		




}

new ProductDesigner();