jQuery(document).ready(function() {

  jQuery(".wp_crm_show_advanced").live("click", function() {
    
    var wrapper = jQuery(this).parents('tr.wp_crm_dynamic_table_row');
    
    jQuery('li.wp_crm_advanced_configuration', wrapper).toggle();
  
  });
  
  
  jQuery("#wp_crm_attribute_fields tbody").sortable();

  jQuery("#wp_crm_attribute_fields tbody tr").live("mouseover", function() {
    jQuery(this).addClass("wp_crm_draggable_handle_show");
  });;

  jQuery("#wp_crm_attribute_fields tbody tr").live("mouseout", function() {
    jQuery(this).removeClass("wp_crm_draggable_handle_show");
  });;


// Show settings array
  jQuery("#wp_crm_show_settings_array, #wp_crm_show_settings_array_cancel").click(function() {

    if(jQuery("#wp_crm_show_settings_array_result").is(":visible")) {
      jQuery("#wp_crm_show_settings_array_cancel").hide();
      jQuery("#wp_crm_show_settings_array_result").hide();
    } else {
      jQuery("#wp_crm_show_settings_array_cancel").show();
      jQuery("#wp_crm_show_settings_array_result").show();
    }

  });
// Show settings array
  jQuery(".wp_crm_toggle_something").click(function() {
    
    var settings_block = jQuery(this).parents('.wp_crm_settings_block');
    
   if(jQuery(".wp_crm_class_pre", settings_block).is(":visible")) {
      jQuery(".wp_crm_class_pre", settings_block).hide();
      jQuery(".wp_crm_link", settings_block).hide();
    } else {
      jQuery(".wp_crm_class_pre", settings_block).show();
      jQuery(".wp_crm_link", settings_block).show();
    } 
 
  });

// Query user object
  jQuery("#wp_crm_show_user_object").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {action: 'wp_crm_user_object', user_id: jQuery("#wp_crm_user_id").val()}, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });

  });
  
  // Generate fake users
  jQuery("#wp_crm_generate_fake_users").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {
      action: 'wp_crm_do_fake_users', 
      number: jQuery("#wp_crm_fake_users").val(),
      do_what: 'generate'
    }, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });

  });
  
// Return reprot of user meta usage and typical data
  jQuery("#wp_crm_show_meta_report").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {action: 'wp_crm_show_meta_report' }, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });


  });


});