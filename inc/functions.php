<?php
	
	if(!function_exists('wpsis_pre')){
	function wpsis_pre($data){
			if(isset($_GET['debug'])){
				wpsis_pree($data);
			}
		}	 
	} 	
	if(!function_exists('wpsis_pree')){
	function wpsis_pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 

	function sanitize_wpsis_data( $input ) {
	
			if(is_array($input)){
			
				$new_input = array();
		
				foreach ( $input as $key => $val ) {
					$new_input[ $key ] = (is_array($val)?sanitize_wpsis_data($val):sanitize_text_field( $val ));
				}
				
			}else{
				$new_input = sanitize_text_field($input);
			}
	
			if(!is_array($new_input)){
	
				if(stripos($new_input, '@') && is_email($new_input)){
					$new_input = sanitize_email($new_input);
				}
	
				if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
					$new_input = sanitize_url($new_input);
				}
	
			}
	
			
			return $new_input;
		}	
	
	function wpsis_admin_enqueue_script()
	{


		global $wpsis_short_script_obj;


		$wpsis_short_script_obj = array(
			'wpsis_nonce' => wp_create_nonce('wpsis_nonce_action'),
			'is_wc_admin' => (isset($_GET['page']) && $_GET['page'] == 'wc-admin'),
		);

		if (isset($_GET['page']) && $_GET['page'] == 'wpsis') {
			
			global $wp_sis_pro, $wp_sis_options;

//			wp_enqueue_scripts('moment');
            wp_enqueue_style('wpsis-fontawesome', plugins_url('css/fontawesome.min.css', dirname(__FILE__)));

            wp_enqueue_script('wpsis_fontawesome_js', plugin_dir_url(dirname(__FILE__)) . 'js/fontawesome.min.js', array('jquery'));
            wp_enqueue_script('wpsis_collect', plugin_dir_url(dirname(__FILE__)) . 'js/collect.min.js', array('jquery'));
			wp_enqueue_script('wpsis_moment', plugin_dir_url(dirname(__FILE__)) . 'js/moment.js', array('jquery'));
			wp_enqueue_script('wpsis_chart', plugin_dir_url(dirname(__FILE__)) . 'js/chart.min.js', array('jquery'));
			wp_enqueue_script('wpsis_bootstrap', plugin_dir_url(dirname(__FILE__)) . 'js/bootstrap.min.js', array('jquery'), time());
			wp_enqueue_style('wpsis-chart', plugins_url('css/chart.min.css', dirname(__FILE__)));
			wp_enqueue_style('wpsis-bootstrap', plugins_url('css/bootstrap.min.css', dirname(__FILE__)), array(), time());
			wp_enqueue_script('wpsis_slim', plugin_dir_url(dirname(__FILE__)) . 'js/slimselect.js', array('jquery'));
			wp_enqueue_style('wpsis-slim', plugins_url('css/slimselect.css', dirname(__FILE__)));


//			wp_enqueue_style('wpsis-font-awesome2-style', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');

			wp_enqueue_media();
	
			wp_enqueue_style('wpsis-admin', plugins_url('css/admin-styles.css', dirname(__FILE__)), array(), time());

			wp_enqueue_script('wpsis_admin_scripts', plugin_dir_url(dirname(__FILE__)) . 'js/admin-scripts.js', array('jquery'), time());
			
			if($wp_sis_pro){
				wp_enqueue_script('wpsis_pro_scripts', plugin_dir_url(dirname(__FILE__)) . 'pro/wp-sis-admin.js', array('jquery'), time());
			}
            
			$wp_sis_tab = '0';
			if(isset($_GET['t'])){

                $wp_sis_tab = sanitize_wpsis_data($_GET['t']);
            }
			wp_localize_script(
				'wpsis_admin_scripts',
				'wpsis_object',
				array(

                    'this_url' => admin_url( 'admin.php?page=wpsis' ),
                    'wpsis_tab' => $wp_sis_tab,
					'ajax_url' => admin_url('admin-ajax.php'),
					'url' => admin_url('admin.php?page=wpsis'),
					'select_role_str' =>  __('Select roles to allow upload', 'si-system'),
					'reset_confirm' => __('Do you want to reset all settings and clear directories?', 'si-system'),
                    'empty_settings' => empty($wp_sis_options),
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    'nonce' => wp_create_nonce('wpsis_product_update_action'),
					'wpsis_remove_records' => __('Do you want to clear all monitoring records as well?', 'si-system')
				)
			);

		}

		if (isset($_GET['page']) && ($_GET['page'] == 'wc-admin')){
			

			wp_enqueue_script('wpsis_wc_admin_scripts', plugin_dir_url(dirname(__FILE__)) . 'js/wc-admin-scripts.js?t='.time(), array('jquery'));
			wp_enqueue_style('wpsis-wc-admin', plugins_url('css/wc-admin-styles.css', dirname(__FILE__)), array(), time());

			wp_localize_script(
				'wpsis_wc_admin_scripts',
				'wpsis_obj',
				$wpsis_short_script_obj
			);

		}
	}
	


    function wpsis_get_user_roles(){
        $ret = array();
        global $wp_roles;
        if(!empty($wp_roles) && isset($wp_roles->roles) && !empty($wp_roles->roles)){

            foreach($wp_roles->roles as $key=>$arr){
                $ret[$key] = $arr['name'];
            }
        }
        return $ret;
    }

    function wpsis_get_user_roles_options(array $selected){

        $wp_sis_get_user_roles = wpsis_get_user_roles();

        $options = '';

        if(!empty($wp_sis_get_user_roles)){

            foreach ($wp_sis_get_user_roles as $role_key => $role_name){

                $selected_option = in_array($role_key, $selected) ? 'selected="selected"' : '';

                $options .= "<option value='$role_key' $selected_option>$role_name</option>";

            }
        }

        return $options;


    }



    function wpsis_get_current_user_role() {
        global $wp_roles;

        $current_user = wp_get_current_user();
        $roles = $current_user->roles;

        $role = array_shift( $roles );

        return isset( $wp_roles->role_names[ $role ] ) ? $role : FALSE;
    }



	add_action('admin_enqueue_scripts', 'wpsis_admin_enqueue_script');

	add_action('wp_enqueue_scripts', 'wpsis_wp_enqueue_script');
	
	function wpsis_wp_enqueue_script()
	{
		global $post, $wp_sis_pro, $wp_sis_url, $wp_sis_options;


	}
	
	if (is_admin()) {
		add_action('admin_menu', 'wpsis_menu');
	}
	function wpsis_menu()
	{
		global $wp_sis_data, $wp_sis_pro;
		
		$wp_sis_data['Name'] = str_replace('System', '', $wp_sis_data['Name']);
	
		$title = $wp_sis_data['Name'] . ' ' . ($wp_sis_pro ? ' ' . __('Pro', 'si-system') : '');
	
		add_submenu_page('woocommerce', $title, $title, 'manage_woocommerce', 'wpsis', 'wpsis_settings' );
		
	}
	function wpsis_settings()
	{
		global $wp_sis_premium_link, $wp_sis_pro, $wp_sis_url;
		$wp_sis_options = get_option('wpsis_options', array());
		$wp_sis_options = is_array($wp_sis_options)?$wp_sis_options:array();
		include_once('wpsis_settings.php');
	}


    add_action('wp_ajax_wpsis_update_option', 'wpsis_update_option');

    if(!function_exists('wpsis_update_option')){
        function wpsis_update_option(){



            if(isset($_POST['wpsis_update_option_nonce'])){



                $nonce = sanitize_wpsis_data($_POST['wpsis_update_option_nonce']);



                if ( ! wp_verify_nonce( $nonce, 'wpsis_product_update_action' ) ){

                   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
                }

                $update = false;

                if(isset($_POST['wpsis_options'])){

                    $wp_sis_options = sanitize_wpsis_data($_POST['wpsis_options']);
                    $wp_sis_options = $wp_sis_options && is_array($wp_sis_options) ? $wp_sis_options : array();


                    $sanitized_option = sanitize_wpsis_data($wp_sis_options);

                    $sanitized_option['allowed_role'] = ((array_key_exists('allowed_role', $sanitized_option) && $sanitized_option['allowed_role'] !== 'empty') ? $sanitized_option['allowed_role'] : array());

                    $update = update_option('wpsis_options', $sanitized_option);
                }





                echo  json_encode($update);

            }

            wp_die();

        }
    }

	if(!function_exists('wpsis_handle_custom_query_var')){
		function wpsis_handle_custom_query_var( $query, $query_vars ) {
		
		
			if ( isset($query_vars['wpsis_product_price_history']) && ! empty( $query_vars['wpsis_product_price_history'] ) ) {
				$query['meta_query'][] = array(
					'key' => '_wpsis_product_price_history',
					'compare' => $query_vars['wpsis_product_price_history'],
				);
			}
		
			if ( isset($query_vars['wpsis_date_query']) && ! empty( $query_vars['wpsis_date_query'] ) ) {
		
				$query['date_query'] = $query_vars['wpsis_date_query'];
			}
		
			return $query;
		}
	}
	add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'wpsis_handle_custom_query_var', 10, 2 );
	
	
	add_action( 'woocommerce_update_product_variation', 'wpsis_sync_on_product_save', 10, 2 );
	add_action( 'woocommerce_update_product', 'wpsis_sync_on_product_save', 10, 2 );
	
	if(!function_exists('wpsis_sync_on_product_save')){
		function wpsis_sync_on_product_save( $product_id, $product ) {
		
			global $wpsis;
		
			if(!isset($_POST['wpsis_products_array'])){
		
				$new_price = $product->get_price();
				$wpsis->update_product_price_history($product_id, $new_price);
		
			}
		}
	}



	if(!function_exists('wpsis_wc_order_summary_quarterly')){
		function wpsis_wc_order_summary_quarterly($state = '', $year = ''){

			global $wpdb, $quarterly_wpsis_state, $quarterly_wpsis_year;
			

			
			

			if(!$state){
				$wpsis_sates = wpsis_get_states();
				$state = ($quarterly_wpsis_state?$quarterly_wpsis_state:current($wpsis_sates));				
			}else{
				
			}
			if(!$year){				
				$year = ($quarterly_wpsis_year?$quarterly_wpsis_year:date('Y'));				
			}else{
				
			}


			$order_type = 'completed';



			$order_quarters = array();
			$quarterly_amount = array();

			$states = array($state);
			
			$quarter_query = "SELECT p.ID, MONTH(p.post_date) AS month_num FROM `".$wpdb->prefix."posts` p, `".$wpdb->prefix."postmeta` pa WHERE p.ID=pa.post_id AND ((pa.meta_key='_billing_state' AND pa.meta_value  IN ('".implode("','", $states)."')) || (pa.meta_key='_shipping_state' AND pa.meta_value  IN ('".implode("','", $states)."'))) AND p.post_type='shop_order' AND p.post_status='wc-".$order_type."' AND YEAR(p.post_date) = $year";
			//wpsis_pree($quarter_query);
			
			$orders_q = $wpdb->get_results($quarter_query);
			//wpsis_pree($orders_q);
			
			if(is_array($orders_q) && !empty($orders_q)){						
				
				
				foreach($orders_q as $orders){
					
					if($orders->month_num>=1 && $orders->month_num<=3){
						$quarter = 1;								
					}

					if($orders->month_num>=4 && $orders->month_num<=6){
						$quarter = 2;								
					}

					if($orders->month_num>=7 && $orders->month_num<=9){
						$quarter = 3;								
					}

					if($orders->month_num>=10 && $orders->month_num<=12){
						$quarter = 4;								
					}
					
					$order_quarters[$quarter][] = $orders->ID;
				}
				//wpsis_pree($order_quarters);
				if(!empty($order_quarters)){
					foreach($order_quarters as $quarter=>$orders){
						$orders_a = $wpdb->get_results("SELECT ROUND(SUM(pa.meta_value), 2) AS amount FROM `".$wpdb->prefix."postmeta` pa WHERE pa.post_id IN(".implode(',', $orders).") AND pa.meta_key='_order_total'");
						if(is_array($orders_a) && !empty($orders_a)){
							$orders_a = current($orders_a);
							if(is_object($orders_a)){
								$quarterly_amount[$quarter] = $orders_a->amount;
							}
						}
					}
				}
			}


			?>

				<div class="woocommerce-summary__item-data">

					<div class="woocommerce-summary__item-value">
						<p class="css-1ahfdc3-Text e15wbhsk0"><?php echo get_woocommerce_currency_symbol() . (isset($quarterly_amount[1]) ? $quarterly_amount[1] : 0); ?></p>
					</div>
					<div class="woocommerce-summary__item-delta" role="presentation">
						<p class="css-1qmnemh-Text e15wbhsk0"><?php echo (isset($quarterly_amount[1]) && is_array($order_quarters[1]) && count($order_quarters[1]) ? count($order_quarters[1]) : ''); ?> (Jan-Mar)</p>
					</div>
					</div>

					<div class="woocommerce-summary__item-data">
					<div class="woocommerce-summary__item-value">
						<p class="css-1ahfdc3-Text e15wbhsk0"><?php echo get_woocommerce_currency_symbol() . (isset($quarterly_amount[2]) && $quarterly_amount[2] ? $quarterly_amount[2] : 0); ?></p>
					</div>
					<div class="woocommerce-summary__item-delta" role="presentation">
						<p class="css-1qmnemh-Text e15wbhsk0"><?php echo (isset($quarterly_amount[2]) && is_array($order_quarters[2]) && count($order_quarters[2]) ? count($order_quarters[2]) : ''); ?> (Apr-Jun)</p>
					</div>
					</div>
					<div class="woocommerce-summary__item-data">

					<div class="woocommerce-summary__item-value">
						<p class="css-1ahfdc3-Text e15wbhsk0"><?php echo get_woocommerce_currency_symbol() . (isset($quarterly_amount[3]) && $quarterly_amount[3] ? $quarterly_amount[3] : 0); ?></p>
					</div>
					<div class="woocommerce-summary__item-delta" role="presentation">
						<p class="css-1qmnemh-Text e15wbhsk0"><?php echo (isset($quarterly_amount[3]) && is_array($order_quarters[3]) && count($order_quarters[3]) ? count($order_quarters[3]) : ''); ?> (Jul-Sep)</p>
					</div>
					</div>
					<div class="woocommerce-summary__item-data">

					<div class="woocommerce-summary__item-value">
						<p class="css-1ahfdc3-Text e15wbhsk0"><?php echo get_woocommerce_currency_symbol() . (isset($quarterly_amount[4]) && $quarterly_amount[4] ? $quarterly_amount[4] : 0); ?></p>
					</div>
					<div class="woocommerce-summary__item-delta" role="presentation">
						<p class="css-1qmnemh-Text e15wbhsk0"><?php echo (isset($quarterly_amount[4]) && is_array($order_quarters[4]) && count($order_quarters[4]) ? count($order_quarters[4]) : ''); ?> (Oct-Dec)</p>
					</div>

				</div>


			<?php

		}
	}

	if (!function_exists('wpsis_order_summary_by_state_year')) {
		function wpsis_order_summary_by_state_year($state = '', $year = '', $type = ''){

			global $wpdb, $all_wpsis_state, $all_wpsis_year, $quarterly_wpsis_state, $quarterly_wpsis_year;
			
			switch($type){
				case 'wpsis_all':
					$this_state = $all_wpsis_state;
					$this_year = $all_wpsis_year;		
				break;
				case 'wpsis_quarterly':
					$this_state = $quarterly_wpsis_state;
					$this_year = $quarterly_wpsis_year;
				break;				
			}
			

			if(!$state){
				$wpsis_sates = wpsis_get_states();
				$state = ($this_state?$this_state:current($wpsis_sates));
				
			}else{
				
			}
			
			//pree($all_wpsis_state.' - '.$all_wpsis_year.' - '.$year.' - '.$state);
			
			if(!$year){
				$year = ($this_year?$this_year:date('Y'));
			}else{
				
			}

			$order_type = 'completed';
			$states = array($state);



			$total_orders = 0;
			$total_amount = 0;
			//COUNT(p.ID) AS total 
			$orders_q = $wpdb->get_results("SELECT p.ID FROM `".$wpdb->prefix."posts` p, `".$wpdb->prefix."postmeta` pa WHERE p.ID=pa.post_id AND (pa.meta_key='_billing_state' AND pa.meta_value IN ('".implode("','", $states)."')) AND p.post_type='shop_order' AND p.post_status='wc-".$order_type."' AND YEAR(p.post_date) = $year");
			if(is_array($orders_q) && !empty($orders_q)){						
				$total_orders = count($orders_q);
				$order_numbers = array();
				foreach($orders_q as $orders){
					$order_numbers[] = $orders->ID;
				}
				if(!empty($order_numbers)){
					$orders_a = $wpdb->get_results("SELECT ROUND(SUM(pa.meta_value), 2) AS amount FROM `".$wpdb->prefix."postmeta` pa WHERE pa.post_id IN(".implode(',', $order_numbers).") AND pa.meta_key='_order_total'");
					if(is_array($orders_a) && !empty($orders_a)){
						$orders_a = current($orders_a);
						if(is_object($orders_a)){
							$total_amount = $orders_a->amount;
						}
					}
				}
			}


			return array(
				'year' => $year,
				'state' => $state,
				'total_orders' => $total_orders,
				'total_amount' => $total_amount,

			);
		}
	}

	if (!function_exists('wpsis_wc_order_summary_all_year')) {
		function wpsis_wc_order_summary_all_year($state = '', $year = '', $type=''){
			
			//wpsis_pree($state.' - '.$year.' - '.$type);
			
			$summary_by_state_year = wpsis_order_summary_by_state_year($state, $year, $type);
			
			//wpsis_pree($summary_by_state_year);

			extract($summary_by_state_year);


			?>
	
				<div class="woocommerce-summary__item-data">
					<div class="woocommerce-summary__item-value">
						<p class="css-1ahfdc3-Text e15wbhsk0"><?php echo get_woocommerce_currency_symbol() . $total_amount; ?></p>
					</div>
					<div class="woocommerce-summary__item-delta" role="presentation">
						<p class="css-1qmnemh-Text e15wbhsk0"><?php echo $total_orders; ?></p>
					</div>
				</div>
	
			<?php
		}
	}

	if (!function_exists('wpsis_wc_order_summary_container')) {
		function wpsis_wc_order_summary_container($container_type){
			global $wp_sis_url, $all_wpsis_state, $all_wpsis_year, $quarterly_wpsis_state, $quarterly_wpsis_year;


			$order_type = 'completed';
			$wpsis_years = wpsis_get_years();	
			$wpsis_sates = wpsis_get_states();

			// wpsis_quarterly
			// wpsis_all
			//wpsis_pree($container_type);
			
			//wpsis_pree($container_type.' - '.$quarterly_wpsis_state.' - '.$quarterly_wpsis_year.' - '.$all_wpsis_state.' - '.$all_wpsis_year);
			
			
			switch ($container_type) {
								
				case 'wpsis_all':
					$current_state = ($all_wpsis_state?$all_wpsis_state:current($wpsis_sates));
					$current_year = ($all_wpsis_year?$all_wpsis_year:current($wpsis_years));
					//wpsis_pree($container_type.' - '.$all_wpsis_state.' - '.$all_wpsis_year);
				break;

				case 'wpsis_quarterly':
					$current_state = ($quarterly_wpsis_state?$quarterly_wpsis_state:current($wpsis_sates));
					$current_year = ($quarterly_wpsis_year?$quarterly_wpsis_year:current($wpsis_years));
					//wpsis_pree($container_type.' - '.$quarterly_wpsis_state.' - '.$quarterly_wpsis_year);
				break;								

			}
	
			?>
	
				<li class="wpsis <?php echo $container_type;?> woocommerce-summary__item-container">
					<a class="woocommerce-summary__item <?php echo ($container_type == 'wpsis_all' ? 'is-good-trend' : 'is-current-trend'); ?>">
						<div class="woocommerce-summary__item-label wpsis_title_container">
							<p class="css-101we7g-Text e15wbhsk0">
		
								<?php echo ucwords($order_type); ?> Orders (<span class="wpsis_current" data-type="states"><?php echo $current_state; ?></span> / <span class="wpsis_current" data-type="years"><?php echo $current_year; ?></span>)
								<span class="wpsis_loading"><img src="<?php echo $wp_sis_url; ?>img/loader.gif" /></span>
		
							</p>
		
							<?php
		
							echo wpsis_get_dropdown_content_years($wpsis_years);
							echo wpsis_get_dropdown_content_sates($wpsis_sates);
		
							?>
						</div>

						<?php 

							switch ($container_type) {
								
								case 'wpsis_all':
								
									wpsis_wc_order_summary_all_year('', '', 'wpsis_all');

								break;

								case 'wpsis_quarterly':
								
									wpsis_wc_order_summary_quarterly('', '', 'wpsis_quarterly');

								break;								

							}


						?>

					</a>
				</li>
	
	
			<?php
	
		}
	}


	
	add_action('admin_head', 'wpsis_admin_head_scripts');
	function wpsis_admin_head_scripts(){
			global $wp_sis_url;
?>
	<style type="text/css">	
		.wpsis_quarterly .css-1ahfdc3-Text{
			font-size:14px;
		}
		.woocommerce-stats-overview__stats .woocommerce-summary__item-container {
			float: left;
		}
		.woocommerce-summary__item.is-current-trend .woocommerce-summary__item-delta {
			background-color: #aea242;
			color: #fff;
		}
		.woocommerce-stats-overview__stats .woocommerce-summary__item-container.wpsis_quarterly{
			/* margin-right: -1px; */
		}

		#wp-admin-bar-wpsis_order_info .ab-sub-wrapper ul li .ab-item {
			height: 65px !important;
		}

		#wp-admin-bar-wpsis_order_info .ab-sub-wrapper ul li.wpsis_current {
			background-color: #4ab866;
			color: #ffffff;
		}

		#wp-admin-bar-wpsis_order_info .ab-sub-wrapper ul li.wpsis_previous {
			background-color: #aea242;
			color: #ffffff;

		}

		#wp-admin-bar-wpsis_order_info .ab-sub-wrapper ul li.wpsis_current a:hover {
			color: #ffffff !important;
		}

		#wp-admin-bar-wpsis_order_info .ab-sub-wrapper ul li.wpsis_previous a:hover {
			color: #ffffff !important;
		}

	</style>
    <script type="text/javascript" language="javascript">
		jQuery(document).ready(function($){
			<?php if(isset($_GET['page']) && $_GET['page']=='wc-admin'): ?>

			history.pushState({}, jQuery('title').text(), '<?php echo admin_url('admin.php?page=wc-admin') ?>');

			setTimeout(function(){
					$('.woocommerce-stats-overview__stats').append(`<?php wpsis_wc_order_summary_container('wpsis_all'); ?>`);
					
					setTimeout(function(){
					
						$('.woocommerce-stats-overview__stats').append(`<?php wpsis_wc_order_summary_container('wpsis_quarterly'); ?>`);			
						
					}, 6000);	
								
			}, 3000);

			<?php endif; ?> 

		});
	</script>
