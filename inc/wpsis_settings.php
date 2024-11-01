<?php

    global $wp_sis_url, $wpsis, $wp_sis_items_per_page, $wpsis_redirection_status, $wp_sis_api_valid_keys, $wp_sis_pro, $wp_sis_premium_link;

    wpsis_downward_compatibility();


    $pages_array = array(5, 10, 50, 100, 250, 500, 1000);

    $wp_sis_options = get_option('wpsis_options', array());

    //    pree($wp_sis_options);exit;
    $is_ajax = array_key_exists('ajax', $wp_sis_options);
    $is_ajax_url = array_key_exists('ajax_url', $wp_sis_options);
    $is_bootstrap = array_key_exists('bootstrap', $wp_sis_options);
    $is_bootstrap = empty($wp_sis_options) ? true: $is_bootstrap;
    $is_file = array_key_exists('file_upload', $wp_sis_options);
    $is_current_user_files = array_key_exists('current_user_files', $wp_sis_options);
    $is_del_from_front = array_key_exists('del_from_front', $wp_sis_options);
    $thumb_image = array_key_exists('thumb_image', $wp_sis_options);

    $details_date = array_key_exists('details_date', $wp_sis_options);
    $details_type = array_key_exists('details_type', $wp_sis_options);
    $details_size = array_key_exists('details_size', $wp_sis_options);

    $is_filename = array_key_exists('filename', $wp_sis_options);

    $is_searchbox = array_key_exists('searchbox', $wp_sis_options);

    $allowed_role = array_key_exists('allowed_role', $wp_sis_options) ? $wp_sis_options['allowed_role'] : array();
	$allowed_role = is_array($allowed_role)?$allowed_role:array();
    $default_ext = 'doc, docx, png, gif, bmp, jpg';
    $allowed_ext = array_key_exists('allowed_ext', $wp_sis_options) ? $wp_sis_options['allowed_ext'] : $default_ext;

	
	$history_products_data = $history_products_data = array();
	$total_products = $max_num_pages = 0;
	
	if(!isset($_GET['t']) || (isset($_GET['t']) && $_GET['t']==0)){ 

		$history_products_data = $wpsis->get_history_products();
		$history_products = $history_products_data->products;
	
		$total_products = $history_products_data->total;
		$max_num_pages = $history_products_data->max_num_pages;

	}

	$wpsis_monitoring = get_option('wpsis_monitoring');
	$wpsis_monitoring = (is_array($wpsis_monitoring)?$wpsis_monitoring:array());
	$wpsis_monitoring_status = (get_option('wpsis_monitoring_status')=='yes');
	$wpsis_record_order_browser = (get_option('wpsis_record_order_browser')=='yes');
	$wpsis_not_found = (get_option('wpsis_not_found')=='yes');


	$all_requests = get_option('wpsis_api_request_sources', array());
				
	$all_requests = (is_array($all_requests)?$all_requests:array());
	
	$validated_requests = get_option('wpsis_api_request_validated', array());
				
	$validated_requests = (is_array($validated_requests)?$validated_requests:array());
	
	//pree($validated_requests);
?>

