<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpd
 * @subpackage Wpd/admin
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $wpd    The ID of this plugin.
     */
    private $wpd;

    /**
     * The version of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    3.0
     * @param      string    $wpd       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($wpd, $version) {

        $this->wpd = $wpd;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    3.0
     */
    public function enqueue_styles() {

        $screen = get_current_screen();
        if ( 
                isset( $screen->base ) && 
                (
                        strpos( $screen->base, 'wpd' ) !== false
                        ||strpos( $screen->post_type, 'wpc' ) !== false
                        ||$screen->post_type == 'product'
                        ||$screen->post_type == 'shop_order'
                        ||$screen->post_type == 'wpd-config'
                )
            ) {
        wp_enqueue_style($this->wpd, plugin_dir_url(__FILE__) . 'css/wpd-admin.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-simplegrid", plugin_dir_url(__FILE__) . 'css/simplegrid.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-common", WPD_URL . 'public/css/wpd-common.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-tooltip-css", plugin_dir_url(__FILE__) . 'css/tooltip.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-colorpicker-css", plugin_dir_url(__FILE__) . 'js/colorpicker/css/colorpicker.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-o-ui", plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-bs-modal-css", WPD_URL . 'public/js/modal/modal.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-datatables-css", WPD_URL . 'admin/js/datatables/jquery.dataTables.min.css', array(), $this->version, 'all');
        wp_enqueue_style("select2-css", plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
        wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
            }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    3.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ( 
                isset( $screen->base ) && 
                (
                        strpos( $screen->base, 'wpd' ) !== false
                        ||strpos( $screen->base, 'wpc' ) !== false
                        ||strpos( $screen->post_type, 'wpc' ) !== false
                        ||$screen->post_type == 'product'
                        ||$screen->post_type == 'shop_order'
                        ||$screen->post_type == 'wpd-config'
                )
            ) {
            wp_enqueue_script('wpd-tabs-js', plugin_dir_url(__FILE__) . 'js/SpryTabbedPanels.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-tooltip-js', plugin_dir_url(__FILE__) . 'js/tooltip.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-colorpicker-js', plugin_dir_url(__FILE__) . 'js/colorpicker/js/colorpicker.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-modal-js', WPD_URL . 'public/js/modal/modal.js', array('jquery'), false, false);
            wp_enqueue_script($this->wpd, plugin_dir_url(__FILE__) . 'js/wpd-admin.js', array('jquery', 'select2-js'), $this->version, false);
            wp_localize_script($this->wpd, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_script('wpd-jquery-cookie-js', plugin_dir_url(__FILE__) . 'js/jquery.cookie.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-datatable-js', plugin_dir_url(__FILE__) . 'js/datatables/jquery.dataTables.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);
            wp_localize_script("o-admin", 'home_url', home_url("/"));
            wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, 'all');
        }
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function add_woo_parts_submenu() {
        global $submenu;
        $icon = WPD_URL . 'admin/images/wpd-dashicon.png';
        add_menu_page('Woocommerce Product Designer', 'WPD', 'manage_product_terms', 'wpc-manage-dashboard', array($this, 'get_fonts_page'), $icon);
        add_submenu_page('wpc-manage-dashboard', __('Fonts', 'wpd'), __('Fonts', 'wpd'), 'manage_product_terms', 'wpc-manage-fonts', array($this, 'get_fonts_page'));
        add_submenu_page('wpc-manage-dashboard', __('Cliparts', 'wpd'), __('Cliparts', 'wpd'), 'manage_product_terms', 'edit.php?post_type=wpc-cliparts', false);
        add_submenu_page('wpc-manage-dashboard', __('Configurations', 'wpd'), __('Configurations', 'wpd'), 'manage_options', 'edit.php?post_type=wpd-config', false);
        add_submenu_page('wpc-manage-dashboard', __('Settings', 'wpd'), __('Settings', 'wpd'), 'manage_product_terms', 'wpc-manage-settings', array($this, 'get_settings_page'));
        add_submenu_page( 'wpc-manage-dashboard', __('Go Premium', 'wpd' ), __( 'Go Premium', 'wpd' ), 'manage_product_terms', 'wpd-premium-features', array($this, "get_premium_features_page"));
        add_submenu_page('wpc-manage-dashboard', __('Get Started', 'wpd'), __('Get Started', 'wpd'), 'manage_product_terms', 'wpc-about', array($this, "get_about_page"));
        
        
        $url = WPD_URL.'user-manual.pdf';
        $submenu['wpc-manage-dashboard'][] = array('User Manual', 'manage_product_terms', $url);

    }

    /**
     * Builds the fonts management page
     */
    function get_fonts_page() {
        include_once( WPD_DIR . '/includes/wpd-add-fonts.php' );
        woocommerce_add_fonts();
    }

    /**
     * Initialize the plugin sessions
     */
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION["wpd-data-to-load"]))
            $_SESSION["wpd-data-to-load"] = "";
	
        $_SESSION["wpd_calculated_totals"]=FALSE;
    }

    /**
     * Redirects the plugin to the about page after the activation
     */
    function wpc_redirect() {
        if (get_option('wpc_do_activation_redirect', false)) {
            delete_option('wpc_do_activation_redirect');
            wp_redirect(admin_url('admin.php?page=wpc-about'));
        }
    }

    /**
     * Builds the about page
     */
    function get_about_page() {
        
        ?>
        <div id='wpd-about-page'>
            <div class="wrap">
                <div id="features-wrap">
                    <h2 class="feature-h2"><?php _e('Getting Started', 'wpd'); ?></h2>
                    <div class="list-posts-content">
                      <div class="o-wrap o-features xl-gutter-8">
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>Pricesbasedonthedesign-19.svg">
                              <h3 class="wpd-title"><?php _e('HOW TO CREATE A WOOCOMMERCE CUSTOM PRODUCT', 'wpd'); ?></h3>
                              <p><?php _e('This tutorial is a step by step guide to teach you how to create a custom product on a woocommerce store using WooCommerce Product Designer plugin...', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/tutorials/create-woocommerce-custom-product/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=how%20to%20create%20a%20woocommerce%20custom%20product" class="button" target="_blank"><?php _e('Learn More', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-features-Yellow-18.svg">
                              <h3 class="wpd-title"><?php _e('HOW TO MANAGE FONTS', 'wpd'); ?></h3>
                              <p><?php _e('Fonts are one of the most basic features in online product customization and yet one of the most critical ones. WPD does not only allow you to use google fonts...', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/tutorials/how-to-manage-fonts/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=how%20to%20manage%20fonts" class="button" target="_blank"><?php _e('Learn More', 'wpd'); ?></a>
                          </div>
                      </div>
                    </div>
                </div>

            </div>       
        </div>
        <?php
    }
    
    function get_premium_features_page()
    {
        ?>
        
        <div id="wpc-advanced-features">
            
            <div class="wrap">
                <div id="features-wrap">
                    <h2 class="feature-h2">Go Premium</h2>
                    <div class="list-posts-content">
                      <div class="o-wrap o-features xl-gutter-8">
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-02.svg">
                              <h3 class="wpd-title"><?php _e('TEMPLATING SYSTEM', 'wpd'); ?></h3>
                              <p><?php _e('Creating the perfect design from scratch can be exhausting. Woocommerce Product Designer lets you create unique designs your clients can browse and start theirs from.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=templating%20system" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-features-Yellow-18.svg">
                              <h3 class="wpd-title"><?php _e('PRINT READY FILES', 'wpd'); ?></h3>
                              <p><?php _e('The product designer understand the value of a print ready PDF file and lets you generate up to 300dpi PDF files with entirely customizable crop and bleed marks.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=print%20ready%20files" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>Pricesbasedonthedesign-19.svg">
                              <h3 class="wpd-title"><?php _e('PRICE BASED ON THE DESIGN', 'wpd'); ?></h3>
                              <p><?php _e('Increase the price based on the number of characters in the text elements, the number of vectors or pictures used in few clicks.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=price%20based%20on%20the%20design" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-03.svg">
                              <h3 class="wpd-title"><?php _e('SUPPORTS ANY FONT', 'wpd'); ?></h3>
                              <p><?php _e('WooCommerce Product Designer lets you easily setup your own fonts, no matter if they are web fonts such as google fonts or custom TTF files.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=supports%20any%20font" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-13.svg">
                              <h3 class="wpd-title"><?php _e('USER UPLOADS CONTROL', 'wpd'); ?></h3>
                              <p><?php _e('Woocommerce Product Designer gives you the entire control on your customers uploads by defining the minimum allowed dimensions and files extensions.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=user%20uploads%20control" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-11.svg">
                              <h3 class="wpd-title"><?php _e('MULTIPLE OUTPUT FORMATS', 'wpd'); ?></h3>
                              <p><?php _e('WooCommerce product Designer can generate multiple output formats such as PDF, PNG, JPEG and SVG and is able to handle up to 15000px wide outputs. CYMK also supported.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=multiple%20output%20formats" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-10.svg">
                              <h3 class="wpd-title"><?php _e('CUSTOM DESIGNS UPLOADS', 'wpd'); ?></h3>
                              <p><?php _e('Do you have customers who don’t necessarily need to go through the design phase? The Woocommerce Product Designer got you covered by allowing them to send you files as attachments to their orders.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=custom%20designs%20uploads" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-07.svg">
                              <h3 class="wpd-title"><?php _e('VECTORS INTEGRATION', 'wpd'); ?></h3>
                              <p><?php _e('Vectors have become a standard in the web to print industry. Woocommerce Product Designer includes a SVG file editor which allows your clients to use and modify their vectors right in the edition area.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=vectors%20integration" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-08.svg">
                              <h3 class="wpd-title"><?php _e('CUSTOM COLORS PALETTE', 'wpd'); ?></h3>
                              <p><?php _e('Do you need to limit the colors that can be used by the customers in their designs? Woocommerce Product Designer allows you to define a custom color palette that can be used for any text, shape or vector.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=custom%20colors%20palette" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-14.svg">
                              <h3 class="wpd-title"><?php _e('SOCIAL NETWORK INTEGRATION', 'wpd'); ?></h3>
                              <p><?php _e('Social networks are today part of everything. Woocommerce Product Designer knows it and let your clients extract and use pictures from their facebook and instagram accounts.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=social%20network%20integration" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-09.svg">
                              <h3 class="wpd-title"><?php _e('ADVANCED DESIGN PRICING', 'wpd'); ?></h3>
                              <p><?php _e('Woocommerce Product Designer takes the product pricing to a whole new level by allowing you to define your own pricing rules based on the elements (pictures, text, shapes…) used by your clients in their designs.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=advanced%20design%20pricing" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-12.svg">
                              <h3 class="wpd-title"><?php _e('DESIGNS AND ORDERS HISTORY', 'wpd'); ?></h3>
                              <p><?php _e('Woocommerce Product Designer let your clients either access their previous ordered designs and start new ones from them or save their design for later.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=designs%20and%20orders%20history" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-Yellow-04.svg">
                              <h3 class="wpd-title"><?php _e('HIGH QUALITY OUTPUTS', 'wpd'); ?></h3>
                              <p><?php _e('Woocommerce Product Designer allows you to configure your output file dimensions and is able to generate up to 15000px wide files, including PDF in portrait or landscape.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=high%20quality%20outputs" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                          <div class="o-col col xl-1-3">
                              <img class="vc_single_image-img " src="<?php echo WPD_URL."admin/images/features/"?>designersuite4wp-features-Yellow-17.svg">
                              <h3 class="wpd-title"><?php _e('MULTIPLE PRODUCTS ORDERING OPTION', 'wpd'); ?></h3>
                              <p><?php _e('Easily order multiple sizes, colors or any variation attributes of the same custom design in few clicks.', 'wpd'); ?></p>
                              <a href="https://designersuiteforwp.com/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=multiple%20products%20ordering%20option" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                          </div>
                      </div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php
    }

    /**
     * Gets the settings and put them in a global variable
     * @global array $wpd_settings Settings
     */
    function init_globals() {
        GLOBAL $wpd_settings;
        $wpd_settings['wpc-general-options'] = get_option("wpc-general-options");
        $wpd_settings['wpc-texts-options'] = get_option("wpc-texts-options");
        $wpd_settings['wpc-shapes-options'] = get_option("wpc-shapes-options");
        $wpd_settings['wpc-images-options'] = get_option("wpc-images-options");
        $wpd_settings['wpc-designs-options'] = get_option("wpc-designs-options");
        $wpd_settings['wpc-colors-options'] = get_option("wpc-colors-options");
        $wpd_settings['wpc-output-options'] = get_option("wpc-output-options");
        $wpd_settings['wpc_social_networks'] = get_option("wpc_social_networks");
        $wpd_settings['wpc-upload-options'] = get_option("wpc-upload-options");
        $wpd_settings['wpc-licence'] = get_option("wpc-licence");
        $wpd_settings['wpc-ui-options'] = get_option("wpc-ui-options");
    }

    private function get_admin_option_field($title, $option_group, $field_name, $type, $default, $class, $css, $tip, $options_array) {
        $field = array(
            'title' => __($title, 'wpd'),
            'name' => $option_group . '[' . $field_name . ']',
            'type' => $type,
            'default' => $default,
            'class' => $class,
            'css' => $css,
            'desc' => __($tip, 'wpd')
        );
        if (!empty($options_array))
            $field['options'] = $options_array;
        return $field;
    }

    /**
     * Callbacks which prints the icon selector field
     * @param type $field Field to print
     */
    public function get_icon_selector_field($field) {
        echo $field["value"];
    }

    private function get_admin_color_field($group_option, $prefix = "") {
        if (!empty($prefix)) {
            return array(
                'label-color' => get_proper_value($group_option, $prefix . '-label-color', ""),
                'normal-color' => get_proper_value($group_option, $prefix . '-normal-color', ""),
                'selected-color' => get_proper_value($group_option, $prefix . '-selected-color')
            );
        } else {
            return array(
                'label-color' => get_proper_value($group_option, 'label-color', ""),
                'normal-color' => get_proper_value($group_option, 'normal-color', ""),
                'selected-color' => get_proper_value($group_option, 'selected-color', "")
            );
        }
    }

    /**
     * Builds the general settings options
     * @return array Settings
     */
    public function get_front_tools_settings() {

        $options = array();
        $defaults_text_fields = array();
        $defaults_shape_fields = array();

        $this->get_skins_settings();


        $actions_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Editor colors', 'wpd'),
            'id' => 'wpc-interface-colors',
            'table' => 'options'
        );
        $actions_options_end = array('type' => 'sectionend');

        $text_default_color_field = $this->get_admin_option_field("Text", "wpc-ui-options", "default-text-color", 'text', '#4f71b9', 'wpc-color', '', '', '');
        $bg_default_color_field = $this->get_admin_option_field("Background", "wpc-ui-options", "default-background-color", 'text', '#4f71b9', 'wpc-color', '', '', '');
        $outline_bg_default_color_field = $this->get_admin_option_field("Outline", "wpc-ui-options", "default-outline-background-color", 'text', '#4f71b9', 'wpc-color', '', '', '');
        array_push($defaults_text_fields, $text_default_color_field);
        array_push($defaults_text_fields, $bg_default_color_field);
        array_push($defaults_text_fields, $outline_bg_default_color_field);
        $shape_default_color_field = $this->get_admin_option_field("Background", "wpc-ui-options", "default-shape-background-color", 'text', '#4f71b9', 'wpc-color', '', '', '');
        $shape_outline_bg_default_color_field = $this->get_admin_option_field("Outline", "wpc-ui-options", "default-shape-outline-background-color", 'text', '#4f71b9', 'wpc-color', '', '', '');
        array_push($defaults_shape_fields, $shape_default_color_field);
        array_push($defaults_shape_fields, $shape_outline_bg_default_color_field);
        $default_text_colors = array(
            'title' => __("Default Text Colors", "wpd"),
            'type' => 'groupedfields',
            'fields' => $defaults_text_fields
        );
        $default_shape_colors = array(
            'title' => __("Default Shape Colors", "wpd"),
            'type' => 'groupedfields',
            'fields' => $defaults_shape_fields
        );
        array_push($options, $actions_options_begin);
//        $options = array_merge($options, $color_grouped_fields);
        array_push($options, $default_text_colors);
        array_push($options, $default_shape_colors);
        array_push($options, $actions_options_end);

        echo o_admin_fields($options);
    }

    private function get_skins_settings() {
        $skin_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-skin-container',
            'table' => 'options',
        );
        $skins_arr = apply_filters("wpd_configuration_skins", array(
            "WPD_Skin_Default" => __("Default", "wpd"),
            "WPD_Skin_Jonquet" => __("Jonquet", "wpd"),
        ));


        $skins = array(
            'title' => __('Skin', 'wpd'),
            'name' => 'wpc-ui-options[skin]',
            'type' => 'select',
            'options' => $skins_arr,
//            'default' => 'WPD_Skin_Jonquet',
            'class' => 'chosen_select_nostd wpd-config-skin',
            'desc' => __('Editor look and feel.', 'wpd'),
        );

        $skin_end = array('type' => 'sectionend');
        $skin_settings = apply_filters("vpc_skins_settings", array(
            $skin_begin,
            $skins,
            $skin_end
        ));

        echo o_admin_fields($skin_settings);
    }

    private function get_general_settings() {
        $options = array();

        $general_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-general-options',
            'table' => 'options',
            'title' => __('General Settings', 'wpd')
        );

        $args = array(
            "post_type" => "page",
            "nopaging" => true,
        );

        $customizer_page = array(
            'title' => __('Design Page', 'wpd'),
            'desc' => __('This setting allows the plugin to locate the page where customizations are made. Please note that this page can only be accessed by our plugin and should not appear in any menu.', 'wpd'),
            'name' => 'wpc-general-options[wpc_page_id]',
            'type' => 'post-type',
            'default' => '',
            'class' => 'chosen_select_nostd',
            'args' => $args
        );

        $content_filter = array(
            'title' => __('Automatically append canvas to the customizer page', 'wpd'),
            'name' => 'wpc-general-options[wpc-content-filter]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to use a shortcode to display the the customizer in the selected page.', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd',
        );


        $customizer_cart_display = array(
            'title' => __('Parts position in cart', 'wpd'),
            'name' => 'wpc-general-options[wpc-parts-position-cart]',
            'default' => 'thumbnail',
            'type' => 'radio',
            'desc' => __('This option allows you to set where to show your customized products parts on the cart page', 'wpd'),
            'options' => array(
                'thumbnail' => __('Thumbnail column', 'wpd'),
                'name' => __('Name column', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );


        $download_button = array(
            'title' => __('Download design', 'wpd'),
            'name' => 'wpc-general-options[wpc-download-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the download button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );
        
        $preview_button = array(
            'title' => __('Preview design', 'wpd'),
            'name' => 'wpc-general-options[wpc-preview-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the preview button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );

        $hide_design_buttons_cart_page = array(
            'title' => __('Hide design buttons on shop page', 'wpd'),
            'name' => 'wpc-general-options[wpc-hide-btn-shop-pages]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the cart button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );
        $add_to_cart_action = array(
            'title' => __('Redirect after adding a custom design to the cart?', 'wpd'),
            'name' => 'wpc-general-options[wpc-redirect-after-cart]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define what to do after adding a design to the cart', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );

        $responsive_canvas = array(
            'title' => __('Responsive behaviour', 'wpd'),
            'name' => 'wpc-general-options[responsive]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to enable the canvas responsive behaviour.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );
        
        $disable_keyboard_shortcuts = array(
            'title' => __('Disable keyword shortcuts', 'wpd'),
            'name' => 'wpc-general-options[disable-keyboard-shortcuts]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to disable the keyboard shortcuts if needed.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );

        $hide_requirements_notices = array(
            'title' => __('Hide requirements notices', 'wpd'),
            'name' => 'wpc-general-options[hide-requirements-notices]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to hide the requirement notice.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd')
            ),
            'row_class' => 'wpd_hide_requirements',
            'class' => 'chosen_select_nostd'
        );

        $general_options_end = array('type' => 'sectionend');


        $conflicts_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_conflicts_options',
            'title' => __('Scripts management', 'wpd'),
            'table' => 'options'
        );

        $load_bs_modal = array(
            'title' => __('Load bootsrap modal', 'wpd'),
            'name' => 'wpc-general-options[wpc-load-bs-modal]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to enable/disable twitter\'s bootstrap modal script', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );
        $conflicts_options_end = array('type' => 'sectionend');



        array_push($options, $general_options_begin);
        array_push($options, $customizer_page);
        array_push($options, $content_filter);
        array_push($options, $customizer_cart_display);
        array_push($options, $preview_button);
        array_push($options, $download_button);
        array_push($options, $add_to_cart_action);
        array_push($options, $responsive_canvas);
        array_push($options, $disable_keyboard_shortcuts);
        array_push($options, $hide_design_buttons_cart_page);
        array_push($options, $hide_requirements_notices);
        array_push($options, $general_options_end);
        array_push($options, $conflicts_options_begin);
        array_push($options, $load_bs_modal);
        array_push($options, $conflicts_options_end);

        $options = apply_filters("wpd_general_options", $options);
        echo o_admin_fields($options);
    }

    /**
     * Builds the uploads settings options
     * @return array Settings
     * @return array
     */
    private function get_uploads_settings() {

        $uploader_type = array(
            'title' => __('File upload script', 'wpd'),
            'name' => 'wpc-upload-options[wpc-uploader]',
            'default' => 'custom',
            'type' => 'radio',
            'desc' => __('This option allows you to set which file upload script you would like to use', 'wpd'),
            'options' => array(
                'custom' => __('Custom with graphical enhancements', 'wpd'),
                'native' => __('Normal', 'wpd')
            ),
            'class' => 'chosen_select_nostd'
        );
        
        $upl_extensions = array(
            'title' => __('Allowed uploads extensions', 'wpd'),
            'name' => 'wpc-upload-options[wpc-upl-extensions]',
            'default' => array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'),
            'type' => 'multiselect',
            'desc' => __('Allowed extensions for uploads', 'wpd'),
            'options' => array(
                'jpg' => __('jpg', 'wpd'),
                'jpeg' => __('jpeg', 'wpd'),
                'png' => __('png', 'wpd'),
                'gif' => __('gif', 'wpd'),
                'bmp' => __('bmp', 'wpd'),
                'svg' => __('svg', 'wpd'),
                'psd' => __('psd', 'wpd'),
                'eps' => __('eps', 'wpd'),
                'pdf' => __('pdf', 'wpd'),
            )
        );
        $upload_settings_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-upload-options',
            'title' => __('Uploads Settings', 'wpd'),
            'table' => 'options'
        );

        $upload_settings_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-upload-options'
        );

        $options = array();
        array_push($options, $upload_settings_begin);
        array_push($options, $uploader_type);
        array_push($options, $upl_extensions);

        array_push($options, $upload_settings_end);
        $options = apply_filters("wpd_uploads_options", $options);
        echo o_admin_fields($options);
    }

    /**
     * Builds the colors settings options
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_colors_settings() {
        $options = array();

        $line_color = array(
            'title' => __('Line Color', 'wpd'),
            'name' => 'wpc-colors-options[line-color]',
            'type' => 'text',
            'class' => 'wpc-color',
        );
        $colors_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-colors-options',
            'title' => __('Colors Settings', 'wpd'),
            'table' => 'options'
        );


        $colors_options_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-colors-options'
        );
        array_push($options, $colors_options_begin);
        array_push($options, $line_color);
        array_push($options, $colors_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the images settings options
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_images_settings() {
        GLOBAL $wpd_settings;
        $options = array();

        $images_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Image Settings', 'wpd'),
            'table' => 'options',
            'id' => 'wpc-images-options',
        );

        $images_options_end = array('type' => 'sectionend');
        $images_all_options = array(
            array(
                'title' => __('Opacity', 'wpd'),
                'name' => 'wpc-images-options[opacity]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Opacity setting in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => ''
            ),
            array(
                'title' => __('Enable lazyload for cliparts galleries', 'wpd'),
                'name' => 'wpc-images-options[lazy]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the lazyload behavior in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => ''
            ),
        );

        array_push($options, $images_options_begin);
        $options = array_merge($options, $images_all_options);
        array_push($options, $images_options_end);
        echo o_admin_fields($options);
    }
    
    private function get_data_upgraders() {
        $options = array();
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-licence',
            'title' => __('Licence Settings', 'wpd'),
            'table' => 'options'
        );

        $end = array(
            'type' => 'sectionend',
            'id' => 'wpc-licence'
        );
        $envato_username = array(
            'title' => __('V5.x', 'wpd'),
            'desc' => __('This will run the data migration from previous versions to 5.0.', 'wpd'),
            'type' => 'custom',
            'callback' => array($this, 'get_v5_upgrader_buttons'),
        );
        

        array_push($options, $begin);
        array_push($options, $envato_username);
        array_push($options, $end);
        echo o_admin_fields($options);
    }
    
    function get_v5_upgrader_buttons()
    {
        ?>
<input type="button" class="button button-primary run-wpd-upgrader" data-version="5" value="Run" style="float: left;"> 
        <div class="wpd-migrate-loading loading" style="display:none;float: left;"></div>
        <br>
        <br>
        <ul style="list-style: circle;margin-left: 20px;">
            <li><?php _e('Products configurations were introduced from the version v5.0 of the product designer', 'wpd');?></li>
            <li><?php _e('This process will extract all parameters from custom products and create the configurations accordingly.', 'wpd');?></li>
            <li><?php _e('If you already have configurations assigned to custom products, this process will create new configurations and assign them to existing products.', 'wpd');?></li>
        </ul>
        <?php
    }

    /**
     * Builds the settings page
     */
    function get_settings_page() {
        wpd_remove_transients();

        if (isset($_POST) && !empty($_POST)) {
            $this->save_wpc_tab_options();
            GLOBAL $wp_rewrite;
//            $this->wpd_add_rewrite_rules();
            $wp_rewrite->flush_rules(false);
        }
        wp_enqueue_media();
        ?>
        <form method="POST">
            <div id="wpc-settings">
                <div class="wrap">
                    <h2><?php _e("Woocommerce Products Designer Settings", "wpd"); ?></h2>
                </div>
                <div id="TabbedPanels1" class="TabbedPanels">
                    <ul class="TabbedPanelsTabGroup ">
                        <li class="TabbedPanelsTab " tabindex="1"><span><?php _e('General', 'wpd'); ?></span> </li>
                        <li class="TabbedPanelsTab" tabindex="2"><span><?php _e('Uploads', 'wpd'); ?> </span></li>
                        <li class="TabbedPanelsTab" tabindex="3" style="display: none;"><span><?php _e('Data Upgraders', 'wpd'); ?></span></li>

                    </ul>

                    <div class="TabbedPanelsContentGroup">
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
        <?php
        $this->get_general_settings();
        ?>                              
                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
                                <?php
                                $this->get_uploads_settings();
                                ?>
                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
                                <?php
                                $this->get_data_upgraders();
                                ?>                              
                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
            <input type="submit" value="<?php _e("Save", "wpd"); ?>" class="button button-primary button-large mg-top-10-i">
        </form>
        <?php
    }

    /**
     * Save the settings
     */
    private function save_wpc_tab_options() {
        if (isset($_POST) && !empty($_POST)) {
            $settings = array("wpc-general-options", "wpc-images-options", "wpc-upload-options");

            foreach ($settings as $key) {
                if(isset($_POST[$key]))
                    update_option($key, $_POST[$key]);
            }

            $this->init_globals();
            ?>
            <div id="message" class="updated below-h2"><p><?php echo __("Settings successfully saved.", "wpd"); ?></p></div>
            <?php
        }
    }

    private function get_custom_palette() {
        GLOBAL $wpd_settings;
        $colors_options = $wpd_settings['wpc-colors-options'];
        $wpc_palette_type = get_proper_value($colors_options, 'wpc-color-palette', "");
        $palette_style = "";
        if (isset($wpc_palette_type) && $wpc_palette_type != "custom")
            $palette_style = "style='display:none;'";
        $palette = get_proper_value($colors_options, 'wpc-custom-palette', "");
        $custom_palette = '<table class="form-table widefat" id="wpd-predefined-colors-options" ' . $palette_style . '>
                <tbody>
                    <tr valign="top">
                <th scope="row" class="titledesc"></th>
                    <td class="forminp">
                    <div class="wpc-colors">';
        if (isset($palette) && is_array($palette)) {
            foreach ($palette as $color) {
                $custom_palette.='<div>
                                    <input type="text" name="wpc-colors-options[wpc-custom-palette][]"style="background-color: ' . $color . '" value="' . $color . '" class="wpc-color">
                                        <button class="button wpc-remove-color">Remove</button>
                                </div>';
            }
        }
        $custom_palette.='</div>
                        <button class="button mg-top-10" id="wpc-add-color">Add color</button>
                    </td>
                    </tr>
                </tbody>
   </table>';
        return $custom_palette;
    }

    /**
     * Format the checkbox option in the settings
     * @param type $option_name
     * @param type $option_array
     */
    private function transform_checkbox_value($option_name, $option_array) {
        foreach ($option_array as $option) {
            if (!isset($_POST[$option_name][$option]))
                $_POST[$option_name][$option] = 'yes';
        }
    }

    /**
     * Alerts the administrator if the customization page is missing
     * @global array $wpd_settings
     */
    function notify_customization_page_missing() {
        GLOBAL $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        $settings_url = get_bloginfo("url") . '/wp-admin/admin.php?page=wpc-manage-settings';
        if (empty($wpc_page_id))
            echo '<div class="error">
                   <p><b>Woocommerce product Designer: </b>The customizer page is not defined. Please configure it in <a href="' . $settings_url . '">woocommerce page settings</a>: .</p>
                </div>';
        if (!extension_loaded('zip'))
            echo '<div class="error">
                   <p><b>Woocommerce product Designer: </b>ZIP extension not loaded on this server. You won\'t be able to generate zip outputs.</p>
                </div>';
    }
    
    function get_help_notices() {
        $screen = get_current_screen();
        if (isset( $screen->base ) && ($screen->base =='wpd_page_wpc-manage-fonts')) 
        {
            echo '<div class="wpd-info">
                   <p><b>'.__('Woocommerce Product Designer: </b>Learn more about fonts management', 'wad').' <a class="button" href="https://goo.gl/wZhfqv" target="_blank">'.__('here', 'wad').'</a></p>
                </div>';
        }
    }
    
    function get_missing_parts_notice() {
        $screen = get_current_screen();
        
        if (isset( $screen->post_type ) && ($screen->post_type =='wpd-config') && isset( $_GET['action']) && ($_GET['action'] =='edit')) 
        {
            $config_id=  $_GET['post'];
            $metas=  get_post_meta($config_id, 'wpd-metas', true);
            $parts= get_proper_value($metas, 'parts');
            if(!$parts)
            echo '<div class="error wpd-error">
                   <p>'.__('This configuration has no part. Please set at least one in the parts section of this page.', 'wpd').'</p>
                </div>';
        }
    }

    /**
     * Alerts the administrator if the minimum requirements are not met
     */
    function notify_minmimum_required_parameters() {
        GLOBAL $wpd_settings;
        $general_options = get_proper_value($wpd_settings, 'wpc-general-options');
        $hide_notices = get_proper_value($general_options, "hide-requirements-notices", false);
        if ($hide_notices)
            return;
        $message = "";
        $minimum_required_parameters = array(
            "memory_limit" => array(128, "M"),
            "max_input_vars" => array(5000, ""),
            "post_max_size" => array(128, "M"),
            "upload_max_filesize" => array(128, "M"),
        );
        foreach ($minimum_required_parameters as $key => $min_arr) {
            $defined_value = ini_get($key);
            $defined_value_int = str_replace($min_arr[1], "", $defined_value);
            if ($defined_value_int < $min_arr[0])
                $message.="Your PHP setting <b>$key</b> is currently set to <b>$defined_value</b>. We recommand to set this value at least to <b>" . implode("", $min_arr) . "</b> to avoid any issue with our plugin.<br>";
        }

        $edit_msg = __("How to fix this: You can edit your php.ini file to increase the specified variables to the recommanded values or you can ask your hosting company to make the changes for you.", "wpd");
        
        if (!empty($message))
            echo '<div class="error">
                   <p><b>Woocommerce Product Designer: </b><br>' . $message . '<br>
                       <b>' . $edit_msg . '</b></p>
                </div>';
        
        $message='';
        $permalinks_structure=get_option('permalink_structure');
        if(strpos( $permalinks_structure, 'index.php')!==FALSE)
                $message.="Your permalinks structure is currently set to <b>custom</b> with index.php present in the structure. We recommand to set this value to <b>Post name</b> to avoid any issue with our plugin.<br>";
        if (!empty($message))
            echo '<div class="error">
                   <p><b>Woocommerce Product Designer: </b><br>' . $message . '</p>
                </div>';
    }

    /**
     * Checks if the database needs to be upgraded
     */
    function run_wpc_db_updates_requirements() {
        $v5_upgrade=  get_option('wpd-db-version');
        if (
                $this->get_meta_count('s:15:"is-customizable";s:1:"1"', false) > 0 && (version_compare( $v5_upgrade, '5.0', '<')||$v5_upgrade==FALSE)
        ) {
            ?>
            <div class="updated" id="wpc-updater-container">
                <strong><?php echo _e("Woocommerce Product Designer database update required", "wpd"); ?></strong>
                <div>
            <?php echo _e("Hi! This version of the Woocommerce Product Designer made some changes in the way it's data are stored. <br>You can learn more about the changes <a href='https://designersuiteforwp.com/v5-changelog-details/' target='_blank'>here</a>.<br>In order to work properly, we just need you to:"
                    . "<ol>"
                    . "<li><span style='font-weight: bold; color: red;'>Backup</span> your entire website in case something goes wrong during the process.</li>"
                    . "<li>Click on the \"Run Updater\" button to move your old settings to the new structure. </li>"
                    . "</ol>  ", "wpd"); ?>
                    
                    <input type="button" value="<?php echo _e("Run the updater", "wpd"); ?>" id="wpc-run-updater" class="button button-primary"/>
                    <br>
                    <strong style='color: red;'><?php echo _e('Note:', 'wpd');?> </strong> <?php echo _e('If something goes wrong, this updater will still be available in <strong>WPD > Settings > Updaters</strong>', 'wpd');?>
                    <div class="loading" style="display:none;"></div>
                </div>
            </div>
            <style>
                #wpc-updater-container
                {
                    padding: 3px 17px;
                    font-size: 15px;
                    line-height: 36px;
                    margin-left: 0px;
                    border-left: 5px solid #e14d43 !important;
                }
                #wpc-updater-container.done
                {
                    border-color: #7ad03a !important;
                }
                #wpc-run-updater {
                    background: #e14d43;
                    border-color: #d02a21;
                    color: #fff;
                    -webkit-box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
                    box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
                    text-shadow: none;
                }

                #wpc-run-updater:focus, #wpc-run-updater:hover {
                    background: #dd362d;
                    border-color: #ba251e;
                    color: #fff;
                    -webkit-box-shadow: inset 0 1px 0 #e8756f;
                    box-shadow: inset 0 1px 0 #e8756f;
                }
                .loading
                {
                    background: url("<?php echo WPD_URL; ?>/admin/images/spinner.gif") 10% 10% no-repeat transparent;
                    background-size: 111%;
                    width: 32px;
                    height: 40px;
                    display: inline-block;
                }
            </style>
            <script>
                //jQuery('.loading').hide();
                jQuery('#wpc-run-updater').click('click', function () {
                    var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
                    if (confirm("It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?")) {
                        jQuery('.loading').show();
                        jQuery.post(
                                ajax_url,
                                {
                                    action: 'run_updater',
                                    version: 5
                                },
                        function (data) {
                            jQuery('.loading').hide();
                            jQuery('#wpc-updater-container').html(data);
                            jQuery('#wpc-updater-container').addClass("done");
                        }
                        );
                    }

                });
            </script>
            <?php
        }
//        else if (empty($wpc_page_id))//First installation
//            update_option("wpd-db-version", WPD_VERSION);
    }

    /**
     * Returns the number of occurences corresponding to a post meta key
     * @global type $wpdb Database object
     * @param type $meta Meta to check
     * @param type $meta_key Is the meta a meta_key of a meta_value
     * @return int Number of occurences
     */
    private function get_meta_count($meta, $meta_key=true) {
        global $wpdb;
        if($meta_key)
            $sql_result = $wpdb->get_var(
                    "
                                SELECT count(*)
                                FROM $wpdb->posts p
                                JOIN $wpdb->postmeta pm on pm.post_id = p.id
                                WHERE p.post_type = 'product'
                                AND pm.meta_key = '" . $meta . "' 
                                AND p.post_status = 'publish'
                          ");
        else
            $sql_result = $wpdb->get_var(
                    "
                                SELECT count(*)
                                FROM $wpdb->posts p
                                JOIN $wpdb->postmeta pm on pm.post_id = p.id
                                WHERE p.post_type = 'product'
                                AND pm.meta_value like '%" . $meta . "%' 
                                AND p.post_status = 'publish'
                          ");
        return $sql_result;
    }

    /**
     * Runs the database upgrade
     */
    function run_wpd_updater() {
        $target=  filter_input(INPUT_POST, 'version');
        switch ($target) {
            case 5:
                $config=new WPD_Config();
                $config->migrate_metas_to_v5();
                $message = __("Your database has been successfully updated.", "wpd");
            break;
        }
        
        echo $message;
        die();
    }

    function wpc_add_custom_mime_types($mimes) {
        return array_merge($mimes, array(
            'svg' => 'image/svg+xml',
            'ttf' => 'application/x-font-ttf',
            'icc' => 'application/vnd.iccprofile',
                //'ttf' => 'application/x-font-truetype',
        ));
    }
    
    public function get_max_input_vars_php_ini() {
        $total_max_normal = ini_get('max_input_vars');
        $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", "vpc");
        ?> 
        <script type="text/javascript">
            var o_max_input_vars = <?php echo $total_max_normal; ?>;
            var o_max_input_msg = "<?php echo $msg; ?>";
        </script>         
        <?php
    }

}
