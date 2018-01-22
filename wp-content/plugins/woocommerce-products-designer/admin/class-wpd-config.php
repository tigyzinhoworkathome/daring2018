<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Contains all methods and hooks callbacks related to the user design
 *
 * @author HL
 */
class WPD_Config {
    
    public function register_cpt_config() {

        $labels = array(
            'name' => _x('Configurations', 'wpd'),
            'singular_name' => _x('Configurations', 'wpd'),
            'add_new' => _x('New configuration', 'wpd'),
            'add_new_item' => _x('New configuration', 'wpd'),
            'edit_item' => _x('Edit configuration', 'wpd'),
            'new_item' => _x('New configuration', 'wpd'),
            'view_item' => _x('View configuration', 'wpd'),
            'not_found' => _x('No configuration found', 'wpd'),
            'not_found_in_trash' => _x('No configuration in the trash', 'wpd'),
            'menu_name' => _x('Product Designer', 'wpd'),
            'all_items' => _x('Configurations', 'wpd'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Configurations',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
        );

        register_post_type('wpd-config', $args);
    }
    
    public function get_config_metabox() {

        $screens = array('wpd-config');

        foreach ($screens as $screen) {
            
            add_meta_box(
                    'wpd-config-basic-config', __('Basic settings', 'wpd'), array($this, 'get_config_basic_page'), $screen
            );

            add_meta_box(
                    'wpd-metas-canvas', __('Canvas', 'wpd'), array($this, 'get_config_canvas_page'), $screen
            );
            
            add_meta_box(
                    'wpd-metas-parts', __('Parts', 'wpd'), array($this, 'get_config_parts_page'), $screen
            );
            
            add_meta_box(
                    'wpd-metas-output', __('Output settings', 'wpd'), array($this, 'get_config_output_page'), $screen
            );
        }
    }
    
    public function close_output_metabox($classes){
        array_push( $classes, 'closed' );
        
        return $classes;
    }
    
    public function get_config_basic_page(){
        
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-basic-settings-container',
            );
        $design_from_blank = array(
            'title' => __('Design from blank', 'wpd'),
            'desc' => __('If enabled, the plugin will display the <strong>Design from blank</strong> button on the product page.','wpd'),
            'name' => 'wpd-metas[can-design-from-blank]',
            'type' => 'checkbox',
            'value' => 1,
            'default' => 1,
            'custom_attributes' => array('onclick'=>'return false;'),
            );
        
        $end = array(
            'type' => 'sectionend',
        );
        
        $settings = array(
            $begin,
            $design_from_blank,
            $end
                );
        echo o_admin_fields($settings);
        
    }
    
    public function get_config_canvas_page(){
        wp_enqueue_media();
        GLOBAL $wpd_settings;
        $canvas_global_settings = get_proper_value($wpd_settings, 'wpc-general-options', array());

        $args = array("post_status" => "publish");
        $pages = get_pages($args);
        $pages_arr = array();

        foreach ($pages as $page) {
            $pages_arr[$page->ID] = $page->post_title;
        }
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-canvas-settings',
        );
        $max_width = array(
            'title' => __('Canvas max width (px)', 'wpd'),
            'name' => "wpd-metas[canvas-w]",
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => 800
        );
        $max_height = array(
            'title' => __('Canvas max height (px)', 'wpd'),
            'name' => "wpd-metas[canvas-h]",
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => 500
        );
        
