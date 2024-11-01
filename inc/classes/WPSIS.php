<?php


class WPSIS
{


    /**
     * WPSIS constructor.
     */
    public function __construct()
    {

        add_action('admin_init', array($this, 'snap_products_prices'));
        add_action('wp_ajax_wpsis_get_paginated_table', array($this, 'wpsis_get_paginated_table'));
        add_action('wp_ajax_wpsis_update_product_price_bulk', array($this, 'wpsis_update_product_price_bulk'));

    }



    public function wpsis_get_paginated_table(){


        if(!empty($_POST) && isset($_POST['load_page'])){

            $page_number = sanitize_wpsis_data($_POST['load_page']);

            $history_products = $this->get_history_products($page_number);
            $products = $history_products->products;

            ob_start();

            $this->get_history_products_html($products);


            $content = ob_get_clean();

            echo $content;

            wp_die();


        }

    }


    /**
     * On first time load take a snap of product's prices and save it in product meta with time stamp;
     *
     */

    public function snap_products_prices(){

        $is_product_snap_taken = get_option('wpsis_product_snap_taken');
        $wp_sis_product_snap_info = get_option('wpsis_product_snap_info', array());

        if($is_product_snap_taken){return;}



        if(isset($wp_sis_product_snap_info['page_done'])){
            $current_page = $wp_sis_product_snap_info['page_done']+1;
        }else{
            $current_page = 1;
        }

        $first_attempt_time = isset($wp_sis_product_snap_info['first_attempt_time']) ? $wp_sis_product_snap_info['first_attempt_time'] : time();


        $year = Date('Y', $first_attempt_time);
        $month = Date('m', $first_attempt_time);
        $hour = Date('H', $first_attempt_time);
        $minute = Date('i', $first_attempt_time);
        $second = Date('s', $first_attempt_time);



        $time_array = compact('year', 'month', 'hour', 'minute', 'second');



        $args = array(

            'limit' => 100,
            'paginate' => true,
            'page' => $current_page,
            'wpsis_date_query' => array(

                    'before' => $time_array,
            )
        );




        $wc_products_data = wc_get_products($args);



        $wc_products = $wc_products_data->products;
        $max_page = $wc_products_data->max_num_pages;


        if(empty($wc_products)){return;}


        foreach ($wc_products as $product){

            $parent_product_id = $product->get_id();
            $parent_product_price = $product->get_price();



            if(!$product->is_purchasable()){

                continue;
            }


            if($product->has_child()){

                $product_children = $product->get_children();


                foreach ($product_children as $v_product_id){

                    $error = false;

                    try {

                        $variation_product = new WC_Product_Variation($v_product_id);
                        $error = false;


                    }catch (Exception $e){

                        $error = true;

                    }

                    if($error){

                        try {

                            $variation_product = new WC_Product($v_product_id);
                            $error = false;



                        }catch (Exception $e){

                            $error = true;



                        }

                    }

                    if(!$variation_product->is_purchasable()){

                        continue;
                    }

                    if(!$error){

                        $v_product_price = $variation_product->get_price();
                        $this->update_product_price_history($v_product_id, $v_product_price);
                    }



                }


            }


            $this->update_product_price_history($parent_product_id, $parent_product_price);


        }


        $wp_sis_product_snap_info['first_attempt_time'] = $first_attempt_time;
        $wp_sis_product_snap_info['max_num_pages'] = $max_page;
        $wp_sis_product_snap_info['page_done'] = $current_page;

        if($max_page == $current_page){

            update_option('wpsis_product_snap_taken', true);

        }
        update_option('wpsis_product_snap_info', $wp_sis_product_snap_info);

    }

    public function update_product_price_history($product_id, $price){


        $product_price_history = get_post_meta($product_id, '_wpsis_product_price_history', true);
        $product_price_history = $product_price_history && is_array($product_price_history) ? $product_price_history : array();


        if(!empty($product_price_history)){

            $last_price = end($product_price_history);
            reset($product_price_history);

            if($last_price !== $price){

                if($price > $last_price){

                    $product_price_history[time()] = $price;

                }else{

                    if(($last_price-$price) >= 0){

                        $product_price_history[time()] = $price;

                    }else{

                        return false;
                    }
                }

            }

        }else{




            $product_price_history[time()] = $price;


        }

        return update_post_meta($product_id, '_wpsis_product_price_history', $product_price_history);

    }