<div class="wrap wpsis-wrapper">

    <h2 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active"><?php _e("Dashboard","si-system"); ?></a>
        <a class="nav-tab"><?php _e("Widgets","si-system"); ?></a>
        <a class="nav-tab traffic-monitoring-tab"><?php _e("Traffic Monitoring","si-system"); ?> <span class="wpsis-green">(<?php echo count($wpsis_monitoring); ?>)</span></a>
        <a class="nav-tab wpsis-api-requests-tab"><i class="fas fa-broadcast-tower"></i> <?php _e("Remote API","si-system"); ?> </a>
        <a class="nav-tab wpsis-redirects-tab"><i class="fas fa-route"></i> <?php _e("Redirects","si-system"); ?> </a>
        <a class="nav-tab" data-tab="help" data-type="free"><i class="far fa-question-circle"></i>&nbsp;<?php _e("Help", 'si-system'); ?></a>
    </h2>

    <div class="nav-tab-content si_section mt-2 hide">

        <div class="wpsis_vertical_menu d-none">
            <div class="btn-group-vertical ">
                <button type="button" class="btn btn-dark"><i class="fa fa-android"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-apple"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-cart-plus"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-calendar"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-android"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-android"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-android"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-android"></i></button>
                <button type="button" class="btn btn-dark"><i class="fa fa-cart-plus"></i></button>





            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="h4 text-center"> <?php _e("Sales vs. Prices","si-system"); ?> <small>(<?php _e("Analysis","si-system"); ?>)</small></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 ">


                        <div style="width: 100%">
                            <canvas id="wpsis_sales_chart"></canvas>
                        </div>





            </div>
            <div class="col-md-6">


                <div style="width: 100%">
                    <canvas id="wpsis_price_chart"></canvas>
                </div>


            </div>
        </div>

        <div class="row mt-3 wpsis_sorting_row hide">

            <div class="col-md-12 text-center">

                <button class="btn btn-primary selected wpsis_sorting_btn" data-type="alpha" data-sort_by="desc" title="<?php _e("Sort legends by alphabetical","si-system"); ?>">
                    <i class="fa fa-sort-alpha-up text-white sort_asc " ></i>
                    <i class="fa fa-sort-alpha-down-alt text-white sort_desc hide"></i>
                    <?php _e("Alphabetical","si-system"); ?>
                </button>
                <button class="btn btn-info wpsis_sorting_btn" data-type="sale" id="wpsis_reset_products" data-sort_by="desc" title="<?php _e("Sort legends by sale","si-system"); ?>">
                    <i class="fa fa-sort-amount-down-alt text-white sort_asc hide" ></i>
                    <i class="fa fa-sort-amount-up text-white sort_desc hide" ></i>
                    <?php _e("Sale","si-system"); ?>
                </button>
                <button class="btn btn-dark  wpsis_sorting_btn" data-type="price" data-sort_by="desc" title="<?php _e("Sort legends by price","si-system"); ?>">

                    <i class="fa fa-sort-amount-down-alt text-white sort_asc hide" ></i>
                    <i class="fa fa-sort-amount-up text-white sort_desc hide" ></i>
                    <?php _e("Price","si-system"); ?>

                </button>

            </div>

        </div>


        <div class="row wpsis_sale">





                <div class="col-md-12 mt-3 wpsis_legend_box" >

                    <div class="row">



                    </div>

                </div>



        </div>


        <hr />


        <div class="row">
            <div class="col-md-12">
                <div class="h4 pb-2"> <?php _e("Price Controls","si-system"); ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 text-center" style="line-height: 100%">

                <div >

                <button class="btn btn-dark w-25 wpsis_price_adjustment_btn selected" data-info="<?php _e("When select adjustment and press update button system will adjust the price as per given values","si-system"); ?>" data-type="adjustment" ><?php _e("Custom","si-system"); ?></button>
                <button class="btn btn-primary w-25 wpsis_price_adjustment_btn" data-info="<?php _e("When select normalize and press update button system will set the average price as per previous history","si-system"); ?>" data-type="normal"><?php _e("Normalize (Avg.)","si-system"); ?></button>
                <button class="btn btn-info w-25 wpsis_price_adjustment_btn" data-info="<?php _e("When select reset and press update button system will undo price to last price, Can undo till last entry in price history","si-system"); ?>" data-type="reset" id="wpsis_reset_products"><?php _e("Reset/Undo","si-system"); ?></button>

                </div>

                <div class="alert alert-info wpsis_price_adjustment_info mt-5">
                    <i class="fa fa-info-circle"></i>
                     <span><?php _e("When select adjustment and press update button system will adjust the price as per given values","si-system"); ?></span>
                </div>

            </div>
            <div class="col-md-6 ">
                <div class="row">

                    <div class="col-md-2"></div>

                    <div class="col-md-8">





                        <div class="wpsis_adjustment_group">

                            <div class="form-group">
                            <label for="wpsis_adjustment_by"><?php _e("Adjustment By","si-system"); ?></label>
                            <select class="form-control by wpsis_calculate_inputs" id="wpsis_adjustment_by">
                                <option value="flat"><?php _e("Flat","si-system"); ?></option>
                                <option value="percent"><?php _e("Percentage","si-system"); ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="wpsis_adjustment_type"><?php _e("Adjustment Type","si-system"); ?></label>
                            <select class="form-control type wpsis_calculate_inputs" id="wpsis_adjustment_type">
                                <option value="increase"><?php _e("Increase","si-system"); ?></option>
                                <option value="decrease"><?php _e("Decrease","si-system"); ?></option>
                            </select>
                        </div>


                        <div class="form-group wpsis_price_adjustment_group">

                            <label for="wpsis_price_adjustment"><?php _e("Price Adjustment","si-system"); ?></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-arrow-down type decrease hide text-danger"></i>
                                        <i class="fa fa-arrow-up type increase text-success"></i>
                                        <strong class="by percent ml-1 hide">%</strong>
                                        <strong class="by flat ml-1"><?php echo get_woocommerce_currency_symbol() ?></strong>
                                    </span>
                                </div>

                                <input type="number" value="0.25" step="0.25" min="0" class="form-control wpsis_calculate_inputs" id="wpsis_price_adjustment">

                            </div>

                        </div>
                        </div>
                        <button class="btn btn-primary wpsi_price_update" disabled><?php _e("Update","si-system"); ?></button>
                    </div>


                </div>



            </div>
        </div>


        <div class="row mt-3">
            <div class="col-md-12">


                <div class="alert alert-warning alert-dismissible fade show wpsis_sale_warning" role="alert" style="display: none;">
                    <span class="text">
                    <?php _e("One or more products are on sale and new price is greater than or equal to regular price so products are removed from sale and use regular price for future.","si-system"); ?>
                   </span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>


                <div class="alert alert-warning alert-dismissible fade show wpsis_alert_warning" role="alert" style="display: none;">
                    <span class="text">
                    <?php _e("Some products will have no effect after update you can unselect these products from selection","si-system"); ?>
                   </span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="alert alert-danger alert-dismissible fade show wpsis_alert_danger" role="alert" style="display: none;">
                    <?php _e("One or more products price will become 0 or less than 0. Please review your selected products before proceed","si-system"); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="alert alert-success alert-dismissible fade show wpsis_all_update" role="alert" style="display: none;">
                    <?php _e("All products are updated successfully.","si-system"); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="alert alert-danger alert-dismissible fade show wpsis_all_not_update" role="alert" style="display: none;">
                    <?php _e("Some products are not updated please see the details below.","si-system"); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

            </div>
        </div>




        <hr />


        <div class="row mt-3">
            <div class="col-md-12">
                <div class="h4"> <?php _e("Price History","si-system"); ?></div>
            </div>
        </div>


        <div class="row mt-3 justify-content-center">
            <div class="col-lg-9 col-md-8">

                <?php

                if($max_num_pages > 1):

                ?>

                <nav aria-label="...">
                    <ul class="wpsis_pagination pagination justify-content-center" data-maxPage="<?php echo $max_num_pages; ?>">
                        <li class="page-item disabled" data-page="previous">
                            <a class="page-link" href="" tabindex="-1" ><?php _e("Previous","si-system"); ?></a>
                        </li>

                        <?php

                            for ($i = 1; $i <= $max_num_pages; $i++){

                                $active = '';
                                if($i == 1){
                                    $active = 'active';
                                }

                                $spiner = '<div class="spinner-border text-primary" role="status" style="display:none;">
                                            <span class="sr-only">Loading...</span>
                                            </div>';

                                echo "<li class='page-item $active' data-page='$i'><a class='page-link' href='' ><span class='text'>$i</span> $spiner</a></li>";

                            }

                        ?>


                        <li class="page-item <?php echo $max_num_pages == 1 ? 'disabled': ''; ?>" data-page="next">
                            <a class="page-link " href="" ><?php _e("Next","si-system"); ?></a>
                        </li>
                    </ul>
                </nav>

                <?php

                    endif;

                ?>


            </div>

            <div class="col-lg-3 col-md-4 col-6 text-md-right text-center">

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="wpsis_options_items_per_page"><?php _e("Items per page","si-system"); ?></label>
                    </div>

                    <select class="custom-select" name="wpsis_options[items_per_page]" data-name="items_per_page" id="wpsis_options_items_per_page">

                        <?php


                                if(!empty($pages_array)){
                                    foreach ($pages_array as $page){


                                        $selected_page = $page == $wp_sis_items_per_page ? 'selected' : '';

                                        echo "<option value='$page' $selected_page>$page</option>";
                                    }
                                }


                        ?>



                    </select>

                </div>

            </div>
        </div>




        <div class="row mt-2">

            <div class="col-md-12">

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="bg-dark text-white">
                        <tr>
                            <th scope="col">
