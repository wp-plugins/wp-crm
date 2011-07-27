<?php

 global $wp_roles;


if(isset($_REQUEST['message'])) {

  switch($_REQUEST['message']) {

    case 'updated':
    WP_CRM_F::add_message('Settings updated.');
    break;
  }
}

if(empty($wp_crm['notifications'])) {
  $wp_crm['notifications']['example']['subject'] = "Subject";
  $wp_crm['notifications']['example']['to'] = "[user_email]";
  $wp_crm['notifications']['example']['message'] = "Hello [display_name], \n\n Thank you for your message.";
  $wp_crm['notifications']['example']['send_from'] = get_bloginfo('admin_email');
}

if(empty($wp_crm['data_structure']['attributes']))
  $wp_crm['data_structure']['attributes'] = array('user_email' => array('title' => 'Email', 'primary' => 'true'));

?>

 <script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery("#wp_crm_settings_tabs").tabs({ cookie: { expires: 30 } });
  });
 </script>

<div class="wrap">
<h2><?php _e('CRM Settings','wp_crm'); ?></h2>

<?php WP_CRM_F::print_messages(); ?>

<form method="post" action="<?php echo admin_url('admin.php?page=wp_crm_settings'); ?>"  enctype="multipart/form-data" >
<?php wp_nonce_field('wp_crm_setting_save'); ?>