<?php        
	}	
	

	if(!function_exists('wpsis_get_states')){
		function wpsis_get_states(){
			global $wpdb;
			$states_query = "SELECT meta_value as state FROM $wpdb->postmeta WHERE meta_key = '_billing_state' AND meta_value != '' GROUP BY state";
			$states_results = $wpdb->get_results($states_query);

			
			$all_states = array();
			if(!empty($states_results)){
				foreach ($states_results as $state_obj) {					
					$all_states[] = $state_obj->state;
				}
			}


			return $all_states;
		}
	}

	if(!function_exists('wpsis_get_years')){
		function wpsis_get_years(){
			global $wpdb;
			$min_year_query = "SELECT MIN(YEAR(post_date)) as min_year FROM $wpdb->posts WHERE post_type = 'shop_order'";
			$available_years_query = "SELECT DISTINCT(YEAR(post_date)) as years FROM $wpdb->posts WHERE post_type = 'shop_order' ORDER BY years DESC ";
			$available_years = $wpdb->get_results($available_years_query);
			$available_years_array = array_map(function($year){ return (int) $year->years;}, $available_years);
			$min_year = $wpdb->get_var($min_year_query);
			$current_year = (int) date('Y');
			$min_year = ($min_year ? $min_year : $current_year);

			$years_array = array();		
			for ($i = $current_year; $i >= $min_year ; $i--) { 
				# code...

				if(is_array($available_years_array) && in_array($i, $available_years_array)){

					
					$years_array[$i] = '';

				}else{

					$years_array[$i] = 'disabled';
				}

			}

			return $years_array;
		}
	}

	if(!function_exists('wpsis_get_dropdown_content_sates')){

		function wpsis_get_dropdown_content_sates($content_array){		
			
			
			if(!empty($content_array)){
				//wpsis_pree($content_array);
				$html = '<ul class="wpsis_dropdown_content" data-type="states">';
				foreach ($content_array as $content_key => $content_value) {
					# code...
					$html .= "<li data-key='$content_value'>$content_value</li>";

				}

				$html .= '</ul>';

			};

			

			return $html;
		}

	}

	if(!function_exists('wpsis_get_dropdown_content_years')){

		function wpsis_get_dropdown_content_years($content_array){		
			
			//wpsis_pree($content_array);
			if(!empty($content_array)){
				$html = '<ul class="wpsis_dropdown_content" data-type="years">';
				foreach ($content_array as $content_key => $content_value) {
					# code...
					$html .= "<li data-key='$content_key' class='$content_value'>$content_key</li>";

				}

				$html .= '</ul>';

			};

			

			return $html;
		}

	}


	



	add_action('wp_ajax_wpsis_get_update_order_states', 'wpsis_get_update_order_states');

	if(!function_exists('wpsis_get_update_order_states')){
		function wpsis_get_update_order_states(){


			$content = '';
			

			if(!isset($_POST['wpsis_nonce']) || !wp_verify_nonce($_POST['wpsis_nonce'], 'wpsis_nonce_action')){

				wp_die(__('Sorry, your nonce did not verified', 'si-system'));

			}else{

				$posted_data = sanitize_wpsis_data($_POST);

				$state = $posted_data['wpsis_state'];
				$year = $posted_data['wpsis_year'];
				$type = $posted_data['wpsis_type'];
				
				
				

				switch ($type) {
					case 'wpsis_all':		
					
						wpsis_wc_order_summary_all_year($state, $year, $type);
						
						if($state)
						update_option('all_wpsis_state', $state);
						
						if($year)
						update_option('all_wpsis_year', $year);		

								
					break;
					
					case 'wpsis_quarterly':
					
						wpsis_wc_order_summary_quarterly($state, $year, $type);
							
						if($state)
						update_option('quarterly_wpsis_state', $state);
						
						if($year)
						update_option('quarterly_wpsis_year', $year);	
											
					break;
					
					
				}

			}


			exit;
		}
	}


	add_shortcode('WPSIS_ORDER_INFO', 'wpsis_order_info_callback');

	if(!function_exists('wpsis_order_info_callback')){
		function wpsis_order_info_callback($attr){
			
			global $wp_sis_dir, $wpsis_short_script_obj;

			$script_file =  $wp_sis_dir.'js/wc-admin-scripts.js';
			$style_file =  $wp_sis_dir.'css/wc-admin-styles.css';

			ob_start();

			$type = (isset($attr['type']) ? $attr['type'] : 'default');
			$print_scripts_style = (isset($attr['scripts']) ? $attr['scripts'] : true);
			
			if(is_admin()){				
				


					?>

						<script type="text/javascript" language="javascript">

							<?php
								if(file_exists($script_file)){
									?>
									var wpsis_obj = <?php echo json_encode($wpsis_short_script_obj); ?>;
									<?php
									include_once($script_file);
								}
							?>

						</script>

						<style type="text/css">	

							<?php
								if(file_exists($style_file)){
									include_once($style_file);
								}
							?>
							
						</style>

						<ul class="woocommerce-stats-overview__stats is-even">

							

					<?php 
						//pree($type);


						switch ($type) {
							case 'all':
								# code...
								wpsis_wc_order_summary_container('wpsis_all');

							break;

							case 'quarterly':
								# code...
								wpsis_wc_order_summary_container('wpsis_quarterly'); 

							break;
							
							default:
								# code...
								wpsis_wc_order_summary_container('wpsis_all');
								wpsis_wc_order_summary_container('wpsis_quarterly'); 
							break;
						}
                       

					?>

				</ul>

			<?php

			}

			$content = ob_get_clean();
			return $content;
		}
	}

	add_action( 'admin_bar_menu', 'wpsis_add_links_to_admin_bar',999 );
 
	if(!function_exists('wpsis_add_links_to_admin_bar')){

		function wpsis_add_links_to_admin_bar($admin_bar) {  
		
			global $all_wpsis_state, $all_wpsis_year, $quarterly_wpsis_state, $quarterly_wpsis_year;

			$wpsis_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

			$is_wc_admin = in_array('woocommerce-admin/woocommerce-admin.php', $wpsis_active_plugins);
			$menu_url = ($is_wc_admin ? admin_url('admin.php?page=wc-admin') : admin_url('admin.php?page=wpsis&t=1'));
			

			
			$statistics = __('Stats', 'si-system').($all_wpsis_state?' ('.$all_wpsis_state.' / '.$all_wpsis_year.') - ':'').($quarterly_wpsis_state?' ('.$quarterly_wpsis_state.' / '.$quarterly_wpsis_year.')':'');
		
			$args = array(
				
				'id'     => 'wpsis_order_info',
				'title'  => $statistics,
				'href'   => '',
				'meta'   => false
			);
			$admin_bar->add_node( $args );
			
			$args = array(
				
				'parent'     => 'wpsis_order_info',
				'id'     => 'wpsis_order_info_current',
				'title'  => wpsis_get_order_menu_title('all'),
				'href'   => esc_url($menu_url),
				'meta'   => array('class' => 'wpsis_current')
			);
			$admin_bar->add_node( $args );
	
	
			$args = array(
				
				'parent'     => 'wpsis_order_info',
				'id'     => 'wpsis_order_info_previous',
				'title'  => wpsis_get_order_menu_title('quarter'),
				'href'   => esc_url($menu_url),
				'meta'   => array('class' => 'wpsis_previous')
			);
			$admin_bar->add_node( $args );
	
	
		}

	}

	if(!function_exists('wpsis_get_order_menu_title')){
		function wpsis_get_order_menu_title($year){
			
			global $all_wpsis_state, $all_wpsis_year, $quarterly_wpsis_state, $quarterly_wpsis_year;

			$years_stats = array();
			$title = '';

			switch ($year) {
				case 'all':
					$years_stats = wpsis_order_summary_by_state_year($all_wpsis_state, $all_wpsis_year);
				break;

				case 'quarter':
					$years_stats = wpsis_order_summary_by_state_year($quarterly_wpsis_state, $quarterly_wpsis_year);
				break;

			}

			if(!empty($years_stats)){
				extract($years_stats);

				$price = get_woocommerce_currency_symbol().$total_amount;
			
				$title .= "Completed Orders ($state / $year) <br> $price <div style='float:right;'>$total_orders</div> <span style='clear:both;'></span>";


			}


			return $title;

		}
	}

	add_action('woocommerce_checkout_create_order', 'wpsis_record_http_referer', 20, 2);
	
	function wpsis_record_http_referer( $order, $data ) {
		$HTTP_REFERER_WC = WC()->session->get( 'HTTP_REFERER' );
		$order->update_meta_data( '_HTTP_REFERER', $HTTP_REFERER_WC );
	}	
	
	add_filter( 'manage_edit-shop_order_columns', 'wpsis_new_order_column' );
	
	function wpsis_new_order_column( $columns ) {
		$columns['REFERER'] = __('REFERER');
		$wpsis_record_order_browser = (get_option('wpsis_record_order_browser')=='yes');
		if($wpsis_record_order_browser){
			$columns['wpsis_browser'] = __('Browser', 'si-system');
		}
		return $columns;
	}
	
	add_filter( 'manage_shop_order_posts_custom_column', 'wpsis_manage_shop_order_posts_custom_column' );
	
	function wpsis_manage_shop_order_posts_custom_column( $column ) {
		global $post;
		
		switch($column){
			case 'REFERER':				

					$HTTP_REFERER = get_post_meta($post->ID, '_HTTP_REFERER', true);
					
					if($HTTP_REFERER){
						$ignore = array(
							home_url()
						);
						echo str_replace($ignore, '', $HTTP_REFERER);
					}
					
			break;
			case 'wpsis_browser':				
					$_wpsis_browser_used = get_post_meta($post->ID, '_wpsis_browser_used', true);
					
					if(is_array($_wpsis_browser_used) && !empty($_wpsis_browser_used)){
						echo '<small>'.$_wpsis_browser_used['browser'].' ('.$_wpsis_browser_used['browser_version'].')<br />';
						echo $_wpsis_browser_used['os_platform'].' ('.$_wpsis_browser_used['device'].')</small>';
						
					}
			break;
				
		}
	}
	
	function wpsis_monitoring_log(){
		
		
		if(isset($_GET['wpsis_monitoring_log'])){
			wpsis_pree(is_404());
			wpsis_pree($_SERVER);
		}
		$log = get_option('wpsis_monitoring');
		$log = is_array($log)?$log:array();
		$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		$only_not_found = (get_option('wpsis_not_found')?is_404():true);
		if($REMOTE_ADDR && $only_not_found){
			$SCRIPT_URL = (isset($_SERVER['SCRIPT_URL'])?$_SERVER['SCRIPT_URL']:'');
			$ignore = array(
				'/wp-login.php',
				'/xmlrpc.php',
				'/wp-json/wc-admin/options',
				'/wp-cron.php',
				'/wp-json/wc-analytics/admin/notes',
				'/wp-json/oembed/1.0/embed',
				'/wc-api/WC_Gateway_Paypal/',
				'/feed/',
				'/wp-admin/',
				'/wp-includes/',
			);
			$ignore_initials = array();
			$SCRIPT_URL_initial = explode('/', $SCRIPT_URL);
			if(!empty($SCRIPT_URL_initial) && substr($SCRIPT_URL, 0, 1)=='/'){
				$SCRIPT_URL_initial = array_filter($SCRIPT_URL_initial);
				$SCRIPT_URL_initial = '/'.current($SCRIPT_URL_initial);				
			}
			if(!empty($ignore)){
				foreach($ignore as $ignore_item){
					$ignore_item_initial = explode('/', $ignore_item);
					if(!empty($ignore_item_initial) && substr($ignore_item, 0, 1)=='/'){
						$ignore_item_initial = array_filter($ignore_item_initial);
						$ignore_item_initial_uri = '/'.current($ignore_item_initial);	
						
						if(!in_array($ignore_item_initial_uri, $ignore_initials)){
							$ignore_initials[] = $ignore_item_initial_uri;
						}
					}
				}
			}
			$ignore[] = '/';
			//pre($SCRIPT_URL_initial);pre($ignore_initials);
			if($SCRIPT_URL && !in_array($SCRIPT_URL, $ignore) && !in_array($SCRIPT_URL_initial, $ignore_initials)){
				
				$log[$REMOTE_ADDR] = array(
					isset($_SERVER['HTTP_FROM'])?$_SERVER['HTTP_FROM']:'',
					$SCRIPT_URL,
					isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'',
					isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'',
					isset($_SERVER['HTTP_AMP_CACHE_TRANSFORM'])?$_SERVER['HTTP_AMP_CACHE_TRANSFORM']:'',
					time(),
					is_404(),
				);
				
				update_option('wpsis_monitoring', $log);
			
			}
		}
	}
	
	add_action( 'wp', 'wpsis_referer_settings' );
	
	function wpsis_referer_settings() {  
		
		if(!is_admin() && class_exists( 'WC_Session_Handler' ) && (get_option('wpsis_monitoring_status')=='yes')){
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
			$HTTP_REFERER = (array_key_exists('HTTP_REFERER', $_SERVER)?$_SERVER['HTTP_REFERER']:'');
			$HTTP_REFERER_WC = WC()->session->get( 'HTTP_REFERER' );
			
			if(!$HTTP_REFERER_WC && $HTTP_REFERER){
				WC()->session->set('HTTP_REFERER', $HTTP_REFERER);
			}
			wpsis_monitoring_log();
			if(isset($_GET['HTTP_REFERER'])){
				pree($HTTP_REFERER);
				pree($HTTP_REFERER_WC);exit;
			}
			
		}
	}	
	add_action('wp_ajax_wpsis_update_traffic', 'wpsis_update_traffic');
	function wpsis_update_traffic(){
		
		 if(isset($_POST['wpsis_update_option_nonce'])){

			$nonce = sanitize_wpsis_data($_POST['wpsis_update_option_nonce']);

			if ( ! wp_verify_nonce( $nonce, 'wpsis_product_update_action' ) ){

			   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
			}
			$update = false;
			
			if(array_key_exists('wpsis_index', $_POST)){
				$wpsis_index = sanitize_wpsis_data($_POST['wpsis_index']);
				
				$wpsis_type = (array_key_exists('wpsis_type', $_POST)?sanitize_wpsis_data($_POST['wpsis_type']):'');
				//wpsis_pree($wpsis_index);
				$wpsis_monitoring = get_option('wpsis_monitoring');
				switch($wpsis_type){
					case 'valid':
						unset($wpsis_monitoring[$wpsis_index]);
						update_option('wpsis_monitoring', $wpsis_monitoring);
						$update = true;
					break;
					case 'invalid':
					break;
				}
				
			}
			
		 	echo  json_encode($update);

		}

		wp_die();

	}
	
	add_action('wp_ajax_wpsis_update_monitoring', 'wpsis_update_monitoring');
	function wpsis_update_monitoring(){
		
		 if(isset($_POST['wpsis_update_option_nonce'])){

			$nonce = sanitize_wpsis_data($_POST['wpsis_update_option_nonce']);

			if ( ! wp_verify_nonce( $nonce, 'wpsis_product_update_action' ) ){

			   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
			}
			$update = false;
			
			if(array_key_exists('wpsis_monitoring', $_POST)){
				$wpsis_monitoring = sanitize_wpsis_data($_POST['wpsis_monitoring']);
				update_option('wpsis_monitoring_status', $wpsis_monitoring);
				$update = true;
				$wpsis_clear = (array_key_exists('wpsis_clear', $_POST)?sanitize_wpsis_data($_POST['wpsis_clear']):'');
				if($wpsis_clear=='yes'){
					delete_option('wpsis_monitoring');
				}
				
			}
			
		 	echo  json_encode($update);

		}

		wp_die();

	}	
	
	add_action('wp_ajax_wpsis_record_order_browser', 'wpsis_record_order_browser');
	function wpsis_record_order_browser(){
		
		 if(isset($_POST['wpsis_update_option_nonce'])){

			$nonce = sanitize_wpsis_data($_POST['wpsis_update_option_nonce']);

			if ( ! wp_verify_nonce( $nonce, 'wpsis_product_update_action' ) ){

			   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
			}
			$update = false;
			
			if(array_key_exists('wpsis_record_order_browser', $_POST)){
				$wpsis_record_order_browser = sanitize_wpsis_data($_POST['wpsis_record_order_browser']);
				update_option('wpsis_record_order_browser', $wpsis_record_order_browser);
				$update = true;
				
			}
			
		 	echo  json_encode($update);

		}

		wp_die();

	}		
	
	add_action('wp_ajax_wpsis_update_not_found', 'wpsis_update_not_found');
	function wpsis_update_not_found(){
		
		 if(isset($_POST['wpsis_update_option_nonce'])){

			$nonce = sanitize_wpsis_data($_POST['wpsis_update_option_nonce']);

			if ( ! wp_verify_nonce( $nonce, 'wpsis_product_update_action' ) ){

			   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
			}
			$update = false;
			
			if(array_key_exists('wpsis_not_found', $_POST)){
				$wpsis_not_found = sanitize_wpsis_data($_POST['wpsis_not_found']);
				update_option('wpsis_not_found', $wpsis_not_found);
				$update = true;
				
			}
			
		 	echo  json_encode($update);

		}

		wp_die();

	}		
	
	function wpsis_get_browser_info(){
		$browserInfo = array('user_agent'=>'','browser'=>'','browser_version'=>'','os_platform'=>'','pattern'=>'', 'device'=>'');
	
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		$bname = 'Unknown';
		$ub = 'Unknown';
		$version = "";
		$platform = 'Unknown';
	
		$deviceType='Desktop';
	
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$u_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($u_agent,0,4))){
	
			$deviceType='Mobile';
	
		}
	
		if($_SERVER['HTTP_USER_AGENT'] == 'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10') {
			$deviceType='Tablet';
		}
	
		if(stristr($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0(iPad;')) {
			$deviceType='Tablet';
		}
	
		//$detect = new Mobile_Detect();
		
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
	
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
	
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
	
		// Next get the name of the user agent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
		{ 
			$bname = 'IE'; 
			$ub = "MSIE";
	
		} else if(preg_match('/Firefox/i',$u_agent))
		{ 
			$bname = 'Mozilla Firefox'; 
			$ub = "Firefox"; 
	
		} else if(preg_match('/Chrome/i',$u_agent) && (!preg_match('/Opera/i',$u_agent) && !preg_match('/OPR/i',$u_agent))) 
		{ 
			$bname = 'Chrome'; 
			$ub = "Chrome"; 
	
		} else if(preg_match('/Safari/i',$u_agent) && (!preg_match('/Opera/i',$u_agent) && !preg_match('/OPR/i',$u_agent))) 
		{ 
			$bname = 'Safari'; 
			$ub = "Safari"; 
	
		} else if(preg_match('/Opera/i',$u_agent) || preg_match('/OPR/i',$u_agent)) 
		{ 
			$bname = 'Opera'; 
			$ub = "Opera"; 
	
		} else if(preg_match('/Netscape/i',$u_agent)) 
		{ 
			$bname = 'Netscape'; 
			$ub = "Netscape"; 
	
		} else if((isset($u_agent) && (strpos($u_agent, 'Trident') !== false || strpos($u_agent, 'MSIE') !== false)))
		{
			$bname = 'Internet Explorer'; 
			$ub = 'Internet Explorer'; 
		} 
		
	
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
	
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
	
			} else {
				$version= @$matches['version'][1];
			}
	
		} else {
			$version= $matches['version'][0];
		}
	
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
	
		return array(
			'user_agent' => $u_agent,
			'browser'      => $bname,
			'browser_version'   => $version,
			'os_platform'  => $platform,
			'pattern'   => $pattern,
			'device'    => $deviceType
		);
	}
	// Add custom checkout field value as custom order meta data
	add_action( 'woocommerce_checkout_create_order', 'wpsis_woocommerce_checkout_create_order' );
	function wpsis_woocommerce_checkout_create_order( $order ) {
		$wpsis_record_order_browser = (get_option('wpsis_record_order_browser')=='yes');
		if($wpsis_record_order_browser){
			$get_browser_info = wpsis_get_browser_info();
			$order->update_meta_data( '_wpsis_browser_used', $get_browser_info );
		}
	}