<!--                                <input type="checkbox" class="wpsis_calculate_inputs_all" > -->
                            </th>
                            <th scope="col"><?php _e("Product Name","si-system"); ?></th>
                            <th scope="col"><?php _e("Category","si-system"); ?></th>
                            <th scope="col"><?php _e("Type","si-system"); ?></th>
                            <th scope="col"><?php _e("Price","si-system"); ?></th>
                        </tr>
                        </thead>

<?php

						if(!isset($_GET['t']) || (isset($_GET['t']) && $_GET['t']==0)){ 
							$wpsis->get_history_products_html($history_products);
						}else{
?>
<tbody><tr><td colspan="5" align="center">
<div class="pt-5 pb-5">
<a href="/wp-admin/admin.php?page=wpsis" class="btn-lg btn-danger mt-5 mb-5" title="<?php _e("Click here to reload the products list","si-system"); ?>"><?php _e("Click here to refresh","si-system"); ?></a>
</div>
</td></tr>
</tbody>
<?php							
						}

?>

                    </table>
                </div>

            </div>

        </div>







    </div>

    <div class="nav-tab-content wpsis_widgets hide">



        
    
        <div class="row mt-3">
            <div class="col-md-12">

                    <?php echo do_shortcode('[WPSIS_ORDER_INFO type="default"]')  ?>
                
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="h4"><?php _e('Shortcode', 'si-system'); ?>:</div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <ul class="list-group">
                    <li class="list-group-item bg-warning">
                        <ul class="list-group">
                            <li class="list-group-item">
                                [WPSIS_ORDER_INFO type="default"] 
                                <br>
                                <br>
                                <strong>type:</strong> default, all, quarterly
                            </li>
                            </ul>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <div class="nav-tab-content wpsis_in_action settings_section hide mt-2">
    
