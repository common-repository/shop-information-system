<div class="row mt-3 wpsis-api-requests">

<?php			

	if(!empty($wp_sis_api_valid_keys)){
		
		$wp_sis_api_valid_keys_str = array();
		foreach($wp_sis_api_valid_keys as $key=>$arr){
			$arr['values'] = explode('|', $arr['options']);
			$wp_sis_api_valid_keys_str[] = '<span>'.$key.'='.current($arr['values']).'</span>';
		}
?>
<div class="wpsis-api-urls">
	<ul>
    	<li><b><?php echo home_url(); ?>/?wpsis-api&</b><?php echo implode('<strong>&</strong>', $wp_sis_api_valid_keys_str); ?> <a style="display:none" href="https://www.youtube.com/embed/si_DUe-8ncY" target="_blank"><i class="fab fa-youtube"></i></a></li>
	</ul>        
</div>
<table cellpadding="0" cellspacing="0">
<?php		
		foreach($wp_sis_api_valid_keys as $param=>$param_data){
?>
<tr title="<?php echo (isset($param_data['tooltip'])?$param_data['tooltip']:''); ?>">
	<td><?php echo $param; ?></td><td><?php echo $param_data['type']; ?></td><td><?php echo $param_data['options']; ?></td>
</tr>    
<?php			
		}
?>
</table>
<?php		
	}
?>	
<input name="validate_request[]" type="checkbox" value="default" checked="checked" style="display:none" />
<table cellpadding="10" cellspacing="0" style="margin:40px 0 0 0">
    <thead>
        <tr>
            <th><?php echo __('Request Source', 'si-system'); ?></th>
            <th><?php echo __('Last Ping', 'si-system'); ?></th>
            <th><?php echo __('Allow/Reject?', 'si-system'); ?></th>
        </tr>                
    </thead>
    <tbody>
    	
        <?php if(!empty($all_requests)): foreach($all_requests as $timestamp=>$source): $valid = in_array($source, $validated_requests); ?>
        <tr>
            <td><?php echo $source; ?></td>
            <td><center><?php echo $timestamp?date('d M, Y h:i:s A', $timestamp):'-'; ?></center></td>
            <td><center>
            
            	<?php if($wp_sis_pro): ?>
                
                <a class="<?php echo $valid?'valid':'invalid'; ?>"><input name="validate_request[]" value="<?php echo $source; ?>" type="checkbox" <?php echo checked($valid); ?> /></a>
                
                <?php else: ?>
                
                <a href="<?php echo $wp_sis_premium_link; ?>" target="_blank" title="<?php echo __('Go Premium', 'si-system'); ?>"><i class="fas fa-lock"></i></a>
                
                <?php endif; ?>
            
            
            </center></td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>

</table>

        </div>