jQuery(document).ready(function($){

    $('body').on('click', '.wpsis_title_container .wpsis_dropdown_content li:not(.disabled)', function(){

        var parent_li = $(this).parents('.wpsis:first');
        var parent_wrapper = $(this).parents('.wpsis_title_container:first');
        var loading_img = parent_wrapper.find('.wpsis_loading img');
        var parent_content = $(this).parents('ul.wpsis_dropdown_content:first');
        var this_type = parent_content.attr('data-type');
        var this_all = parent_content.find('li');
        var this_view = parent_wrapper.find('.wpsis_current[data-type="'+this_type+'"]');
        var this_key = $(this).attr('data-key');

        if(this_view.text().trim() == this_key){
            return;
        }

        this_view.html(this_key);
        this_all.removeClass('active');
        $(this).addClass('active');
        parent_content.hide();
        // loading_img.show();

        var current_state = parent_wrapper.find('.wpsis_current[data-type="states"]').text().trim();
        var current_year = parent_wrapper.find('.wpsis_current[data-type="years"]').text().trim();
        var li_type = parent_li.hasClass('wpsis_all') ? 'wpsis_all' : 'wpsis_quarterly';

        var current_url = new URL(window.location.href);

        current_url.searchParams.set('wpsis_state', current_state);
        current_url.searchParams.set('wpsis_year', current_year);
        current_url.searchParams.set('wpsis_type', li_type);

        loading_img.show();
        var ajax_data = {
            action: 'wpsis_get_update_order_states',
            wpsis_year: current_year, 
            wpsis_state: current_state, 
            wpsis_type: li_type, 
            wpsis_nonce: wpsis_obj.wpsis_nonce
        }


        $.post(ajaxurl, ajax_data, function(resp, code){
            
            loading_img.hide();
            
            if(code == 'success' && resp){
                parent_li.find('.woocommerce-summary__item-data').remove();
                parent_wrapper.after(resp);
            }        

        });

      


    });
    $('body').on('click', '.wpsis_title_container .wpsis_current', function(){
    
        var type = $(this).data('type');
        var parent_wrapper = $(this).parents('.wpsis_title_container:first');
        $('.wpsis_title_container .wpsis_current').attr('data-show', false);
    
        var all_content = $('ul.wpsis_dropdown_content');
        var dropdown_content_visible = parent_wrapper.find('ul.wpsis_dropdown_content[data-type="'+type+'"]:visible');
        var dropdown_content = parent_wrapper.find('ul.wpsis_dropdown_content[data-type="'+type+'"]');
        all_content.hide();

        if(dropdown_content_visible.length > 0){
        }else{
            $(this).attr('data-show', true);
            dropdown_content.show();
        }
    
    });







});

