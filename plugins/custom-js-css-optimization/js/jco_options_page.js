
jQuery(document).ready(function(){
	jQuery(document).on('change','.in_footer_input',function(){
		//alert("Script location modified");
		var select = jQuery(this);
		var table_cell=select.closest('td') ;                            
		//var table_row=select.closest('tr') ;                            
  	var table_cell_class=table_cell.attr("class"); 
  	console.log('class : ', table_cell_class); 
  	if ( table_cell_class == "modified") {
  		console.log('found modified class');
  		table_cell.removeClass( "modified" );   
  	} 
		else {
  		console.log('modified class not found'); 
  		table_cell.addClass( "modified" );   
		} 		
//  	}
//  	else {
//  	}
//  	var modified = table_row.find('input.modified');
//  	console.log('modified value before update : ', modified.val()); 
// 		modified.val('true');
//  	console.log('modified value after update : ', modified.val()); 
  	
  	//'name': $(this).children('input[name="paramName"]').val(),
    //'value': $(this).children('input[name="paramPrice"]').val()         
               
	});
});