    public function undo_product_price_history($product_id){


        $product_price_history = get_post_meta($product_id, '_wpsis_product_price_history', true);
        $product_price_history = $product_price_history && is_array($product_price_history) ? $product_price_history : array();




        if(!empty($product_price_history)){


//            $product_price_history_values = array_values($product_price_history);
            $product_price_history_keys = array_keys($product_price_history);
            $product_history_count = count($product_price_history);

            if($product_history_count > 1){

                $last_index = end($product_price_history_keys);

                unset($product_price_history[$last_index]);

            }else{


                return  'no_more';

            }

        }

        return update_post_meta($product_id, '_wpsis_product_price_history', $product_price_history);

    }

    public function get_history_products($page = 1){

        global $wp_sis_items_per_page;



        $args = array(

            'limit' => $wp_sis_items_per_page,
            'wpsis_product_price_history' => 'EXIST',
            'paginate' => true,
            'page' => $page,

        );

        $history_products =  wc_get_products($args);

//        pree($history_products);

		return $history_products;



    }

    public function get_history_products_child($product){

		global $wp_sis_items_per_page;
		 
        $products_child_array = array();

        if($product->has_child()){

            $product_children = $product->get_children();

			$items = 0;
			
            foreach ($product_children as $v_product_id){
				
				if($items>=$wp_sis_items_per_page){ continue; }

                $error = false;

                try {

                    $variation_product = new WC_Product_Variation($v_product_id);
                    $error = false;


                }catch (Exception $e){

                    $error = true;

                }

                if($error){

                    try {

                        $variation_product = new WC_Product($v_product_id);
                        $error = false;



                    }catch (Exception $e){

                        $error = true;



                    }

                }

                $get_history_var = get_post_meta($variation_product->get_id(), '_wpsis_product_price_history', true);

                if(!$get_history_var){ continue; }
				
				
				
				$items++;

				
                $products_child_array[] = $variation_product;



            }


        }

        return $products_child_array;

    }

