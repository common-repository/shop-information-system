// JavaScript Document
var wpsis_date = new Date();
var sales_data;
var year = wpsis_date.getFullYear();


jQuery(document).ready(function($){

	var load_modal = $('#wpsis_load_modal');


	var data_sales_obj = {
		labels : _.keys(get_selected_year_months()),
		datasets : [],
	}

	var data_prices_obj = {
		labels : _.keys(get_selected_year_months()),
		datasets : [],
	}

	var sales_chart = new Chart(document.getElementById("wpsis_sales_chart"), {
		type: 'line',
		data: data_sales_obj,
		options: {
			title: {
				display: true,
				text: 'Product Sales'
			},
			legend: {
				display :false
			}


		}
	});

	$('body').on('click', '.wpsis_in_action ul li button.wpsis-copy', function(){
		
		var copyText = $(this).data('str');
		
		var obj = $(this);
		
		navigator.clipboard.writeText(copyText);
		
		obj.val(obj.data('copied'));
		obj.html(obj.data('copied')).removeClass('btn-warning').addClass('btn-secondary');
		
		
		
		setTimeout(function(){
			obj.val(obj.data('copy'));
			obj.html(obj.data('copy')).removeClass('btn-secondary').addClass('btn-warning');
		}, 5000);

	});




	$('body').on('click', '.wpsis_in_action input.wpsis-monitoring', function(){
		
		var obj = $(this);
		
		var wpsis_records = (obj.prop('checked')?false:confirm(wpsis_object.wpsis_remove_records));

		var data = {

			action : 'wpsis_update_monitoring',
			wpsis_update_option_nonce : wpsis_object.nonce,
			wpsis_monitoring : obj.prop('checked')?'yes':'no',
			wpsis_clear : (wpsis_records?'yes':'no'),
		}

		$.post(ajaxurl, data, function(code, response){
			
		});

	});
	
	$('body').on('click', '.wpsis_in_action input.wpsis-order-browser', function(){
		
		var obj = $(this);
		
		

		var data = {

			action : 'wpsis_record_order_browser',
			wpsis_update_option_nonce : wpsis_object.nonce,
			wpsis_record_order_browser : obj.prop('checked')?'yes':'no',
		}

		$.post(ajaxurl, data, function(code, response){
			
		});

	});
	

	$('body').on('click', '.wpsis_in_action input.wpsis-not-found', function(){
		
		var obj = $(this);
		
		var data = {

			action : 'wpsis_update_not_found',
			wpsis_update_option_nonce : wpsis_object.nonce,
			wpsis_not_found : obj.prop('checked')?'yes':'no',
		}

		$.post(ajaxurl, data, function(code, response){
			
		});

	});
		
	$('body').on('click', '.wpsis_in_action ul li button.wpsis-valid, .wpsis_in_action ul li button.wpsis-invalid', function(){
		
		var li_obj = $(this).closest('li');
		var wpsis_index_val = li_obj.data('index');
		var wpsis_type_val = '';
		

		if($(this).hasClass('wpsis-valid')){
			wpsis_type_val = 'valid';
		}
		if($(this).hasClass('wpsis-invalid')){
			wpsis_type_val = 'invalid';
		}
		
		switch(wpsis_type_val){
			case 'valid':
				$('.wpsis-valid').addClass('btn-secondary').removeClass('btn-success').prop('disabled', true);
				$('.wpsis-invalid').addClass('btn-secondary').removeClass('btn-danger').prop('disabled', true);
				var data = {
	
					action : 'wpsis_update_traffic',
					wpsis_update_option_nonce : wpsis_object.nonce,
					wpsis_index : wpsis_index_val,
					wpsis_type : wpsis_type_val,
	
				}
				li_obj.slideUp();		
				$.post(ajaxurl, data, function(code, response){
					li_obj.remove();
					$('.wpsis-valid').removeClass('btn-secondary').addClass('btn-success').prop('disabled', false);
					$('.wpsis-invalid').removeClass('btn-secondary').addClass('btn-danger').prop('disabled', false);
					$('.traffic-monitoring-tab > span').html('('+$('.wpsis_in_action ul li').length+')');
				});
			break;
			case 'invalid':
				
			break;
		}
		
	});

	var prices_chart = new Chart(document.getElementById("wpsis_price_chart"), {
			type: 'line',
			data: data_prices_obj,
			options: {
				title: {
					display: true,
					text: 'Product Prices'
				},
				legend: {
					display :false
				}
			}
		});


	$('.wpsis-wrapper a.nav-tab').click(function(){


			$(this).siblings().removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$('.nav-tab-content').hide();
			$('.nav-tab-content').eq($(this).index()).show();
			window.history.replaceState('', '', wpsis_object.this_url+'&t='+$(this).index());
			wpsis_object.wpsis_tab = $(this).index();

		});

		$('body').on('click', '.wpsis-reset', function(){
			var reset_confirmation = confirm(wpsis_object.reset_confirm);

			if(!reset_confirmation){
				return false;
			}
		});

		$('input[name^="wpsis_options"], select[name^="wpsis_options"]').on('change', function(){
			//console.log($(this));
			//console.log($(this).parents().eq(1));
			$(this).parents().eq(1).find('ul').toggleClass('d-none');

			var wpsis_option_ajax = $('input[name^="wpsis_options"][value="ajax"]');
			var wpsis_option_ajax_url = $('input[name^="wpsis_options"][value="ajax_url"]');

			var wpsis_option_file_upload = $('input[name^="wpsis_options"][value="file_upload"]');
			var wpsis_option_current_user_files = $('input[name^="wpsis_options"][value="current_user_files"]');
			var wpsis_option_del_from_front = $('input[name^="wpsis_options"][value="del_from_front"]');
			var this_input = $(this);

			if(wpsis_option_file_upload.prop('checked') == false){

				wpsis_option_current_user_files.prop('checked', false);
				wpsis_option_del_from_front.prop('checked', false);

			}


			if(wpsis_option_ajax.prop('checked') == false){

				wpsis_option_ajax_url.prop('checked', false);

			}

			var wpsis_option_checked = $('input[name^="wpsis_options"][type="checkbox"]:checked');
			var wpsis_option_text = $('input[name^="wpsis_options"][type="text"]');
			var wpsis_option_select = $('select[name^="wpsis_options"]');

			var wpsis_options_post = {};

			if(wpsis_object.empty_settings){

				wpsis_options_post['wpsis_options_update'] = true;

			}


				if(wpsis_option_select.length > 0 ){
					$.each(wpsis_option_select, function () {

						wpsis_options_post[$(this).data('name')] = $(this).val();

					});
				}



				if(wpsis_option_text.length > 0 ){
					$.each(wpsis_option_text, function () {

						wpsis_options_post[$(this).data('name')] = $(this).val();

					});
				}

				if(wpsis_option_checked.length > 0 ){
					$.each(wpsis_option_checked, function () {

						wpsis_options_post[$(this).val()] = true;

					});
				}

			var wpsis_option_colors = $('input[name^="wpsis_options"][type="color"]');

			if(wpsis_option_colors.length > 0 && !wpsis_object.empty_settings){
				$.each(wpsis_option_colors, function () {

					wpsis_options_post[$(this).attr('id')] = $(this).val();

				});
			}

			if(!wpsis_options_post.allowed_role){
				wpsis_options_post.allowed_role = 'empty';
			}



			var data = {

				action : 'wpsis_update_option',
				wpsis_update_option_nonce : wpsis_object.nonce,
				wpsis_options : wpsis_options_post,

			}

			$.post(ajaxurl, data, function(code, response){

				//console.log(response);

				if(response == 'success'){

					$('.wpsis-options .alert').removeClass('d-none').addClass('show');
					setTimeout(function(){
						$('.wpsis-options .alert').addClass('d-none');
					}, 10000);

					if(this_input.data('name') == 'items_per_page'){
						window.location.href = window.location.href;
					}

				}



			});


		});

		if(wpsis_object.empty_settings){

			$('input[name^="wpsis_options"]').change();

		}

		/*new SlimSelect({

			select:'#wpsis_options_allowed_role',
			placeholder: wpsis_object.select_role_str,
		});*/


		function hide_status_icons(){
			var product_row = $('.wpsis_product_row');
			var success_all = product_row.find('.svg-inline--fa.success');
			var error_all = product_row.find('.svg-inline--fa.error');

			success_all.hide();
			error_all.hide();
		}

		var price_group = $('.wpsis_price_adjustment_group');

		$('.wpsis-wrapper select.type').on('change', function(){

			var val = $(this).val();
			$('.svg-inline--fa.type').hide();
			$('.svg-inline--fa.type.'+val).show();

		});

		$('.wpsis-wrapper select.by').on('change', function(){

			var val = $(this).val();
			$('strong.by').hide();
			$('strong.by.'+val).show();

			var adjustment_input = $('#wpsis_price_adjustment');

			if(val == 'percent'){

				if(adjustment_input.val() > 100){
					adjustment_input.val(0.25);
				}


				adjustment_input.prop('max', 100);
			}else{
				adjustment_input.prop('max', '');
			}


		});




		var wpsis_price_adjustment_object ={};
		var wpsis_adjustment_type_global = 'adjustment';

		function get_array_normal_price(history){

			var sum = _.reduce(history, function(memo, num){

				memo = parseFloat(memo);
				num = parseFloat(num);
				return memo + num;

				}, 0);

			sum = parseFloat(sum);

			return sum/(history.length);

		}


		function get_actual_adjustment_price(price, adjustment, type, by){

			var adjustment_price = parseFloat(adjustment);
			price = parseFloat(price);
			adjustment = parseFloat(adjustment);

			var new_price;



			if(by == 'percent'){

				adjustment_price = (price*adjustment)/100;
			}



			if(type == 'increase'){

				new_price = price+adjustment_price;

			}else if(type == 'decrease'){

				new_price = price-adjustment_price;

			}



			return new_price;

		}

		

		function product_price_calculate(type){
			
			type = (type?type:'normal');

			var wpsis_adjustment_type = $('#wpsis_adjustment_type');
			var wpsis_adjustment_by = $('#wpsis_adjustment_by');
			var wpsis_price_adjustment = $('#wpsis_price_adjustment');
			var history_table = $('#wpsis_product_history_table');
			var all_rows = $('tr.wpsis_product_row');
			var type_val = wpsis_adjustment_type.val();
			var by_val = wpsis_adjustment_by.val();
			var adjustment_val = wpsis_price_adjustment.val();
			var adjustment_update_btn = $('.wpsi_price_update');
			var alert_warning = $('.wpsis_alert_warning');
			var sale_warning = $('.wpsis_sale_warning');
			all_rows.removeClass('bg-warning');
			alert_warning.hide();
			sale_warning.hide();




			var checked_products = history_table.find('input[type="checkbox"]:not([disabled])');
			var checked_inputs = history_table.find('input[type="checkbox"]:checked');


			all_rows.find('span.current_price').css('text-decoration', 'none');
			all_rows.find('span.new_price').html('');


			wpsis_price_adjustment_object.adjustment_type = type_val;
			wpsis_price_adjustment_object.adjustment_by = by_val;
			wpsis_price_adjustment_object.price_adjustment = adjustment_val;
			wpsis_price_adjustment_object.products = [];

			if(checked_products.length > 0 && ((wpsis_adjustment_type_global == 'reset' || wpsis_adjustment_type_global == 'normal') || adjustment_val > 0)){

				var error_array = [];
				var warning_array = [];
				var warning_array_sale = [];

				$.each(checked_products, function(){




					var this_checkbox = $(this);
					var this_row = this_checkbox.parents('tr.wpsis_product_row:first');
					var product_history = atob(this_row.data('history'));
					product_history = JSON.parse(product_history);
					var  current_price = this_row.data('price');
					var is_on_sale = this_row.data('is_on_sale');
					var regular_price = this_row.data('regular_price');
					var sale_price = this_row.data('sale_price');
					regular_price = parseFloat(regular_price);
					sale_price = parseFloat(sale_price);
					var new_price = 0;
					var history_keys = _.keys(product_history);
					var history_val = _.values(product_history);
					var history_len = history_val.length;

					switch (type) {

						case "adjustment":

							new_price = get_actual_adjustment_price(current_price, adjustment_val, type_val, by_val);

							break;

						case "reset":

							var history_index = 0;
							if(history_len > 1){
								history_index = history_len-2;
							}

							new_price = history_val[history_index];

							break;

						case "normal":

							new_price = get_array_normal_price(history_val);

							break;

						default:

							break;

					}


					new_price = parseFloat(new_price);



					var single_product_obj = {

						'product_id': this_row.data('product'),
						'current_price': current_price,
						'new_price': new_price,
						'type': this_row.data('type'),

					};

					if(new_price <= 0){

						this_row.addClass('bg-danger price_error');

						error_array.push(this_row.data('product'));

						this_row.find('span.current_price').css('text-decoration', 'line-through');
						this_row.find('span.new_price').html(wpsis_object.currency_symbol+''+new_price.toFixed(2));

					}else{

						this_row.removeClass('bg-danger price_error');

					}


					if(this_checkbox.prop('checked')){



						this_row.find('span.current_price').css('text-decoration', 'line-through');
						this_row.find('span.new_price').html(wpsis_object.currency_symbol+''+new_price.toFixed(2));

						if(is_on_sale == 1 && new_price >= regular_price){

							this_row.addClass('bg-warning');
							warning_array_sale.push(this_row.data('product'));

						}

						if(new_price == current_price){

							this_row.addClass('bg-warning');
							warning_array.push(this_row.data('product'));

						}

						wpsis_price_adjustment_object.products.push(single_product_obj);

					}


				});

				if(checked_inputs.length > 0) {

					adjustment_update_btn.prop('disabled', false);

				}else{

					adjustment_update_btn.prop('disabled', true);

				}


				if(error_array.length > 0){

					$('.wpsis_alert_danger').show();

				}else{

					$('.wpsis_alert_danger').hide();

				}


				if(warning_array.length > 0){

					alert_warning.show();

				}else{

					alert_warning.hide();

				}

				if(warning_array_sale.length > 0){

					sale_warning.show();

				}else{

					sale_warning.hide();

				}

				// console.log(wpsis_price_adjustment_object);


			}else{



					adjustment_update_btn.prop('disabled', true);




			}


		}

		var wpsis_adjustment_group = $('.wpsis_adjustment_group');
		var adjustment_group_inputs = wpsis_adjustment_group.find('.wpsis_calculate_inputs');
		var all_adj_btn = $('.wpsis_price_adjustment_btn');



		$('body ').on('click','.wpsis_price_adjustment_btn', function(){

			all_adj_btn.removeClass('selected');
			wpsis_adjustment_type_global = $(this).data('type');
			$(this).addClass('selected');



			if(wpsis_adjustment_type_global == 'adjustment'){

				adjustment_group_inputs.prop('disabled', false);


			}else{

				adjustment_group_inputs.prop('disabled', true);

			}

			$('.wpsis_price_adjustment_info span').html($(this).data('info'));


			product_price_calculate(wpsis_adjustment_type_global);

		});

		$('body ').on('change keyup','.wpsis_calculate_inputs', function(){

			product_price_calculate(wpsis_adjustment_type_global);

		});

		$('body').on('click','.wpsis_pagination .page-item.active', function(e){

			e.preventDefault();

		});


		$('body').on('click','.wpsis_pagination .page-item:not(.disabled):not(.active)', function(e){

		e.preventDefault();

		var max_page = $('.wpsis_pagination').data('maxpage');
		var page = $(this).data('page');
		var active_page = $('.wpsis_pagination .page-item.active');
		var all_page_items = $('.wpsis_pagination .page-item');
		var active_page_val = active_page.data('page');

		switch (page) {

			case 'next':

				page = active_page_val+1;

				break;
			case 'previous':

				page = active_page_val-1;

				break;

			default:

				break;

		}


		var new_active_page = $('.wpsis_pagination .page-item[data-page="'+page+'"]');
		var next = $('.wpsis_pagination .page-item[data-page="next"]');
		var previous = $('.wpsis_pagination .page-item[data-page="previous"]');



		new_active_page.find('.text').hide();
		new_active_page.find('.spinner-border').show();




		var data = {
			action : 'wpsis_get_paginated_table',
			load_page : page,
		}

		$.post(ajaxurl, data, function(response, code){

			if(code == 'success'){



				$('#wpsis_product_history_table').replaceWith(response);

				data_sales_obj.datasets = [];
				data_prices_obj.datasets = [];

				sales_chart.data = data_sales_obj;
				prices_chart.data = data_prices_obj;

				sales_chart.update();
				prices_chart.update();



				new_active_page.find('.text').show();
				new_active_page.find('.spinner-border').hide();

				all_page_items.removeClass('active');
				all_page_items.removeClass('disabled');
				new_active_page.addClass('active');

				if(max_page == page){
					next.addClass('disabled');
				}

				if(1 == page){
					previous.addClass('disabled');
				}

			}

		});







		// alert(page);




	});




		$('body').on('click', '.wpsi_price_update', function(){

			hide_status_icons();

			load_modal.show();

			var all_checkbox = $('#wpsis_product_history_table input[type="checkbox"]:checked');
			var all_updated = $('.wpsis_all_update');
			var all_not_updated = $('.wpsis_all_not_update');

			$('.wpsis_product_row').removeClass('bg-danger price_error');
			$('.wpsis_product_row').removeClass('bg-warning price_warning');
			$('.wpsis_alert_danger').hide();
			$('.wpsis_alert_warning').hide();

			var data = {

				action : 'wpsis_update_product_price_bulk',
				wpsis_products_array : wpsis_price_adjustment_object,
				wpsis_nonce : wpsis_object.nonce,
				wpsis_type : wpsis_adjustment_type_global
			}

			var success_count = 0;





			$.post(ajaxurl, data, function(response, code){

				load_modal.hide();

				if(code == 'success'){

					$.each(response, function(key, value){



						var product_row = $('.wpsis_product_row[data-product="'+key+'"]');
						var new_price = product_row.find('span.new_price');
						var current_price = product_row.find('span.current_price');

						var success = product_row.find('.svg-inline--fa.success');
						var error = product_row.find('.svg-inline--fa.error');
						if(value.status){

							success_count++;
							success.show();
							success.prop('title', value.msg)
							new_price.html('');
							current_price.html(value.current_price);
							current_price.css('text-decoration', 'none');
							product_row.data('price', value.price);
							product_row.data('history', value.history);
							product_row.data('is_on_sale', value.is_on_sale);
							product_row.data('regular_price', value.regular_price);
							product_row.data('sale_price', value.sale_price);
							product_row.find('input[type="checkbox"]').prop('checked', false);



						}else{

							error.show();
							error.prop('title', value.msg);
						}






					});

					$('tbody .wpsis_calculate_inputs[type="checkbox"]:first').change();





					if(success_count == all_checkbox.length){

						all_updated.show();
					}else{
						all_not_updated.show();
					}

					setTimeout(function(){

						all_not_updated.hide();
						all_updated.hide();

					}, 5000)





					$('#wpsis_price_adjustment').change();




				}

			});


		})


		$('body').on('change', '.wpsis_calculate_inputs_all', function(){

			var this_checkbox = $(this);
			var all_checkbox = $('.wpsis_calculate_inputs:not([disabled])');

			if(this_checkbox.prop('checked')){
				all_checkbox.prop('checked', true);
			}else{
				all_checkbox.prop('checked', false);
			}

			all_checkbox.change();

		})

		function get_selected_year_months(){


			var months_array = ['Jan-'+year, 'Feb-'+year, 'Mar-'+year, 'Apr-'+year, 'May-'+year, 'Jun-'+year, 'Jul-'+year, 'Aug-'+year, 'Sep-'+year, 'Oct-'+year, 'Nov-'+year, 'Dec-'+year, ];

			var new_month_array = [];

			for(var i = 0; i < months_array.length; i++){

				// console.log(months_array);
				var month = moment(months_array[i], "MMM-YYYY");
				var current = moment();



				if(month.isAfter(current)){

					break;

				}

				new_month_array[i] = months_array[i];

			}

			var months_val = [];
			for(var i = 0; i <= 12; i++){
				months_val.push(0);
			}
			var month_wise = collect(new_month_array).combine(months_val).all();

			return month_wise;

		}


		function get_sales_by_month(current_product_sale){


			var month_wise = get_selected_year_months();

			if(current_product_sale.length > 0){

				$.each(current_product_sale, function(key, sale_data){

					var date = moment(sale_data.date);

					var current_sale = parseFloat(sale_data.line_total);

					var current_month = date.format('MMM-YYYY');

					var is_belong_to = date.isSameOrAfter(year+'-01-01') && date.isSameOrBefore(year+'-12-31');

					if(is_belong_to){

						if(month_wise[current_month] == undefined){
							month_wise[current_month] = current_sale.toFixed(2);
						}else{

							var current_month_existing = parseFloat(month_wise[current_month]);
							var current_month_new = current_sale + current_month_existing;
							current_month_new = parseFloat(current_month_new);
							current_month_new = current_month_new.toFixed(2)
							month_wise[current_month] = current_month_new;
						}

					}

				});

			}

			return month_wise;


		}

		function get_price_by_month(price_history){



			var month_wise = get_selected_year_months();


				$.each(price_history, function(date, price){


					date = moment(parseInt(date)*1000);
					var current_month = date.format('MMM-YYYY');

					// console.log(current_month);



					var current_price = parseFloat(price);



					var is_belong_to = date.isSameOrAfter(year+'-01-01') && date.isSameOrBefore(year+'-12-31');

					if(is_belong_to){

						if(!$.isArray(month_wise[current_month])){
							month_wise[current_month] = [];
							month_wise[current_month].push(current_price);
						}else{

							month_wise[current_month].push(current_price);

						}

					}

				});

				var month_wise_avg = {};
				var month_wise_min_max = {};



				$.each(month_wise, function(month, price_array){

					if($.isArray(price_array)){

						var price_collection = collect(price_array);
						var avg_price = price_collection.avg();
						var min_price = price_collection.min();
						var max_price = price_collection.max();
						avg_price = avg_price.toFixed(2);
						month_wise_avg[month] = avg_price;

						if(min_price != max_price){

							month_wise_min_max[month] = '['+min_price+' - '+max_price+']';

						}else{

							month_wise_min_max[month] = '['+min_price+']';


						}

					}else{

						month_wise_avg[month] = price_array;
						month_wise_min_max[month] = '[0]';

					}


				});


			return {avg : month_wise_avg, min_max : month_wise_min_max};


		}

		var sale_chart_datasets = [];
		var price_chart_datasets = [];
		var sale_legends = [];
		var sorting_type = 'alpha';
		var sorting_by = 'asc';

		var wpsis_sort = function(a, b) {

			a = $(a);
			b = $(b);

			if(sorting_type == 'alpha'){

				if(sorting_by == 'desc'){

					return b.find('span').text().toLowerCase() > a.find('span').text().toLowerCase();

				}else{

					return a.find('span').text().toLowerCase() > b.find('span').text().toLowerCase();

				}

			}else if(sorting_type == 'price'){

				var a_price = a.data('price');
				var b_price = b.data('price');


				if(sorting_by == 'desc'){

					return b_price > a_price;

				}else{

					return a_price > b_price;

				}


			}else if(sorting_type == 'sale'){

				var a_sale = a.data('sale');
				var b_sale = b.data('sale');


				if(sorting_by == 'desc'){

					return b_sale > a_sale;

				}else{

					return a_sale > b_sale;

				}


			}


		}



		$('body').on('change', 'tbody .wpsis_calculate_inputs[type="checkbox"]', function(){


			sales_data = $('tfoot.wpsis_sales_data').data('sales');
			sales_data = atob(sales_data);
			sales_data = JSON.parse(sales_data);
			const sales_collection = collect(sales_data);

			var all_checkboxes = $('tbody .wpsis_calculate_inputs[type="checkbox"]:checked');

			$('.wpsis_sorting_row').hide();
			if(all_checkboxes.length > 0){
				$('.wpsis_sorting_row').show();
			}

			data_sales_obj.datasets = [];
			data_prices_obj.datasets = [];

			$.each(all_checkboxes, function(){

				var this_checkbox = $(this);
				var this_row = this_checkbox.parents('tr.wpsis_product_row:first');
				var product_id = this_row.data('product');
				var price_history = this_row.data('history');
				var label = this_row.data('title');
				var color = this_row.data('color');
				price_history = atob(price_history);
				price_history = JSON.parse(price_history);



				const current_product_collection = sales_collection.where('product_id', product_id+"");
				var current_product_sale = current_product_collection.all();

				var sales_by_months = get_sales_by_month(current_product_sale);
				var prices_by_months = get_price_by_month(price_history);
				var prices_by_avg = prices_by_months.avg;

				// console.log(sales_by_months);
				// console.log(prices_by_months);

				var price_chart_data = {

					label: label.trim(),
					price: this_row.data('price'),
					product: product_id,
					data: _.values(prices_by_avg),
					borderColor: color,
					fill: false,

				};

				var sale_chart_data = {

					label: label.trim(),
					price: this_row.data('price'),
					product: product_id,
					data: _.values(sales_by_months),
					borderColor: color,
					fill: false,

				};


				data_prices_obj.datasets.push(price_chart_data);
				data_sales_obj.datasets.push(sale_chart_data);


			});


			sales_chart.data = data_sales_obj;
			prices_chart.data = data_prices_obj;


			sale_chart_datasets = data_sales_obj.datasets;
			price_chart_datasets = data_sales_obj.datasets;
			var sale_legend_box = $('.wpsis_sale .wpsis_legend_box .row');
			sale_legend_box.html('');

			sale_legends = [];

			$.each(sale_chart_datasets, function(sale_key, sale_dataset){

				var sale_collection = collect(sale_dataset.data);
				var current_total_sale = sale_collection.sum();

				var legend = `<div class="col-md-3 wpsis_single_legend" data-legend_id="`+sale_key+`" data-sale="`+current_total_sale+`" data-product_id="`+sale_dataset.product+`" data-price="`+sale_dataset.price+`">
                                <i class="fa fa-square" style="color: `+sale_dataset.borderColor+`"></i>
                                <span>
                                    `+sale_dataset.label+`
                                </span>
                                <i class="fa fa-minus-circle text-danger" style=""></i>
                                

                            </div>`;

				sale_legends.push($(legend).get()[0]);


			});



			sale_legends.sort(wpsis_sort);


			$.each(sale_legends, function(){

				sale_legend_box.append(this);

			});


			sales_chart.update();
			prices_chart.update();


			//
			// console.log(prices_chart);
			// console.log(sales_chart.getDatasetMeta(0).hidden = true);


		} );










	$('.wpsis_sorting_row .wpsis_sorting_btn').on('click', function(){

		var sorting_row = $('.wpsis_sorting_row');
		var all_btns = $('.wpsis_sorting_row .wpsis_sorting_btn');
		var this_btn = $(this);
		sorting_type = this_btn.data('type');
		sorting_by = this_btn.data('sort_by');



		sorting_row.find('.svg-inline--fa').hide();
		this_btn.find('.svg-inline--fa.sort_'+sorting_by).show();
		all_btns.removeClass('selected');
		this_btn.addClass('selected');

		var sale_legend_box = $('.wpsis_sale .wpsis_legend_box .row');
		sale_legend_box.html('');

		sale_legends.sort(wpsis_sort);

		$.each(sale_legends, function(){

			sale_legend_box.append(this);

		});



		if(this_btn.data('sort_by') == 'asc'){
			this_btn.data('sort_by', 'desc');
		}else{
			this_btn.data('sort_by', 'asc');

		}


	});

	$('body').on('click','.wpsis_single_legend .fa-minus-circle', function(){

		var parent = $(this).parents('.wpsis_single_legend:first');

		var selected_row = $('.wpsis_product_row[data-product="'+parent.data('product_id')+'"]');

		selected_row.find('input[type="checkbox"]').click();

	});

	$('body').on('click', 'div.wpsis-api-requests input[name^="validate_request"]', function(){
		
		
		var requests = {};
		
		$.each($('div.wpsis-api-requests input[name^="validate_request"]:checked'), function(i,v){
			requests[i] = $(this).val();
		})
		
		var data = {

			action: 'wpsis_validate_api_requests',
			wpsis_validate_request: requests,
			wpsis_nonce_check: wpsis_object.nonce,
		}
		
		$.blockUI({ message: false });
		$.post(wpsis_object.ajax_url, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}
		});
		
	});
	
	$('input#wpsis-redirects-status').bind('click', function (e) {
		var data = {

			action: 'wpsis_redirects_status',
			status: $(this).is(':checked')?$(this).val():'',
			wpsis_nonce_field: wpsis_object.nonce,
		}

		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}

		});


	});	

});

