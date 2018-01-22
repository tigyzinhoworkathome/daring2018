<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access


add_shortcode('product_designer', 'product_designer_display');

function product_designer_display(){
	
	$product_designer_upload_clipart = get_option('product_designer_upload_clipart');	
	$product_designer_page_id = get_option('product_designer_page_id');
	$product_designer_page_url = get_permalink($product_designer_page_id);
	
	$post_id = get_the_ID();
	$page_url = get_permalink($post_id);
	
	
	if(!empty($_GET['product_id'])){
		$product_id = sanitize_text_field($_GET['product_id']);
		
		$side_data = get_post_meta( $product_id, 'side_data', true );
		$templates = get_post_meta( $product_id, 'templates', true );	
		
		if(!empty($_GET['side'])){
			$current_side = sanitize_text_field($_GET['side']);
			
			
			
			}
		else{
			$current_side = '';	
			$current_side_empty = array();
			if(!empty($side_data))
			foreach($side_data as $id=>$side){
				$current_side_empty[] = $id;
				}
			if(!empty($current_side_empty[0])){
				$current_side = $current_side_empty[0];
				}
			else{
				$current_side = '';
				}
			}
		
	
		//var_dump($side_data);
		
		?>
        <div class="TDesigner">
        
        	<div class="menu">
            	
                <div class="templates item tooltip" title="Templates" ><span class="icon"><i class="fa fa-film" ></i></span>
                 	<div class="child">
                    	<span><?php echo __('Sample templates:', product_designer_textdomain); ?></span>	
                        <ul class="template-list">
                        
                        <?php 
                        
						if(!empty($templates)){
							
								foreach($templates as $side_id=>$template_group){
									foreach($template_group as $t_id=>$template){				
									
									if($side_id==$current_side){
										
										$name = $template['name'];
										$content = $template['content'];
										
										echo '<li class="template" side_id="'.$side_id.'" t_id="'.$t_id.'">';
										echo $name;
										echo '</li>';
										
										}

									}
								}
							
							
							}
						else{
							echo __('Sorry no template found for this item.', product_designer_textdomain);
							}


                        
                        ?>       
                        
                        
                        
                        </ul>
                   </div>
               </div>                
                
                
                <div class="side item tooltip" title="Sides"><span class="icon"><i class="fa fa-cube" ></i></span>
                 	<div class="child">
                    	
                        
                        <ul class="side-list">
                        
                        <?php 
                        
						if(!empty($_COOKIE['side_customized'])){
							$cook_data = $_COOKIE['side_customized'];
							}
						else{
							$cook_data = '';
							}
						
						//var_dump(stripslashes($cook_data));
						//var_dump(unserialize($cook_data));	
						$cook_data = unserialize(stripslashes($cook_data));	
						//var_dump($cook_data);				
						if(!empty($cook_data[$product_id])){
							$prduct_cook_data = $cook_data[$product_id];
							}
						else{
							$prduct_cook_data = array();
							}
						
						if(!empty($side_data)){
							
							foreach($side_data as $id=>$side){
								
								$name = $side['name'];
								$src = $side['src'];	
								
								if($current_side==$id){
									$active = 'active';
									
									}
								else{
									$active = '';
									}
								
								
								if(!empty($src)){
									echo '<li>';
									echo '<a title="'.__('Original design.', product_designer_textdomain).'" class="tooltip '.$active.'" href="'.$page_url.'?product_id='.$product_id.'&side='.$id.'">'.$name.'<img src="'.$src.'" /></a>';
									echo '<i class="fa fa-hand-o-right" ></i>';
									
									if(!empty($prduct_cook_data[$id])){
										$attach_id = $prduct_cook_data[$id];
										//var_dump($customized_data);
										$attach_url = wp_get_attachment_url( $attach_id );
										echo ' <a class="tooltip" title="'.__('Your design.', product_designer_textdomain).'" href="#">&nbsp;<img src="'.$attach_url.'" /></a>';
										}
									else{
											echo ' <a class="tooltip" title="'.__('Empty', product_designer_textdomain).'" href="#">&nbsp;<img src="'.product_designer_plugin_url.'assets/front/images/placeholder.png" /></a>';
										}
									
									
									
									
									echo '</li>';
									}
								
								
								}

							}
						else{
							
							echo '<span>'.__('Not avilable.', product_designer_textdomain).'</span>';
							
							}
							
							

                        
                        ?>
                        
                        </ul>

                    
                    
                    </div>
                
                </div>           
            	<div class="clipart item " title="<?php echo __('Clip Art', product_designer_textdomain); ?>"><span class="icon"><i class="fa fa-file-image-o" ></i></span>
                	<div class="child">
						
                        <select title="<?php echo __('Categories', product_designer_textdomain); ?>" id="clipart-cat">
                        
						<?php
                        
                                $args=array(
                                  'orderby' => 'name',
                                  'order' => 'ASC',
                                  'taxonomy' => 'clipart_cat',
                                  );
                                

                                
                                echo '<option value="all">'.__('All', product_designer_textdomain).'</option>';
                                
                                $categories = get_categories($args);
                                
                                foreach($categories as $category){
                                    
                                  echo '<option value='.$category->cat_ID.'>'.$category->cat_name.'</option>';	
                                
                                }
                                        

                               	//echo '<span class="sticker-cat-loading">Loading...</span>';	
                        
                        ?> 
                        
                        
                        
                        
                        
                        
                        </select>
                        
                        
<?php

	if($product_designer_upload_clipart=='yes'){
		

	$field_id = 'clipart';


	echo '<div id="plupload-'.$field_id.'">
			<div id="plupload-drag-drop'.$field_id.'" >
				<span id="plupload-browse-'.$field_id.'" class="clipart-upload button tooltip" title="'.__('Upload custom clipart', product_designer_textdomain).'"><i class="fa fa-upload"></i> '.__('Upload', product_designer_textdomain).'</span>	  
			 </div>
		  </div>';


  $plupload_init = array(
    'runtimes'            => 'html5,silverlight,flash,html4',
    'browse_button'       => 'plupload-browse-'.$field_id.'',
	//'multi_selection'	  =>false,
    'container'           => 'plupload-'.$field_id.'',
    'drop_element'        => 'plupload-drag-drop'.$field_id.'',
    'file_data_name'      => 'async-upload',
    'multiple_queues'     => true,
    'max_file_size'       => wp_max_upload_size().'b',
    'url'                 => admin_url('admin-ajax.php'),
    //'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
    //'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters'             => array(array('title' => __('Allowed Files', product_designer_textdomain), 'extensions' => 'gif,png,jpg,jpeg')),
    'multipart'           => true,
    'urlstream_upload'    => true,

    // additional post data to send to our ajax hook
    'multipart_params'    => array(
      '_ajax_nonce' => wp_create_nonce('photo-upload'),
      'action'      => 'clipart_upload',            // the ajax action name
    ),
  );

  // we should probably not apply this filter, plugins may expect wp's media uploader...
  $plupload_init = apply_filters('plupload_init', $plupload_init);
  
  
  echo '
  			
		 <script>
		
			jQuery(document).ready(function($){
		
			  // create the uploader and pass the config from above
			  var uploader_'.$field_id.' = new plupload.Uploader('.json_encode($plupload_init).');
		
			  // checks if browser supports drag and drop upload, makes some css adjustments if necessary
			  uploader_'.$field_id.'.bind("Init", function(up){
				var uploaddiv = $("#plupload-'.$field_id.'");
		
				if(up.features.dragdrop){
				  uploaddiv.addClass("drag-drop");
					$("#plupload-drag-drop'.$field_id.'")
					  .bind("dragover.wp-uploader", function(){ uploaddiv.addClass("drag-over"); })
					  .bind("dragleave.wp-uploader, drop.wp-uploader", function(){ uploaddiv.removeClass("drag-over"); });
		
				}else{
				  uploaddiv.removeClass("drag-drop");
				  $("#plupload-drag-drop'.$field_id.'").unbind(".wp-uploader");
				}
			  });
		
			  uploader_'.$field_id.'.init();
		
			  // a file was added in the queue
			  uploader_'.$field_id.'.bind("FilesAdded", function(up, files){
				var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
		
				plupload.each(files, function(file){
				  if (max > hundredmb && file.size > hundredmb && up.runtime != "html5"){
					// file size error?
					console.log("Error...");
				  }else{

					
				  }
				});
		
				up.refresh();
				up.start();
			  });
		
			  // a file was uploaded 
			  uploader_'.$field_id.'.bind("FileUploaded", function(up, file, response) {

				var result = $.parseJSON(response.response);

				var attach_url = result.html.attach_url;
				var attach_id = result.html.attach_id;
				var attach_title = result.html.attach_title;
				
				//alert(attach_url);
				
				
				
				var html_new = "<img src="+attach_url+" />";
				
				$(".clipart-list").prepend(html_new); 
				 
			  });
		
			});   
		
		  </script>
  
  
  ';
  
		
		}

  

  

?>    
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        <div class="clipart-list">
                        
                        <?php
                        $product_designer_posts_per_page = get_option('product_designer_posts_per_page', 10);
						
						
						$args = array(
									'post_type'=>'clipart',
									'posts_per_page'=> $product_designer_posts_per_page,
									);
						
						
						$wp_query = new WP_Query($args);
						
						if ( $wp_query->have_posts() ) :
						while ( $wp_query->have_posts() ) : $wp_query->the_post();	
						$thumb = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full' );
						$thumb_url = $thumb['0'];
						
						if(!empty($thumb_url))
						echo '<img class="tooltip" title="'.get_the_title().'" src="'.$thumb_url.'" />';
						
						endwhile;
						wp_reset_query();				
						endif;	
						?>
                        
                        
                        </div>
                        
                        <div class="clipart-pagination">
                        
                        <?php
                       		$paged = 1;
							$big = 999999999; // need an unlikely integer
							echo paginate_links( array(
								'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
								'format' => '?paged=%#%',
								'current' => max( 1, $paged ),
								
								'prev_text'          => '',
								'next_text'          => '',
								'total' => $wp_query->max_num_pages
								) );
						
						?>
                        
                        
                        
                        </div>

                    </div>
                </div>
                
            	<div class="text item tooltip" title="Text Art"><span class="icon"><i class="fa fa-file-word-o" ></i></span>
                	<div class="child">
                    <textarea class="input-text"></textarea><br>
                    <input type="button" class="button add-text" value="<?php echo __('Add Text', product_designer_textdomain); ?>">
                    </div>
                
                </div>
                
            	<div class="shapes item tooltip" title="Shapes"><span class="icon"><i class="fa fa-map-o" ></i></span>
                
                	<div class="child">
                    	<div class="shape-list">
                        	
                            <div class="tooltip" title="<?php echo __('Rectangle', product_designer_textdomain); ?>" id="shapes-rectangle"><?php echo __('Rectangle', product_designer_textdomain); ?></div>                            
                            <div class="tooltip" title="<?php echo __('Circle', product_designer_textdomain); ?>" id="shapes-circle"><?php echo __('Circle', product_designer_textdomain); ?></div>
                            <div class="tooltip" title="<?php echo __('Triangle', product_designer_textdomain); ?>" id="shapes-triangle"><?php echo __('Triangle', product_designer_textdomain); ?></div>                            
                            
                            
                            
                        </div>
                    
                    </div>
                
                </div>                 
                
            	<div class="save item tooltip" title="Save"><span class="icon"><i class="fa fa-floppy-o" ></i></span>
                
                </div>  
                
                <?php
                
				if(current_user_can('administrator')){
					
					?>
                        <div title="Export" class="export item tooltip" ><span class="icon"><i class="fa fa-file-code-o" ></i></span>
                        	<div class="child">
                                <div class="export-list">
                                    
                                    <div class="tooltip" title="<?php echo __('Create New', product_designer_textdomain); ?>" id="export-new"><?php echo __('Create New', product_designer_textdomain); ?></div>                            
                                    <div class="tooltip" title="<?php echo __('Update', product_designer_textdomain); ?>" id="export-update"><?php echo __('Update', product_designer_textdomain); ?></div>
                        
                                    
                                    
                                    
                                </div>
                            </div>
                        </div>
                    <?php
					
					}
				
				?>
                
            	<div title="<?php echo __('Finalize', product_designer_textdomain); ?>" class="finalize item tooltip" ><span class="icon"><i class="fa fa-paper-plane-o" ></i></span>
                
                </div>   
                
            	<div title="<?php echo __('Loading', product_designer_textdomain); ?>" class="loading item tooltip" ><span class="icon"><i class="fa fa-cog fa-spin" ></i></span>
                
                </div>                  
                         
                         
                         
                         
                        
                               
            </div>
            
            <div class="editing">
                <div class="edit-text">
					
                    <input class="tooltip" title="<?php echo __('Text Content', product_designer_textdomain); ?>" size="3" id="text-content" type="text" value="" />                    
                    <input class="tooltip" title="<?php echo __('Fonts', product_designer_textdomain); ?>" size="3" id="font-size" type="number" value="16" />
                                       
                    <input  title="Color" id="font-color" class="color tooltip tool-button" placeholder="<?php echo __('Color', product_designer_textdomain); ?>"  type="text" value="#fff" />
                    
                        <?php
                        
						$Tdesigner_google_fonts = Tdesigner_google_fonts();
						
						
						//var_dump($Tdesigner_google_fonts);
						?> 
                    
                    
                    <select class="tooltip " title="<?php echo __('Font family', product_designer_textdomain); ?>" id="font-family">

						<?php
                        
						foreach($Tdesigner_google_fonts as $font){
							
							$name = $font['name'];
							$name_id = str_replace(' ','+',$name);
							
							
							?>
                            <option style="font-family:<?php echo $name_id; ?>" value="<?php echo $name; ?>"><?php echo $name; ?></option>  
                            <?php
							}
						
						?>
                    </select>
                    
                    
					<input  class="tooltip tool-button" title="Opacity" id="font-opacity" type="range" min="0" max="1" step="0.1" value="1" />

                    <span class="tooltip tool-button" title="<?php echo __('Bold text', product_designer_textdomain); ?>" id="text-bold"><i class="fa fa-bold" ></i></span>
                    <span class="tooltip tool-button" title="<?php echo __('Italic text', product_designer_textdomain); ?>" id="text-italic"><i class="fa fa-italic" ></i></span>                    
                    <span class="tooltip tool-button" title="<?php echo __('Underline text', product_designer_textdomain); ?>" id="text-underline"><i class="fa fa-underline" ></i></span> 
                    <span class="tooltip tool-button" title="<?php echo __('Strikethrough text', product_designer_textdomain); ?>" id="text-strikethrough"><i class="fa fa-strikethrough" ></i></span>                                        
                	
                    <span class="item tooltip" title="<?php echo __('Rotate left', product_designer_textdomain); ?>"><i class="fa fa-undo" ></i>
                    <input class="tool-button" id="text-rot-left" type="range" min="0" max="360" step="1" value="0" />
                    </span>    
                    
                    <span class="item tooltip " title="<?php echo __('Rotate right', product_designer_textdomain); ?>"><i class="fa fa-repeat" ></i>
                    <input class="tool-button" id="text-rot-right" type="range" min="0" max="360" step="1" value="0" />
                    </span>                      

                                 
<!-- 

                    <span class="tooltip" title="Align left" id="text-align-left"><i class="fa fa-align-left" ></i></span>                                  
                    <span class="tooltip" title="Align center" id="text-align-center"><i class="fa fa-align-center" ></i></span>                                  
                    <span class="tooltip" title="Akign right" id="text-align-right"><i class="fa fa-align-right" ></i></span> 

-->                                 
                    
                    <span class="tooltip tool-button" title="<?php echo __('Flip vertical', product_designer_textdomain); ?>" id="text-flip-v"><i class="cpd-icon-flip-vertical" ></i></span>                    
                    <span class="tooltip tool-button" title="<?php echo __('Flip horizontal', product_designer_textdomain); ?>" id="text-flip-h"><i class="cpd-icon-flip-horizontal" ></i></span>                                                     
                    <span class="tooltip tool-button" title="<?php echo __('Lock X movement', product_designer_textdomain); ?>" id="text-lockMovementX"><i class="fa fa-arrows-v" aria-hidden="true"></i></span>                    
                    <span class="tooltip tool-button" title="<?php echo __('Lock Y movement', product_designer_textdomain); ?>" id="text-lockMovementY"><i class="fa fa-arrows-h" aria-hidden="true"></i></span>                     
                    <span class="tooltip tool-button" title="<?php echo __('Lock rotation', product_designer_textdomain); ?>" id="text-lockRotation"><i class="fa fa-undo" aria-hidden="true"></i></span>                    
                    <span class="tooltip tool-button" title="<?php echo __('Lock X Scaling', product_designer_textdomain); ?>" id="text-lockScalingX"><i class="fa fa-expand" aria-hidden="true"></i></span> 
                    <span class="tooltip tool-button" title="<?php echo __('Lock Y Scaling', product_designer_textdomain); ?>" id="text-lockScalingY"><i class="fa fa-expand" aria-hidden="true"></i></span>                                       
                    
                          
                    <span class="tooltip tool-button" title="<?php echo __('Delete', product_designer_textdomain); ?>" id="text-delete"><i class="fa fa-trash-o" ></i></span>
                </div>
                
                <div class="edit-img">
                
                
                    <span class="tooltip tool-button" title="<?php echo __('Duplicate', product_designer_textdomain); ?>" id="img-clone"><i class="fa fa-clone" ></i></span>                
                    <span class="tooltip tool-button" title="<?php echo __('Center horizontally', product_designer_textdomain); ?>" id="img-center-h"><i class="cpd-icon-align-horizontal-middle"></i></span>
                    <span class="tooltip tool-button" title="<?php echo __('Center vertically', product_designer_textdomain); ?>" id="img-center-v"><i class="cpd-icon-align-vertical-middle"></i></span>
                    
<!-- 

                    <span class="tooltip tool-button" title="Position left" id="img-pos-left"><i class="cpd-icon-align-left"></i></span>                     
                    <span class="tooltip tool-button" title="Position right" id="img-pos-right"><i class="cpd-icon-align-right"></i></span>                                       
                    <span class="tooltip tool-button" title="Position top" id="img-pos-top"><i class="cpd-icon-align-top"></i></span>                                
                    <span class="tooltip tool-button" title="Position bottom" id="img-pos-bottom"><i class="cpd-icon-align-bottom"></i></span>

-->                                                         
                                                       
                                                       
                    <span class="tooltip tool-button" title="<?php echo __('Flip vertical', product_designer_textdomain); ?>" id="img-flip-v"><i class="cpd-icon-flip-vertical" ></i></span>                    
                    <span class="tooltip tool-button" title="<?php echo __('Flip horizontal', product_designer_textdomain); ?>" id="img-flip-h"><i class="cpd-icon-flip-horizontal" ></i></span>                                                       
                
                    <span class="tooltip tool-button" title="<?php echo __('Delete', product_designer_textdomain); ?>" id="img-delete"><i class="fa fa-trash-o" ></i></span>

                                     
                </div>                
                
                <div class="edit-shape">
                
               		<input  title="<?php echo __('Color', product_designer_textdomain); ?>" id="shape-color" class="color tooltip tool-button" placeholder="<?php echo __('Color', product_designer_textdomain); ?>"  type="text" value="#fff" />
                    <span class="tooltip tool-button" title="<?php echo __('Duplicate', product_designer_textdomain); ?>" id="shape-clone"><i class="fa fa-clone" ></i></span>                
                                                       
                
                    <span class="tooltip tool-button" title="<?php echo __('Delete', product_designer_textdomain); ?>" id="shape-delete"><i class="fa fa-trash-o" ></i></span>

                                     
                </div>                 
                
                
                
                
                
                
                
                
                
                
            
            </div>
            
            
            
            
            <div id="designer" class="designer">
                <canvas id="c" width="500" height="600"></canvas>  
            </div>

            <div id="final" class="final">
  
  				<div class="product-order">
                	<p><?php echo get_the_title($product_id); ?></p>
                    
                    <?php
					
					
					$cook_data = $_COOKIE['side_customized'];
					
					//$cook_data = (stripslashes($cook_data));	
					$cook_data = unserialize(stripslashes($cook_data));	
					//var_dump($cook_data);				
					if(!empty($cook_data[$product_id])){
						$prduct_cook_data = $cook_data[$product_id];
						}	
					
					
					
					
					
						
						$post_type = get_post_type($product_id);
						if($post_type=='product'){
							

							
							global $woocommerce;
							global $product;
							$product = new WC_Product( $product_id );
							
							$_product = wc_get_product( $product_id );
							
							$price = $_product->get_price_html();
							echo 'Price: '.$product->get_price_html();
								
								
								
							
							echo '<form class="cart" enctype="multipart/form-data" method="post" action="'.$product_designer_page_url.'?product_id='.$product_id.'&final">
							<input class="input-text qty text" type="number" size="4" title="Qty" value="1" name="quantity" min="1" step="1">
							<input type="hidden" value="'.$product_id.'" name="add-to-cart">
							<input type="hidden" value='.serialize($prduct_cook_data).' name="tdesigner_custom_design" size="3">
		
							<input type="hidden" value="cart" name="custom_design_cart" size="3">';
							
							
							
							if ( $_product->is_type( 'variable' ) ) {
						
/*

								$attributes = $_product->get_attributes();
						
								//echo '<pre>'.var_export($attributes, true).'</pre>';	

								
								foreach ( $attributes as $attribute ) {
						
									$attribute_name = $attribute['name'];
									
									$terms = wc_get_product_terms( $_product->id, $attribute_name, array( 'fields' => 'all' ) );
									
									echo '<p class="variation">';
									echo $attribute_name;
									
									echo '<select name="">';
									foreach($terms as $term){
										
										$term_id = $term->term_id;
										$name = $term->name;										
										
										echo '<option value="'.$term_id.'">'.$name.'</option>';
										
										}
									echo '</select>';
									echo '</p>';
									//echo '<pre>'.var_dump($terms, true).'</pre>';
									
									
								}

*/
								

							}	
							
							
							
							
							
							
							
							
							echo '<button class="single_add_to_cart_button button alt" type="submit">'.__('Add to cart', product_designer_textdomain).'</button>';

							echo '</form>'; 
							
							
							
							if(isset($_POST['custom_design_cart']) )
								{
									echo '<a href="'.$woocommerce->cart->get_cart_url().'"><strong>'.__('View Cart', product_designer_textdomain).'</strong></a>';
									
									echo '<style type="text/css">';
									
									echo '#designer, .menu{display:none}';
									echo '#final{display:block}';							
									
									echo '</style>';
									
								}

								//var_dump($product);
								

								







						}
						
						
						
						
						
						
						
						
					elseif($post_type=='download'){
						
						$edd_price = edd_price($product_id,false);
						echo '<p>Price: '.$edd_price.'</p>';
						
						echo do_shortcode('[purchase_link id="'.$product_id.'" text="'.__('Add to Cart', product_designer_textdomain).'" style="button"]');
						
						}
					else{
							if(!empty($_POST)){
								
								
								$response = product_designer_create_order( $_POST );
								
								if(!empty($response)){
									
									echo __('Order submitted', product_designer_textdomain);
									
									}
									
								echo '<style type="text/css">';
								
								echo '#designer, .menu{display:none}';
								echo '#final{display:block}';							
								
								echo '</style>';
									
									
									
								}
							else{
								
								echo '<form class="cart" enctype="multipart/form-data" method="post" action="'.$product_designer_page_url.'?product_id='.$product_id.'&final">
								<input class="input-text qty text" type="number" size="4" title="Qty" value="1" name="quantity" min="1" step="1">
								<input type="hidden" value='.$product_id.' name="product_id" size="3"><br/>								
								<input type="hidden" value='.serialize($prduct_cook_data).' name="tdesigner_custom_design" size="3"><br/>
								
								Name:<br/>
								<input type="text" value="" name="customer_name"><br/>							
								
								Address:<br/>
								<textarea name="address"></textarea><br/>
								<input type="submit" value="Submit" />
								</form>';
								
								
								}
						
						
						
						
 
						
						}








                    ?>
   
                    
                </div>
  
  
  
                <ul class="final-list">
                
                <?php 
                
                if(!empty($_COOKIE['side_customized'])){
                    $cook_data = $_COOKIE['side_customized'];
                    }
                else{
                    $cook_data = '';
                    }
                
                //var_dump(stripslashes($cook_data));
                //var_dump(unserialize($cook_data));	
                $cook_data = unserialize(stripslashes($cook_data));	
                //var_dump($cook_data);				
                if(!empty($cook_data[$product_id])){
                    $prduct_cook_data = $cook_data[$product_id];
                    }
                else{
                    $prduct_cook_data = array();
                    }
                
                if(!empty($side_data)){
                    
                    foreach($side_data as $id=>$side){
                        
                        $name = $side['name'];
                        $src = $side['src'];	
                        
                        if($current_side==$id){
                            $active = 'active';
                            
                            }
                        else{
                            $active = '';
                            }
                        
                        
                        if(!empty($src)){
                            echo '<li>';
                            echo '<a title="'.__('Original design.', product_designer_textdomain).'" class="tooltip '.$active.'" href="'.$page_url.'?product_id='.$product_id.'&side='.$id.'">'.$name.'<img src="'.$src.'" /></a>';
                            echo '<i class="fa fa-hand-o-right" ></i>';
                            
                            if(!empty($prduct_cook_data[$id])){
                                $attach_id = $prduct_cook_data[$id];
                                //var_dump($customized_data);
                                $attach_url = wp_get_attachment_url( $attach_id );
                                echo ' <a class="tooltip" title="'.__('Your design.', product_designer_textdomain).'" href="#">Your design<img src="'.$attach_url.'" /></a>';
                                }
                            else{
                                    echo ' <a class="tooltip" title="'.__('Empty', product_designer_textdomain).'" href="#">&nbsp;<img src="'.product_designer_plugin_url.'assets/front/images/placeholder.png" /></a>';
                                }
                            
                            
                            
                            
                            echo '</li>';
                            }
                        
                        
                        }

                    }
                else{
                    
                    echo '<span>'.__('Not avilable.', product_designer_textdomain).'</span>';
                    
                    }
                    
                    

                
                ?>
                
                </ul>

 
                
                
                
                
                
                
                
                
            </div>
        
        </div>
        
         <style>
			<?php
			foreach($Tdesigner_google_fonts as $font){
				
								
				$Fontname = $font['name'];
				$name = str_replace(' ','+',$Fontname);
				
				if(!empty($font['src'])){
					$src = $font['src'];
					?>
						@font-face {
										font-family: <?php echo $Fontname; ?>;
										src: url("<?php echo $src; ?>");
									}
				
					<?php					
					
					
					}
				else{
					?>
						@import url('https://fonts.googleapis.com/css?family=<?php echo $name; ?>');
				
					<?php
					}
				

				}
			
			?>
		</style>
        
        
        
        
        
        
        
        
            <div class="preview" id="preview">
            	
            </div>
            
        
                <?php 
                
				if(!empty($side_data))
                foreach($side_data as $id=>$side){
                    $src = $side['src'];	
                    
                    if($current_side==$id){
						
						?>
						<style type="text/css">
						.canvas-container{
							background:rgba(0, 0, 0, 0) url("<?php echo $src; ?>") no-repeat scroll 0 0; }
						</style>						
						
						<?php

                        }
    
                    }
               // var_dump($product_id);
                ?>
      
<script>
var canvas = new fabric.Canvas('c');


var current_side = <?php echo $current_side; ?>;
var product_id = <?php echo $product_id; ?>;








                <?php 
                
				
				
                foreach($side_data as $id=>$side){
                    $src = $side['src'];	
                    
                    if($current_side==$id){
						
						?>
						


						var newImg = new Image();
						newImg.src = '<?php echo $src; ?>';
						var height = newImg.height;
						var width = newImg.width;
						
						fabric.Image.fromURL('<?php echo $src; ?>', function(img){
							img.setWidth(width);
							img.setHeight(height);
							canvas.add(img);
						});


						
						
						<?php

                        }
    
                    }
                
                ?>

</script>
   
        
        <?php

		
		}

	}
	
	
	
	
	
	
	