    public function get_history_products_html($history_products){


        $used_colors = array();

        if(!empty($history_products)){

//                                $produt = new WC_Product(25);
//                                $produt->get_sl();
//
//                                wc_get_product_category_list()

            ?>

            <tbody id="wpsis_product_history_table">


            <?php
            $product_ids_array = array();

            foreach ($history_products as $history_product){



                $product_type = $history_product->get_type();
                $disabled_checkbox = $product_type == 'variable' ? 'disabled="disabled"' : '';
                $variable_bg = $product_type == 'variable' ? 'variable_product' : '';
                $is_variable = $product_type == 'variable';

                $product_history = get_post_meta($history_product->get_id(), '_wpsis_product_price_history', true);
                $product_history = $product_history ? $product_history : array();
                $product_history = json_encode($product_history);
                $product_history = base64_encode($product_history);

                $product_ids_array[] = $history_product->get_id();


                ?>


                <tr data-product="<?php echo $history_product->get_id(); ?>" class="wpsis_product_row <?php echo $variable_bg; ?>"
                    data-history='<?php echo $product_history;  ?>' data-price="<?php echo $history_product->get_price(); ?>"
                    data-type="<?php echo $history_product->get_type() ?>" data-regular_price="<?php echo $history_product->get_regular_price(); ?>"
                    data-sale_price="<?php echo $history_product->get_sale_price(); ?>" data-is_on_sale="<?php echo $history_product->is_on_sale(); ?>"
                    data-title="<?php echo $history_product->get_name(); ?>" data-color="<?php echo wpsis_get_unique_color(); ?>">


                    <th scope="row"><input type="checkbox" class="wpsis_calculate_inputs" <?php echo $disabled_checkbox ?>></th>
                    <td>
                        <a href="<?php echo get_edit_post_link($history_product->get_id()); ?>" target="_blank">
                            <?php echo $history_product->get_name(); ?>
                        </a>
                    </td>
                    <td><?php echo wc_get_product_category_list($history_product->get_id()); ?></td>
                    <td><?php echo $history_product->get_type(); ?></td>
                    <td>
                                            <span class="current_price">
                                                    <?php echo !$is_variable ? wc_price($history_product->get_price()) : ''; ?>
                                            </span>
                        <span class="ml-2 text-success new_price">

                                            </span>

                        <i class="ml-2 text-success fa fa-check-circle success">
                        </i>
                        <i class="ml-2 text-danger fa fa-times-circle error">
                        </i>
                    </td>
                </tr>


                <?php


                if($history_product->has_child()){

                    $child_products = $this->get_history_products_child($history_product);

                    if(!empty($child_products)){


                        foreach ($child_products as $child_product){

//
                            $product_ids_array[] = $child_product->get_id();


                            $child_product_history = get_post_meta($child_product->get_id(), '_wpsis_product_price_history', true);
                            $child_product_history = $child_product_history ? $child_product_history : array();
                            $child_product_history = json_encode($child_product_history);
                            $child_product_history = base64_encode($child_product_history);



                            ?>




                            <tr data-product="<?php echo $child_product->get_id(); ?>" class="wpsis_product_row variation_product"
                                data-history='<?php echo $child_product_history;  ?>' data-price="<?php echo $child_product->get_price(); ?>"
                                data-type="<?php echo $child_product->get_type() ?>" data-regular_price="<?php echo $child_product->get_regular_price(); ?>"
                                data-sale_price="<?php echo $child_product->get_sale_price(); ?>" data-is_on_sale="<?php echo $child_product->is_on_sale(); ?>"
                                data-title="<?php echo $child_product->get_name(); ?>" data-color="<?php echo wpsis_get_unique_color(); ?>">




                                <th scope="row"><input type="checkbox" class="wpsis_calculate_inputs"></th>
                                <td>
                                    <a href="<?php echo get_edit_post_link($history_product->get_id()); ?>" title="<?php echo $child_product->get_slug() ?>" target="_blank">
                                        <?php echo $child_product->get_name(); ?>
                                    </a>
                                </td>
                                <td><?php echo wc_get_product_category_list($history_product->get_id()); ?></td>
                                <td><?php echo $child_product->get_type(); ?></td>
                                <td>
                                                         <span class="current_price">
                                                        <?php echo wc_price($child_product->get_price()); ?>
                                                        </span>
                                    <span class="ml-2 text-success new_price">
                                                       </span>
                                    <i class="ml-2 text-success fa fa-check-circle success">
                                                       </i>
                                    <i class="ml-2 text-danger fa fa-times-circle error">
                                                       </i>
                                </td>
                            </tr>




                            <?php



                        }

                    }


                }



            }


            $products_sales = $this->get_sales_by_product_ids($product_ids_array);

            $products_sales_str = base64_encode(json_encode($products_sales));

            ?>

            </tbody>
            <tfoot style="display: none" class="wpsis_sales_data" data-sales="<?php echo $products_sales_str ?>">

            </tfoot>

            <?php


        }

    }