<div id="wp_crm_settings_tabs" class="clearfix">
  <ul class="tabs">
    <li><a href="#tab_main"><?php _e('Main','wp_crm'); ?></a></li>
    <li><a href="#tab_user_data"><?php _e('Data','wp_crm'); ?></a></li>
    <li><a href="#tab_user_roles"><?php _e('Roles','wp_crm'); ?></a></li>
    <li><a href="#tab_notifications"><?php _e('Notifications','wp_crm'); ?></a></li>
      <?php


        $wp_crm_plugin_settings_nav = apply_filters('wp_crm_settings_nav', array());

        if(is_array($wp_crm_plugin_settings_nav)) {
          foreach($wp_crm_plugin_settings_nav as $feature_slug => $nav) {

            if($wp_crm['available_features'][$feature_slug]['status'] === 'disabled')
              continue;

            echo "<li><a href='#tab_{$nav['slug']}'>{$nav['title']}</a></li>\n";
          }
        }

    ?>

    <?php if(count($wp_crm['available_features']) > 0): ?>
    <li><a href="#tab_plugins"><?php _e('Plugins','wp_crm'); ?></a></li>
    <?php endif; ?>
    <li><a href="#tab_troubleshooting"><?php _e('Help','wp_crm'); ?></a></li>


  </ul>


  <div id="tab_main">


    <table class="form-table">
    <tr>
      <th>
        <?php _e('Overview Page User Card','wp_crm'); ?>
        <span class="description"><?php _e("Information to display in primary user information cell on overview table.", 'wp_crm'); ?></span>
      </th>
      <td>
        <div class="wp-tab-panel">
        <ul>
         <?php foreach(apply_filters('wp_crm_user_card_keys', $wp_crm['data_structure']['attributes']) as $key => $attribute_data): $rand = rand(1000,9999); ?>
          <li>
            <input type="checkbox" value="<?php echo $key; ?>" <?php CRM_UD_UI::checked_in_array($key, $wp_crm['configuration']['overview_table_options']['main_view']); ?> name="wp_crm[configuration][overview_table_options][main_view][]" id="<?php echo $key.$rand; ?>" />
            <label for="<?php echo $key.$rand; ?>"><?php echo ($attribute_data['title'] ? $attribute_data['title'] : CRM_UD_F::de_slug($key)); ?></label>
          </li>
        <?php endforeach; ?>
        </ul>

        </div>
      </td>
    </tr>

    </table>
  </div>

  <div id="tab_notifications">
    <div class="wp_crm_inner_tab">
      <p>
        <?php _e('This tab allows you to create and modify your notifications.  Notifications can be assigned to various events, such as new user registration, general contact messages, etc.', 'wp_crm'); ?>
      </p>
     <table id="wp_crm_notification_messages" class="form-table wp_crm_form_table ud_ui_dynamic_table widefat">
      <thead>
        <tr>
           <th class="wp_crm_message_header_col"><?php _e('Message Header','wpp') ?></th>
           <th class="wp_crm_message_col"><?php _e('Message','wpp') ?></th>
          <th class="wp_crm_settings_col"><?php _e('Trigger Actions','wpp') ?></th>
          <th class="wp_crm_delete_col">&nbsp;</th>
          </tr>
      </thead>
      <tbody>
      <?php  foreach($wp_crm['notifications'] as $notification_slug => $data):  $row_hash = rand(100,999); ?>
        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $notification_slug; ?>"  new_row='false'>
          <td class='wp_crm_message_header_col'>
            <ul class="wp_crm_notification_main_configuration">
              <li>
                <label for=""><?php _e('Subject:', 'wp_crm'); ?></label>
                <input type="text" id="subject_<?php echo $row_hash; ?>" class="slug_setter regular-text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][subject]" value="<?php echo $data['subject']; ?>" />
              </li>
              <li>
                <label for=""><?php _e('To:', 'wp_crm'); ?></label>
                <input type="text"  id="to_<?php echo $row_hash; ?>"  class="regular-text"   name="wp_crm[notifications][<?php echo $notification_slug; ?>][to]" value="<?php echo $data['to']; ?>" />
             </li>
              <li>
                <label for=""><?php _e('BCC:', 'wp_crm'); ?></label>
                <input type="text"  id="bcc_<?php echo $row_hash; ?>"  class="regular-text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][bcc]" value="<?php echo $data['bcc']; ?>"/>
              </li>
              <li>
                <label for=""><?php _e('Send From:', 'wp_crm'); ?></label>
                <input type="text"  id="send_from_<?php echo $row_hash; ?>"  class="regular-text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][send_from]" value="<?php echo $data['send_from']; ?>"/>
               </li>

            </ul>
          </td>
          <td>
              <textarea   name="wp_crm[notifications][<?php echo $notification_slug; ?>][message]"/><?php echo $data['message']; ?></textarea>
          </td>
          <td class="wp_crm_settings_col">

            <?php if(is_array($wp_crm['notification_actions'])): ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['notification_actions'] as $action_slug => $action_title): $action_hash = rand(1000,9999); ?>
                <li>
                  <input type="checkbox" id="action_<?php echo $action_hash; ?>" <?php CRM_UD_UI::checked_in_array($action_slug, $data['fire_on_action']); ?> name="wp_crm[notifications][<?php echo $notification_slug; ?>][fire_on_action][]"  value="<?php echo $action_slug; ?>" />
                  <label for="action_<?php echo $action_hash; ?>" ><?php echo $action_title; ?></label>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php else: ?>
              <p><?php _e('You do not have any notification actions yet. ', 'wp_crm'); ?></p>
            <?php endif; ?>
          </td>
          <td valign="middle"><span class="wp_crm_delete_row  button"><?php _e('Delete','wpp') ?></span></td>
        </tr>
      </tbody>
      <?php endforeach; ?>

      <tfoot>
        <tr>
          <td colspan='4'>
          <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e('Add Row','wpp') ?>" />
          </td>
        </tr>
      </tfoot>

      </table>
      <p><?php _e('For the <b>Send From</b> value enter an e-mailed address or a name and e-mail using the folloiwng format: John Smith <john.smith@gmail.com>', 'wp_crm'); ?></p>
      <p><?php _e('To see list of variables you can use in notifications open up the "Help" tab and view the user data structure.  Any variable you see in there can be used in the subject field, to field, BCC field, and the message body. Example: [user_email] would include the recipient\'s e-mail.', 'wp_crm'); ?></p>
      <p><?php _e('To add notification actions use the <b>wp_crm_notification_actions</b> filter, then call the action within <b>wp_crm_send_notification()</b> function, and the messages association with the given action will be fired off.', 'wp_crm'); ?></p>
    <?php do_action('wp_crm_settings_notification_tab'); ?>
    </div>
  </div>

  <div id="tab_user_data">

  <table class="form-table">

  <tr>
    <th><?php _e('General Settings','wp_crm'); ?></th>
    <td>


    <table id="wp_crm_attribute_fields" class="ud_ui_dynamic_table widefat">
      <thead>
        <tr>
          <th class='wp_crm_draggable_handle'>&nbsp;</th>
          <th class="wp_crm_attribute_col"><?php _e('Attribute','wpp') ?></th>
          <th class="wp_crm_settings_col"><?php _e('Settings','wpp') ?></th>
          <th class="wp_crm_type_col"><?php _e('Input Type','wpp') ?></th>
          <th class="wp_crm_values_col"><?php _e('Predefined Values','wpp') ?></th>
          <th class="wp_crm_delete_col">&nbsp;</th>
          </tr>
      </thead>
      <tbody>
        <?php  foreach($wp_crm['data_structure']['attributes'] as $slug => $data):  $row_hash = rand(100,999); ?>

        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $slug; ?>"  new_row='false'>
        <th class="wp_crm_draggable_handle">&nbsp;</th>

        <td >
          <ul>
          <li>
          <label><?php _e('Title'); ?></label>
          <input class="slug_setter" type="text" name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][title]" value="<?php echo $data['title']; ?>" />
          </li>
          <li>
          <label><?php _e('Slug'); ?> </label>
          <input type="text" class="slug" readonly='readonly' value="<?php echo $slug; ?>" />
          </li>
          </ul>
        </td>
        <td>
        <ul>
        <li>
            <input id="<?php echo $row_hash; ?>_primary" value='true' type="checkbox"  <?php checked($wp_crm['data_structure']['attributes'][$slug]['primary'], 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][primary]" />
            <label for="<?php echo $row_hash; ?>_primary" ><?php _e('Primary', 'wp_crm'); ?></label>
        </li>
        <li>
            <input  id="<?php echo $row_hash; ?>_overview_column"  value='true' type="checkbox"  <?php checked($wp_crm['data_structure']['attributes'][$slug]['overview_column'], 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][overview_column]" />
            <label for="<?php echo $row_hash; ?>_overview_column" ><?php _e('Overview Column', 'wp_crm'); ?></label>
        </li>
        <li>
            <input  id="<?php echo $row_hash; ?>_allow_multiple"  value='true' type="checkbox"  <?php checked($wp_crm['data_structure']['attributes'][$slug]['allow_multiple'], 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][allow_multiple]" />
            <label  for="<?php echo $row_hash; ?>_allow_multiple"  ><?php _e('Allow Multiple', 'wp_crm'); ?></label>
        </li>
        <li>
            <input  id="<?php echo $row_hash; ?>_autocomplete"  value='true' type="checkbox"  <?php checked($wp_crm['data_structure']['attributes'][$slug]['autocomplete'], 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][autocomplete]" />
            <label  for="<?php echo $row_hash; ?>_autocomplete"  ><?php _e('Autocomplete Field', 'wp_crm'); ?></label>
        </li>

        </ul>
        </td>
        <td>
            <select name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][input_type]">
              <?php foreach($wp_crm['configuration']['input_types'] as $this_input_type_slug => $this_input_type_label): ?>
              <option value="<?php echo $this_input_type_slug; ?>" <?php selected($wp_crm['data_structure']['attributes'][$slug]['input_type'] == $this_input_type_slug); ?>><?php echo $this_input_type_label; ?></option>
              <?php endforeach; ?>
          </select>
        </td>

        <td class='wp_crm_values_col'>
          <textarea  name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][options]"><?php echo $wp_crm['data_structure']['attributes'][$slug]['options']; ?></textarea>
        </td>

        <td><span class="wp_crm_delete_row  button"><?php _e('Delete','wpp') ?></span></td>
        </tr>

        <?php endforeach; ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan='6'>
          <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e('Add Row','wpp') ?>" />
          </td>
        </tr>
      </tfoot>

    </table>

  </tr>
  </table>
  </div>

