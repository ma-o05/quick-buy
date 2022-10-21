/*------------------------ 
Backend related javascript
------------------------*/

(function( $ ) {
	'use strict';
	$('.qb-variation-id select').change(function() {
		var qb_variations = $('.qb-variation-id').serializeArray();
		var last = qb_variations.length - 1;
		$.ajax({
			url: 'https://localhost/wordpress-plugins/wp-admin/admin-ajax.php',
			type: 'POST',
			data: {
				action: 'qb_get_variation_id',
				qb_variations: qb_variations
			},
		})
		.error(function(response) {
			console.log(response);
		})
		.success(function(response) {
			$('#qb-add-'+qb_variations[last].value+' input[name=variation-id]').val(response);
			if (response == 0) {
				$('#qb-add-'+qb_variations[last].value+' .single_add_to_cart_button').attr('disabled','disabled');
			}else{
				$('#qb-add-'+qb_variations[last].value+' .single_add_to_cart_button').removeAttr('disabled');
			}
		})
	});

	$('.qb-add-to-cart .single_add_to_cart_button').on('click', function(event) {
		event.preventDefault();
		var parent = $(this).parent().attr('id');	
		var product_data = $('#'+parent).serializeArray();
		$.ajax({
			url: 'https://localhost/wordpress-plugins/wp-admin/admin-ajax.php',
			type: 'POST',
			data: {
				action: 'qb_custom_add_to_cart',
				product_data: product_data
			},
			beforeSend: function(response) {
				$('#'+parent+' .single_add_to_cart_button').addClass('clicked');
			}
		})
		.error(function(response) {
			console.log(response);
		})
		.success(function(response) {
			$('.qb-float-cart .qb-float-cart-items-count').text(response);
			$('#'+parent+' .single_add_to_cart_button').removeClass('clicked');
		})
	});
})( jQuery );