<?php





/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function product_designer_add_images() {

	$product_designer_posttype = get_option('product_designer_posttype');
	if(empty($product_designer_posttype)){
		
		$product_designer_posttype = array();
		}

    $screens = $product_designer_posttype;

    foreach ( $screens as $screen ) {

        add_meta_box(
            'product_designer_add_images',
            __( 'Product Designer Options', product_designer_textdomain ),
            'product_designer_inner_custom_box',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'product_designer_add_images' );


function product_designer_inner_custom_box( $post ) {


	wp_nonce_field( 'product_designer_inner_custom_box', 'product_designer_inner_custom_box_nonce' );
	
	$side_data = get_post_meta( $post->ID, 'side_data', true );
	$templates = get_post_meta( $post->ID, 'templates', true );	

  

?>

<style type="text/css">
.tshirt-hint{
	font-size:12px;
	color:#696969;
	margin-top:10px;
	display:block;}
</style>

    <div class="para-settings">
    
    <div class="option-box">
    	<p class="option-title">Item sides</p>
    </div>
    
    
    <div class="product-sides">
        <div class="side-list">

			<?php
            
			//var_dump($side_data);
			
			if(!empty($side_data)){
					
				foreach($side_data as $id=>$side){
					
					$name = $side['name'];
					$src = $side['src'];
					
					?>
					
					<div class="side">
						<span class="remove"><i class="fa fa-times" aria-hidden="true"></i></span>
						<span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span>
						<input placeholder="<?php echo __('Name', product_designer_textdomain); ?>" type="text" name="side_data[<?php echo $id; ?>][name]" value="<?php echo $name; ?>" />
						<input type="text" placeholder="http://" name="side_data[<?php echo $id; ?>][src]" value="<?php echo $src; ?>" />
						<span class="button upload_side" ><i class="fa fa-crosshairs"></i> <?php echo __('Upload', product_designer_textdomain); ?></span>
					</div> 
					
					
					<?php
					
					
					}
				}
			else{

				?>
					<div class="side">
						<span class="remove"><i class="fa fa-times" aria-hidden="true"></i></span>
						<span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span>
						<input placeholder="<?php echo __('Name', product_designer_textdomain); ?>" type="text" name="side_data[<?php echo time(); ?>][name]" value="" />
						<input type="text" placeholder="http://" name="side_data[<?php echo time(); ?>][src]" value="" />
						<span class="button upload_side" ><i class="fa fa-crosshairs"></i> <?php echo __('Upload', product_designer_textdomain); ?></span>
					</div> 
                <?php
				
				}
			
			
			?>
            
            
        </div>
        
        
        <br />
        <input type="button" class="button add_side" value="<?php echo __('Add more', product_designer_textdomain); ?>" />
        
    
    </div>
    
    
		 <script>
         jQuery(document).ready(function($)
            {
				$(function() {
					$( ".side-list" ).sortable({ handle: '.move' });
				//$( ".items-container" ).disableSelection();
				});
				
				
				
					$(document).on('click','.side .remove',function(){
						
						$(this).parent().remove();
						
						})			
				
					$(document).on('click','.add_side',function(){
						
						now = $.now();
						
						html = '<div class="side"><span class="remove"><i class="fa fa-times" aria-hidden="true"></i></span> <span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span> <input placeholder="<?php echo __('Name', product_designer_textdomain); ?>" type="text" name="side_data['+now+'][name]" value="" /> <input type="text" placeholder="http://" name="side_data['+now+'][src]" value="" /> <span class="button upload_side" ><i class="fa fa-crosshairs" aria-hidden="true"></i> <?php echo __('Upload', product_designer_textdomain); ?></span></div>';
						$('.side-list').append(html);
						
						//alert(html);
						
						})
				
				
				var side_uploader;
				
				$(document).on('click','.upload_side',function(e){
		
						this_ = $(this);
						//alert(target_input);
						
						e.preventDefault();
				 
						//If the uploader object has already been created, reopen the dialog
						if (side_uploader) {
							side_uploader.open();
							return;
						}
				 
						//Extend the wp.media object
						side_uploader = wp.media.frames.file_frame = wp.media({
							title: '<?php echo __('Choose Image', product_designer_textdomain); ?>',
							button: {
								text: '<?php echo __('Choose Image', product_designer_textdomain); ?>'
							},
							multiple: false
						});
				 
						//When a file is selected, grab the URL and set it as the text field's value
						side_uploader.on('select', function() {
							attachment = side_uploader.state().get('selection').first().toJSON();
							
							src_url = attachment.url;
							//console.log(attachment);
				
							$(this_).prev().val(src_url);
							//$('input[name=' + target_input + ']').val(attachment.url);
							//jQuery('#product_designer_front_img_preview').attr("src",attachment.url);
						});
						
				 
						//Open the uploader dialog
						side_uploader.open();

					
					})

			})
		</script>
    
    
    <div class="option-box">
    	<p class="option-title"><?php echo __('Templates', product_designer_textdomain); ?></p>
    </div>
    
    <div class="templates">
    
    
        <div class="templates-list">    
        
<?php
			if(!empty($templates)){
				
				foreach($templates as $side_id=>$template_group){
					foreach($template_group as $t_id=>$template){				
					
					
					$name = $template['name'];
					$content = $template['content'];
					
					?>
					
					<div class="template">
						<span class="remove"><i class="fa fa-times" aria-hidden="true"></i></span>
						<span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span>
						<input placeholder="<?php echo __('Name', product_designer_textdomain); ?>" type="text" name="templates[<?php echo $side_id; ?>][<?php echo $t_id; ?>][name]" value="<?php echo $name; ?>" />
						<textarea name="templates[<?php echo $side_id; ?>][<?php echo $t_id; ?>][content]" ><?php echo $content; ?></textarea>
		
					</div>
					
					<?php

					}
				}
			}


		


?>
        
        
 
        </div>   

    </div>
    

	</div>






<?php


}


function product_designer_save_postdata( $post_id ) {



	if ( ! isset( $_POST['product_designer_inner_custom_box_nonce'] ) )
	return $post_id;
	
	$nonce = $_POST['product_designer_inner_custom_box_nonce'];
	
	if ( ! wp_verify_nonce( $nonce, 'product_designer_inner_custom_box' ) )
	  return $post_id;
	
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	  return $post_id;
	
	
	if ( 'page' == $_POST['post_type'] ) {
	
	if ( ! current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	
	} else {
	
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	}
	
	
	if(!empty($_POST['side_data'])){
		$side_data = $_POST['side_data'];
		}
	else{
		$side_data = array();
		}
	
	if(!empty($_POST['templates'])){
		$templates = $_POST['templates'];
		}
	else{
		$templates = array();
		}	
	
	
	$side_data = stripslashes_deep( $side_data );
	$templates = stripslashes_deep( $templates );	

	
	update_post_meta( $post_id, 'side_data', $side_data );
	update_post_meta( $post_id, 'templates', $templates );	
	

   
}
add_action( 'save_post', 'product_designer_save_postdata' );

