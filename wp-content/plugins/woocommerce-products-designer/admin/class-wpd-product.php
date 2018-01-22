<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-product
 *
 * @author HL
 */
class WPD_Product {

    public $variation_id;
    public $root_product_id;
    public $product;
    public $settings;
    public $variation_settings;

    public function __construct($id) {
        if ($id) {
            $this->root_product_id = $this->get_parent($id);
            //If it's a variable product
            if ($id != $this->root_product_id)
                $this->variation_id = $id;
            //Simple product and others
            else
                $this->variation_id = $this->root_product_id;

            $this->product = wc_get_product($id);
            $config=get_post_meta($this->root_product_id, "wpd-metas", true);
            
            if(isset($config[$this->variation_id]))
            {
                $config_id=$config[$this->variation_id]['config-id'];
                if($config_id)
                {
                    $this->settings=get_post_meta($config_id, "wpd-metas", true);
                    $product_metas=  get_post_meta( $this->root_product_id, 'wpc-metas', true);
//                    var_dump($product_metas['related-products']);
                    if(isset($product_metas['related-products']))
                        $this->settings['related-products']=$product_metas['related-products'];
                }
            }
        }
    }

    private function get_checkbox_value($values, $search_key, $default_value) {
        if (get_proper_value($values, $search_key, $default_value) == 1)
            $is_checked = "checked='checked'";
        else
            $is_checked = "";
        return $is_checked;
    }

    /**
     * Saves the product custom data
     * @param type $product_id Product ID
     */
//    function save_customizable_meta($product_id) {
//        if (isset($_POST['wpc-metas']))
//        {
//            update_post_meta($product_id, 'wpc-metas', $_POST['wpc-metas']);
//        }
//    }

    /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label() {
        ?>
        
        <li class="wpc_related_products_tab show_if_variable"><a href="#wpc_related_products_tab_data"><?php _e('Related Products / Quantities', 'wpd'); ?></a></li>
        <?php
    }

    /**
     * Adds the Custom column to the default products list to help identify which ones are custom
     * @param array $defaults Default columns
     * @return array
     */
    function get_product_columns($defaults) {
        $defaults['is_customizable'] = __('Custom', 'wpd');
        return $defaults;
    }

    /**
     * Sets the Custom column value on the products list to help identify which ones are custom
     * @param type $column_name Column name
     * @param type $id Product ID
     */
    function get_products_columns_values($column_name, $id) {
        if ($column_name === 'is_customizable') {
            $wpc_metas = get_post_meta($id, 'wpd-metas', true);
            if (empty($wpc_metas))
                _e("No", "wpd");
            else
                _e("Yes", "wpd");
        }
    }

    public function is_customizable() {
        $is_customizable = get_proper_value($this->settings, 'wpd-metas', "");
        return (!empty($is_customizable));
    }

    public function extract_usable_attributes() {
        $product = $this->product;
        $attributes = $product->get_attributes();
        $usable_attributes = array();
        foreach ($attributes as $attribute) {
            $sanitized_name = sanitize_title($attribute["name"]);
            if ($attribute["is_visible"] && $attribute["is_variation"]) {
                if ($attribute["is_taxonomy"]) {
                    $values = wc_get_product_terms($product->get_id(), $attribute['name'], array('fields' => 'all'));
                    $taxonomy = get_taxonomy($attribute["name"]);
                    $key = "attribute_" . $sanitized_name;
                    $usable_attributes[$attribute["name"]] = array("key" => $key, "label" => $taxonomy->labels->singular_name, "values" => $values); //$values;
//                        var_dump($values);
                } else {
                    $key = "attribute_" . $sanitized_name;
                    // Convert pipes to commas and display values
                    $values = array_map('trim', explode(WC_DELIMITER, $attribute['value']));
                    $usable_attributes[$attribute["name"]] = array("key" => $key, "label" => $attribute["name"], "values" => $values);
                }
            }
        }

        return $usable_attributes;
    }