    public function wpsis_update_product_price_bulk(){


        if(!empty($_POST) && isset($_POST['wpsis_products_array'])){


            $return_array = array();


            if(!isset($_POST['wpsis_nonce']) || !wp_verify_nonce($_POST['wpsis_nonce'], 'wpsis_product_update_action')){


                wp_die(__('Sorry, your nonce did not verified', 'si-system'));


            }else{


                $wp_sis_products_array = sanitize_wpsis_data($_POST['wpsis_products_array']);
                $wp_sis_type = sanitize_wpsis_data($_POST['wpsis_type']);


                if(!empty($wp_sis_products_array)){


                    $products_to_update = $wp_sis_products_array['products'];

                    if(!empty($products_to_update)){


                        foreach ($products_to_update as $product){




                            $product_id = $product['product_id'];
                            $product_price = $product['new_price'];



                            $return_array[$product_id] = false;


//                            if($update_history){

                              $current_product = $this->wpsis_get_current_product($product_id);

                                if($current_product){


                                    $update_price = false;

                                    if($current_product->is_on_sale()){

                                        $regular_price =  get_post_meta($current_product->get_id(), '_regular_price', true);

                                        if($product_price < $regular_price){

                                            $update_price =    update_post_meta($current_product->get_id(), '_sale_price', $product_price);

                                        }else{


                                            $update_price_sale =    update_post_meta($current_product->get_id(), '_sale_price', '');
                                            $update_price_regular =    update_post_meta($current_product->get_id(), '_regular_price', $product_price);

                                            $update_price = $update_price_sale || $update_price_regular;


                                        }



                                    }else{

                                        $update_price =  update_post_meta($current_product->get_id(), '_regular_price', $product_price);

                                    }

                                    if($update_price){



                                        if($wp_sis_type == 'reset'){

                                            $update_history = $this->undo_product_price_history($product_id, $product_price);

                                        }else{

                                            $update_history = $this->update_product_price_history($product_id, $product_price);

                                        }


                                        if(!$update_history){

                                            $return_array[$product_id] = array('status' => 'false', 'msg' => __('No change in price', 'si-system'));

                                        }

                                        $update_price = update_post_meta($current_product->get_id(), '_price', $product_price);

                                    }



                                    if($update_price){

                                        if($update_history === 'no_more'){

                                            $return_array[$product_id] = array('status' => false, 'msg' => __('Product reached to last saved history', 'si-system'));


                                        }elseif ($update_history === true){

                                            $update_price_history = get_post_meta($product_id, '_wpsis_product_price_history', true);
                                            $update_price_history = base64_encode(json_encode($update_price_history));

                                            $current_product = $this->wpsis_get_current_product($product_id);

                                            $return_array[$product_id] = array(

                                                'status' => true,
                                                'msg' => __("Price updated successfully", 'si-system'),
                                                'price' => $product_price,
                                                'current_price' => wc_price($product_price),
                                                'is_on_sale' => $current_product->is_on_sale(),
                                                'regular_price' => $current_product->get_regular_price(),
                                                'sale_price' => $current_product->get_sale_price(),
                                                'history' => $update_price_history,
                                            );

                                        }else{

                                            $return_array[$product_id] = array('status' => false, 'msg' => __('No change in price', 'si-system'));

                                        }
                                    }else{


                                        $check_prev = !isset($return_array[$product_id]['status']);

                                        if($check_prev){
                                            $return_array[$product_id] = array('status' => false, 'msg' => __('No change in price', 'si-system'));
                                        }


                                    }



                                }

//                            }else{



//                            }

                        }

                    }

                }

            }

            wp_send_json($return_array);

        }


    }

    public function wpsis_get_current_product($product_id)
    {

        $error = false;
        $current_product = null;



        try {

            $current_product = new WC_Product($product_id);
            $error = false;



        }catch (Exception $e){

            $error = true;



        }



        if($error) {


            try {

                $current_product = new WC_Product_Variation($product_id);
                $error = false;


            } catch (Exception $e) {

                $error = true;

            }

        }


        return  $current_product;

    }


    public function get_sales_by_product_ids(array $product_ids){

        global $wpdb;

        $results = array();
        if(!empty($product_ids)){

            $product_ids_str = 'IN('.implode(', ', $product_ids).')';

            $query = "
                SELECT order_item_meta_2.meta_value as product_id, order_item_meta.meta_value as line_total, order_item_meta_3.meta_value as qty,
                 posts.post_date as `date`, posts.ID as `order`
                 FROM {$wpdb->prefix}woocommerce_order_items as order_items
        
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
                LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        
                WHERE 	posts.post_type 	= 'shop_order'	
                AND 	order_items.order_item_type = 'line_item'
                AND 	(order_item_meta_2.meta_key = '_product_id' || order_item_meta_2.meta_key = '_variation_id')
                AND 	order_item_meta_3.meta_key = '_qty'
                AND 	order_item_meta_2.meta_value $product_ids_str 
                AND 	order_item_meta.meta_key = '_line_total'
        
            ";

            $results = $wpdb->get_results($query);

        }

        return $results;

    }


}