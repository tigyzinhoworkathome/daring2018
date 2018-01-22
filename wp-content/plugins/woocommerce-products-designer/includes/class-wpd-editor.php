<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-editor
 *
 * @author HL
 */
class WPD_Editor {

    public $item_id;
    public $root_item_id;
    public $wpd_product;

    public function __construct($item_id) {
        if ($item_id) {
            $this->item_id = $item_id;
            $this->wpd_product = new WPD_Product($item_id);
            $this->root_item_id = $this->wpd_product->root_product_id;
        }
    }

    function get_editor() {
        GLOBAL $wpd_settings, $wp_query;
        

        ob_start();
        $product = wc_get_product($this->item_id);
        if (!$product) {
            _e('Error: Invalid product: ', 'wpd');
            echo "$this->item_id<br>";
            _e('1- Is your customization page defined as homepage?', 'wpd');
            echo "<br>";
            _e('2- Is your customization page defined as one of woocommerce pages?', 'wpd');
            echo "<br>";
            _e('3- Does the product you are trying to customize exists and is published in your shop?', 'wpd');
            echo "<br>";
            _e('4- Are you accessing this page from one of the product designer buttons?', 'wpd') . "<br>";
            return;
        }
        if (!$this->wpd_product->has_part()) {
            _e('Error: No active part defined for this product. A customizable product should have at least one part defined.', 'wpd');
            return;
        }
        $wpc_metas = $this->wpd_product->settings;
        
        wpd_init_canvas_vars($wpc_metas, $product, $this);
        $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
        $skin = get_proper_value($ui_options, "skin", "WPD_Skin_Default");
        
        $editor = new $skin($this, $wpc_metas);

        $raw_output = $editor->display();
        echo $raw_output;
        
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    

}
