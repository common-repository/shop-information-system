<?php 
	if(isset($_POST['wpsis-redirects'])){
		
		if ( ! isset( $_POST['wpsis_redirects_nonce_field'] ) 
			|| ! wp_verify_nonce( $_POST['wpsis_redirects_nonce_field'], 'wpsis_redirects_nonce_action' ) 
		) {
		   
		} else {
		   // process form data
		   $redirects = ($_POST['wpsis-redirects']);
		   
		   update_option('wpsis-redirects', $redirects);
		   
		}
	}
	
	$wpsis_redirects = get_option('wpsis-redirects', '');
	
?>
<label class="switch" style="float:right; margin:20px; 0 0 0">
  <input <?php checked(get_option('wpsis_redirects_status')==true); ?> name="wpsis-redirects-status" id="wpsis-redirects-status" value="yes" type="checkbox" data-on="<?php echo __('Enabled', 'si-system'); ?>" data-off="<?php echo __('Disabled', 'si-system'); ?>" />
  <span class="slider round"></span>
</label>
<form action="" method="post">
	<?php wp_nonce_field( 'wpsis_redirects_nonce_action', 'wpsis_redirects_nonce_field' ); ?>
    <div style="padding:40px 0 0 0">
    <ul>
        <li><textarea name="wpsis-redirects" style="height:600px; width:70%;"><?php echo $wpsis_redirects; ?></textarea></li>
        
        <li><input type="submit" class="btn btn-info" value="<?php esc_html_e('Save Changes', 'si-system'); ?>" /> <a class="btn btn-link" href="https://gumpyguy.wordpress.com/2024/06/12/404-not-found-shop-information-system-wordpress-plugin/" target="_blank">Read more..</a></li>
    </ul>
    </div>
</form>