<div id="tab_user_roles">

  <table class="form-table">

  <tr>
    <th><?php _e('User Roles','wp_crm'); ?></th>
    <td>

<table id="" class="ud_ui_dynamic_table widefat">
  <thead>
    <tr>
      <th><?php _e('Role'); ?></th>
      <th><?php _e('Hidden Attributes', 'wp_crm'); ?></th>
      <th><?php _e('Capabilities'); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($wp_roles->roles as $role_slug => $role):  $rand_id = rand(1000,9999); ?>
   <tr class="wp_crm_dynamic_table_row" slug="<?php echo $role_slug; ?>"  new_row='false'>

      <td><?php echo $role['name']; ?></td>

        <td >
         <ul class="wp-tab-panel">
          <?php foreach($wp_crm['data_structure']['attributes'] as $key => $attribute_data): ?>
          <li>
            <input <?php CRM_UD_UI::checked_in_array($key, $wp_crm['hidden_attributes'][$role_slug]); ?> id="<?php echo $key; ?>_<?php echo $rand_id; ?>" type="checkbox" name="wp_crm[hidden_attributes][<?php echo $role_slug; ?>][]" value="<?php echo $key; ?>" />
            <label for="<?php echo $key; ?>_<?php echo $rand_id; ?>"><?php echo $attribute_data['title']; ?></label>
          </li>
          <?php endforeach; ?>
          </ul>
        </td>
        <td>

          <ul class="wp-tab-panel">
          <?php foreach($role['capabilities'] as $cap_slug => $cap_setting): ?>

            <li>
            <?php echo $cap_slug; ?>
            </li>

          <?php endforeach; ?>
          </ul>
        </td>
        </tr>
   <?php endforeach; ?>
  </tbody>
