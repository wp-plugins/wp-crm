<?php



  /**
   * Metaboxes for the main overview page 
   *
    * @since 0.01
   *
    */
  class toplevel_page_wp_crm {
  
  /**
   * Actions metabox used for primary filtering purposes
   *
   * 
   * @uses CRM_User_List_Table class 
    * @since 0.01
   *
    */  
    function actions($wp_list_table) {

    ?>
    <div class="misc-pub-section">
      
    <?php $wp_list_table->search_box( 'Search', 'post' ); ?>
 
    <?php $wp_list_table->views(); ?>
      
    </div>
    
    <div class="major-publishing-actions">
      <div class="publishing-action">
        <?php submit_button( __('Filter Results'), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>
    

    <?php
    }

  }


class crm_page_wp_crm_add_new {
  function primary_information($user_object) {
    global $wp_crm;
    
    ?>
    <table class="form-table">
    <?php if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) : ?>
      <?php foreach($wp_crm['data_structure']['attributes'] as $slug => $attribute): ?>
        <?php $class = (is_array($wp_crm['hidden_attributes'][$user_object->user_role[0]['slug']]) && in_array($slug, $wp_crm['hidden_attributes'][$user_object['user_role']['default']])) ? 'hidden' : ''; ?>
        <tr class="wp_crm_user_entry_row <?php echo $class . ' ' .  (@$attribute['primary'] == 'true' ? 'primary' : 'not_primary')?> wp_crm_<?php echo $slug; ?>_row">
          <th>
          <?php if(@$attribute['input_type'] != 'checkbox' || isset($attribute['options'])): ?>
            <?php ob_start();?>
            <label for="wp_crm_<?php echo $slug; ?>_field">
              <?php echo $attribute['title']; ?>
            </label>
            <?php $label = ob_get_contents(); ob_end_clean(); ?>
            <?php echo apply_filters('wp_crm_user_input_label', $label, $slug, $attribute, $user_object); ?>
          <?php endif; ?>
          </th>
          <td>
            <div class="blank_slate hidden" show_attribute="<?php echo $slug; ?>"><?php echo (!empty($attribute['blank_message']) ? $attribute['blank_message'] : "Add {$attribute['title']}"); ?></div>
            <?php echo WP_CRM_F::user_input_field($slug, $user_object[$slug], $attribute, $user_object); ?>
 
            <?php if(isset($attribute['allow_multiple']) && $attribute['allow_multiple'] == 'true'): ?>
              <div class="add_another"><?php _('Add Another'); ?></div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </table>
  <?php
  
  }

  /**
   * Sidebar metabox for administrative user actions
   *
   * 
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */  
  function special_actions($object) {
  
   

?>
<div id="minor-publishing">
  <ul>

  <li>Add a <b><a href='#' class='wp_crm_toggle_message_entry'>general note</a></b>.</li> 
  
  <?php if(current_user_can( 'edit_users' )): ?>
  <li><?php _('User Role:'); ?> <select id="wp_crm_user_role" name="wp_crm[user_data][user_role][<?php echo rand(1000,9999); ?>][value]"><option value=""></option><?php wp_dropdown_roles($object['user_role']['default'][0]); ?></select>
  <?php endif; ?>
  
  <li class="wp_crm_advanced_user_actions">
    <div class="wp_crm_toggle_advanced_user_actions wp_crm_link"><?php _e('Toggle Advanced User Settings'); ?></div>
    <div class="wp_crm_advanced_user_actions hidden wp-tab-panel">
    <?php _e('Set Password:'); ?>
    <ul>
      <li>
        <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_1" name="wp_crm[user_data][user_pass][<?php echo rand(1000,9999); ?>][value]" />
        <span class="description"><?php _e('Type in new password twice to change.'); ?></span>
      </li>
      
      <li>
        <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_2" />
        <span class="description"><?php _e('Type your new password again.'); ?></span>
      </li>
      
    </ul>
    </div>
  </li>
  
  </ul>
  <?php do_action('wp_crm_metabox_special_actions'); ?>
</div>
  <div id="major-publishing-actions">
    <div id="delete-action">
    <?php if(!$object->new): ?>
    <a href="<?php echo  wp_nonce_url( "users.php?action=delete&amp;user={$object->ID[0][value]}", 'bulk-users' ); ?>" class="submitdelete deletion"><?php _e('Delete'); ?></a>
    <?php endif; ?>
    </div>

<div id="publishing-action">
<img alt="" id="ajax-loading" class="ajax-loading" src="http://development.twincitiestech.com/wp-crm/wp-admin/images/wpspin_light.gif">
    <input type="hidden" value="Publish" id="original_publish" name="original_publish">
    <input type="submit" accesskey="p" tabindex="5" value="Save" class="button-primary" id="publish" name="publish"></div>
<div class="clear"></div>
</div>
<?php



  }

  
  /**
   * Contact history and messages for a user
   *
   * 
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */  
  function user_activity_history($object) {
 
    global $wpdb;
    $user_id = WP_CRM_F::get_first_value($object['ID']);
 
    ?>
  <div class="wp_crm_activity_top">
    <input class='wp_crm_toggle_message_entry button' type='button' value='<?php _e('Add Message'); ?>' />
    <?php do_action('wp_crm_user_activity_history_top', $object); ?>
  </div>
  
  <div class="wp_crm_new_message hidden">  
    <textarea id='wp_crm_message_content'></textarea>
    
    <div class="wp_crm_new_message_options_line">
    
      <div class="alignleft">
        <div class="wp_crm_show_message_options"><?php _e('Show Options', 'wp_crm'); ?></div>
        <div class="wp_crm_message_options hidden">
        Date:
        <input class="datepicker" />
        </div>        
      </div>
      <div class="alignright"><input type='button' id='wp_crm_add_message' value='<?php _e('Add Message', 'wp_crm'); ?>'/></div>
    </div>
   </div>
 
   <table id="wp_crm_user_activity_stream">
    <thead>
    </thead>
    <tbody>
    <?php if($user_id) { WP_CRM_F::get_user_activity_stream("user_id={$user_id} "); } ?>
    </tbody>
   </table>
   
   
  <?php

  }


}


