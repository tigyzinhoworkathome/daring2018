<?php	


if ( ! defined('ABSPATH')) exit; // if direct access 



if(empty($_POST['product_designer_hidden']))
	{
		$product_designer_page_id = get_option( 'product_designer_page_id' );		
		$product_designer_posts_per_page = get_option( 'product_designer_posts_per_page' );
		$product_designer_posttype = get_option( 'product_designer_posttype' );
		$product_designer_upload_clipart = get_option( 'product_designer_upload_clipart' );		
		
	
		
		
	}
else
	{	
		if($_POST['product_designer_hidden'] == 'Y') {
			//Form data sent
			
			$product_designer_page_id = sanitize_text_field($_POST['product_designer_page_id']);
			update_option('product_designer_page_id', $product_designer_page_id);			
			
			$product_designer_posts_per_page = sanitize_text_field($_POST['product_designer_posts_per_page']);
			update_option('product_designer_posts_per_page', $product_designer_posts_per_page);
	
			$product_designer_posttype = stripslashes_deep($_POST['product_designer_posttype']);
			update_option('product_designer_posttype', $product_designer_posttype);			
			

			$product_designer_upload_clipart = sanitize_text_field($_POST['product_designer_upload_clipart']);
			update_option('product_designer_upload_clipart', $product_designer_upload_clipart);				
			
			
		
			
			
	
			?>
			<div class="updated"><p><strong><?php _e('Changes Saved.',  product_designer_textdomain ); ?></strong></p></div>
	
			<?php
			} 
	}
?>

<div class="wrap">
	<?php echo "<h2>".sprintf(__('%s Settings',  product_designer_textdomain), product_designer_plugin_name)."</h2>";
	
    $product_designer_customer_type = get_option('product_designer_customer_type');
    $product_designer_version = get_option('product_designer_version');
	
	//var_dump($product_designer_posttype);
	?>
    <br />
		<form  method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<input type="hidden" name="product_designer_hidden" value="Y">
        <?php settings_fields( 'product_designer_plugin_options' );
				do_settings_sections( 'product_designer_plugin_options' );
			
		?>

    <div class="para-settings">
        <ul class="tab-nav"> 
            <li nav="1" class="nav1 active"><?php echo __('Options', product_designer_textdomain); ?></li>

                       
            
        </ul> <!-- tab-nav end -->  
        
		<ul class="box">
            <li style="display: block;" class="box1 tab-box active">
            
            
				<div class="option-box">
                    <p class="option-title"><?php echo __('Designer page id', product_designer_textdomain); ?></p>
                    <p class="option-info"></p>
                	
                    <select name="product_designer_page_id">
                    
                    <?php
                    $product_designer_page_list_ids = product_designer_page_list_ids();
					
					foreach($product_designer_page_list_ids as $id=>$title){
						
						if($product_designer_page_id == $id){
							echo '<option selected value="'.$id.'" >'.$title.'</option>';
							}
						else{
							echo '<option value="'.$id.'" >'.$title.'</option>';
							}
						
						
						}
					
					
					?>
                    
                    </select>
                    
                    
                </div>            
            
				<div class="option-box">
                    <p class="option-title"><?php echo __('Number of items on list', product_designer_textdomain); ?></p>
                    <p class="option-info"></p>
                	<input size="15" type="text" name="product_designer_posts_per_page" value="<?php if(!empty($product_designer_posts_per_page)) echo $product_designer_posts_per_page; else echo 10; ?>" />
                </div>
            
				<div class="option-box">
                    <p class="option-title"><?php echo __('Product designer on post type', product_designer_textdomain); ?></p>
                    <p class="option-info"></p>
                	<select multiple="multiple" size="6" name="product_designer_posttype[]">
                    <?php 
					

					
						$post_types_all = get_post_types( '', 'names' ); 
						foreach ( $post_types_all as $post_type ) {
				
							global $wp_post_types;
							$obj = $wp_post_types[$post_type];
							
							if(in_array($post_type, $product_designer_posttype)){
								$selected = 'selected';
								}
							else{
								$selected = '';
								}
				
							?>
                            <option <?php echo $selected; ?> value="<?php echo $post_type; ?>" ><?php echo $obj->labels->singular_name; ?></option>
                            <?php
						}
						

					
					?>
                    </select>
                    
                </div>   

				<div class="option-box">
                    <p class="option-title"><?php echo __('User can upload clipart', product_designer_textdomain); ?></p>
                    <p class="option-info"></p>
                	<select name="product_designer_upload_clipart">
                    	<option <?php if($product_designer_upload_clipart=='no') {echo 'selected'; } ?> value="no"><?php echo __('No', product_designer_textdomain); ?></option>
                        <option <?php if($product_designer_upload_clipart=='yes') {echo 'selected'; } ?> value="yes"><?php echo __('Yes', product_designer_textdomain); ?></option>
                    </select>
                </div>

                
				                
                
                
            
            </li>
			
            
            
                    
        
        
    
    </div>
<p class="submit">
                    <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', product_designer_textdomain ); ?>" />
                </p>
		</form>
        
</div> <!-- wrap end -->