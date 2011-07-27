jQuery(document).ready(function () {


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