<div class="form-check form-switch">
  <input class="form-check-input wpsis-monitoring" type="checkbox" id="wpsis-monitoring" <?php checked($wpsis_monitoring_status); ?> />
  <label class="form-check-label" for="wpsis-monitoring"><?php _e('Turn Monitoring ON', 'si-system'); ?></label>
</div>
<div class="form-check form-switch" title="<?php _e('Turn Browser Recording ON/OFF during WooCommerce Order Checkout', 'si-system'); ?>">
  <input class="form-check-input wpsis-order-browser" type="checkbox" id="wpsis-order-browser" <?php checked($wpsis_record_order_browser); ?> />
  <label class="form-check-label" for="wpsis-order-browser"><?php _e('Turn Browser Recording ON', 'si-system'); ?></label>
</div>
<div class="form-check form-switch">
  <input class="form-check-input wpsis-not-found" type="checkbox" id="wpsis-not-found" <?php checked($wpsis_not_found); ?> />
  <label class="form-check-label" for="wpsis-not-found"><?php _e('404 Not Found Only?', 'si-system'); ?></label>
</div>



<?php
		
		if(!empty($wpsis_monitoring)){
			
			if($wpsis_redirection_status){
?>
<div class="alert alert-success" role="alert"><?php _e('Congratulations! Redirection plugin is installed and activated. You can enjoy the control over orphan query strings to repair your 404 not found damages.', 'si-system'); ?></div>
<?php				
			}else{
?>
<div class="alert alert-danger" role="alert"><?php _e('Optional! Redirection plugin can be installed and activated to unlock more features.', 'si-system'); ?> <a class="btn-sm btn-danger" href="<?php echo admin_url(); ?>/plugin-install.php?s=Redirection%20-%20Manage%20301%20redirections%20-%20John%20Godley&tab=search&type=term" target="_blank"><?php _e('Click here to install & activate', 'si-system'); ?></a></div>
<?php				
			}
?>

<ul>
<?php
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			$wpsis_monitoring_unique = array();
			$wpsis_monitoring_extra = array();
			foreach($wpsis_monitoring as $i=>$wpsis_traffic_data){ if(in_array($wpsis_traffic_data[1], $wpsis_monitoring_unique)){ $wpsis_monitoring_extra[] = $i; continue; } $wpsis_monitoring_unique[] = $wpsis_traffic_data[1];
?>
<li data-index="<?php echo $i; ?>" class="<?php echo ($wpsis_traffic_data[6]?'wpsis-404':''); ?>"><span><?php echo ($wpsis_traffic_data[5]?date($date_format.' '.$time_format, $wpsis_traffic_data[5]):''); echo ($wpsis_traffic_data[6]?'<i title="'.__('404 - Page not found', 'si-system').'" class="fas fa-map-signs wpsis-red"></i>':''); ?></span>
<a href="<?php echo home_url().''.$wpsis_traffic_data[1]; ?>" target="_blank"><?php echo $wpsis_traffic_data[1]; ?></a><?php echo ($wpsis_traffic_data[2]?' - <a href="'.$wpsis_traffic_data[2].'" style="font-size:12px;" target="_blank">'.$wpsis_traffic_data[2].'</a>':''); ?>
<br /><small><?php echo $wpsis_traffic_data[3]; ?></small><br />
<div><button type="button" class="btn-sm btn-success ml-1 wpsis-valid"><?php _e('I am happy with it!', 'si-system'); ?></button> <button type="button" class="btn-sm btn-danger ml-1 wpsis-invalid"><?php _e('Redirect it to?', 'si-system'); ?></button> <button data-copy="<?php _e('Copy', 'si-system'); ?>" data-copied="<?php _e('Copied!', 'si-system'); ?>" data-str="<?php echo $wpsis_traffic_data[1]; ?>" type="button" class="btn-sm btn-warning wpsis-copy"><?php _e('Copy', 'si-system'); ?></button></div>
</li>
<?php				
			}
			
			if(!empty($wpsis_monitoring_extra)){
				//pree($wpsis_monitoring);pree($wpsis_monitoring_extra);exit;
				//pree(count($wpsis_monitoring));
				foreach($wpsis_monitoring_extra as $ip){
					if(array_key_exists($ip, $wpsis_monitoring)){
						unset($wpsis_monitoring[$ip]);
					}
				}
				//pree(count($wpsis_monitoring));
				$wpsis_monitoring = update_option('wpsis_monitoring', $wpsis_monitoring);
			}
?>
</ul>
<?php			
		}else{
			if($wpsis_monitoring_status){
?>
<div class="alert alert-info" role="alert"><?php _e('Please wait, system is working, results will start appearing soon.', 'si-system'); ?></div>
<?php				
			}else{
?>
<div class="alert alert-primary" role="alert"><?php _e('Please turn ON monitoring to see the traffic trends.', 'si-system'); ?></div>
<?php				
				
			}
			
		}
