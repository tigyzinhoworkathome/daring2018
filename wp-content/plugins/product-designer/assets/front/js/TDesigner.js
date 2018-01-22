jQuery(document).ready(function($)
	{


$('.tooltip').tooltipster();







	$(document).on('change','#clipart-cat',function(){
		
		$('.TDesigner .menu .loading').fadeIn();
		
		var cat = $(this).val();
		
		$.ajax(
			{
		type: 'POST',
		url: product_designer_ajax.product_designer_ajaxurl,
		data: {"action": "product_designer_ajax_get_clipart_list","cat":cat},
		success: function(data)
				{
					
					var response 		= JSON.parse(data)
					var clip_list 	= response['clip_list'];	
					var paginatioon 	= response['paginatioon'];	
					
					$('.clipart-list').html(clip_list);
					$('.clipart-pagination').html(paginatioon);		
					$('.TDesigner .menu .loading').fadeOut();			
					
				}
			});
		
	})


	$(document).on('click','.clipart-pagination .page-numbers',function(event){
		
		event.preventDefault();
		cat = $('#clipart-cat').val();
		paged = $(this).text();
		
		$('.TDesigner .menu .loading').fadeIn();
		
		$.ajax(
			{
		type: 'POST',
		url: product_designer_ajax.product_designer_ajaxurl,
		data: {"action": "product_designer_ajax_paged_clipart_list","paged":paged,"cat":cat},
		success: function(data)
				{
					
					var response 		= JSON.parse(data)
					var clip_list 	= response['clip_list'];	
					var paginatioon 	= response['paginatioon'];	
					
					$('.clipart-list').html(clip_list);
					$('.clipart-pagination').html(paginatioon);					
					$('.TDesigner .menu .loading').fadeOut();
				}
			});
		
		})














function onObjectSelected(e) {
	
	type = e.target.get('type')
	
	$('.edit-text').removeClass('active');
	$('.edit-img').removeClass('active');
	$('.edit-shape').removeClass('active');
	
	
	if(type=='text'){
		
		$('.edit-text').addClass('active');
		
		val = canvas.getActiveObject().getText();
		$('.TDesigner #text-content').val(val);
		
		
		
		}
	else if(type=='image'){
		$('.edit-img').addClass('active');
		}
		
	
		
	else if(type=='circle' || type=='triangle' || type=='rect'){
		$('.edit-shape').addClass('active');
		}		
		
  console.log(e.target.get('type'));
}

canvas.on('object:selected', onObjectSelected);
	



	$(document).on('keyup','.TDesigner #text-content',function(){


		

		val = $(this).val();
		
		//alert(val);
		canvas.getActiveObject().setText(val);
		canvas.renderAll(); 
		console.log(val);
		
		})


	$(document).on('change','.TDesigner #font-size',function(){

		val = $(this).val();
		
		//alert(val);
		canvas.getActiveObject().set("fontSize", val);
		canvas.renderAll(); 
		console.log(font_size);
		
		})


	$(document).on('change','.TDesigner #font-color',function(){
		
		val = $(this).val();
		
		//alert('Hello');
		canvas.getActiveObject().setColor('#'+val);
		canvas.renderAll(); 
		console.log(val);
		
		})

	$(document).on('change','.TDesigner #font-family',function(){
		
		val = $(this).val();
		
		//alert('Hello');
		canvas.getActiveObject().setFontFamily(val);
		canvas.renderAll(); 
		console.log(val);
		
		})


	$(document).on('change','.TDesigner #font-opacity',function(){
		
		val = $(this).val();
		
		//alert('Hello');
		canvas.getActiveObject().set("opacity", val);
		//canvas.getActiveObject().opacity(val);
		canvas.renderAll(); 
		console.log(val);
		
		})







	$(document).on('click','.TDesigner #text-bold',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("fontWeight", 'normal');
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("fontWeight", 'bold');
		 }

		canvas.renderAll(); 
		
		})

	$(document).on('click','.TDesigner #text-italic',function(){
		
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("fontStyle", 'normal');
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("fontStyle", 'italic');
		 }

		//canvas.getActiveObject().set("fontStyle", 'italic');
		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('click','.TDesigner #text-underline',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("textDecoration", 'normal');
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("textDecoration", 'underline');
		 }
		
		
		//canvas.getActiveObject().fontWeight('bold');
		//canvas.getActiveObject().set("textDecoration", 'underline');
		canvas.renderAll(); 
		//console.log(val);
		
		})

	$(document).on('click','.TDesigner #text-strikethrough',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("textDecoration", 'normal');
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("textDecoration", 'line-through');
		 }
		
		
		//canvas.getActiveObject().fontWeight('bold');
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})



	$(document).on('change','.TDesigner #text-rot-left',function(){
		
		
		val = $(this).val();
		
		
		canvas.getActiveObject().setAngle(-val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('change','.TDesigner #text-rot-right',function(){
		
		
		val = $(this).val();
		
		
		canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})



	$(document).on('click','.TDesigner #text-flip-v',function(){
		
		var $this = $(this);
		

		 
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
			canvas.getActiveObject().set("flipY", false);
		   
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   
		   canvas.getActiveObject().set("flipY", true);
		   
		 } 
		 
		 
		 
		 
		 
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('click','.TDesigner #text-flip-h',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   //canvas.getActiveObject().flipX(true);
		   canvas.getActiveObject().set("flipX", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   
		   
		   canvas.getActiveObject().set("flipX", true);
		 }
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('click','.TDesigner #text-lockMovementX',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("lockMovementX", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("lockMovementX", true);
		 }
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})

	$(document).on('click','.TDesigner #text-lockMovementY',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("lockMovementY", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("lockMovementY", true);
		 }
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})






	$(document).on('click','.TDesigner #text-lockRotation',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("lockRotation", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("lockRotation", true);
		 }

		canvas.renderAll(); 

		
		})




	$(document).on('click','.TDesigner #text-lockScalingX',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("lockScalingX", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("lockScalingX", true);
		 }
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})



	$(document).on('click','.TDesigner #text-lockScalingY',function(){
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive')
		   canvas.getActiveObject().set("lockScalingY", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("lockScalingY", true);
		 }

		canvas.renderAll(); 

		
		})






	$(document).on('click','.TDesigner #text-delete',function(){

		canvas.getActiveObject().remove();
		canvas.renderAll(); 
		//console.log(val);
		
		})





	$(document).on('click','.TDesigner #img-clone',function(){
		
		//alert('Hello');
		var selected_object = canvas.getActiveObject();
        var new_object = fabric.util.object.clone(selected_object);
        new_object.set("top", new_object.top + 10);
        new_object.set("left", new_object.left + 10);
       // wpd_editor.setCustomProperties(new_object);
        canvas.add(new_object);

		//canvas.getActiveObject().clone();
		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('click','.TDesigner #img-delete',function(){

		canvas.getActiveObject().remove();
		canvas.renderAll(); 
		//console.log(val);
		
		})
		
		
		
	$(document).on('click','.TDesigner #img-center-h',function(){
		
		canvas.getActiveObject().centerH();
		canvas.renderAll(); 
		//console.log(val);
		
		})
				
		
	$(document).on('click','.TDesigner #img-center-v',function(){
		
		canvas.getActiveObject().centerV();
		canvas.renderAll(); 
		//console.log(val);
		
		})		
		
		
		
	$(document).on('click','.TDesigner #img-pos-left',function(){
		
		
 		canvas.getActiveObject().set("left", '0');
		canvas.renderAll(); 
		//console.log(val);
		
		})		
				
		
	$(document).on('click','.TDesigner #img-pos-right',function(){
		
		
		canvas.getActiveObject().set("right", '0');
		canvas.renderAll(); 
		//console.log(val);
		
		})			
		
	$(document).on('click','.TDesigner #img-pos-top',function(){
		
		
		
		canvas.getActiveObject().set("top", '0');
		canvas.renderAll(); 
		//console.log(val);
		
		})			
		
		
	$(document).on('click','.TDesigner #img-pos-bottom',function(){
		
		

		canvas.getActiveObject().set("bottom", '0');
		canvas.renderAll(); 
		//console.log(val);
		
		})			
		
		
	$(document).on('click','.TDesigner #img-flip-v',function(){
		
/*

			var selected_object = canvas.getActiveObject();
			if (selected_object.get("flipY") == true){
				selected_object.set("flipY", false);
				//alert('Hello');
				}
                
            else{
				selected_object.set("flipY", true);
				}

*/
                
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive');
		   //canvas.getActiveObject().flipX(true);
		   canvas.getActiveObject().set("flipY", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("flipY", true);
		 }
		canvas.renderAll(); 
		
		
		})		
		
		
	$(document).on('click','.TDesigner #img-flip-h',function(){
		
		

		
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   $this.removeClass('active').addClass('inactive');
		   //canvas.getActiveObject().flipX(true);
		   canvas.getActiveObject().set("flipX", false);
		 }else{
		   $this.removeClass('inactive').addClass('active');
		   canvas.getActiveObject().set("flipX", true);
		 }
		//val = $(this).val();
		
		
		//canvas.getActiveObject().setAngle(val);
		//canvas.getActiveObject().set("textDecoration", 'line-through');
		canvas.renderAll(); 
		//console.log(val);
		
		})			
		
		
	$(document).on('change','.TDesigner #shape-color',function(){
		
		val = $(this).val();
		
		//alert('Hello');
		canvas.getActiveObject().setColor('#'+val);
		canvas.renderAll(); 
		console.log(val);
		
		})
		
		
	$(document).on('click','.TDesigner #shape-delete',function(){

		canvas.getActiveObject().remove();
		canvas.renderAll(); 
		//console.log(val);
		
		})
		
		
	$(document).on('click','.TDesigner #shape-clone',function(){
		
		//alert('Hello');
		var selected_object = canvas.getActiveObject();
        var new_object = fabric.util.object.clone(selected_object);
        new_object.set("top", new_object.top + 10);
        new_object.set("left", new_object.left + 10);
       // wpd_editor.setCustomProperties(new_object);
        canvas.add(new_object);

		//canvas.getActiveObject().clone();
		canvas.renderAll(); 
		//console.log(val);
		
		})	
		
		
		
		
	$(document).on('click','.TDesigner #shapes-rectangle',function(){
		
		var rectangle = new fabric.Rect({
		  width: 50, height: 50, fill: 'blue', left: 50, top: 50
		});

		canvas.add(rectangle);	

		canvas.renderAll(); 
		//console.log(val);
		
		})

		
	$(document).on('click','.TDesigner #shapes-circle',function(){
		
		//alert('Hello');
		circle = new fabric.Circle({ radius: 30, fill: '#f55', top: 100, left: 100 })

		canvas.add(circle);	

		canvas.renderAll(); 
		//console.log(val);
		
		})


	$(document).on('click','.TDesigner #shapes-triangle',function(){
		
		var triangle = new fabric.Triangle({
		  width: 50, height: 60, fill: 'blue', left: 50, top: 50
		});

		canvas.add(triangle);	

		canvas.renderAll(); 
		//console.log(val);
		
		})











	$(document).on('click','.TDesigner .menu .add-text',function(){
		
		$('.TDesigner .menu .loading').fadeIn();
		text = $('.input-text').val();
		

		
		var text = new fabric.Text(text, { left: 100, top: 100 });
		canvas.add(text);
		
		//console.log(JSON.stringify(canvas));
		$('.TDesigner .menu .loading').fadeOut();
		})


	jQuery(document).on('click','.clipart-list img',function(){
		
		//$(this).parent().remove();
		$('.TDesigner .menu .loading').fadeIn();
		src = jQuery(this).attr('src');
		
		//alert(src);
			var newImg = new Image();
			newImg.src = src;
			var height = newImg.height;
			var width = newImg.width;	
		
		
		
		
		
		fabric.Image.fromURL(src, function(img){
			img.setWidth(width);
			img.setHeight(height);
			canvas.add(img);
		});
		
		
		$('.TDesigner .menu .loading').fadeOut();
		
		
		
		})





	$(document).on('click','.TDesigner .menu .save',function(){
		
		$('.TDesigner .menu .loading').fadeIn();
		
		canvas.renderAll();
		var convertToImage=function(){
		canvas.deactivateAll().renderAll();  
 
		  
		  base_64 = canvas.toDataURL('png');
		 
		  
			$.ajax(
				{
			type: 'POST',
			url: product_designer_ajax.product_designer_ajaxurl,
			data: {"action": "product_designer_ajax_base64_uplaod","current_side":current_side,"product_id":product_id,"base_64":base_64},
			success: function(data)
					{
						$('.TDesigner .menu .loading').fadeOut();
						//var response 		= JSON.parse(data)
						//var attach_id 	= response['attach_id'];
						
						
						
						
						//alert(attach_id);
						//console.log(da_ta);
						//
						//var response 		= JSON.parse(data)
						//var attach_id 	= response['attach_id'];	
						//var attachment_url 	= response['attachment_url'];
						//html = '<img src="'+attachment_url+'" />';
						//$('#preview').html(html);
						//console.log(response);
						//$(".sticker-list").append(data);
						//$('.sticker-cat-loading').fadeOut();

					}
				}); 

		}
		convertToImage();
		
		
		

		})


	$(document).on('click','.TDesigner .menu .finalize',function(){
		
		$('#designer').fadeOut();
		$('.menu').fadeOut();
		$('.editing').fadeOut();		
				
		$('#final').fadeIn();		
		
		
		})

	$(document).on('click','.TDesigner .menu .export #export-new',function(){
			
		$('.TDesigner .menu .loading').fadeIn();
		json = JSON.stringify(canvas);
  
		$.ajax(
			{
		type: 'POST',
		url: product_designer_ajax.product_designer_ajaxurl,
		data: {"action": "product_designer_ajax_save_template","current_side":current_side,"product_id":product_id,"json":json},
		success: function(data)
				{
					$('.TDesigner .menu .loading').fadeOut();
				}
			}); 
	
			
		})
		
		
	$(document).on('click','.TDesigner .menu .export #export-update',function(){
			
		console.log(t_id);
		
		$('.TDesigner .menu .loading').fadeIn();
		json = JSON.stringify(canvas);
  
		$.ajax(
			{
		type: 'POST',
		url: product_designer_ajax.product_designer_ajaxurl,
		data: {"action": "product_designer_ajax_update_template","current_side":current_side,"product_id":product_id,"json":json,"t_id":t_id},
		success: function(data)
				{
					$('.TDesigner .menu .loading').fadeOut();
				}
			}); 
	
			
		})		
		
		
		
		
		
		
		
		
		
		
	$(document).on('click','.templates  .template-list .template',function(){
		
		
		$('.templates  .template-list .template').removeClass('active');
		
		var $this = $(this);
		
		 if($this.hasClass('active')){
		   //$this.removeClass('active').addClass('inactive');
		 }else{
		   $this.removeClass('inactive').addClass('active');
		 }
		
		side_id = $(this).attr('side_id');		
		t_id = $(this).attr('t_id');
		
		$('.TDesigner .menu .loading').fadeIn();
		
		$.ajax(
			{
		type: 'POST',
		url: product_designer_ajax.product_designer_ajaxurl,
		data: {"action": "product_designer_ajax_load_template","side_id":side_id,"product_id":product_id,"t_id":t_id},
		success: function(data)
				{
					//var canvas = new fabric.Canvas('c');
					canvas.loadFromJSON(data);
					//console.log(data);
					console.log(t_id);
					$('.TDesigner .menu .loading').fadeOut();
				}
			}); 
		
		
		
		
		

		
		
		//alert('Hello');
		
		})	
		
		
		
		
		
		
		

	});	