        $dimensions = array(
          'title' => __('Dimensions','wpd'),
          'type' => 'groupedfields',
          'desc' => __('Width and Height of the design area in the browser.','wpd'),
          'fields' => array($max_width, $max_height)
        );


        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $dimensions,
            $end
                );
        echo o_admin_fields($settings);
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }
    
    public function get_config_parts_page(){
        wp_enqueue_media();
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-bbox-settings',
        );
        
        $name = array(
            'title' => __('Name','wpd'),
            'type' => 'text',
            'name' => 'name'
        );
        
        $part_icon = array(
            'title' => __('Icon', 'wpd'),
            'name' => 'icon',
            'type' => 'image',
            'set' => 'Set',
            'remove'=> 'Remove',
        );
        $bg_inc = array(
            'title' => __('Bg. (inc.)', 'wpd'),
            'name' => 'bg-inc',
            'type' => 'image',
            'set' => 'Set',
            'remove'=> 'Remove',
            'desc' => __('Canvas background image included in the output.', 'wpd'),
        );
        $bg_not_inc = array(
            'title' => __('Bg. (not inc.)', 'wpd'),
            'name' => 'bg-not-inc',
            'type' => 'image',
            'set' => 'Set',
            'remove'=> 'Remove',
            'desc' => __('Canvas background image not included in the output.', 'wpd'),
        );
        
        $overlay = array(
            'title' => __('Overlay', 'wpd'),
            'name' => 'ov-img',
            'type' => 'image',
            'set' => 'Set',
            'remove'=> 'Remove',
        );

        $overlay_inc = array(
            'title' => __('Inc. overlay in output', 'wpd'),
            'name' => 'ov-inc',
            'type' => 'checkbox',
            'value' => 1,
        );
        
        $parts = array(
            'title' => __('Parts','wpd'),
            'name' => 'wpd-metas[parts]',
            'desc' => __('Product parts settings. <br><strong>Icon</strong>: Part icon <br><strong>Bg</strong>: Background <br><strong>Ov</strong>: Overlay<br><strong>Inc</strong>: Included','wpd'),
            'type' => 'repeatable-fields',
            'fields' => array($name,$part_icon, $bg_inc,$bg_not_inc,$overlay,$overlay_inc),
            'ignore_desc_col' => false,
            'add_btn_label' => __("Add Part", 'wpd'),
        );
        $end = array('type' => 'sectionend');
        
        $settings = array(
            $begin,
            $parts,
            $end
        );
        echo o_admin_fields($settings);
        
    }
    
    public function get_config_output_page(){
        GLOBAL $wpd_settings;
        $output_global_settings = get_proper_value($wpd_settings, 'wpc-output-options', array());

        $options = array();
        $output_format = array(
            'title' => __('Output Format', 'wpd'),
            'name' => 'wpd-metas[output-settings][output-format]',
            'type' => 'radio',
            'row_class' => 'config-output-format',
            'options' => array(
                'png' => __('PNG', 'wpd'),
                'jpg' => __('JPG', 'wpd'),
                'svg' => __('SVG', 'wpd'),             
            ),
            'default' => 'png'
        );
        
        $output_loop_delay = array(
            'title' => __('Output loop delay (milliseconds)', 'wpd'),
            'name' => 'wpd-metas[output-settings][wpc-output-loop-delay]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'wpc-output-loop-delay')
        );
        
        $zip_output = array(
            'title' => __('Zip output file', 'wpd'),
            'name' => 'wpd-metas[output-settings][zip-output]',
            'type' => 'radio',
            'row_class' => 'zip-output',
            'options' => array(
                'yes' => __('Yes', 'wpd'),
                'no' => __('No', 'wpd'),              
            ),
            'default' => 'no'
        );
        $zip_folder_name = array(
            'title' => __('Zip output folder prefix', 'wpd'),
            'name' => 'wpd-metas[output-settings][zip-folder-name]',
            'type' => 'text',
            'row_class' => 'show-if-zip',
        );
        $cmyk_conversion = array(
            'title' => __('CMYK conversion (Requires ImageMagick)', 'wpd'),
            'name' => 'wpd-metas[output-settings][wpc-cmyk-conversion]',
            'type' => 'radio',
            'row_class' => 'show-if-jpg',
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd')
            ),
            'default' => get_proper_value($output_global_settings, 'wpc-cmyk-conversion')
        );

        $output_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Output Settings', 'wpd'),
            'table' => 'metas',
            'id' => 'wpc_product_output'
        );

        $output_options_end = array('type' => 'sectionend',
            'id' => 'wpc_product_output'
        );

        array_push($options, $output_options_begin);
        array_push($options, $output_format);
        array_push($options, $output_loop_delay);
        array_push($options, $zip_output);
        array_push($options, $zip_folder_name);
        array_push($options, $output_options_end);
        ?>
        
        <?php
        echo o_admin_fields($options);
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl = <?php echo json_encode($o_row_templates);?>;
        </script>
        <?php
    }
    
    public function save_config($post_id){
        $meta_key="wpd-metas";
       if(isset($_POST[$meta_key]))
       {
           update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
       }
    }
    
    function migrate_metas_to_v5()
    {
        global $wpdb;
                
        $metas_request = $wpdb->get_results(
            "
            select post_id , meta_value
            from $wpdb->postmeta, $wpdb->posts
            where ID=post_id 
            and post_type='product'
            and meta_key = 'wpc-metas'
            "
        );
        
        foreach($metas_request as $metas_row){
            $product_metas=array();
            $product=wc_get_product( $metas_row->post_id );
            if($product->get_type()=="variable")
            {
                $variations = $product->get_available_variations();
                foreach($variations as $vars){
                    $configuration_id=$this->migrate_metas($vars['variation_id'], $metas_row->meta_value);
                    if($configuration_id)
                    $product_metas[$vars['variation_id']]['config-id']=$configuration_id;
                }
            }
            else
            {
                $configuration_id=$this->migrate_metas($metas_row->post_id, $metas_row->meta_value);
                if($configuration_id)
                $product_metas[$metas_row->post_id]['config-id']=$configuration_id;
            }
            
            if($product_metas)
                update_post_meta($metas_row->post_id,'wpd-metas',$product_metas);
        }
        update_option('wpd-db-version', '5.0');
    }
    
    private function get_part_name($part_key)
    {
        $parts = get_option("wpc-parts");
        foreach ($parts as $part)
        {
            if(sanitize_title( $part)==$part_key)
                return $part;
        }
    }
    
    private function migrate_metas($product_id, $serialized_old_metas){
        global $wpd_settings;
        $wpc_output_options = $wpd_settings['wpc-output-options'];
        $pdf=false;
        if(isset($wpc_output_options['wpc-generate-pdf']) && $wpc_output_options['wpc-generate-pdf'] === "yes")
            $pdf=true;
        
        $svg=false;
        if(isset($wpc_output_options['wpc-generate-svg']) && $wpc_output_options['wpc-generate-svg'] === "yes")
            $svg=true;
        
        if ($pdf && $svg) {
            $output_format='pdf+svg';
        }
        elseif($svg)
        {
            $output_format='svg';
        }
        elseif($pdf)
            $output_format='pdf+png';
        else
            $output_format='png';
        
        $old_metas=  unserialize($serialized_old_metas);
            
        $new_metas['can-upload-custom-design'] = $old_metas['can-upload-custom-design'];
        $new_metas['can-design-from-blank'] = $old_metas['can-design-from-blank'];
        $new_metas['pricing-rules'] = $old_metas['pricing-rules'];
        $i=0;
        $new_metas=array_merge($new_metas, $old_metas[$product_id]);
        $old_parts_structure=$new_metas['parts'];
         unset($new_metas['parts']);
        //Parts
        foreach($old_parts_structure as $part_sanitized_name => $part){
            if(!isset($part['enabled'])||empty($part['enabled']))
                continue;
            $new_metas['parts'][$i] = $old_parts_structure[$part_sanitized_name];
            $new_metas['parts'][$i]['name'] = $this->get_part_name($part_sanitized_name);
            foreach($old_parts_structure[$part_sanitized_name] as $ovs){
                if(isset($ovs['img']))
                    $new_metas['parts'][$i]['ov-img'] = $ovs['img'];
                else
                    $new_metas['parts'][$i]['ov-img'] = '';
                if(isset($ovs['inc']))
                    $new_metas['parts'][$i]['ov-inc'] = $ovs['inc'];
                else
                    $new_metas['parts'][$i]['ov-inc'] = '';
                unset($new_metas['parts'][$i]['ov']);
            }
            $i++;
        }
        //Product or variation not customizable
        if(!isset($new_metas['parts']))
            return false;
        
        $new_metas['output-settings']['output-format']=$output_format;
        
        $config_post_args = array(
                    'post_title' => 'Product '.$product_id,
                    'post_type' => 'wpd-config',
                    'post_status' => 'publish'
            );
        $configuration_id = wp_insert_post($config_post_args);
        if($configuration_id)
        {
            update_post_meta($configuration_id,'wpd-metas',$new_metas);
            update_post_meta($configuration_id,'wpd-metas',$new_metas);
        }
        return $configuration_id;
    }
    
    public function get_product_config_selector() {
        $id=  get_the_ID();
        
        $args = array(
            "post_type" => "wpd-config",
            "nopaging" => true,
        );
        $configs = get_posts($args);
        $configs_ids=array(""=> "None");
        foreach ($configs as $config)
        {
            $configs_ids[$config->ID]=$config->post_title;
        }
        ?><div id="vpc_config_data" class="show_if_simple"><?php
         $this->get_product_tab_row($id, $configs_ids, "Configuration");
        ?></div><?php
    }
    
    private function get_product_tab_row($pid, $configs_ids, $title)
    {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-metas-data',
        );
        
        $configurations = array(
            'title' => $title,
            'name' => "wpd-metas[$pid][config-id]",
            'type' => 'select',
            'options' => $configs_ids,
        );

        
        $end = array('type' => 'sectionend');
        $settings = apply_filters("vpc_product_tab_settings", array(
            $begin,
            $configurations,
            $end
        ));
        
        echo "<div class='vpc-product-config-row'>".o_admin_fields($settings)."</div>";
    }
    
    /*
     *  set Variables product configuration form*
    */
    public function get_variation_product_config_selector( $loop, $variation_data, $variation ) {
            $id = $variation->ID;
        
            $args = array(
                "post_type" => "wpd-config",
                "nopaging" => true,
            );
            $configs = get_posts($args);
            $configs_ids=array(""=> "None");
            foreach ($configs as $config)
            {
                $configs_ids[$config->ID]=$config->post_title;
            }
        ?>
                <tr>
                    <td><?php
                        $this->get_product_tab_row($id, $configs_ids, "Configuration");
                    ?></td>
                </tr>
        <?php

    }
    
    public function get_metabox_order($order) {
        $order["advanced"] = "wpd-config-basic-config,wpd-metas-canvas,wpd-metas-parts,wpd-metas-pricing-rules,wpd-metas-output,submitdiv";
        return $order;
    }
    
    function get_duplicate_post_link( $actions, $post ) {
            if ($post->post_type=='wpd-config' && current_user_can('edit_posts')) {
                    $actions['duplicate'] = '<a href="admin.php?action=wpd_duplicate_config&amp;post=' . $post->ID . '&amp;duplicate_nonce=' . wp_create_nonce(basename( __FILE__ )). '" title="Duplicate this item" rel="permalink">Duplicate</a>';
            }
            return $actions;
    }
    
    /*
    * Function creates post duplicate as a draft and redirects then to the edit post screen
    */
   function wpd_duplicate_config(){       
           global $wpdb;
           if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpd_duplicate_config' == $_REQUEST['action'] ) ) ) {
                   wp_die('No post to duplicate has been supplied!');
           }

           /*
            * Nonce verification
            */
           if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
                   return;

           /*
            * get the original post id
            */
           $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
           /*
            * and all the original post data then
            */
           $post = get_post( $post_id );

           /*
            * if you don't want current user to be the new post author,
            * then change next couple of lines to this: $new_post_author = $post->post_author;
            */
           $current_user = wp_get_current_user();
           $new_post_author = $current_user->ID;

           /*
            * if post data exists, create the post duplicate
            */
           if (isset( $post ) && $post != null) {

                   /*
                    * new post data array
                    */
                   $args = array(
                           'comment_status' => $post->comment_status,
                           'ping_status'    => $post->ping_status,
                           'post_author'    => $new_post_author,
                           'post_content'   => $post->post_content,
                           'post_excerpt'   => $post->post_excerpt,
                           'post_name'      => $post->post_name,
                           'post_parent'    => $post->post_parent,
                           'post_password'  => $post->post_password,
                           'post_status'    => 'draft',
                           'post_title'     => $post->post_title.__(' - copy','wpd'),
                           'post_type'      => $post->post_type,
                           'to_ping'        => $post->to_ping,
                           'menu_order'     => $post->menu_order
                   );

                   /*
                    * insert the post by wp_insert_post() function
                    */
                   $new_post_id = wp_insert_post( $args );

                   /*
                    * get all current post terms ad set them to the new post draft
                    */
                   $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
                   foreach ($taxonomies as $taxonomy) {
                           $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                           wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                   }

                   /*
                    * duplicate all post meta just in two SQL queries
                    */
                   $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
                   if (count($post_meta_infos)!=0) {
                           $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                           foreach ($post_meta_infos as $meta_info) {
                                   $meta_key = $meta_info->meta_key;
                                   if( $meta_key == '_wp_old_slug' ) continue;
                                   $meta_value = addslashes($meta_info->meta_value);
                                   $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
                           }
                           $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                           $wpdb->query($sql_query);
                   }


                   /*
                    * finally, redirect to the edit post screen for the new draft
                    */
                   wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
                   exit;
           } else {
                   wp_die('Post creation failed, could not find original post: ' . $post_id);
           }
   }
   
   public function save_variation_settings_fields($variation_id){
        $meta_key="wpd-metas";
       if(isset($_POST[$meta_key]))
       {
           $variation= wc_get_product($variation_id);
           $old_metas=  get_post_meta($variation->parent->id, $meta_key, true);
           if(empty($old_metas))
               $old_metas=array();
           $new_metas=  array_replace($old_metas, $_POST[$meta_key]);
           update_post_meta($variation->parent->id, $meta_key, $new_metas);
       }

    }

}
