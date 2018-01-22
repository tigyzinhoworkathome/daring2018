<?php

function generate_adobe_thumb($working_dir, $input_filename, $output_filename) {
    $pos = strrpos($input_filename, ".");
    $input_extension = substr($input_filename, $pos + 1);
    $input_path = $working_dir . "/$input_filename";
    $output_extension = "png";
    $image = new Imagick();
    $image->setResolution(300, 300);
    $image->readImage($input_path);
    $image->setImageFormat($output_extension);
    if ($input_extension == "psd") {
        $image->setIteratorIndex(0);
    }
    $success = $image->writeImage($working_dir . "/$output_filename");
    return $success;
}

function wpd_get_custom_products() {
    global $wpdb;
//    $transient_key = "orion_wpd_custom_products_transient";
//    $cached_output = get_transient($transient_key);
//    if ($cached_output) {
//        return $cached_output;
//    }
    $search = '"is-customizable";s:1:"1"';
    $products = $wpdb->get_results(
            "
                       SELECT p.id
                       FROM $wpdb->posts p
                       JOIN $wpdb->postmeta pm on pm.post_id = p.id 
                       WHERE p.post_type = 'product'
                       AND pm.meta_key = 'wpc-metas'
                       AND pm.meta_value like '%$search%'
                       ");
//    set_transient($transient_key, $products, 12 * HOUR_IN_SECONDS);
    return $products;
}

function wpd_generate_css($values) {
    $first_value=  current( $values);
    if(empty($first_value))
        return;
    
    reset( $values);
    ?>
    <style>
    <?php
    foreach ($values as $key => $value) {
        echo $key;
        ?>
            {
        <?php
        foreach ($value as $attr => $val) {
            echo $attr . ':' . $val . '!important;';
        }
        ?>
            }
        <?php
    }
    ?>
    </style>
    <?php
}

function wpd_build_attributes_from_array($custom_attributes) {
    $output = array();
    if (!empty($custom_attributes) && is_array($custom_attributes)) {
        foreach ($custom_attributes as $attribute => $attribute_value) {
            $output[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
        }
    }
    return $output;
}

function wpd_remove_transients() {
    global $wpdb;
    $sql = "delete from $wpdb->options where option_name like '%_orion_wpd_%transient_%'";
    $wpdb->query($sql);
}

function wpd_fix_template_data($tpl_id) {
    GLOBAL $wpdb;
    $sql = "select meta_value from $wpdb->postmeta where post_id='$tpl_id' and meta_key='data'";
//            var_dump($sql);
    $value_0 = $wpdb->get_var($sql);
    //Replace the line breaks (create an issue during the import)
    $value = mb_eregi_replace("\n", "|n", $value_0);

    //$data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value);//Deprecated
    $data_0 = preg_replace_callback('!s:(d+):"(.*?)";!ms', function($matches){return 's:'.strlen($matches[2]).':”'.$matches[2].'”;';}, $value );
    $data = unserialize($data_0);
    if ($data)
        update_post_meta($tpl_id, "data", stripslashes_deep($data));
    return $data;
}

/**
 * Return the default fonts list
 * @return array
 */
function wpd_get_default_fonts() {
    $default = array(
        array("Shadows Into Light", "http://fonts.googleapis.com/css?family=Shadows+Into+Light"),
        array("Droid Sans", "http://fonts.googleapis.com/css?family=Droid+Sans:400,700"),
        array("Abril Fatface", "http://fonts.googleapis.com/css?family=Abril+Fatface"),
        array("Arvo", "http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic"),
        array("Lato", "http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic"),
        array("Just Another Hand", "http://fonts.googleapis.com/css?family=Just+Another+Hand")
    );

    return $default;
}

/**
     * Builds a select dropdpown
     * @param type $name Name
     * @param type $id ID
     * @param type $class Class
     * @param type $options Options
     * @param type $selected Selected value
     * @param type $multiple Can select multiple values
     * @return string HTML code
     */
    function wpd_get_html_select($name, $id, $class, $options, $selected = '', $multiple = false) {
        ob_start();
        ?>
        <select name="<?php echo $name; ?>" <?php echo ($id) ? "id=\"$id\"" : ""; ?> <?php echo ($class) ? "class=\"$class\"" : ""; ?> <?php echo ($multiple) ? "multiple" : ""; ?> >
            <?php
            if (is_array($options) && !empty($options)) {
                foreach ($options as $name => $label) {
                    if (!$multiple && $name == $selected) {
                        ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else if ($multiple && in_array($name, $selected)) {
                        ?> <option value="<?php echo $name ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                    } else {
                        ?> <option value="<?php echo $name ?>"> <?php echo $label; ?></option> <?php
                    }
                }
            }
            ?>
        </select>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    function wpd_register_fonts() {
        $fonts = get_option("wpc-fonts");
        if (empty($fonts)) {
            $fonts = wpd_get_default_fonts();
        }

        foreach ($fonts as $font) {
            $font_label = $font[0];
            $font_url = str_replace('http://', '//', $font[1]);
            if ($font_url) {
                $handler = sanitize_title($font_label) . "-css";
                wp_register_style($handler, $font_url, array(), false, 'all');
                wp_enqueue_style($handler);
            }
            else if(!empty ($font[2])&&  is_array($font[2]))
            {
                wpd_get_ttf_font_style($font);
            }
        }
    }
    
    function wpd_get_ttf_font_style($font)
    {
        $font_label = $font[0];
        $font_ttf_files=$font[2];
        foreach ($font_ttf_files as $font_file)
        {            
            $font_styles=$font_file["styles"];
            $font_file_url=  wp_get_attachment_url($font_file["file_id"]);
            if(!$font_file_url)
                continue;
            foreach ($font_styles as $font_style)
            {            
                if($font_style=="")
                    $font_style_css="";
                elseif($font_style=="I")
                    $font_style_css="font-style:italic;";
                elseif($font_style=="B")
                    $font_style_css="font-weight:bold;"
                ?>
                <style>
                    @font-face {
                            font-family: "<?php echo $font_label;?>";
                            src: url('<?php echo $font_file_url;?>') format('truetype');
                            <?php echo $font_style_css;?>
                    }
                </style>
                <?php
            }
        }
    }
    
    function wpd_register_upload_scripts() {
        GLOBAL $wpd_settings;
        $options = $wpd_settings['wpc-upload-options'];
        $uploader = $options['wpc-uploader'];
        if ($uploader == "native") {
            wp_register_script('wpd-jquery-form-js', WPD_URL . 'public/js/jquery.form.min.js');
            wp_enqueue_script('wpd-jquery-form-js', array('jquery'), WPD_VERSION, false);
        } else {
            wp_register_script('wpd-widget', WPD_URL . 'public/js/upload/js/jquery.ui.widget.min.js');
            wp_enqueue_script('wpd-widget', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-fileupload', WPD_URL . 'public/js/upload/js/jquery.fileupload.min.js');
            wp_enqueue_script('wpd-fileupload', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-iframe-transport', WPD_URL . 'public/js/upload/js/jquery.iframe-transport.min.js');
            wp_enqueue_script('wpd-iframe-transport', array('jquery'), WPD_VERSION, false);

            wp_register_script('wpd-knob', WPD_URL . 'public/js/upload/js/jquery.knob.min.js');
            wp_enqueue_script('wpd-knob', array('jquery'), WPD_VERSION, false);
        }
    }
    
    function wpd_get_opacity_dropdown($name, $id, $class = "") {
        $options = array();
        for ($i = 0; $i <= 10; $i++) {
            $key = $i / 10;
            $value = $i * 10;
            $options["$key"] = "$value%";
        }
        echo wpd_get_html_select($name, $id, $class, $options, 1);
    }
    
    function wpd_get_price_format() {
    $currency_pos = get_option('woocommerce_currency_pos');
    $format = '%s%v';

    switch ($currency_pos) {
        case 'left' :
            $format = '%s%v';
            break;
        case 'right' :
            $format = '%v%s';
            break;
        case 'left_space' :
            $format = '%s %v';
            break;
        case 'right_space' :
            $format = '%v %s';
            break;
        default: 
            $format = '%s%v';
            break;
    }
    return $format;
}
    
    function wpd_init_canvas_vars($wpc_metas, $product, $editor)
    {
        GLOBAL $wpd_settings, $wp_query, $wpdb;
        $wpd_query_vars = array();
        
        $general_options = $wpd_settings['wpc-general-options'];
        $product_price = $product->get_price();
//        $shop_currency_symbol=get_woocommerce_currency_symbol();
        $colors_options = $wpd_settings['wpc-colors-options'];
        $wpc_output_options = $wpd_settings['wpc-output-options'];
        if (isset($wpc_output_options['wpc-generate-layers']) && $wpc_output_options['wpc-generate-layers'] === "yes")
            $generate_layers = true;
        else
            $generate_layers = false;

        if (isset($wpc_output_options['wpc-generate-svg']) && $wpc_output_options['wpc-generate-svg'] === "yes")
            $generate_svg = true;
        else
            $generate_svg = false;
        
        $canvas_w = $editor->wpd_product->get_option($wpc_metas, $general_options, "canvas-w", 800);
        $canvas_h = $editor->wpd_product->get_option($wpc_metas, $general_options, "canvas-h", 500);
        $watermark = get_proper_value($wpc_metas, "watermark", "");
        
        $raw_output_format=get_proper_value($wpc_metas["output-settings"], 'output-format');
        if($raw_output_format=='png'||$raw_output_format=='pdf+png')
            $output_format='png';
        elseif($raw_output_format=='jpg'||$raw_output_format=='pdf+jpg')
            $output_format='jpg';
        else
            $output_format='png';
        
        if($raw_output_format=='svg'||$raw_output_format=='pdf+svg')
            $generate_svg='svg';

        $bounding_data = get_proper_value($wpc_metas, 'bounding_box', array());
        $clip_w = get_proper_value($bounding_data, "width", "");
        $clip_h = get_proper_value($bounding_data, "height", "");
        $clip_x = get_proper_value($bounding_data, "x", "");
        $clip_y = get_proper_value($bounding_data, "y", "");
        $clip_radius = get_proper_value($bounding_data, "radius", "");
        $clip_radius_rect = get_proper_value($bounding_data, "r_radius", 0);
        $clip_type = get_proper_value($bounding_data, "type", "");
        $clip_border = get_proper_value($bounding_data, "border_color", "");
        $clip_include_in_output = get_proper_value($bounding_data, "include_in_output", "no");

        $output_settings = get_proper_value($wpc_metas, 'output-settings', array());
        $output_w = $editor->wpd_product->get_output_image_width();
        $output_loop_delay = $editor->wpd_product->get_option($output_settings, $wpc_output_options, "wpc-output-loop-delay", 1000);

        $svg_colorization = get_proper_value($colors_options, 'wpc-svg-colorization', 'none');
        $wpc_palette_type = get_proper_value($colors_options, 'wpc-color-palette', 'unlimited');
        $palette = get_proper_value($colors_options, 'wpc-custom-palette', '');
        $palette_tpl = "";

        if (isset($general_options['wpc-redirect-after-cart']) && !empty($general_options['wpc-redirect-after-cart']))
            $redirect_after = $general_options['wpc-redirect-after-cart'];
        else
            $redirect_after = 0;

        if (isset($general_options['responsive']) && !empty($general_options['responsive']))
            $responsive = $general_options['responsive'];
        else
            $responsive = 0;
        
        $disable_shortcut=  get_proper_value($general_options, 'disable-keyboard-shortcuts', 0);

        if (!empty($palette) && is_array($palette)) {
	$palette_tpl = apply_filters('wpd_custom_palette_tpl', $palette_tpl, $palette);
	//Check if the custom template is not already set. Avoid useless loop.
	if (empty($palette_tpl)) {
            foreach ($palette as $color) {
                $hex = str_replace("#", "", $color);
                $palette_tpl.='<span style="background-color: ' . $color . '" data-color="' . $hex . '" class="wpc-custom-color"></span>';
            }
        }
    }
        if (isset($wp_query->query_vars["tpl"])) {
            $tpl_id = $wp_query->query_vars["tpl"];
            $wpd_query_vars["tpl"] = $tpl_id;
            $data = get_post_meta($tpl_id, "data", true);
//        Fix serialisation issue after moving the data
            if ($data === false) {
//            var_dump("Trying to fix");
                $data = wpd_fix_template_data($tpl_id);
            }
        } else if (is_admin() && get_post_type() == "wpc-template") {
            $tpl_id = get_the_ID();
            $data = get_post_meta($tpl_id, "data", true);
            //        Fix serialisation issue after moving the data
            if ($data === false) {
//            var_dump("Trying to fix");
                $data = wpd_fix_template_data($tpl_id);
            }
        } else if (isset($wp_query->query_vars["edit"])) {
            $variation_id = $wp_query->query_vars["product_id"];
            $cart_item_key = $wp_query->query_vars["edit"];
            $wpd_query_vars["edit"] = $cart_item_key;
            global $woocommerce;
            $cart = $woocommerce->cart->get_cart();
            $data = $cart[$cart_item_key]["wpc_generated_data"];
            //Useful when editing cart item
            if ($data)
                $data = stripslashes_deep($data);
        } else if (isset($wp_query->query_vars["design_index"])) {
            global $current_user;
            $design_index = $wp_query->query_vars["design_index"];
            $wpd_query_vars["design_index"] = $design_index;
            $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
            $data = $user_designs[$design_index][2];
        } else if (isset($wp_query->query_vars["oid"])) {
            $order_item_id = $wp_query->query_vars["oid"];
            $wpd_query_vars["oid"] = $order_item_id;
            $sql = "select meta_value FROM " . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key='wpc_data'";
            //echo $sql;
            $wpc_data = $wpdb->get_var($sql);
            $data = unserialize($wpc_data);
        }

        //Previous data to load overwrites everything
        if (isset($_SESSION["wpd-data-to-load"]) && !empty($_SESSION["wpd-data-to-load"])) {
            $previous_design_str = stripslashes_deep($_SESSION["wpd-data-to-load"]);
            $previous_design = json_decode($previous_design_str);
            if (is_object($previous_design))
                $previous_design = (array) $previous_design;
            //We make sure the structure of the data matches the one loaded by the plugin
            foreach ($previous_design as $part_key => $part_data) {
//                $last_data=$part_data[count($part_data)-1];
                $previous_design[$part_key] = array("json" => $part_data);
            }
//            var_dump($previous_design);
//            var_dump($_COOKIE["wpd-data-to-load"]);
            $data = $previous_design;
//            setcookie("wpd-data-to-load");
            unset($_SESSION["wpd-data-to-load"]);
        }

        if (isset($data) && !empty($data)) {
            $design = new WPD_Design();
            $a_price = $design->get_additional_price($editor->root_item_id, $data);
            $product_price+=$a_price;
            ?>
            <script>
                var to_load =<?php echo json_encode($data); ?>;
            </script>
            <?php
        }
        $available_variations = array();
        if ($product->get_type() == "variable")
            $available_variations = $editor->get_available_variations();
        
        $price_format = wpd_get_price_format();//str_replace(html_entity_decode(htmlentities(get_woocommerce_currency_symbol())), "$", $raw_price_format);
        
        $editor_params = apply_filters("wpd_editor_params", array(
            "canvas_w" => $canvas_w,
            "canvas_h" => $canvas_h,
            "watermark" => $watermark,
            "clip_w" => $clip_w,
            "clip_h" => $clip_h,
            "clip_x" => $clip_x,
            "clip_r" => $clip_radius,
            "clip_rr" => $clip_radius_rect,
            "clip_y" => $clip_y,
            "clip_type" => $clip_type,
            "clip_border" => $clip_border,
            "clip_include_in_output" => $clip_include_in_output,
            "output_w" => $output_w,
            "output_loop_delay" => $output_loop_delay,
            "svg_colorization" => $svg_colorization,
            "palette_type" => $wpc_palette_type,
            "print_layers" => $generate_layers,
            "generate_svg" => $generate_svg,
            "global_variation_id" => $editor->item_id,
            "redirect_after" => $redirect_after,
            "responsive" => $responsive,
            'disable_shortcuts' => $disable_shortcut,
            "palette_tpl" => $palette_tpl,
            "translated_strings" => array(
                "deletion_error_msg" => __("The deletion of this object is not allowed", "wpd"),
                "loading_msg" => __("Just a moment", "wpd"),
                "empty_object_msg" => __("The edition area is empty.", "wpd"),
                "delete_all_msg" => __("Do you really want to delete all items in the design area ?", "wpd"),
                "delete_msg" => __("Do you really want to delete the selected items ?", "wpd"),
                "empty_txt_area_msg" => __("Please enter the text to add.", "wpd"),
                "cart_item_edition_switch" => __("You're editing a cart item. If you switch to another product and update the cart, the previous item will be removed from the cart. Do you really want to continue?", "wpd"),
                "svg_background_tooltip" => __("Background color (SVG files only)", "wpd"),
                "cliparts_search_no_result" => __("There are no results that match your search.", "wpd"),
            ),
            "query_vars" => $wpd_query_vars,
            "thousand_sep" => wc_get_price_thousand_separator(),
            "decimal_sep" => wc_get_price_decimal_separator(),
            "nb_decimals" => wc_get_price_decimals(),
            "currency" => get_woocommerce_currency_symbol(),
            'price_format' => $price_format,
            "variations" => $available_variations,
            "lazy_placeholder" => WPD_URL . "/public/images/rolling.gif",
            "output_format" => $output_format
        ));
        ?>
            <script>
                var wpd =<?php echo json_encode($editor_params); ?>;
            </script>
        <?php
    }
    
    function wpd_get_variation_from_attributes($attributes, $root_item_id) {
        $available_variations = wpd_get_available_variations($root_item_id);
        foreach ($available_variations as $variation_id => $variation_attributes) {
            $diff = array_udiff($attributes, $variation_attributes, 'strcasecmp');
//            if ($attributes == $variation_attributes)
            if (empty($diff))
                return $variation_id;
        }
        return false;
    }

    function wpd_get_available_variations($root_item_id) {
        $root_product = wc_get_product($root_item_id);
        $default_available_variations = $root_product->get_available_variations();
        $variations = array();
        foreach ($default_available_variations as $variation_data) {
            $variations[$variation_data["variation_id"]] = $variation_data["attributes"];
        }

        return $variations;
    }
    
    /**
     * Builds the pagination used in the shortcodes
     * @global object $wp_rewrite
     * @param object $wp_query
     * @return string
     */
    function wpd_get_pagination($wp_query) {

        global $wp_rewrite;
        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

        $pagination = array(
            'base' => @add_query_arg('page', '%#%'),
            'format' => '',
            'total' => $wp_query->max_num_pages,
            'current' => $current,
            'show_all' => false,
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'list',
            'next_text' => '»',
            'prev_text' => '«'
        );

        if ($wp_rewrite->using_permalinks())
        {
            if(!empty($_GET))
            {
                $get_params_key=  array_keys($_GET);
                $pagination['base'] = user_trailingslashit(trailingslashit(remove_query_arg($get_params_key, get_pagenum_link(1))) . 'page/%#%/', 'paged');
            }
            
        }
        
        if(!empty($_GET))
        {
            foreach ($_GET as $key=>$value)
                $pagination['add_args'] = $_GET;
        }

        return "<nav class='wpd-pagination'>".str_replace('page/1/', '', paginate_links($pagination))."</nav>";
    }
    
    /**
     * Returns user ordered designs
     * @global object $wpdb
     * @param type $user_id
     * @return array
     */
    function wpd_get_user_orders_designs($user_id) {
        global $wpdb;
        $designs = array();
        $args = array(
            'numberposts' => -1,
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'post_type' => 'shop_order',
            'post_status' => array('wc-processing', 'wc-completed', 'wc-on-hold')
        );

        $orders = get_posts($args);
        foreach ($orders as $order) {
            $sql_1 = "select distinct order_item_id FROM " . $wpdb->prefix . "woocommerce_order_items where order_id=$order->ID";
            $order_items_id = $wpdb->get_col($sql_1);
            foreach ($order_items_id as $order_item_id) {
                $sql_2 = "select meta_key, meta_value FROM " . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key in ('_product_id', '_variation_id', 'wpc_data')";
                $order_item_metas = $wpdb->get_results($sql_2);
                $normalized_item_metas = array();
                foreach ($order_item_metas as $order_item_meta) {
                    $normalized_item_metas[$order_item_meta->meta_key] = $order_item_meta->meta_value;
                }
                if (!isset($normalized_item_metas["wpc_data"]))
                    continue;

                if ($normalized_item_metas["_variation_id"])
                    $product_id = $normalized_item_metas["_variation_id"];
                else
                    $product_id = $normalized_item_metas["_product_id"];
                array_push($designs, array($product_id, $order->post_date, unserialize($normalized_item_metas["wpc_data"]), $order_item_id));
            }
        }
        return $designs;
    }
    
    function get_wpd_bounding_boxes($wpc_metas, $editor)
    {
        $bounding_box_array = get_proper_value($wpc_metas, $editor->item_id, array());
        $bounding_data = get_proper_value($bounding_box_array, 'bounding_box', array());
        $clip_w = get_proper_value($bounding_data, "width", "");
        $clip_h = get_proper_value($bounding_data, "height", "");
        $clip_x = get_proper_value($bounding_data, "x", "");
        $clip_y = get_proper_value($bounding_data, "y", "");
        $clip_radius = get_proper_value($bounding_data, "radius", "");
        $clip_radius_rect = get_proper_value($bounding_data, "r_radius", 0);
        $clip_type = get_proper_value($bounding_data, "type", "");
        $clip_border = get_proper_value($bounding_data, "border_color", "");
        if($clip_w&&$clip_h)
        {
            ?><span class='sc-section-box' style='width:<?php echo $clip_w;?>px;height: <?php echo $clip_h;?>px; top: <?php echo $section["top"];?>px; left: <?php echo $section["left"];?>px; border: 1px solid <?php echo $clip_border;?>'></span><?php
        }
    }
    
    function get_wpd_pdf_formats()
    {
        return array('Custom size' => array('custom'=>__("Custom","wpd")), 'ISO 216 A Series + 2 SIS 014711 extensions' => array('A0' => 'A0 (841x1189 mm ; 33.11x46.81 in)', 'A1' => 'A1 (594x841 mm ; 23.39x33.11 in)', 'A2' => 'A2 (420x594 mm ; 16.54x23.39 in)', 'A3' => 'A3 (297x420 mm ; 11.69x16.54 in)', 'A4' => 'A4 (210x297 mm ; 8.27x11.69 in)', 'A5' => 'A5 (148x210 mm ; 5.83x8.27 in)', 'A6' => 'A6 (105x148 mm ; 4.13x5.83 in)', 'A7' => 'A7 (74x105 mm ; 2.91x4.13 in)', 'A8' => 'A8 (52x74 mm ; 2.05x2.91 in)', 'A9' => 'A9 (37x52 mm ; 1.46x2.05 in)', 'A10' => 'A10 (26x37 mm ; 1.02x1.46 in)', 'A11' => 'A11 (18x26 mm ; 0.71x1.02 in)', 'A12' => 'A12 (13x18 mm ; 0.51x0.71 in)',), 'ISO 216 B Series + 2 SIS 014711 extensions' => array('B0' => 'B0 (1000x1414 mm ; 39.37x55.67 in)', 'B1' => 'B1 (707x1000 mm ; 27.83x39.37 in)', 'B2' => 'B2 (500x707 mm ; 19.69x27.83 in)', 'B3' => 'B3 (353x500 mm ; 13.90x19.69 in)', 'B4' => 'B4 (250x353 mm ; 9.84x13.90 in)', 'B5' => 'B5 (176x250 mm ; 6.93x9.84 in)', 'B6' => 'B6 (125x176 mm ; 4.92x6.93 in)', 'B7' => 'B7 (88x125 mm ; 3.46x4.92 in)', 'B8' => 'B8 (62x88 mm ; 2.44x3.46 in)', 'B9' => 'B9 (44x62 mm ; 1.73x2.44 in)', 'B10' => 'B10 (31x44 mm ; 1.22x1.73 in)', 'B11' => 'B11 (22x31 mm ; 0.87x1.22 in)', 'B12' => 'B12 (15x22 mm ; 0.59x0.87 in)',), 'ISO 216 C Series + 2 SIS 014711 extensions + 2 EXTENSION' => array('C0' => 'C0 (917x1297 mm ; 36.10x51.06 in)', 'C1' => 'C1 (648x917 mm ; 25.51x36.10 in)', 'C2' => 'C2 (458x648 mm ; 18.03x25.51 in)', 'C3' => 'C3 (324x458 mm ; 12.76x18.03 in)', 'C4' => 'C4 (229x324 mm ; 9.02x12.76 in)', 'C5' => 'C5 (162x229 mm ; 6.38x9.02 in)', 'C6' => 'C6 (114x162 mm ; 4.49x6.38 in)', 'C7' => 'C7 (81x114 mm ; 3.19x4.49 in)', 'C8' => 'C8 (57x81 mm ; 2.24x3.19 in)', 'C9' => 'C9 (40x57 mm ; 1.57x2.24 in)', 'C10' => 'C10 (28x40 mm ; 1.10x1.57 in)', 'C11' => 'C11 (20x28 mm ; 0.79x1.10 in)', 'C12' => 'C12 (14x20 mm ; 0.55x0.79 in)', 'C76' => 'C76 (81x162 mm ; 3.19x6.38 in)', 'DL' => 'DL (110x220 mm ; 4.33x8.66 in)',), 'SIS 014711 E Series' => array('E0' => 'E0 (879x1241 mm ; 34.61x48.86 in)', 'E1' => 'E1 (620x879 mm ; 24.41x34.61 in)', 'E2' => 'E2 (440x620 mm ; 17.32x24.41 in)', 'E3' => 'E3 (310x440 mm ; 12.20x17.32 in)', 'E4' => 'E4 (220x310 mm ; 8.66x12.20 in)', 'E5' => 'E5 (155x220 mm ; 6.10x8.66 in)', 'E6' => 'E6 (110x155 mm ; 4.33x6.10 in)', 'E7' => 'E7 (78x110 mm ; 3.07x4.33 in)', 'E8' => 'E8 (55x78 mm ; 2.17x3.07 in)', 'E9' => 'E9 (39x55 mm ; 1.54x2.17 in)', 'E10' => 'E10 (27x39 mm ; 1.06x1.54 in)', 'E11' => 'E11 (19x27 mm ; 0.75x1.06 in)', 'E12' => 'E12 (13x19 mm ; 0.51x0.75 in)',), 'SIS 014711 G Series' => array('G0' => 'G0 (958x1354 mm ; 37.72x53.31 in)', 'G1' => 'G1 (677x958 mm ; 26.65x37.72 in)', 'G2' => 'G2 (479x677 mm ; 18.86x26.65 in)', 'G3' => 'G3 (338x479 mm ; 13.31x18.86 in)', 'G4' => 'G4 (239x338 mm ; 9.41x13.31 in)', 'G5' => 'G5 (169x239 mm ; 6.65x9.41 in)', 'G6' => 'G6 (119x169 mm ; 4.69x6.65 in)', 'G7' => 'G7 (84x119 mm ; 3.31x4.69 in)', 'G8' => 'G8 (59x84 mm ; 2.32x3.31 in)', 'G9' => 'G9 (42x59 mm ; 1.65x2.32 in)', 'G10' => 'G10 (29x42 mm ; 1.14x1.65 in)', 'G11' => 'G11 (21x29 mm ; 0.83x1.14 in)', 'G12' => 'G12 (14x21 mm ; 0.55x0.83 in)',), 'ISO Press' => array('RA0' => 'RA0 (860x1220 mm ; 33.86x48.03 in)', 'RA1' => 'RA1 (610x860 mm ; 23.02x33.86 in)', 'RA2' => 'RA2 (430x610 mm ; 16.93x23.02 in)', 'RA3' => 'RA3 (305x430 mm ; 12.01x16.93 in)', 'RA4' => 'RA4 (215x305 mm ; 8.46x12.01 in)', 'SRA0' => 'SRA0 (900x1280 mm ; 35.43x50.39 in)', 'SRA1' => 'SRA1 (640x900 mm ; 25.20x35.43 in)', 'SRA2' => 'SRA2 (450x640 mm ; 17.72x25.20 in)', 'SRA3' => 'SRA3 (320x450 mm ; 12.60x17.72 in)', 'SRA4' => 'SRA4 (225x320 mm ; 8.86x12.60 in)',), 'German DIN 476' => array('4A0' => '4A0 (1682x2378 mm ; 66.22x93.62 in)', '2A0' => '2A0 (1189x1682 mm ; 46.81x66.22 in)',), 'Variations on the ISO Standard' => array('A2_EXTRA' => 'A2_EXTRA (445x619 mm ; 17.52x24.37 in)', 'A3+' => 'A3+ (329x483 mm ; 12.95x19.02 in)', 'A3_EXTRA' => 'A3_EXTRA (322x445 mm ; 12.68x17.52 in)', 'A3_SUPER' => 'A3_SUPER (305x508 mm ; 12.01x20.00 in)', 'SUPER_A3' => 'SUPER_A3 (305x487 mm ; 12.01x19.17 in)', 'A4_EXTRA' => 'A4_EXTRA (235x322 mm ; 9.25x12.68 in)', 'A4_SUPER' => 'A4_SUPER (229x322 mm ; 9.02x12.68 in)', 'SUPER_A4' => 'SUPER_A4 (227x356 mm ; 8.94x13.02 in)', 'A4_LONG' => 'A4_LONG (210x348 mm ; 8.27x13.70 in)', 'F4' => 'F4 (210x330 mm ; 8.27x12.99 in)', 'SO_B5_EXTRA' => 'SO_B5_EXTRA (202x276 mm ; 7.95x10.87 in)', 'A5_EXTRA' => 'A5_EXTRA (173x235 mm ; 6.81x9.25 in)',), 'ANSI Series' => array('ANSI_E' => 'ANSI_E (864x1118 mm ; 33.00x43.00 in)', 'ANSI_D' => 'ANSI_D (559x864 mm ; 22.00x33.00 in)', 'ANSI_C' => 'ANSI_C (432x559 mm ; 17.00x22.00 in)', 'ANSI_B' => 'ANSI_B (279x432 mm ; 11.00x17.00 in)', 'ANSI_A' => 'ANSI_A (216x279 mm ; 8.50x11.00 in)',), 'Traditional "Loose" North American Paper Sizes' => array('LEDGER, USLEDGER' => 'LEDGER, USLEDGER (432x279 mm ; 17.00x11.00 in)', 'TABLOID, USTABLOID, BIBLE, ORGANIZERK' => 'TABLOID, USTABLOID, BIBLE, ORGANIZERK (279x432 mm ; 11.00x17.00 in)', 'LETTER, USLETTER, ORGANIZERM' => 'LETTER, USLETTER, ORGANIZERM (216x279 mm ; 8.50x11.00 in)', 'LEGAL, USLEGAL' => 'LEGAL, USLEGAL (216x356 mm ; 8.50x13.00 in)', 'GLETTER, GOVERNMENTLETTER' => 'GLETTER, GOVERNMENTLETTER (203x267 mm ; 8.00x10.50 in)', 'JLEGAL, JUNIORLEGAL' => 'JLEGAL, JUNIORLEGAL (203x127 mm ; 8.00x5.00 in)',), 'Other North American Paper Sizes' => array('QUADDEMY' => 'QUADDEMY (889x1143 mm ; 35.00x45.00 in)', 'SUPER_B' => 'SUPER_B (330x483 mm ; 13.00x19.00 in)', 'QUARTO' => 'QUARTO (229x279 mm ; 9.00x11.00 in)', 'FOLIO, GOVERNMENTLEGAL' => 'FOLIO, GOVERNMENTLEGAL (216x330 mm ; 8.50x13.00 in)', 'EXECUTIVE, MONARCH' => 'EXECUTIVE, MONARCH (184x267 mm ; 7.25x10.50 in)', 'MEMO, STATEMENT, ORGANIZERL' => 'MEMO, STATEMENT, ORGANIZERL (140x216 mm ; 5.50x8.50 in)', 'FOOLSCAP' => 'FOOLSCAP (210x330 mm ; 8.27x13.00 in)', 'COMPACT' => 'COMPACT (108x171 mm ; 4.25x6.75 in)', 'ORGANIZERJ' => 'ORGANIZERJ (70x127 mm ; 2.75x5.00 in)',), 'Canadian standard CAN 2-9.60M' => array('P1' => 'P1 (560x860 mm ; 22.05x33.86 in)', 'P2' => 'P2 (430x560 mm ; 16.93x22.05 in)', 'P3' => 'P3 (280x430 mm ; 11.02x16.93 in)', 'P4' => 'P4 (215x280 mm ; 8.46x11.02 in)', 'P5' => 'P5 (140x215 mm ; 5.51x8.46 in)', 'P6' => 'P6 (107x140 mm ; 4.21x5.51 in)',), 'North American Architectural Sizes' => array('ARCH_E' => 'ARCH_E (914x1219 mm ; 36.00x48.00 in)', 'ARCH_E1' => 'ARCH_E1 (762x1067 mm ; 30.00x42.00 in)', 'ARCH_D' => 'ARCH_D (610x914 mm ; 23.00x36.00 in)', 'ARCH_C, BROADSHEET' => 'ARCH_C, BROADSHEET (457x610 mm ; 18.00x23.00 in)', 'ARCH_B' => 'ARCH_B (305x457 mm ; 12.00x18.00 in)', 'ARCH_A' => 'ARCH_A (229x305 mm ; 9.00x12.00 in)',), 'Announcement Envelopes' => array('ANNENV_A2' => 'ANNENV_A2 (111x146 mm ; 4.37x5.75 in)', 'ANNENV_A6' => 'ANNENV_A6 (121x165 mm ; 4.75x6.50 in)', 'ANNENV_A7' => 'ANNENV_A7 (133x184 mm ; 5.25x7.25 in)', 'ANNENV_A8' => 'ANNENV_A8 (140x206 mm ; 5.50x8.12 in)', 'ANNENV_A10' => 'ANNENV_A10 (159x244 mm ; 6.25x9.62 in)', 'ANNENV_SLIM' => 'ANNENV_SLIM (98x225 mm ; 3.87x8.87 in)',), 'Commercial Envelopes' => array('COMMENV_N6_1/4' => 'COMMENV_N6_1/4 (89x152 mm ; 3.50x6.00 in)', 'COMMENV_N6_3/4' => 'COMMENV_N6_3/4 (92x165 mm ; 3.62x6.50 in)', 'COMMENV_N8' => 'COMMENV_N8 (98x191 mm ; 3.87x7.50 in)', 'COMMENV_N9' => 'COMMENV_N9 (98x225 mm ; 3.87x8.87 in)', 'COMMENV_N10' => 'COMMENV_N10 (105x241 mm ; 4.12x9.50 in)', 'COMMENV_N11' => 'COMMENV_N11 (114x263 mm ; 4.50x10.37 in)', 'COMMENV_N12' => 'COMMENV_N12 (121x279 mm ; 4.75x11.00 in)', 'COMMENV_N14' => 'COMMENV_N14 (127x292 mm ; 5.00x11.50 in)',), 'Catalogue Envelopes' => array('CATENV_N1' => 'CATENV_N1 (152x229 mm ; 6.00x9.00 in)', 'CATENV_N1_3/4' => 'CATENV_N1_3/4 (165x241 mm ; 6.50x9.50 in)', 'CATENV_N2' => 'CATENV_N2 (165x254 mm ; 6.50x10.00 in)', 'CATENV_N3' => 'CATENV_N3 (178x254 mm ; 7.00x10.00 in)', 'CATENV_N6' => 'CATENV_N6 (191x267 mm ; 7.50x10.50 in)', 'CATENV_N7' => 'CATENV_N7 (203x279 mm ; 8.00x11.00 in)', 'CATENV_N8' => 'CATENV_N8 (210x286 mm ; 8.25x11.25 in)', 'CATENV_N9_1/2' => 'CATENV_N9_1/2 (216x267 mm ; 8.50x10.50 in)', 'CATENV_N9_3/4' => 'CATENV_N9_3/4 (222x286 mm ; 8.75x11.25 in)', 'CATENV_N10_1/2' => 'CATENV_N10_1/2 (229x305 mm ; 9.00x12.00 in)', 'CATENV_N12_1/2' => 'CATENV_N12_1/2 (241x318 mm ; 9.50x12.50 in)', 'CATENV_N13_1/2' => 'CATENV_N13_1/2 (254x330 mm ; 10.00x13.00 in)', 'CATENV_N14_1/4' => 'CATENV_N14_1/4 (286x311 mm ; 11.25x12.25 in)', 'CATENV_N14_1/2' => 'CATENV_N14_1/2 (292x368 mm ; 11.50x14.50 in)', 'Japanese' => 'Japanese (JIS P 0138-61) Standard B-Series', 'JIS_B0' => 'JIS_B0 (1030x1456 mm ; 40.55x57.32 in)', 'JIS_B1' => 'JIS_B1 (728x1030 mm ; 28.66x40.55 in)', 'JIS_B2' => 'JIS_B2 (515x728 mm ; 20.28x28.66 in)', 'JIS_B3' => 'JIS_B3 (364x515 mm ; 14.33x20.28 in)', 'JIS_B4' => 'JIS_B4 (257x364 mm ; 10.12x14.33 in)', 'JIS_B5' => 'JIS_B5 (182x257 mm ; 7.17x10.12 in)', 'JIS_B6' => 'JIS_B6 (128x182 mm ; 5.04x7.17 in)', 'JIS_B7' => 'JIS_B7 (91x128 mm ; 3.58x5.04 in)', 'JIS_B8' => 'JIS_B8 (64x91 mm ; 2.52x3.58 in)', 'JIS_B9' => 'JIS_B9 (45x64 mm ; 1.77x2.52 in)', 'JIS_B10' => 'JIS_B10 (32x45 mm ; 1.26x1.77 in)', 'JIS_B11' => 'JIS_B11 (22x32 mm ; 0.87x1.26 in)', 'JIS_B12' => 'JIS_B12 (16x22 mm ; 0.63x0.87 in)',), 'PA Series' => array('PA0' => 'PA0 (840x1120 mm ; 33.07x43.09 in)', 'PA1' => 'PA1 (560x840 mm ; 22.05x33.07 in)', 'PA2' => 'PA2 (420x560 mm ; 16.54x22.05 in)', 'PA3' => 'PA3 (280x420 mm ; 11.02x16.54 in)', 'PA4' => 'PA4 (210x280 mm ; 8.27x11.02 in)', 'PA5' => 'PA5 (140x210 mm ; 5.51x8.27 in)', 'PA6' => 'PA6 (105x140 mm ; 4.13x5.51 in)', 'PA7' => 'PA7 (70x105 mm ; 2.76x4.13 in)', 'PA8' => 'PA8 (52x70 mm ; 2.05x2.76 in)', 'PA9' => 'PA9 (35x52 mm ; 1.38x2.05 in)', 'PA10' => 'PA10 (26x35 mm ; 1.02x1.38 in)',), 'Standard Photographic Print Sizes' => array('PASSPORT_PHOTO' => 'PASSPORT_PHOTO (35x45 mm ; 1.38x1.77 in)', 'E' => 'E (82x120 mm ; 3.25x4.72 in)', '3R, L' => '3R, L (89x127 mm ; 3.50x5.00 in)', '4R, KG' => '4R, KG (102x152 mm ; 3.02x5.98 in)', '4D' => '4D (120x152 mm ; 4.72x5.98 in)', '5R, 2L' => '5R, 2L (127x178 mm ; 5.00x7.01 in)', '6R, 8P' => '6R, 8P (152x203 mm ; 5.98x7.99 in)', '8R, 6P' => '8R, 6P (203x254 mm ; 7.99x10.00 in)', 'S8R, 6PW' => 'S8R, 6PW (203x305 mm ; 7.99x12.01 in)', '10R, 4P' => '10R, 4P (254x305 mm ; 10.00x12.01 in)', 'S10R, 4PW' => 'S10R, 4PW (254x381 mm ; 10.00x15.00 in)', '11R' => '11R (279x356 mm ; 10.98x13.02 in)', 'S11R' => 'S11R (279x432 mm ; 10.98x17.01 in)', '12R' => '12R (305x381 mm ; 12.01x15.00 in)', 'S12R' => 'S12R (305x456 mm ; 12.01x17.95 in)',), 'Common Newspaper Sizes' => array('NEWSPAPER_BROADSHEET' => 'NEWSPAPER_BROADSHEET (750x600 mm ; 29.53x23.62 in)', 'NEWSPAPER_BERLINER' => 'NEWSPAPER_BERLINER (470x315 mm ; 18.50x12.40 in)', 'NEWSPAPER_COMPACT, NEWSPAPER_TABLOID' => 'NEWSPAPER_COMPACT, NEWSPAPER_TABLOID (430x280 mm ; 16.93x11.02 in)',), 'Business Cards' => array('CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810' => 'CREDIT_CARD, BUSINESS_CARD, BUSINESS_CARD_ISO7810 (54x86 mm ; 2.13x3.37 in)', 'BUSINESS_CARD_ISO216' => 'BUSINESS_CARD_ISO216 (52x74 mm ; 2.05x2.91 in)', 'BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES' => 'BUSINESS_CARD_IT, BUSINESS_CARD_UK, BUSINESS_CARD_FR, BUSINESS_CARD_DE, BUSINESS_CARD_ES (55x85 mm ; 2.17x3.35 in)', 'BUSINESS_CARD_US, BUSINESS_CARD_CA' => 'BUSINESS_CARD_US, BUSINESS_CARD_CA (51x89 mm ; 2.01x3.50 in)', 'BUSINESS_CARD_JP' => 'BUSINESS_CARD_JP (55x91 mm ; 2.17x3.58 in)', 'BUSINESS_CARD_HK' => 'BUSINESS_CARD_HK (54x90 mm ; 2.13x3.54 in)', 'BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE' => 'BUSINESS_CARD_AU, BUSINESS_CARD_DK, BUSINESS_CARD_SE (55x90 mm ; 2.17x3.54 in)', 'BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL' => 'BUSINESS_CARD_RU, BUSINESS_CARD_CZ, BUSINESS_CARD_FI, BUSINESS_CARD_HU, BUSINESS_CARD_IL (50x90 mm ; 1.97x3.54 in)',), 'Billboards' => array('4SHEET' => '4SHEET (1016x1524 mm ; 40.00x60.00 in)', '6SHEET' => '6SHEET (1200x1800 mm ; 47.24x70.87 in)', '12SHEET' => '12SHEET (3048x1524 mm ; 120.00x60.00 in)', '16SHEET' => '16SHEET (2032x3048 mm ; 80.00x120.00 in)', '32SHEET' => '32SHEET (4064x3048 mm ; 160.00x120.00 in)', '48SHEET' => '48SHEET (6096x3048 mm ; 240.00x120.00 in)', '64SHEET' => '64SHEET (8128x3048 mm ; 320.00x120.00 in)', '96SHEET' => '96SHEET (12192x3048 mm ; 480.00x120.00 in)', 'Old Imperial English' => 'Old Imperial English (some are still used in USA)', 'EN_EMPEROR' => 'EN_EMPEROR (1219x1829 mm ; 48.00x72.00 in)', 'EN_ANTIQUARIAN' => 'EN_ANTIQUARIAN (787x1346 mm ; 31.00x53.00 in)', 'EN_GRAND_EAGLE' => 'EN_GRAND_EAGLE (730x1067 mm ; 28.75x42.00 in)', 'EN_DOUBLE_ELEPHANT' => 'EN_DOUBLE_ELEPHANT (679x1016 mm ; 26.75x40.00 in)', 'EN_ATLAS' => 'EN_ATLAS (660x864 mm ; 26.00x33.00 in)', 'EN_COLOMBIER' => 'EN_COLOMBIER (597x876 mm ; 23.50x34.50 in)', 'EN_ELEPHANT' => 'EN_ELEPHANT (584x711 mm ; 23.00x28.00 in)', 'EN_DOUBLE_DEMY' => 'EN_DOUBLE_DEMY (572x902 mm ; 22.50x35.50 in)', 'EN_IMPERIAL' => 'EN_IMPERIAL (559x762 mm ; 22.00x30.00 in)', 'EN_PRINCESS' => 'EN_PRINCESS (546x711 mm ; 21.50x28.00 in)', 'EN_CARTRIDGE' => 'EN_CARTRIDGE (533x660 mm ; 21.00x26.00 in)', 'EN_DOUBLE_LARGE_POST' => 'EN_DOUBLE_LARGE_POST (533x838 mm ; 21.00x33.00 in)', 'EN_ROYAL' => 'EN_ROYAL (508x635 mm ; 20.00x25.00 in)', 'EN_SHEET, EN_HALF_POST' => 'EN_SHEET, EN_HALF_POST (495x597 mm ; 19.50x23.50 in)', 'EN_SUPER_ROYAL' => 'EN_SUPER_ROYAL (483x686 mm ; 19.00x27.00 in)', 'EN_DOUBLE_POST' => 'EN_DOUBLE_POST (483x775 mm ; 19.00x30.50 in)', 'EN_MEDIUM' => 'EN_MEDIUM (445x584 mm ; 17.50x23.00 in)', 'EN_DEMY' => 'EN_DEMY (445x572 mm ; 17.50x22.50 in)', 'EN_LARGE_POST' => 'EN_LARGE_POST (419x533 mm ; 16.50x21.00 in)', 'EN_COPY_DRAUGHT' => 'EN_COPY_DRAUGHT (406x508 mm ; 16.00x20.00 in)', 'EN_POST' => 'EN_POST (394x489 mm ; 15.50x19.25 in)', 'EN_CROWN' => 'EN_CROWN (381x508 mm ; 15.00x20.00 in)', 'EN_PINCHED_POST' => 'EN_PINCHED_POST (375x470 mm ; 14.75x18.50 in)', 'EN_BRIEF' => 'EN_BRIEF (343x406 mm ; 13.50x16.00 in)', 'EN_FOOLSCAP' => 'EN_FOOLSCAP (343x432 mm ; 13.50x17.00 in)', 'EN_SMALL_FOOLSCAP' => 'EN_SMALL_FOOLSCAP (337x419 mm ; 13.25x16.50 in)', 'EN_POTT' => 'EN_POTT (318x381 mm ; 12.50x15.00 in)',), 'Old Imperial Belgian' => array('BE_GRAND_AIGLE' => 'BE_GRAND_AIGLE (700x1040 mm ; 27.56x40.94 in)', 'BE_COLOMBIER' => 'BE_COLOMBIER (620x850 mm ; 24.41x33.46 in)', 'BE_DOUBLE_CARRE' => 'BE_DOUBLE_CARRE (620x920 mm ; 24.41x36.22 in)', 'BE_ELEPHANT' => 'BE_ELEPHANT (616x770 mm ; 24.25x30.31 in)', 'BE_PETIT_AIGLE' => 'BE_PETIT_AIGLE (600x840 mm ; 23.62x33.07 in)', 'BE_GRAND_JESUS' => 'BE_GRAND_JESUS (550x730 mm ; 21.65x28.74 in)', 'BE_JESUS' => 'BE_JESUS (540x730 mm ; 21.26x28.74 in)', 'BE_RAISIN' => 'BE_RAISIN (500x650 mm ; 19.69x25.59 in)', 'BE_GRAND_MEDIAN' => 'BE_GRAND_MEDIAN (460x605 mm ; 18.11x23.82 in)', 'BE_DOUBLE_POSTE' => 'BE_DOUBLE_POSTE (435x565 mm ; 17.13x22.24 in)', 'BE_COQUILLE' => 'BE_COQUILLE (430x560 mm ; 16.93x22.05 in)', 'BE_PETIT_MEDIAN' => 'BE_PETIT_MEDIAN (415x530 mm ; 16.34x20.87 in)', 'BE_RUCHE' => 'BE_RUCHE (360x460 mm ; 14.17x18.11 in)', 'BE_PROPATRIA' => 'BE_PROPATRIA (345x430 mm ; 13.58x16.93 in)', 'BE_LYS' => 'BE_LYS (317x397 mm ; 12.48x15.63 in)', 'BE_POT' => 'BE_POT (307x384 mm ; 12.09x15.12 in)', 'BE_ROSETTE' => 'BE_ROSETTE (270x347 mm ; 10.63x13.66 in)',), 'Old Imperial French' => array('FR_UNIVERS' => 'FR_UNIVERS (1000x1300 mm ; 39.37x51.18 in)', 'FR_DOUBLE_COLOMBIER' => 'FR_DOUBLE_COLOMBIER (900x1260 mm ; 35.43x49.61 in)', 'FR_GRANDE_MONDE' => 'FR_GRANDE_MONDE (900x1260 mm ; 35.43x49.61 in)', 'FR_DOUBLE_SOLEIL' => 'FR_DOUBLE_SOLEIL (800x1200 mm ; 31.50x47.24 in)', 'FR_DOUBLE_JESUS' => 'FR_DOUBLE_JESUS (760x1120 mm ; 29.92x43.09 in)', 'FR_GRAND_AIGLE' => 'FR_GRAND_AIGLE (750x1060 mm ; 29.53x41.73 in)', 'FR_PETIT_AIGLE' => 'FR_PETIT_AIGLE (700x940 mm ; 27.56x37.01 in)', 'FR_DOUBLE_RAISIN' => 'FR_DOUBLE_RAISIN (650x1000 mm ; 25.59x39.37 in)', 'FR_JOURNAL' => 'FR_JOURNAL (650x940 mm ; 25.59x37.01 in)', 'FR_COLOMBIER_AFFICHE' => 'FR_COLOMBIER_AFFICHE (630x900 mm ; 24.80x35.43 in)', 'FR_DOUBLE_CAVALIER' => 'FR_DOUBLE_CAVALIER (620x920 mm ; 24.41x36.22 in)', 'FR_CLOCHE' => 'FR_CLOCHE (600x800 mm ; 23.62x31.50 in)', 'FR_SOLEIL' => 'FR_SOLEIL (600x800 mm ; 23.62x31.50 in)', 'FR_DOUBLE_CARRE' => 'FR_DOUBLE_CARRE (560x900 mm ; 22.05x35.43 in)', 'FR_DOUBLE_COQUILLE' => 'FR_DOUBLE_COQUILLE (560x880 mm ; 22.05x34.65 in)', 'FR_JESUS' => 'FR_JESUS (560x760 mm ; 22.05x29.92 in)', 'FR_RAISIN' => 'FR_RAISIN (500x650 mm ; 19.69x25.59 in)', 'FR_CAVALIER' => 'FR_CAVALIER (460x620 mm ; 18.11x24.41 in)', 'FR_DOUBLE_COURONNE' => 'FR_DOUBLE_COURONNE (460x720 mm ; 18.11x28.35 in)', 'FR_CARRE' => 'FR_CARRE (450x560 mm ; 17.72x22.05 in)', 'FR_COQUILLE' => 'FR_COQUILLE (440x560 mm ; 17.32x22.05 in)', 'FR_DOUBLE_TELLIERE' => 'FR_DOUBLE_TELLIERE (440x680 mm ; 17.32x26.77 in)', 'FR_DOUBLE_CLOCHE' => 'FR_DOUBLE_CLOCHE (400x600 mm ; 15.75x23.62 in)', 'FR_DOUBLE_POT' => 'FR_DOUBLE_POT (400x620 mm ; 15.75x24.41 in)', 'FR_ECU' => 'FR_ECU (400x520 mm ; 15.75x20.47 in)', 'FR_COURONNE' => 'FR_COURONNE (360x460 mm ; 14.17x18.11 in)', 'FR_TELLIERE' => 'FR_TELLIERE (340x440 mm ; 13.39x17.32 in)', 'FR_POT' => 'FR_POT (310x400 mm ; 12.20x15.75 in)',));
    }
    
    function wpd_get_template_price($tpl_id)
    {
        if(empty($tpl_id))
            return 0;
        
        $tpl_base_price= get_post_meta($tpl_id, "base-price", true);
        if(empty($tpl_base_price))
            $tpl_base_price=0;
        
        return $tpl_base_price;
    }
    
    function wpd_replace_key_in_array($input_array, $search, $replace)
    {
        $output_array=$input_array;
        $output_array[$replace]=$output_array[$search];
        unset($output_array[$search]);
        
        return $output_array;
    }
    
    function wpd_hex_to_rgb($hex)
    {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return array($r, $g, $b);
    }