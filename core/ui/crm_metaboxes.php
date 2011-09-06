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

    <?php $wp_list_table->search_box( 'Search', 'wp_crm_text_search' ); ?>

    <?php $wp_list_table->views(); ?>

    </div>

    <div class="major-publishing-actions">
      <div class="other-action">
        <span class="wp_crm_subtle_link wp_crm_toggle" toggle="wp_crm_user_actions"><?php _e('Show Actions'); ?></span>
      </div>
      <div class="publishing-action">
        <?php submit_button( __('Filter Results'), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>

    <div class="wp_crm_user_actions hidden">
      <ul class="wp_crm_action_list">
      <li class="wp_crm_orange_link wp_crm_export_to_csv"><?php _e("Export to CSV"); ?></li>
      <?php do_action('wp_crm_user_actions'); ?>
      </ul>
    </div>


    <?php
    }

  }


class crm_page_wp_crm_add_new {
  function primary_information($user_object) {
    global $wp_crm;
    $user_role = WP_CRM_F::get_first_value($user_object['role']);

    ?>
    <table class="form-table">
    <?php if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) : ?>
      <?php foreach($wp_crm['data_structure']['attributes'] as $slug => $attribute): ?>
        <?php
        unset($row_classes);

        $row_classes[] = 'wp_crm_user_entry_row';
        $row_classes[] = "wp_crm_{$slug}_row";
        $row_classes[] = (@$attribute['uneditable'] == 'true' ? 'wp_crm_attribute_uneditable' : '');
        $row_classes[] = (@$attribute['required'] == 'true' ? 'wp_crm_attribute_required' : '');
        $row_classes[] = (@$attribute['primary'] == 'true' ? 'primary' : 'not_primary');
        $row_classes[] = ((is_array($wp_crm['hidden_attributes'][$user_role]) && in_array($slug, $wp_crm['hidden_attributes'][$user_role])) ? 'hidden' : '');
        ?>
        <tr meta_key="<?php echo esc_attr($slug); ?>" wp_crm_input_type="<?php echo esc_attr($attribute['input_type']); ?>" class="<?php echo implode(' ', $row_classes); ?>">
          <th>
          <?php if(@$attribute['input_type'] != 'checkbox' || isset($attribute['options'])): ?>
            <?php ob_start();?>
            <label for="wp_crm_<?php echo $slug; ?>_field">
              <?php echo $attribute['title']; ?>
            </label>
            <div class="wp_crm_description"><?php echo $attribute['description']; ?></div>
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
   $user_id = $object['ID']['default'][0];

   ?>
<div id="minor-publishing">
  <ul>

  <?php if(current_user_can( 'WP-CRM: Add User Messages' )) { ?>
    <li><?php _e('Add a <b><a href="#" class="wp_crm_toggle_message_entry">general note</a></b>.', 'wp_crm'); ?></li>
  <?php } else { ?>
    
  <?php } ?>

  <?php if(current_user_can( 'edit_users' )) { ?>
  <li><?php _('User Role:'); ?> <select id="wp_crm_role" name="wp_crm[user_data][role][<?php echo rand(1000,9999); ?>][value]"><option value=""></option><?php wp_dropdown_roles($object['role']['default'][0]); ?></select>
  

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
  <?php } ?>

  </ul>
  <?php if(current_user_can( 'edit_users' ))  { do_action('wp_crm_metabox_special_actions'); } ?>
</div>
  
  <div id="major-publishing-actions">
  <?php if(current_user_can( 'remove_users' ) || current_user_can( 'delete_users' )) { ?>
    <div id="delete-action">
    <?php if(!$object['new']): ?>
    <a href="<?php echo  wp_nonce_url( "admin.php?wp_crm_action=delete_user&page=wp_crm&user_id={$user_id}", 'wp-crm-delete-user-' . $user_id ); ?>" class="submitdelete deletion"><?php _e('Delete'); ?></a>
    <?php endif; ?>
    </div>
  <?php } ?>

  <div id="publishing-action">  
      <input type="hidden" value="Publish" id="original_publish" name="original_publish">
      <?php if(current_user_can( 'edit_users' ) || (current_user_can('add_users') && $object['new'])) { ?>
      <input type="submit" accesskey="p" tabindex="5" value="<?php echo ($object['new'] ? __('Save', 'wpp_crm') : __('Update', 'wpp_crm')); ?>" class="button-primary" id="publish" name="publish">
      <?php } else { ?>
      <input type="submit" accesskey="p" tabindex="5" value="<?php echo ($object['new'] ? __('Save', 'wpp_crm') : __('Update', 'wpp_crm')); ?>" class="button-primary" id="publish" name="publish" disabled="true">
      <?php } ?>
    </div>
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
  <?php if(current_user_can('WP-CRM: Add User Messages')) { ?>
  <div class="wp_crm_activity_top">
    <input class='wp_crm_toggle_message_entry button' type='button' value='<?php _e('Add Message'); ?>' />
    <?php do_action('wp_crm_user_activity_history_top', $object); ?>
  </div>
  <?php } ?>

  <div class="wp_crm_new_message hidden">
    <textarea id='wp_crm_message_content'></textarea>

    <div class="wp_crm_new_message_options_line">

      <div class="alignleft">
        <div class="wp_crm_show_message_options"><?php _e('Show Options', 'wp_crm'); ?></div>
        <div class="wp_crm_message_options hidden">
        <?php _e('Date:', 'wp_crm'); ?>
        <input class="datepicker" />
        </div>
      </div>
      <div class="alignright"><input type='button' id='wp_crm_add_message' value='<?php _e('Add Message', 'wp_crm'); ?>'/></div>
    </div>
   </div>

   <table id="wp_crm_user_activity_stream" cellpadding="0" cellspacing="0">
    <thead></thead>
    <tbody>
    <?php if($user_id) { WP_CRM_F::get_user_activity_stream("user_id={$user_id} "); } ?>
    </tbody>
   </table>


  <?php

  }


}


