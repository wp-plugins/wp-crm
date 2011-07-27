/**
 * WP-Property Global Admin Scripts
 *
 * This file is included on all back-end pages, so extra care needs be taken to avoid conflicts
 *
*/
jQuery(document).ready(function() {
  
  jQuery('.wp_crm_message_quick_action').live('click',function() {
    var action = jQuery(this).attr('wp_crm_action');
    var object_id = jQuery(this).attr('object_id');
    var parent_element = jQuery(this).parents('tr');
    
    jQuery.post(ajaxurl, {action: 'wp_crm_quick_action', wp_crm_quick_action: action, object_id: object_id}, function(result) {
      if(result.success = 'true') {
      
      }
      
      switch(result.action) {
        case 'hide_element':
 
          jQuery(parent_element).hide();
        break;
      }
    }, 'json');
  });
  
  // Add row to UD UI Dynamic Table
  jQuery(".wp_crm_add_row").live("click" , function() {
    
    var table = jQuery(this).parents('.ud_ui_dynamic_table');
    var table_id = jQuery(table).attr("id");
    
    // Clone last row
    var cloned = jQuery(".wp_crm_dynamic_table_row:last", table).clone();
    
    // Find and replace attribute ID (FOR) to exclude DOM bugs: ID should be unique.
    jQuery(cloned).children().each(function(i, e) {
      if(jQuery('ul', e).length > 0) {
        var liEl = jQuery('ul', e).children();
        if(liEl.length > 0) {
          liEl.each(function(i,e){
            var label = jQuery('label', e);
            var input = jQuery('input', e);
            if(label.length > 0 && input.length > 0) {
              var attrFor = label.attr('for');
              var attrId = input.attr('id');
              if(attrFor != '' && attrId != '') {
                var rand=Math.floor(Math.random()*10000);
                label.attr('for', 'new_field_'+rand);
                input.attr('id', 'new_field_'+rand);
              }
            }
          });
        }
      }
    });
    
    // Insert new row after last one
    jQuery(cloned).appendTo(table);
    
    // Get Last row to update names to match slug
    var added_row = jQuery(".wp_crm_dynamic_table_row:last", table);
    
    // Display row ust in case
    jQuery(added_row).show();
    
    // Blank out all values
    jQuery("input[type=text]", added_row).val('');
    jQuery("input[type=checkbox]", added_row).attr('checked', false);
    jQuery("textarea", added_row).val('');
    
    // Unset 'new_row' attribute
    jQuery(added_row).attr('new_row', 'true');
    
    jQuery('.slug_setter', added_row).focus();
    
  });

  // When the .slug_setter input field is modified, we update names of other elements in row
  jQuery(".wp_crm_dynamic_table_row[new_row=true] input.slug_setter").live("change", function() {

    //console.log('Name changed.');
  
    var this_row = jQuery(this).parents('tr.wp_crm_dynamic_table_row');

    // Slug of row in question
    var old_slug = jQuery(this_row).attr('slug');
    
    // Get data from input.slug_setter
    var new_slug = jQuery(this).val();

    // Conver into slug
    var new_slug = wp_crm_create_slug(new_slug);
    //console.log("New slug: "  + new_slug);

    // Don't allow to blank out slugs
    if(new_slug == "")
      return;

    // If slug input.slug exists in row, we modify it
    jQuery(".slug" , this_row).val(new_slug);

    // Update row slug
    jQuery(this_row).attr('slug', new_slug);
    
    // Cycle through all child elements and fix names
    jQuery('input,select,textarea', this_row).each(function(element) {
    
    
      var old_name = jQuery(this).attr('name');
      
      if (typeof old_name != 'undefined') {
      
        var new_name =  old_name.replace(old_slug,new_slug);

        if(jQuery(this).attr('id')) {
          var old_id = jQuery(this).attr('id');    
          var new_id =  old_id.replace(old_slug,new_slug);
        }
        
        // Update to new name
        jQuery(this).attr('name', new_name);
        jQuery(this).attr('id', new_id);
      }

    });
    
    // Cycle through labels too
      jQuery('label', this_row).each(function(element) {
      var old_for = jQuery(this).attr('for');
      var new_for =  old_for.replace(old_slug,new_slug);
      
      // Update to new name
      jQuery(this).attr('for', new_for);
      

    });
 
  });




  // Delete image type
  jQuery(".wp_crm_delete_row").live("click", function() {

    
    var parent = jQuery(this).parents('tr.wp_crm_dynamic_table_row');
    var row_count = jQuery(".wp_crm_delete_row:visible").length;
 
  
    
    // Blank out all values
    jQuery("input[type=text]", parent).val('');
    jQuery("textarea", parent).val('');
    jQuery("input[type=checkbox]", parent).attr('checked', false);
    
    jQuery(parent).attr('new_row', 'true');
    
    // Don't hide last row
    if(row_count > 1) {
      jQuery(parent).hide();
      jQuery(parent).remove();  
    }
  });

    jQuery('.wp_crm_overview_filters .all').click(function(){
      if (jQuery(this).find('input').attr('checked')){
        jQuery(this).siblings('li').find('input').removeAttr('checked');
      }
    })

    jQuery('.wp_crm_role_list').change(function(){
      jQuery('.wp_crm_overview_filters .all').find('input').removeAttr('checked');
    })

 
    jQuery('.wpp_crm_filter_show').click(function(){
  
      var parent = jQuery(this).parents('.wp_crm_overview_filters');
      jQuery(' .wp_crm_checkbox_filter', parent).show();
        
      jQuery('.wpp_crm_filter_show', parent).hide();
 

    
    });
    
    
    
  });


function wp_crm_create_slug(slug) {


    slug = slug.replace(/[^a-zA-Z0-9_\s]/g,"");
    slug = slug.toLowerCase();
    slug = slug.replace(/\s/g,'_');

    return slug;
}