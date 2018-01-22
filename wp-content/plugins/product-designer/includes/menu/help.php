<?php	


if ( ! defined('ABSPATH')) exit; // if direct access 


    $product_designer_customer_type = get_option('product_designer_customer_type');
    $product_designer_version = get_option('product_designer_version');

?>

<div class="wrap">
	<?php echo "<h2>".sprintf(__('%s Help', product_designer_textdomain), product_designer_plugin_name)."</h2>";
	

	
	//var_dump($product_designer_posttype);
	?>
    <br />



    <div class="para-settings product-designer-admin">
        <ul class="tab-nav"> 
            <li nav="1" class="nav1 active"><?php echo __('Help', product_designer_textdomain); ?></li>

                       
            
        </ul> <!-- tab-nav end -->  
        
		<ul class="box">
            <li style="display: block;" class="box1 tab-box active">
            
				<div class="option-box">
                    <p class="option-title"><?php echo __('Need Help ?', product_designer_textdomain); ?></p>
                    <p class="option-info">Feel free to contact with any issue for this plugin, Ask any question via forum <a href="<?php echo product_designer_qa_url; ?>"><?php echo product_designer_qa_url; ?></a> <strong style="color:#139b50;">(free)</strong><br />

    <?php

    if(product_designer_customer_type=="free")
        {
    
            echo 'You are using <strong> '.product_designer_customer_type.' version  '.$product_designer_version.'</strong> of <strong>'.product_designer_plugin_name.'</strong>, To get more feature you could try our premium version. ';
            
            echo '<br /><a href="'.product_designer_pro_url.'">'.product_designer_pro_url.'</a>';
            
        }
    else
        {
    
            echo 'Thanks for using <strong> premium version  '.$product_designer_version.'</strong> of <strong>'.product_designer_plugin_name.'</strong> ';	
            
            
        }
    
     ?>       

                    
                    </p>

                </div>
            
  
   		<div class="option-box">
            <p class="option-title"><?php _e('Watch video tutorial',product_designer_textdomain); ?></p>
            <p class="option-info"></p>
            
            <div class="tutorials expandable">
            <?php
            $class_product_designer_functions = new class_product_designer_functions();
			$tutorials =  $class_product_designer_functions->tutorials();
			
			foreach($tutorials as $tutorial){
				
				echo '<div class="items">';
				echo '<div class="header "><i class="fa fa-play"></i>&nbsp;&nbsp;'.$tutorial['title'].'</div>';
				echo '<div class="options"><iframe width="640" height="480" src="//www.youtube.com/embed/'.$tutorial['video_id'].'" frameborder="0" allowfullscreen></iframe></div>';				
				
				echo '</div>';				
				
				}
			
			?>

            </div>

        </div>
  
  
   		<div class="option-box">
            <p class="option-title"><?php _e('FAQ', product_designer_textdomain); ?></p>
            <p class="option-info"></p>
            
            <div class="faq">
            <?php
            $class_product_designer_functions = new class_product_designer_functions();
			$faq =  $class_product_designer_functions->faq();
			
			echo '<ul>';
			foreach($faq as $faq_data){
				echo '<li>';
				$title = $faq_data['title'];
				$items = $faq_data['items'];				
				
				echo '<span class="group-title">'.$title.'</span>';
				
					echo '<ul>';
					foreach($items as $item){
						
							echo '<li class="item">';
							echo '<a href="'.$item['answer_url'].'"><i class="fa fa-question-circle-o" aria-hidden="true"></i> '.$item['question'].'</a>';
						
							
							echo '</li>';	

					}		
					echo '</ul>';
			
				echo '</li>';
				}
				
				echo '</ul>';
			?>

            </div>

        </div>
  
  
  
  
                
            
            </li>
			
            
            
                    
        
        
    
    </div>

        
</div> <!-- wrap end -->