</table>

    </td>
   </tr>

   </table>
 </div>

  <?php
  if(is_array($wp_crm_plugin_settings_nav)) {
    foreach($wp_crm_plugin_settings_nav as $nav) {
      echo "<div id='tab_{$nav['slug']}'>";
      do_action("wp_crm_settings_content_{$nav['slug']}", $wp_crm);
      echo "</div>";
    }
  }

  ?>



<?php if(count($wp_crm['available_features']) > 0): ?>
  <div id="tab_plugins">

      <table id="wp_crm_premium_feature_table" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
          <td colspan="2" class="wp_crm_premium_feature_intro">
              <span class="header"><?php _e('WP-Property Premium Features','wp_crm') ?></span>
              <p><?php _e('Premium features will become available shortly, we are still waiting on more feedback on the core of the plugin.','wp_crm') ?></p>
              <?php /*<p><?php _e('If you're recently purchased a premium feature, <span id="wp_crm_check_premium_updates" class="wp_crm_link">download updates</a>.','wp_crm') ?></p> */ ?>
              <p id="wp_crm_plugins_ajax_response" class="hidden"></p>
          </td>
        </tr>
        </thead>
      <?php foreach($wp_crm['available_features'] as $plugin_slug => $plugin_data): ?>

        <input type="hidden" name="wp_crm[available_features][<?php echo $plugin_slug; ?>][title]" value="<?php echo $plugin_data['title']; ?>" />
        <input type="hidden" name="wp_crm[available_features][<?php echo $plugin_slug; ?>][tagline]" value="<?php echo $plugin_data['tagline']; ?>" />
        <input type="hidden" name="wp_crm[available_features][<?php echo $plugin_slug; ?>][image]" value="<?php echo $plugin_data['image']; ?>" />
        <input type="hidden" name="wp_crm[available_features][<?php echo $plugin_slug; ?>][description]" value="<?php echo $plugin_data['description']; ?>" />

        <?php $installed = (!empty($wp_crm['installed_features'][$plugin_slug]['version']) ? true : false); ?>
        <?php $active = (@$wp_crm['installed_features'][$plugin_slug]['disabled'] != 'false' ? true : false); ?>
        <tr class="wp_crm_premium_feature_block">

          <td valign="top" class="wp_crm_premium_feature_image">
            <a href="http://twincitiestech.com/plugins/wp-property/"><img src="<?php echo $plugin_data['image']; ?>" /></a>
          </td>

          <td valign="top">
            <div class="wp_crm_box">
            <div class="wp_crm_box_header">
              <strong><?php echo $plugin_data['title']; ?></strong>
              <p><?php echo $plugin_data['tagline']; ?> <a href="http://twincitiestech.com/plugins/wp-property/premium/"><?php _e('[learn more]','wp_crm') ?></a>
              </p>
            </div>
            <div class="wp_crm_box_content">
              <p><?php echo $plugin_data['description']; ?></p>

            </div>

            <div class="wp_crm_box_footer clearfix">
              <?php if($installed): ?>

                <div class="alignleft">
                <?php
                           $disable_text = __('Disable plugin.','wp_crm');
                           echo CRM_UD_UI::checkbox("name=wp_crm_settings[installed_features][$plugin_slug][disabled]&label=$disable_text", $wp_crm['installed_features'][$plugin_slug]['disabled']); ?>
                </div>

                <div class="alignright"><?php _e('Feature installed, using version','wp_crm') ?> <?php echo $wp_crm['installed_features'][$plugin_slug]['version']; ?>.</div>
              <?php else: ?>
                <?php $pr_link = 'http://twincitiestech.com/plugins/wp-property/premium/'; echo sprintf(__('Please visit <a href="%s">TwinCitiesTech.com</a> to purchase this feature.','wp_crm'),$pr_link); ?>
              <?php endif; ?>
            </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </table>

  </div>
  <?php endif; ?>

  <div id="tab_troubleshooting">
    <div class="wp_crm_inner_tab">


      <div class="wp_crm_settings_block">
        <?php _e('Look up the <b>$wp_crm</b> global settings array.  This array stores all the default settings, which are overwritten by database settings, and custom filters.','wp_crm') ?>
        <input type="button" value="<?php _e('Show $wp_crm','wpp') ?>" id="wp_crm_show_settings_array"> <span id="wp_crm_show_settings_array_cancel" class="wp_crm_link hidden"><?php _e('Cancel','wpp') ?></span>
        <pre id="wp_crm_show_settings_array_result" class="wp_crm_class_pre hidden"><?php print_r($wp_crm); ?></pre>
      </div>


      <div class="wp_crm_settings_block">
        <?php _e('Show user data structure:','wp_crm') ?>
        <input type="button" value="<?php _e('Show WP_CRM_F::user_object_structure()','wpp') ?>" id="" class='wp_crm_toggle_something'> <span id="" class="wp_crm_toggle_something wp_crm_link hidden"><?php _e('Cancel','wpp') ?></span>
        <pre class="wp_crm_class_pre hidden"> All possible meta keys:
          <?php print_r(WP_CRM_F::user_object_structure());?>
          Root Only:
          <?php print_r(WP_CRM_F::user_object_structure('root_only=true'));?>

         </pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Lookup a user object by its ID.','wp_crm') ?>
        <input type="input" value="<?php echo get_current_user_id(); ?>" id="wp_crm_user_id">
        <input type="button" value="<?php _e('Load User','wpp') ?>" id="wp_crm_show_user_object">
        <span class="wp_crm_link hidden"><?php _e('Cancel','wpp') ?></span>
        <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Get user meta report. Will return an array of common meta keys and a few sample values.  This should be used to help you analyze a website with a lot of existing user meta.','wp_crm') ?>
       <input type="button" value="<?php _e('Get Report','wpp') ?>" id="wp_crm_show_meta_report">
        <span class="wp_crm_link hidden"><?php _e('Cancel','wpp') ?></span>
        <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Generate ','wp_crm') ?> <input type="input" value="5" id="wp_crm_fake_users"> <?php _e('fake users. ','wp_crm') ?>
        <input type="button" value="<?php _e('Generate','wpp') ?>" id="wp_crm_generate_fake_users">
       <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e("Restore Backup of WP-CRM Configuration", 'wpp'); ?>: <input name="wp_crm[settings_from_backup]" type="file" />
        <a href="<?php echo wp_nonce_url( "admin.php?page=wp_crm_settings&message=updated&wp_crm_action=download-wp_crm-backup", 'download-wp_crm-backup'); ?>"><?php _e("Download Backup of Current WP-CRM Configuration.");?></a>
      </div>

      <?php do_action('wp_crm_settings_help_tab'); ?>
    </div>
  </div>

</div>


<br class="cb" />

<p class="wp_crm_save_changes_row">
<input type="submit" value="<?php _e('Save Changes','wp_crm');?>" class="button-primary" name="Submit">
 </p>


</form>
</div>