?>

    </div>
    
    <div class="nav-tab-content container-fluid hide" data-content="api">

        <?php include_once('wpsis-api.php'); ?>

    </div>

    <div class="nav-tab-content container-fluid hide" data-content="redirects">

        <?php include_once('wpsis-redirects-tab.php'); ?>

    </div>
    
    
    
    <div class="nav-tab-content container-fluid hide" data-content="help">

        <div class="row mt-3 si_help_section">
        
        	<ul class="position-relative">
            	<li><a class="btn btn-sm btn-info" href="https://wordpress.org/support/plugin/shop-information-system/" target="_blank"><?php _e('Open a Ticket on Support Forums', 'si-system'); ?></a></li>
                <li><a class="btn btn-sm btn-warning" href="http://demo.androidbubble.com/contact/" target="_blank"><?php _e('Contact Developer', 'si-system'); ?></a><i class="fas fa-headset"></i></li>
                <li><iframe class="hide" width="560" height="315" src="https://www.youtube.com/embed/N-ewX28pLXs" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></li>
			</ul>                
        </div>

    </div>


    <div class="modal" id="wpsis_load_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajax_load_modalLabel" >
        <div class="modal-dialog" role="document" style="max-width: 50px;">
            <div class="modal-content" style="margin-top: 45vh; width: max-content">

                <img src="<?php echo  $wp_sis_url ?>img/loader.gif" style="width: 50px; height: 50px"/>

            </div>
        </div>
    </div>



</div>


<script type="text/javascript" language="javascript">

    jQuery(document).ready(function($){


        <?php

        if(isset($_GET['t'])):

            $tab = sanitize_wpsis_data( $_GET['t']);

        ?>



        $('.nav-tab-wrapper .nav-tab:nth-child(<?php echo $tab+1; ?>)').click();

        <?php else: ?>
		$('.nav-tab-wrapper .nav-tab:nth-child(1)').click();
		<?php endif; ?>

    });

</script>