<?php defined( 'ABSPATH' ) or die( 'No fankari bachay!' );
/*
Plugin Name: Shop Information System
Plugin URI: http://androidbubble.com/blog/shop-information-system
Description: An intuitive way of measuring and controlling your declines in sales due to external factors like COVID-19 situation.
Author: Fahad Mahmood
Version: 1.0.6
Text Domain: si-system
Domain Path: /languages
Author URI: https://profiles.wordpress.org/fahadmahmood/
License: GPL2
	
This WordPress Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This free software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.	
*/
//    return;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if(!function_exists('wpsis_check_plugin_active_status')){
        function wpsis_check_plugin_active_status($plugin = ''){


            $wpsis_active_plugins = get_site_option( 'active_sitewide_plugins' );
            $wpsis_active_plugins = is_array($wpsis_active_plugins)?$wpsis_active_plugins:array();
            $wpsis_network_active_plugins = is_array($wpsis_active_plugins)?apply_filters( 'active_plugins', array_keys($wpsis_active_plugins) ):array();
            $wpsis_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

            $plugin_status = ((is_multisite() && in_array($plugin, $wpsis_network_active_plugins))
                ||
                in_array($plugin, $wpsis_active_plugins)
            );

            return $plugin_status;

        }
    }	

	
	global $wp_sis_data, $wp_sis_pro, $wp_sis_premium_link, $wp_sis_dir, $wp_sis_levels, $wp_sis_versions_type, $wp_sis_url, $wp_sis_icon_sub_path, $wp_sis_options, $wpsis, $wp_sis_items_per_page, $wp_sis_chart_colors, $wpsis_short_script_obj;
	global $all_wpsis_state, $all_wpsis_year, $quarterly_wpsis_state, $quarterly_wpsis_year, $wpsis_redirection_status, $wp_sis_api_valid_keys;
	
	
	$wpsis_redirection_status = wpsis_check_plugin_active_status('redirection/redirection.php');
	
	$wp_sis_data = get_plugin_data(__FILE__);
	$wp_sis_dir = plugin_dir_path( __FILE__ );
    $wp_sis_url = plugin_dir_url( __FILE__ );
	$wp_sis_versions_type = get_option('wpsis_versions_type', 'old');
    $wp_sis_icon_sub_path = 'img/filetype-icons/';
    $wp_sis_options = get_option('wpsis_options', array());
    $wp_sis_items_per_page= array_key_exists('items_per_page', $wp_sis_options) ? $wp_sis_options['items_per_page'] : 100;
    $wp_sis_chart_colors = array();

	$all_wpsis_state = get_option('all_wpsis_state', true);
	$all_wpsis_year = get_option('all_wpsis_year', true);
	$quarterly_wpsis_state = get_option('quarterly_wpsis_state', true);
	$quarterly_wpsis_year = get_option('quarterly_wpsis_year', true);
	
	$wp_sis_api_valid_keys = array(					
		'action' => array('type'=>'string', 'options'=>'get|set'),
		'item' => array('type'=>'string', 'options'=>'products|categories|pages|images|blog', 'tooltip'=>__('', 'si-system')),
		'format' => array('type'=>'string', 'options'=>'json|default'),
	);

    $wp_sis_premium_link = 'https://shop.androidbubbles.com/product/shop-information-system';//https://shop.androidbubble.com/products/wordpress-plugin?variant=36439508156571';//
	
	
	$wp_sis_pro_file = $wp_sis_dir.'pro/wp-sis-pro.php';

    

	if(!class_exists('WPSIS')){

        include_once 'inc/classes/WPSIS.php';

    }

    $wpsis = new WPSIS();


		

	include_once 'inc/common.php';

    $wp_sis_pro = file_exists($wp_sis_pro_file);
    if($wp_sis_pro){
        include($wp_sis_pro_file);
    }	
	
	include_once('inc/functions.php');
	
	//if(is_admin()){
	//	wpsis_pre($quarterly_wpsis_state.' - '.$quarterly_wpsis_year.' - '.$all_wpsis_state.' - '.$all_wpsis_year);
	//}	

    function wpsis_plugin_links($links) {
        global $wp_sis_premium_link, $wpsisi_pro;

        $settings_link = '<a href="'.admin_url('admin.php?page=wpsis').'">'.__('Settings', 'si-system').'</a>';

        if($wpsisi_pro){

            array_unshift($links, $settings_link);

        }else{

            $wpdocs_premium_link = '<a href="'.esc_url($wp_sis_premium_link).'" title="'.__('Go Premium', 'si-system').'" target="_blank">'.__('Go Premium', 'si-system').'</a>';
            array_unshift($links, $settings_link, $wpdocs_premium_link);

        }


        return $links;
    }

	if(is_admin()){
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'wpsis_plugin_links' );
	}


    function wpsis_rand_color() {


        return  '#' . str_pad(dechex(mt_rand(0x111111, 0x999999)), 6, '0', STR_PAD_LEFT);

    }

	function wpsis_get_unique_color(){

        global $wp_sis_chart_colors;

        $unique_color = wpsis_rand_color();

        while (in_array($unique_color, $wp_sis_chart_colors)){
            $unique_color = wpsis_rand_color();
        }


        $wp_sis_chart_colors[] = $unique_color;

        return $unique_color;

    }

//    echo wpsis_get_unique_color();exit;










	