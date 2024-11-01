<?php
	add_action('admin_init', 'wpsis_version_type_update');
	
	function wpsis_version_type_update(){

				if(isset($_POST['version_type'])){			
					if ( 
						! isset( $_POST['version_type_nonce_field'] ) 
						|| ! wp_verify_nonce( $_POST['version_type_nonce_field'], 'version_type_action' ) 
					) {
					
					   wp_die(__('Sorry, your nonce did not verified', 'si-system'));
					
					} else {
					
					   // process form data
					

                            $version_type =  sanitize_wpsis_data($_POST['version_type']);
							
							update_option('wpsis_versions_type', $version_type);
							
							if($version_type == 'new')
							wp_redirect('options-general.php?page=wpsis');
							else
							wp_redirect('admin.php?page=wpsis-engine.php');
							
							exit;
						
					}
				}
	}
				
	function wpsis_downward_compatibility(){
		global $wp_sis_data, $wp_sis_versions_type, $wp_sis_pro, $wp_sis_url;
?>		
			<h3 class="pb-1 pt-3"><?php echo $wp_sis_data['Name']; ?> <?php echo '('.$wp_sis_data[__('Version', 'si-system')].($wp_sis_pro?') Pro':')'); ?> - <?php _e('Settings', 'si-system'); ?></h3>
            <?php if(!$wp_sis_pro): ?>
            <a style="float:right; position:relative; top:-40px; display:none;" href="<?php echo esc_url($wp_sis_premium_link); ?>" target="_blank"><?php _e('Go Premium', 'si-system'); ?></a>
            <?php endif; ?>
         

            
            <?php 
							

				$wp_sis_versions_type = get_option('wpsis_versions_type', 'old');
			?>
            
            <style type="text/css">
				.versions_type{
					background-color:#CCC;
					border-radius:4px;
					padding:10px 20px 20px 20px;
					display:none;
					
				}
				.versions_type label{
					font-weight:normal;
					padding:0;
					margin:0;					
				}
				.versions_type input[type="radio"]{
					padding:0;
					margin:0;
				}
				.update-nag{
					display:none;
				}
			</style>
            <script type="text/javascript" language="javascript">
				jQuery(document).ready(function($){
					$('.versions_type input[type="radio"]').on('click', function(){
						$('.versions_type > form').submit();
					});
				});				
			</script>
            
            <div class="versions_type">
            	<form action="" method="post">
                <?php wp_nonce_field( 'version_type_action', 'version_type_nonce_field' ); ?>
                </form>
            </div>

<?php
	}