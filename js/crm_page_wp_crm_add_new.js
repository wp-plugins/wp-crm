jQuery(document).bind('wp_crm_value_changed', function(event, data) {

  var object = data.object;
  var parent = jQuery(object).parents('tr');

  //console.log(data.action);
  if(data.action == 'option_mousedown'){
    return;
  }
  
  if(jQuery(object).val() == '') {
    jQuery('.input_div', parent).hide();
    jQuery('.blank_slate', parent).show();
  }
  
});


jQuery(document).ready(function() {
 
  //** Verify deletion saving */
  jQuery('.submitdelete').click(function() {
  
    
    return confirm('Are you sure you want to delete user?');
  });
  
  
  //** Handles form saving */
  jQuery("#crm_user").submit(function(form) {
    return wp_crm_save_user_form(form);
  });
  
  jQuery('div.wp_crm_toggle_advanced_user_actions').click(function() {
    
    jQuery('div.wp_crm_advanced_user_actions').toggle();
    
  });
  
  jQuery('.form-table tr.not_primary').each(function() {

    //* Don't hide anything for checkboxes */      
    if(jQuery('div.wp_checkbox_input', this).length > 0) {
      return;
    }
    
    if(jQuery('input,textarea', this).val() == '') {
      jQuery('.input_div', this).hide();
      jQuery('.blank_slate', this).show();
    }
    
  });
    
  jQuery('tr.not_primary .wp_crm_input_wrap input,  tr.not_primary .wp_crm_input_wrap textarea').live('focusout', function() {
  
    var parent = jQuery(this).parents('.wp_crm_input_wrap');
 
    if(jQuery('select', parent).length) {
      //** Don't hide element if there's a select field in there */
      return;
    }
    
    jQuery(this).trigger('wp_crm_value_changed', {object: this, action: 'input_focusout'});
  });
  
  
  jQuery('tr.not_primary .wp_crm_input_wrap select,  tr.not_primary .wp_crm_input_wrap select').live('mousedown', function() {
    jQuery(this).trigger('wp_crm_value_changed', {object: this, action: 'option_mousedown'});
  });  
 
  jQuery( ".datepicker" ).datepicker({
    changeMonth: true,
    changeYear: true
  });
    
  
  jQuery(".wp_crm_truncated_show_hidden").click(function() {  
    var parent = jQuery(this).parent();    
    jQuery('.truncated_content:first', parent).toggle();  
  });

  jQuery(".wp_crm_show_message_options").click(function() {
      jQuery('.wp_crm_message_options').toggle();   
  });
  
  jQuery(".wp_crm_toggle_message_entry").click(function() {
    jQuery(".wp_crm_new_message").toggle();  
    jQuery(".wp_crm_new_message #wp_crm_message_content").focus();  
  });

  
  jQuery("#wp_crm_user_role").change(function() {
  
    jQuery(".wp_crm_user_entry_row").show();
    
    var new_setting = jQuery('option:selected', this).val();   
    
    jQuery(wp_crm_hidden_attributes[new_setting]).each(function(index,value) {    
      jQuery('tr.wp_crm_' + value + '_row').hide();    
    });
  
  });  
  
  
  jQuery("#wp_crm_add_message").click(function() {

    var user_id = wp_crm.user_id;
    
    var wp_crm_message_content = jQuery("#wp_crm_message_content").val();
    
    if(wp_crm_message_content == '') {
      return;
    }

    jQuery.post(ajaxurl,
      {
        action: 'wp_crm_insert_activity_message',
        time: jQuery('.wp_crm_message_options .datepicker').val(),
        content: wp_crm_message_content,
        user_id: user_id
      }, function(response) {

        if(response.success == 'true') {
          wp_crm_update_activity_stream();
          
          // Clear out message
          jQuery('#wp_crm_message_content').val('');
          jQuery('.wp_crm_new_message').hide();
          
        } else {

          alert('Could not save entry');
        }

      },
      "json"
    );

    return;

  });

  jQuery('.blank_slate').click(function() {

    var parent_row = jQuery(this).closest(".wp_crm_user_entry_row");

    jQuery('.input_div', parent_row).show();
    jQuery('input,textarea', parent_row).focus();
    jQuery('.blank_slate', parent_row).hide();


  });

  jQuery('.add_another').live("click", function() {


    var parent_row     =  jQuery(this).closest(".wp_crm_user_entry_row");
    var input_div       =  jQuery('.input_div:last', parent_row);
     var new_input_div   =  input_div.clone();
     
    jQuery('input', new_input_div).val('');
    // Get current ash
    var current_hash = jQuery('input', new_input_div).attr('random_hash');
    // Fix hashes
    var new_hash = Math.floor((9999)*Math.random()) + 1000;
    // Need a more elegant way of doing this
    
    if(jQuery('input', new_input_div).length) {
      jQuery('input', new_input_div).attr('random_hash', new_hash)
      var old_name = jQuery('input', new_input_div).attr('name');
      jQuery('input', new_input_div).attr('name', old_name.replace(current_hash, new_hash));    
 
    }

    if(jQuery('select', new_input_div).length) {
      jQuery('select', new_input_div).attr('random_hash', new_hash)
      var old_name = jQuery('select', new_input_div).attr('name');
       jQuery('select', new_input_div).attr('name', old_name.replace(current_hash, new_hash));      
    }
    
    
      
    // Insert row
      jQuery(new_input_div).insertAfter(input_div);

    // hide 'add another'
    jQuery(this).hide();


  });

  jQuery('div.allow_multiple input').live("keyup", function() {

    var parent_row =  jQuery(this).closest(".wp_crm_user_entry_row");

    if(jQuery(this).val() != '') {

      jQuery('.add_another', parent_row).show();

    } else {

      jQuery('.add_another', parent_row).hide();
    }
  });
});

  /**
   * Contact history and messages for a user
   *
   *
   */
  function wp_crm_update_activity_stream() {

    var user_id = jQuery("#user_id").val();
 
    jQuery.post(ajaxurl, {action: 'wp_crm_get_user_activity_stream', user_id: user_id}, function(response) {
      jQuery("#wp_crm_user_activity_stream tbody").html(response);
    });

  }

  /**
   * Contact history and messages for a user
   *
   *
   */
  function wp_crm_save_user_form(form) {
  
    var password_1 = jQuery("#wp_crm_password_1").val();
    var password_2 = jQuery("#wp_crm_password_2").val();
    
    //** Check if password has been entered and they match */
    if(password_1 != "") {
    
      if(password_1 != password_2) {
        jQuery(".wp_crm_advanced_user_actions").show();
        jQuery("#wp_crm_password_1").focus();
        return false;
      }
    
    }
    return true;
  }