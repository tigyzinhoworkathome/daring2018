<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpd
 * @subpackage Wpd/public
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Public {

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
     * @param      string    $wpd       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($wpd, $version) {

        $this->wpd = $wpd;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    3.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wpd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wpd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->wpd, plugin_dir_url(__FILE__) . 'css/wpd-public.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-simplegrid", WPD_URL . 'admin/css/simplegrid.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-common", WPD_URL. 'public/css/wpd-common.css', array(), $this->version, 'all');
//                wp_enqueue_style( "SpryAccordion-css", WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.css', array(), $this->version, 'all' );
        wp_enqueue_style("wpd-tooltip-css", WPD_URL . 'admin/css/tooltip.min.css', array(), $this->version, 'all');
//                wp_enqueue_style( "wpd-fancyselect-css", WPD_URL . 'public/css/fancySelect.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-colorpicker-css", WPD_URL . 'admin/js/colorpicker/css/colorpicker.min.css', array(), $this->version, 'all');
        wp_enqueue_style("wpd-bs-modal-css", WPD_URL . 'public/js/modal/modal.min.css', array(), $this->version, 'all');


//                $design_page = $general_options['wpc_page_id'];
//                if(function_exists("icl_object_id"))
//                    $design_page= icl_object_id($design_page, 'page', false,ICL_LANGUAGE_CODE);
//                
//                $current_page_id=  get_the_ID();
//                if ( $design_page ==$current_page_id)
//                {
//                    WPD_editor::register_fonts();
//                }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    3.0
     */
    public function enqueue_scripts() {
        GLOBAL $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        wp_enqueue_script('jquery');
        wp_enqueue_script("wpd-tooltip-js", WPD_URL . '/admin/js/tooltip.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wpd-colorpicker-js", WPD_URL . 'admin/js/colorpicker/js/colorpicker.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->wpd, plugin_dir_url(__FILE__) . 'js/wpd-public.js', array('jquery'), $this->version, false);
//        if (!isset($options["wpc-load-bs-modal"]) || ($options["wpc-load-bs-modal"] == "1")) {
            wp_enqueue_script('wpd-bs-modal', WPD_URL . 'public/js/modal/modal.min.js', array('jquery'), $this->version, false);
//        }
        wp_localize_script($this->wpd, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    /**
     * Register the plugin shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('wpc-products', array($this, 'get_products_display'));
        add_shortcode('wpc-editor', array($this, 'get_editor_shortcode_handler'));
        
        add_shortcode('wpd-products', array($this, 'get_products_display'));
        add_shortcode('wpd-editor', array($this, 'get_editor_shortcode_handler'));
    }

    public function get_editor_shortcode_handler() {
        global $wp_query;
        if(!isset($wp_query->query_vars["product_id"]))
            return __("You're trying to access the customization page whitout a product to customize. This page should only be accessed using one of the customization buttons.", "wpd");
        $item_id = $wp_query->query_vars["product_id"];
        $editor_obj = new WPD_Editor($item_id);
        return $editor_obj->get_editor();
    }

    function get_products_display($atts) {
        global $wpdb;
        
        extract(shortcode_atts(array(
            'cat' => '',
            'products' => '',
            'cols' => '3'
                        ), $atts, 'wpc-products'));

        $where = "";
        if (!empty($cat)) {
            $where.=" AND $wpdb->term_relationships.term_taxonomy_id IN ($cat)";
        } else if (!empty($products))
            $where.=" AND p.ID IN ($products)";
        else
            $where = "";
        $search = '"is-customizable";s:1:"1"';

        $products = $wpdb->get_results(
                "
                            SELECT distinct p.id
                            FROM $wpdb->posts p
                            JOIN $wpdb->postmeta pm on pm.post_id = p.id
                            INNER JOIN $wpdb->term_relationships ON (p.ID = $wpdb->term_relationships.object_id	) 
                            WHERE p.post_type = 'product'
                            AND p.post_status = 'publish'
                            AND pm.meta_key = 'wpc-metas'
                            $where
                            AND pm.meta_value like '%$search%'
                            ");
        ob_start();
        ?>
        <div class='container wp-products-container wpc-grid wpc-grid-pad'>
            <?php
            $shop_currency_symbol = get_woocommerce_currency_symbol();
            foreach ($products as $product) {
                $prod = wc_get_product($product->id);
                $url = get_permalink($product->id);
                $wpd_product = new WPD_Product($product->id);
                $wpc_metas = $wpd_product->settings;
                $can_design_from_blank = get_proper_value($wpc_metas, 'can-design-from-blank', "");
                $template_pages_urls = $this->get_template_pages($product->id, $prod, $wpc_metas);
                ?>
                <div class='wpc-col-1-<?php echo $cols; ?> cat-item-ctn'>
                    <div class='cat-item'>
                        <h3><?php echo $prod->get_title(); ?> 
                            <span><?php echo $shop_currency_symbol . '' . $prod->get_price() ?></span>
                        </h3>
                        <?php echo get_the_post_thumbnail($product->id, 'medium'); ?>
                        <hr>
                        <?php
                        if ($prod->get_type() == "simple") {
                            if (!empty($can_design_from_blank)) {
                                ?><a href="<?php echo $wpd_product->get_design_url() ?>" class='btn-choose wpc-customize-product'> <?php _e("Design from blank", "wpd"); ?></a><?php
                            }
                            if (array_key_exists($product->id, $template_pages_urls)) {
                                $templates_page_url = $template_pages_urls[$product->id];
                                echo '<a href="' . $templates_page_url . '" class="btn-choose tpl"> ' . __("Browse our templates", "wpd") . '</a>';
                            }
                        } else {
                            ?><a href="<?php echo $url; ?>" class='btn-choose wpc-customize-product'> <?php _e("Design from blank", "wpd"); ?></a><?php
                            $variations = $prod->get_available_variations();
                            foreach ($variations as $variation) {
                                $variation_id = $variation['variation_id'];
                                if (array_key_exists($variation_id, $template_pages_urls) || array_key_exists($product->id, $template_pages_urls)) {
                                    echo '<a href="' . $url . '" class="btn-choose tpl" id="btn-tpl"> ' . __("Browse our templates", "wpd") . '</a>';
                                    break;
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    function get_design_from_blank_urls($post_id, $product) {
        $design_from_blank_urls = array();
        if ($product->get_type() == 'variable') {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                $wpd_product = new WPD_Product($variation_id);
                $design_from_blank_urls[$variation_id] = $wpd_product->get_design_url();
            }
        } else {
            $wpd_product = new WPD_Product($post_id);
            $design_from_blank_urls[$post_id] = $wpd_product->get_design_url();
        }
        ?>
        <script>
            var design_from_blank_urls =<?php echo json_encode($design_from_blank_urls); ?>;
        </script>
        <?php
    }

    function get_template_pages($post_id, $product, $wpc_metas) {
        global $wpd_settings;
        $general_options = $wpd_settings['wpc-general-options'];
        $global_templates_page=  get_proper_value($general_options, "wpd-templates-page");
        $template_pages_urls = array();
        $templates_page = get_proper_value($wpc_metas, 'templates-page', $global_templates_page);
        if (!empty($templates_page)) {
            $templates_page_url = get_permalink($templates_page);
            $template_pages_urls[$post_id] = $templates_page_url;
        }
        if ($product->get_type() == 'variable') {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                $variations_canvas_datas = get_proper_value($wpc_metas, $variation_id, array());
                $templates_page = get_proper_value($variations_canvas_datas, 'templates-page', "");
                if (!empty($templates_page)) {
                    $templates_page_url = get_permalink($templates_page);
                    $template_pages_urls[$variation_id] = $templates_page_url;
                }
            }
        } else {
            $variations_canvas_datas = get_proper_value($wpc_metas, $post_id, array());
            $templates_page = get_proper_value($variations_canvas_datas, 'templates-page', "");
            if (!empty($templates_page)) {
                $templates_page_url = get_permalink($templates_page);
                $template_pages_urls[$post_id] = $templates_page_url;
            }
        }
        ?>
        <script>
            if(typeof template_pages_urls!="undefined")
            {
                var wpd_tpl_tmp=<?php echo json_encode($template_pages_urls); ?>;
                for (var attrname in wpd_tpl_tmp) { template_pages_urls[attrname] = wpd_tpl_tmp[attrname]; }
            }
            else
            {
                var template_pages_urls =<?php echo json_encode($template_pages_urls); ?>;
                var template_page_message = "<?php _e("No template available for this product.", "wpd"); ?>";
            }
        </script>
        <?php
        return $template_pages_urls;
    }
    
    function get_customize_btn()
    {
        $product_id = get_the_ID();
        $wpd_product=new WPD_Product($product_id);
        echo $wpd_product->get_buttons(true);
    }
    
    function get_customize_btn_loop($html, $product)
    {
        global $wpd_settings;
        $general_options = $wpd_settings['wpc-general-options'];
        $hide_buttons_shop_page=  get_proper_value($general_options, "wpc-hide-btn-shop-pages", 0);
        if($hide_buttons_shop_page)
            return;
        $product_class=  get_class($product);
        if($product_class=="WC_Product_Variable")
        {
            
        }
        else if($product_class=="WC_Product_Simple")
        {
            $wpd_product=new WPD_Product($product->get_id());
            $html.=$wpd_product->get_buttons();
        }
        return $html;
    }

    private function wpc_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it 
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            // Otherwise return null
            return NULL;
        }
    }

    function set_variable_action_filters() {
        GLOBAL $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        $woo_version = $this->wpc_get_woo_version_number();
        if ($options['wpc-parts-position-cart'] == "name") {
            if ($woo_version < 2.1) {
                //Old WC versions
                add_filter("woocommerce_in_cart_product_title", array($this, "get_wpd_data"), 10, 3);
            } else {
                //New WC versions
                add_filter("woocommerce_cart_item_name", array($this, "get_wpd_data"), 10, 3);
            }
        } else {
            if ($woo_version < 2.1) {
                //Old WC versions
                add_filter("woocommerce_in_cart_product_thumbnail", array($this, "get_wpd_data"), 10, 3);
            } else {
                //New WC versions
                add_filter("woocommerce_cart_item_thumbnail", array($this, "get_wpd_data"), 10, 3);
            }
        }
        $append_content_filter = $options['wpc-content-filter'];

        if ($append_content_filter !== "0" && !is_admin()) {

            add_filter("the_content", array($this, "filter_content"), 99);
        }
    }

    function filter_content($content) {
        GLOBAL $wpd_settings;
        global $wp_query;
        $options = $wpd_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id"))
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        $current_page_id = get_the_ID();
        if ($wpc_page_id == $current_page_id) {
            $item_id = $wp_query->query_vars["product_id"];
            //var_dump($item_id);
            $editor_obj = new WPD_Editor($item_id);
            $content.=$editor_obj->get_editor();
        }
        return $content;
    }

    function handle_picture_upload() {
        $nonce = $_POST['nonce'];
//        check_ajax_referer( 'wpc-picture-upload-nonce', 'nonce', false);
        if (!check_ajax_referer( 'wpc-picture-upload-nonce', 'nonce', false)) {
            $busted = __("Cheating huh?", "wpd");
            die($busted);
        }

        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir["basedir"]."/WPC";
        if(!is_dir($generation_path))
            wp_mkdir_p ($generation_path);
        $generation_url = $upload_dir["baseurl"]."/WPC";
        $file_name = uniqid();
        $options = get_option('wpc-upload-options');
        $valid_formats = $options['wpc-upl-extensions'];
        if (!$valid_formats)
            $valid_formats = array("jpg", "png", "gif", "bmp", "jpeg", "psd", "eps"); //wpc-upl-extensions



            
//    var_dump($valid_formats);
        $name = $_FILES['userfile']['name'];
        $size = $_FILES['userfile']['size'];

        if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {
            if (strlen($name)) {
                $success = 0;
                $message = "";
                $img_url = "";
                $img_id = uniqid();
//                    list($txt, $ext) = explode(".", $name);
                $path_parts = pathinfo($name);
                $ext = $path_parts['extension'];
                $ext = strtolower($ext);
                if (in_array($ext, $valid_formats)) {
                    $tmp = $_FILES['userfile']['tmp_name'];
                    if (move_uploaded_file($tmp, $generation_path . "/" . $file_name . ".$ext")) {
                        $min_width = $options['wpc-min-upload-width'];
                        $min_height = $options['wpc-min-upload-height'];
                        $custom_attributes=  apply_filters("wpd_uploads_attributes", array(), $name);
                        $custom_attributes=  wpd_build_attributes_from_array($custom_attributes);
                        $valid_formats_for_thumb = array("psd", "eps", "pdf");
                        if (in_array($ext, $valid_formats_for_thumb)) {
                            //                        $output_thumb=  uniqid().".png";
                            $thumb_generation_success = generate_adobe_thumb($generation_path, $file_name . ".$ext", $file_name . ".png");
                            //If the thumb generation is a success, we force the extension to be png so the rest of the code can use it
                            if ($thumb_generation_success)
                                $ext = "png";
                        }
                        if ($min_width > 0 || $min_height > 0) {
                            list($width, $height, $type, $attr) = getimagesize($generation_path . "/" . $file_name . ".$ext");
                            if (($min_width > $width || $min_height > $height) && $ext != "svg") {
                                $success = 0;
                                $message = sprintf(__('Uploaded file dimensions: %1$spx x %2$spx, minimum required ', 'wpd'), $width, $height);
                                if ($min_width > 0 && $min_height > 0)
                                    $message.=__("dimensions:", "wpd")." $min_height" . "px" . " x $min_height" . "px";
                                else if ($min_width > 0)
                                    $message.="width: $min_width" . "px";
                                else if ($min_height > 0)
                                    $message.="height: $min_height" . "px";
                            }
                            else {
                                $success = 1;
                                $message = "<span class='clipart-img'><img id='$img_id' src='$generation_url/$file_name.$ext'  ".implode(' ', $custom_attributes)."></span>";
                                $img_url = "$generation_url/$file_name.$ext";
                            }
                        } else {
                            $success = 1;
                            $img_url=  apply_filters("wpd_image_uploaded", "$generation_url/$file_name.$ext", "$generation_path/$file_name.$ext");
                            $message = "<span class='clipart-img'><img id='$img_id' src='$img_url'  ".implode(' ', $custom_attributes)."></span>";
//                            $img_url = "$generation_url/$file_name.$ext";
                        }
                        if ($success == 0)
                            unlink($generation_path . "/" . $file_name . ".$ext");
                    }
                    else {
                        $success = 0;
                        $message = __('An error occured during the upload. Please try again later', 'wpd');
                    }
                } else {
                    $success = 0;
                    $message = __('Incorrect file extension: ' . $ext . '. Allowed extensions: ', 'wpd') . implode(", ", $valid_formats);
                }
                
                echo json_encode(
                        array(
                            "success" => $success,
                            "message" => $message,
                            "img_url" => $img_url,
                            "img_id" => $img_id,
                        )
                );
            }
        }
        die();
    }

//    private function generate_adobe_thumb($working_dir, $input_filename, $output_filename) {
//        $pos = strrpos($input_filename, ".");
//        $input_extension = substr($input_filename, $pos + 1);
//        $input_path = $working_dir . "/$input_filename";
//        $output_extension = "png";
//        $image = new Imagick($input_path);
//        $image->setResolution(300, 300);
//        $image->setImageFormat($output_extension);
//        if ($input_extension == "psd") {
//            $image->setIteratorIndex(0);
//        }
//        $success = $image->writeImage($working_dir . "/$output_filename");
//        return $success;
//    }

    function get_wpd_data($thumbnail_code, $values, $cart_item_key) {
        $variation_id = $values["variation_id"];
//        var_dump($values);
        if (isset($values["wpc_design_pricing_options"]) && !empty($values["wpc_design_pricing_options"])) {
            $wpc_design_pricing_options_data = WPD_Design::get_design_pricing_options_data($values["wpc_design_pricing_options"]);
            $thumbnail_code.= "<br>" . $wpc_design_pricing_options_data;
        }

        if (isset($values["wpc_generated_data"]) && isset($values["wpc_generated_data"]["output"])) {
            $thumbnail_code.="<br>";
            $customization_list = $values["wpc_generated_data"];
            $upload_dir = wp_upload_dir();
            $modals = "";
            //        var_dump($customization_list["output"]);
            $i = 0;
            foreach ($customization_list["output"]["files"] as $customisation_key => $customization) {
                $tmp_dir = $customization_list["output"]["working_dir"];
                $generation_url = $upload_dir["baseurl"] . "/WPC/$tmp_dir/$customisation_key/";
                if (isset($customization["preview"]))
                    $image = $generation_url . $customization["preview"];
                else
                    $image = $generation_url . $customization["image"];
                $original_part_img_url = $customization_list[$customisation_key]["original_part_img"];
                //$modal_id=$variation_id."_".$cart_item_key."$customisation_key-". uniqid();//Creates issue on checkout page
                $modal_id = $variation_id . "_" . $cart_item_key . "$customisation_key-$i";

                $thumbnail_code.='<span><a class="button" data-toggle="o-modal" data-target="#' . $modal_id . '">' . ucfirst($customisation_key) . '</a></span>';
                $modals.='<div class="omodal fade wpc-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="omodal-dialog">
                                      <div class="omodal-content">
                                        <div class="omodal-header">
                                          <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                          <h4 class="omodal-title">'.__("Preview", "wpd").'</h4>
                                        </div>
                                        <div class="omodal-body txt-center">
                                            <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $image . '"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>';
                $i++;
            }
            array_push(wpd_retarded_actions::$code, $modals);
            add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);

            $wpd_product = new WPD_Product($variation_id);
            $edit_item_url =$wpd_product->get_design_url(false, $cart_item_key);
            $thumbnail_code.='<a class="button alt" href="' . $edit_item_url . '">'.__("Edit", "wpd").'</a>';
        } else if (isset($values["wpc-uploaded-designs"])) {
            $thumbnail_code.="<br>";
            foreach ($values["wpc-uploaded-designs"] as $custom_design) {
                $thumbnail_code.='<span class="wpd-custom-design"><a class="button" href=' . $custom_design . '>' . __("Custom design", "wpd") . '</a></span>';
            }
        }
        return $thumbnail_code;
    }

    public function wpd_add_query_vars($aVars) {
        $aVars[] = "product_id";
        $aVars[] = "tpl";
        $aVars[] = "edit";
        $aVars[] = "design_index";
        $aVars[] = "oid";
        return $aVars;
    }

    public function wpd_add_rewrite_rules($param) {
        GLOBAL $wpd_settings;
        GLOBAL $wp_rewrite;
        $options = $wpd_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id"))
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        $wpc_page = get_post($wpc_page_id);
        if (is_object($wpc_page)) {
            //$slug = $wpc_page->post_name;
            $raw_slug = get_permalink($wpc_page->ID);
            $home_url = home_url('/');
            $slug = str_replace($home_url, '', $raw_slug);
            //If the slug does not have the trailing slash, we get 404 (ex postname = /%postname%)
            $sep="";
            if(substr($slug, -1)!="/")
                $sep="/";
//            var_dump(substr($slug, -1));
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . $sep.'design' . '/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . $sep.'design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&tpl=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . $sep.'edit' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&edit=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );
            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . $sep.'ordered-design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&oid=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );

            add_rewrite_rule(
                    // The regex to match the incoming URL
                    $slug . $sep.'saved-design' . '/([^/]+)/([^/]+)/?$',
                    // The resulting internal URL: `index.php` because we still use WordPress
                    // `pagename` because we use this WordPress page
                    // `designer_slug` because we assign the first captured regex part to this variable
                    'index.php?pagename=' . $slug . '&product_id=$matches[1]&design_index=$matches[2]',
                    // This is a rather specific URL, so we add it to the top of the list
                    // Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
                    'top'
            );

            $wp_rewrite->flush_rules(false);
        }
    }

            public function set_email_order_item_meta($item_id, $item, $order){
            $output="";
            if(is_order_received_page())
                return;
            if (isset($item['item_meta']['_wpc_design_pricing_options']) && (!empty($item['item_meta']['_wpc_design_pricing_options']) && (is_array($item['item_meta']['_wpc_design_pricing_options'])))) {
                $output.=  "<div class='vpc-order-config o-wrap xl-gutter-8'><div class='o-col xl-2-3'>";
                foreach ($item['item_meta']['_wpc_design_pricing_options'] as $key => $ninjaform_option) {
                    $output.= $ninjaform_option;
}
                $output .="</div></div>";
            }
            echo $output;   
                
        }
        
        public function add_class_to_body($classes) {
            GLOBAL $wp_query;
            if (isset($wp_query->query_vars["product_id"])) {
                array_push($classes, "wpd-customization-page wpd-product-".$wp_query->query_vars["product_id"]);
            }
            return $classes;
        }
        
        public function get_item_class( $classes, $class, $post_id)
        {
            GLOBAL $wpd_settings;
            $general_options = $wpd_settings['wpc-general-options'];
            $hide_cart_button = get_proper_value($general_options, 'wpd-hide-cart-button', false);
            
            if(in_array('product', $classes))
            {
                $wpd_product=new WPD_Product($post_id);
                if($wpd_product->is_customizable())
                    array_push ($classes, 'wpd-is-customizable');
                if($hide_cart_button)
                    array_push ($classes, 'wpd-hide-cart-button');
            }
            return $classes;
        }

}