    /**
     * Checks the product contains at least one active part
     * @return boolean
     */
    public function has_part() {
        $parts=  get_proper_value($this->settings, 'parts');
        return !empty($parts);
    }

    /**
     * Returns the customization page URL
     * @global Array $wpd_settings
     * @param int $design_index Saved design index to load
     * @param mixed $cart_item_key Cart item key to edit
     * @param int $order_item_id Order item ID to load
     * @param int $tpl_id ID of the template to load
     * @return String
     */
    public function get_design_url($design_index = false, $cart_item_key = false, $order_item_id = false, $tpl_id = false) {
        GLOBAL $wpd_settings;

        if ($this->variation_id)
            $item_id = $this->variation_id;
        else
            $item_id = $this->root_product_id;

        $options = $wpd_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists("icl_object_id")) {
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        }
        $wpc_page_url = "";
        if ($wpc_page_id) {
            $wpc_page_url = get_permalink($wpc_page_id);
            if ($item_id) {
                $query = parse_url($wpc_page_url, PHP_URL_QUERY);
                // Returns a string if the URL has parameters or NULL if not
                if (get_option('permalink_structure')) {
                    if (substr($wpc_page_url, -1) != '/') {
                        $wpc_page_url .= '/';
                    }
                    if ($design_index || $design_index === 0) {
                        $wpc_page_url .= "saved-design/$item_id/$design_index/";
                    } elseif ($cart_item_key) {
                        $wpc_page_url .= "edit/$item_id/$cart_item_key/";
                    } elseif ($order_item_id) {
                        $wpc_page_url .= "ordered-design/$item_id/$order_item_id/";
                    } else {
                        $wpc_page_url .= 'design/' . $item_id . '/';
                        if ($tpl_id) {
                            $wpc_page_url .= "$tpl_id/";
                        }
                    }
                } else {
                    if ($design_index !== false) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&design_index=' . $design_index;
                    } elseif ($cart_item_key) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&edit=' . $cart_item_key;
                    } elseif ($order_item_id) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&oid=' . $order_item_id;
                    } else {
                        $wpc_page_url .= '&product_id=' . $item_id;
                        if ($tpl_id) {
                            $wpc_page_url .= "&tpl=$tpl_id";
                        }
                    }
                }
            }
        }

        return $wpc_page_url;
    }

    /**
     * Returns a variation root product ID
     * @param type $variation_id Variation ID
     * @return int
     */
    public function get_parent($variation_id) {
        $variable_product = wc_get_product($variation_id);
        if (!$variable_product)
            return false;
        if ($variable_product->get_type() != "variation")
            $product_id = $variation_id;
        else {
            //$product_id=$variable_product->parent->id;
            $product_id = $variable_product->get_parent_id();
        }

        return $product_id;
    }

    /**
     * Returns the defined value for a product setting which can be local(product metas) or global (options)
     * @param array $product_settings Product options
     * @param array $global_settings Global options
     * @param string $option_name Option name / Meta key
     * @param int $field_value Default value to return if empty
     * @return string
     */
    public function get_option($product_settings, $global_settings, $option_name, $field_value = "") {
        if (isset($product_settings[$option_name]) && ( (!empty($product_settings[$option_name]))||$product_settings[$option_name]==="0"))
            $field_value = $product_settings[$option_name];
        else if (isset($global_settings[$option_name]) && !empty($global_settings[$option_name]))
            $field_value = $global_settings[$option_name];

        return $field_value;
    }

    function set_custom_upl_cart_item_data($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        global $woocommerce;
        if($variation_id)
            $element_id = $variation_id;
        else
            $element_id = $product_id;
//        if (isset($variation_id) && !empty($variation_id))
//            $element_id = $variation_id;

        if (isset($_SESSION["wpc-user-uploaded-designs"][$element_id])) {
            if(!isset($woocommerce->cart->cart_contents[$cart_item_key]["wpc-uploaded-designs"]))
                $woocommerce->cart->cart_contents[$cart_item_key]["wpc-uploaded-designs"]=array();
            
            array_push($woocommerce->cart->cart_contents[$cart_item_key]["wpc-uploaded-designs"], $_SESSION["wpc-user-uploaded-designs"][$element_id]);
            $woocommerce->cart->calculate_totals();
            unset($_SESSION["wpc-user-uploaded-designs"][$element_id]);
        }
        if (!isset($woocommerce->cart->cart_contents[$cart_item_key]["wpc_design_pricing_options"]))
            $woocommerce->cart->cart_contents[$cart_item_key]["wpc_design_pricing_options"] = array();

        if (isset($_POST['wpd-design-opt']))
        {
            $woocommerce->cart->cart_contents[$cart_item_key]["wpc_design_pricing_options"] = $_POST['wpd-design-opt'];
            $woocommerce->cart->calculate_totals();
        }
    }

    /**
     * Returns the minimum and maximum order quantities
     * @return type
     */
    function get_purchase_properties() {
        if ($this->variation_id) {
            $defined_min_qty = get_post_meta($this->variation_id, 'variation_minimum_allowed_quantity', true);
            //We consider the values defined for the all of them
            if (!$defined_min_qty)
                $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);

            if (!$defined_min_qty)
                $defined_min_qty = 1;

            $defined_max_qty = get_post_meta($this->variation_id, 'variation_maximum_allowed_quantity', true);
            //We consider the values defined for the all of them
            if (!$defined_max_qty)
                $defined_max_qty = get_post_meta($this->root_product_id, 'maximum_allowed_quantity', true);
        }
        else {
            $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);
            if (!$defined_min_qty)
                $defined_min_qty = 1;

            $defined_max_qty = get_post_meta($this->root_product_id, 'variation_maximum_allowed_quantity', true);
        }


        $step = apply_filters('woocommerce_quantity_input_step', '1', $this->product);
        $min_qty = apply_filters('woocommerce_quantity_input_min', $defined_min_qty, $this->product);

        if (!$defined_max_qty)
            $defined_max_qty = apply_filters('woocommerce_quantity_input_max', $this->product->backorders_allowed() ? '' : $this->product->get_stock_quantity(), $this->product);

        $min_to_purchase = $min_qty;
        if (!$min_qty)
            $min_to_purchase = 1;

        
        $defaults = array(
            'max_value'   => $defined_max_qty,
            'min_value'   => $min_qty,
            'step'        => $step,
        );
        $args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( array(), $defaults ), $this->product );

        return array(
            "min" => $args["min_value"],
            "min_to_purchase" => $args["min_value"],
            "max" => $args["max_value"],
            "step" => $args["step"]
        );
    }

    function get_related_product_desc() {
        $purchase_properties = $this->get_purchase_properties();
        if($purchase_properties["min"] > 1)
            return __("Requires a minimum purchase of ", "wpd") . $purchase_properties["min"] . __(" item(s).", "wpd");
        else
            return "";
    }

    public function save_product_settings_fields($item_id) {
        $meta_key = "wpc-metas";
        if (isset($_POST[$meta_key])) {
//           var_dump($_POST[$meta_key]);
//           echo "<hr>";
            $variation = wc_get_product($item_id);
            //If we're dealing with a variation, Product ID is the root ID of the product
            if (get_class($variation) == "WC_Product_Variation")
                $product_id = $variation->get_parent_id();
            else
                $product_id = $item_id;
            //Careful this hooks only send the updated data, not the complete form
            $old_metas = get_post_meta($product_id, $meta_key, true);
            if (empty($old_metas))
                $old_metas = array();
            $new_metas = array_replace($old_metas, $_POST[$meta_key]);

            //If the related products and quantities are not in the post variable, that means the user is disabling them
            if (!isset($_POST[$meta_key]["related-products"]))
                $new_metas["related-products"] = array();
            if (!isset($_POST[$meta_key]["related-quantities"]))
                $new_metas["related-quantities"] = array();
            update_post_meta($product_id, $meta_key, $new_metas);
        }
    }

    public function get_custom_products_body_class($classes, $class) {
        if (is_singular(array("product"))) {
            GLOBAL $wpd_settings;
            $general_options = $wpd_settings['wpc-general-options'];
            $hide_cart_button = get_proper_value($general_options, 'wpd-hide-cart-button', false);
            $pid = get_the_ID();
            $product = new WPD_Product($pid);
            if ($product->is_customizable()) {
//                var_dump($hide_cart_button);
                array_push($classes, "wpd-is-customizable");
                if ($hide_cart_button)
                    array_push($classes, "wpd-hide-cart-button");
            }
        }
        return $classes;
    }


    function get_buttons($with_upload=false) {
        ob_start();
        $product = $this->product;
        $wpc_metas = $this->settings;
        
        if ($this->variation_id)
            $item_id = $this->variation_id;
        else
            $item_id = $this->root_product_id;

        if ($product->get_type() == 'variable') {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                if(!$variation["is_purchasable"]||!$variation["is_in_stock"])
                    continue;
                $wpd_product=new WPD_Product($variation["variation_id"]);
                echo $wpd_product->get_buttons($with_upload);
            }
            
        } else {
            $has_parts=$this->has_part();
            if(!$has_parts)
            {
                $output=  ob_get_clean();
                return $output;
            }
            ?>
                    <div class="wpd-buttons-wrap-<?php echo $product->get_type();?>" data-id="<?php echo $this->variation_id;?>">
            <?php
            //Design from blank
            if (isset($wpc_metas['can-design-from-blank'])&&!empty($wpc_metas['can-design-from-blank']))
            {
                $design_from_blank_url = $this->get_design_url();
                echo '<a  href="' . $design_from_blank_url . '" class="mg-top-10 wpc-customize-product">' . __("Design from blank", "wpd") . '</a>';
            }
            
            ?>
                    </div>
            <?php
        }
        
        $output=  ob_get_clean();
        return $output;

    }
    
    function get_variable_product_details_location_notice()
    {
        ?>
            <div class="options_group show_if_simple show_if_variable">
                <p class="form-field _sold_individually_field show_if_simple show_if_variable" style="background-color: red; color: white;">
                    <?php _e("The variable product custom data for the product designer are shown by variation. In order to setup the product parts, canvas, bounding box and other properties, please access the variations parameters tab.", "wpd");?>
                </p>
            </div>
        <?php
    }
    
    function duplicate_product_metas($new_product, $old_product)
    {
        $meta_key = "wpc-metas";
        $old_metas = get_post_meta($old_product->get_id(), $meta_key, true);
        
        $new_metas=wpd_replace_key_in_array($old_metas, $old_product->get_id(), $new_product->get_id());
        
        //Variable products
        //Duplicated product children are the same as the original so we can't tell the difference and update the metas accordingly.
//        if($old_product->get_type()=="variable")
//        {
//            $old_variations = $old_product->get_available_variations();
//            $new_variations = $new_product->get_available_variations();
//            
//            foreach ($old_variations as $i=>$variation) {
//                $old_variation_id = $variation['variation_id'];
//                $new_variation_id = $new_variations[$i]['variation_id'];
//                
//                $new_metas=wpd_replace_key_in_array($new_metas, $old_variation_id, $new_variation_id);
//                
//            }
//        }
        update_post_meta($new_product->get_id(), $meta_key, $new_metas);
    }
    
    public function save_config($post_id){
        $meta_key="wpd-metas";
       if(isset($_POST[$meta_key]))
       {
           update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
       }
    }
    
    public function get_output_image_width()
    {
        $canvas_w = get_proper_value($this->settings, "canvas-w", 800);
        $output_settings = get_proper_value($this->settings, 'output-settings', array());
        $output_w=get_proper_value($output_settings, "wpc-min-output-width", $canvas_w);
        return $output_w;
    }

}
