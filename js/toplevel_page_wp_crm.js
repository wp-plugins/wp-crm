
jQuery(document).bind('wp_crm_user_results', function(data) {
 
});



jQuery(document).ready(function () {

  jQuery("#wp_crm_text_search").focus();

  jQuery("#actions .misc-pub-section input").change(function() {
    
    jQuery(".wp_crm_user_actions").hide();
  });
  
  
  jQuery(".wp_crm_export_to_csv").click(function() {
  
    var filters = jQuery('#wp-crm-filter').serialize()
  
    wp_crm_csv_download = ajaxurl + '?action=wp_crm_csv_export&' + filters;
    
    location = wp_crm_csv_download;
  });


  // Show filter options when items are selected
  jQuery(".check-column input").change(function() {
    var selected_elements = jQuery(".check-column input:checked").length;

    if(selected_elements > 0)
      jQuery(".tablenav .wp_crm_bulk").css('display', 'inline');
    else
      jQuery(".tablenav .wp_crm_bulk").css('display', 'none');

  });
  

  jQuery(".wp_crm_bulk select").change(function() {

    var selected = jQuery('option:selected', this).val();
    alert(selected);

  });

  jQuery('thead th.check-column input[type=checkbox]').change(function() {

    var wp_result_user_count = 10;

    if(jQuery(this).is(":checked")) {
      jQuery(".wp_crm_above_overview_table").html('<div class="updated"><p><span class="wp_crm_link">Select all ' + wp_result_user_count + ' users?</span></p></div>');
    } else {
      jQuery(".wp_crm_above_overview_table div").remove();
    }

  });


});