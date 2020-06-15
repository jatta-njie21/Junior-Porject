/*price range*/

 $('#sl2').slider();

	var RGBChange = function() {
	  $('#RGB').css('background', 'rgb('+r.getValue()+','+g.getValue()+','+b.getValue()+')')
	};

/*scroll to top*/

$(document).ready(function(){
	$(function () {
		$.scrollUp({
	        scrollName: 'scrollUp', // Element ID
	        scrollDistance: 300, // Distance from top/bottom before showing element (px)
	        scrollFrom: 'top', // 'top' or 'bottom'
	        scrollSpeed: 300, // Speed back to top (ms)
	        easingType: 'linear', // Scroll to top easing (see http://easings.net/)
	        animation: 'fade', // Fade, slide, none
	        animationSpeed: 200, // Animation in speed (ms)
	        scrollTrigger: false, // Set a custom triggering element. Can be an HTML string or jQuery object
					//scrollTarget: false, // Set a custom target element for scrolling to the top
	        scrollText: '<i class="fa fa-angle-up"></i>', // Text for element, can contain HTML
	        scrollTitle: false, // Set a custom <a> title if required.
	        scrollImg: false, // Set true to use image
	        activeOverlay: false, // Set CSS color to display scrollUp active point, e.g '#00FFFF'
	        zIndex: 2147483647 // Z-Index for the overlay
		});
	});
    $(function () {
        $('input').isCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
});

function loadCategoryNav(id,_token) {
	$.ajax({
		url: '/tab_category/products',
		type: 'GET',
		dataType: 'json',
		data: {id: id},
		success: function (data) {
			console.log(data);
            $("#first").empty();
            $.each(data['res'], function (k,v) {
				var x = '<div class="col-sm-3">\n' +
                    '<div class="product-image-wrapper">\n' +
                    '<div class="single-products">\n' +
                    '<div class="productinfo text-center">\n' +
                    '<a href="/product/'+v.id+'"> <img height="200px" src="'+v.avatar+'" alt="" /></a>\n' +
                    '<h2>D'+v.price+'</h2>\n' +
                    '<p>'+v.name+'</p>\n' +
                    '<a href="#/" onclick="addToCart(\''+v.id+'\',\''+_token+'\')" class="btn btn-default add-to-cart"><i class="fa fa-shopping-cart"></i>Add to cart</a>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '</div>\n';
				$("#first").append(x);
            })
        },
		error: function (err) {
			console.log(err.responseText);
        }
	})

}

function addToCart(id,_token) {
	$.ajax({
		url: '/add-to-cart',
		type: 'POST',
		dataType: 'json',
		data: {id: id},
        headers: {
            'X-CSRF-Token': _token
        },
		success: function (data) {
			console.log(data['res']);
			if (data['res'] == 'found_product')
				$.notify("Product is already in your cart", "info");
			else {
                $.notify("Product is added to your cart", "success");
                $(".cartItemCount").empty().append(data['item_count']);
			}
    },
		error: function (err) {
			console.log(err.responseText);
      $.notify("Something went wrong, please send us an email.", "error");
    }
	})
}

function addQuantity(id,_token) {
	var new_val = $("#quantityOf"+id).val();
	new_val++;
	$.ajax({
		url: '/cart/update-cart',
		type: 'POST',
		dataType: 'json',
        headers: {
            'X-CSRF-Token': _token
        },
		data: {new_val: new_val, id: id, kind: 'inc'},
		success: function (data) {
			console.log(data);
            $("#quantityOf"+id).val(new_val);
        },
		error: function (err) {
			console.log(err.responseText);
            $("#quantityOf"+id).val($("#quantityOf"+id).val());
        }
	});
}

function subtractQuantity(id,_token) {
    var new_val = $("#quantityOf"+id).val();
    new_val--;
    if (new_val <= 0)
    	$("#quantityOf"+id).val(new_val);
    else {
        $.ajax({
            url: '/cart/update-cart',
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-Token': _token
            },
            data: {new_val: new_val, id: id, kind: 'dec'},
            success: function (data) {
                console.log(data);
                $("#quantityOf"+id).val(new_val);
            },
            error: function (err) {
                console.log(err.responseText);
            }
        });
	}
}

function billing() {
	$("#checkOutCartPane").css({"display":"none"});
	$("#checkOutCartOptionPane").css({"display":"none"});
	$(".shopper-informations").css({"display":"block"});
}

function checkOut(cart_id,total_price,_token) {

	var company = $("#company_name").val();
	var email = $("#email").val();
	var name = $("#name").val();
	var address = $("#address").val();
	var zip_code = $("#zip_code").val();
	var phone = $("#phone").val();
	var mobile_phone = $("#mobile_phone").val();
	var fax = $("#fax").val();
	var message = $("#message").val();
	var payment_method = $(".paymentMethod:checked").val();
	  var shipping_to_billing = $(".shipping_to_billing:checked").val();
	  var shipping_to_billing_value = '';
	  if (shipping_to_billing == 'checked')
		shipping_to_billing_value = '1';
	  else
		shipping_to_billing_value = '0';

	  if (email == '' || name == '' || address == '' || phone == '' ||
	   mobile_phone == '' || message == '' || payment_method == '') {
		  $("#billingStatus").css({"display":"block"});
	  }
	  else {
	  //	alert(cart_id);
		console.log(_token);
		console.log(total_price);
		console.log(company);
		total_price = parseFloat(Math.round(total_price * 100) / 100).toFixed(2);
	  //	alert(total_price);
		$.ajax({
			url: '/process_order',
			type: 'POST',
			dataType: 'json',
			data: {
				cart_id: cart_id,
				total_price: total_price,
				company: company,
				email: email,
				name: name,
				address: address,
				zip_code: zip_code,
				phone: phone,
				mobile_phone: mobile_phone,
				fax: fax,
				message: message,
				payment_method: payment_method,
				shipping_to_billing: shipping_to_billing_value
			},
		  headers: {
			  'X-CSRF-Token': _token
		  },
			success : function (data) {
			//	alert(data['res']);
				console.log(data);
			$.notify("Thank you, your order has been processed. We will email you with details of your order", "success");
			window.location.href = ""+data['res']+"";
		  },
			error: function (err) {
			 //   alert(err.responseText);
				console.log(err.responseText);
			$.notify("Something went wrong your order could not be processed. Please send us an email at info@suncreekonline.com", "error");
		  }
		})
	  }

}


// function rentNow(car_id, _token) {
//   $.ajax({
// 		url: '/rent-car',
// 		type: 'POST',
// 		dataType: 'json',
// 		data: {id: car_id},
//         headers: {
//             'X-CSRF-Token': _token
//         },
// 		success: function (data) {
// 			console.log(data['res']);
//       if (data['res'] == 'not-logged-in')
//         window.location.href = '/login';
//     },
// 		error: function (err) {
// 			console.log(err.responseText);
//       $.notify("Something went wrong, please send us an email.", "error");
//     }
// 	})
// }
