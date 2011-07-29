<?php
/*
Name: Contact Messages
Class: class_contact_messages
Version: 0.1
Description: Syncronize e-mails from multiple e-mail accounts.
*/

add_action('wp_crm_init', array('class_contact_messages', 'init'));


/**
 * class_contact_messages Class
 *
 *
 * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
 *
 * @version 1.0
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
 * @subpackage Email Synchronizer
 */
class class_contact_messages {

  /**
   * Init level functions for email syncronziation management
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function init() {

    class_contact_messages::add_capabilities();

    add_action("admin_menu", array('class_contact_messages', "admin_menu"), 101);
    add_action('wp_crm_settings_content_contact_messages', array('class_contact_messages', 'settings_page_tab_content'));
    add_action('wp_ajax_process_crm_message', array('class_contact_messages', 'process_crm_message'));
    add_action('wp_ajax_nopriv_process_crm_message', array('class_contact_messages', 'process_crm_message'));
    add_action('admin_enqueue_scripts', array('class_contact_messages', 'admin_enqueue_scripts'));

    add_action("wp_ajax_wp_crm_messages_table", create_function('',' echo class_contact_messages::ajax_table_rows(); die; '));

    add_filter('wp_crm_settings_nav', array('class_contact_messages', "settings_page_nav"));
    add_filter('widget_text', 'do_shortcode');
    add_filter('wp_crm_notification_actions',array('class_contact_messages', 'default_wp_crm_actions'));
    add_filter('admin_init',array('class_contact_messages', 'admin_init'));
    add_filter('wp_list_table_cell',array('class_contact_messages', 'wp_list_table_cell'));
    add_filter('wp_crm_list_table_object',array('class_contact_messages', 'wp_crm_list_table_object'));
    add_filter('wp_crm_quick_action',array('class_contact_messages', 'wp_crm_quick_action'));

    add_shortcode( 'wp_crm_form',array('class_contact_messages', 'shortcode_wp_crm_form'));

  }

/**
   * Highest level admin functions
   *
   * @since 0.1
   */
   function admin_init() {

    //** A work around to load the table columns early enough for ajax functions to use them */
    add_filter("manage_crm_page_wp_crm_contact_messages_columns", array('class_contact_messages', "overview_columns"));

    add_meta_box('wp_crm_messages_filter', __('Filter') , array('class_contact_messages','metabox_filter'), 'crm_page_wp_crm_contact_messages', 'normal', 'default');

   }

/**
   * Highest level admin functions
   *
   * @since 0.1
   */
   function wp_crm_quick_action($action) {
    global $wpdb;

    if($action['action'] == 'trash_message') {

      $success = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}crm_log WHERE id = {$action['object_id']}"));

      if($success) {
        $return['success'] = 'true';
        $return['message'] = _('Message trashed.', 'wp_crm');
        $return['action'] = 'hide_element';
        return $return;
      }

    }



    return false;
   }

/**
   * Sidebar filter for contact messages.
   *
   * @todo finish function
   * @since 0.1
   */
   function metabox_filter($wp_list_table) {
    global $wpdb, $wp_crm;

    $contact_forms = $wp_crm['wp_crm_contact_system_data'];
    $search = $_REQUEST['wp_crm_message_search'];

    if(empty($search)) {
      foreach($contact_forms as $form_slug => $form_data) {
        $search['form_name'][] = $form_slug;
      }
    }

    ?>
    <div class="misc-pub-section">

      <?php if(is_array($contact_forms)) {  ?>
      <ul class="wp_crm_overview_filters">
        <li class="wpp_crm_filter_section_title"><?php _e('Originating Form'); ?></li>
      <?php foreach($contact_forms as $form_slug => $form_data) { ?>

        <li>
          <input type="checkbox" name="wp_crm_message_search[form_name][]" value="<?php echo $form_slug; ?>" <?php (is_array($search[$form_slug]) && in_array($option_slug, $search[$form_slug]) ? "checked" : ""); ?>/>
          <label><?php echo $form_data['title']; ?></label>
        </li>

      <?php } ?>
      </ul>
      <?php }  ?>



    </div>

    <div class="major-publishing-actions">
      <div class="publishing-action">
        <?php submit_button( __('Filter Results'), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>

    <?php

   }


/**
   * Hooks into list table cell.
   *
   * Executed on all, so need to only apply to messages.
   * Converts passed object for this table_scope into standard array usable by single_cell
   *
   * @since 0.1
   */
    function wp_crm_list_table_object($data) {


      if($data['table_scope'] != 'wp_crm_contact_messages') {
        return $data;
      }

      $object = (array) $data['object'];


      $return_data['ID'] = $object['message_id'];
      $return_data['user_id'] = $object['user_id'];
      $return_data['total_messages'] = $object['total_messages'];
      $return_data['text'] = $object['text'];
      $return_data['time'] = $object['time'];

      return $return_data;

    }

/**
   * Hooks into list table cell.
   *
   * Executed on all, so need to only apply to messages.
   *
   * @since 0.1
   */
    function wp_list_table_cell($cell_data) {
      global $wp_crm;

      if($cell_data['table_scope'] != 'wp_crm_contact_messages') {
        return $current;
      }

      $object = $cell_data['object'];
      $user_id = $object['user_id'];


      switch($cell_data['column_name']) {

      case 'user_card':

        $user_object = wp_crm_get_user($user_id);

          ob_start();
                  ?>

            <div class='user_avatar'>
              <a href='<?php echo admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id"); ?>'><?php echo  get_avatar( $user_id, 50 ); ?></a>
            </div>
            <ul>
              <li class='primary'>
              <a href='<?php echo  admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id"); ?>'>
              <?php echo WP_CRM_F::get_primary_display_value($user_object); ?></a>
              </li>
              <?php foreach($wp_crm['configuration']['overview_table_options']['main_view'] as $key): ?>
                <li class="<?php echo $key; ?>">
                  <?php
                    $echo_value = apply_filters('wp_crm_display_' . $key, WP_CRM_F::get_first_value($user_object[$key]),$user_id, $user_object,  'user_card');
                    if(CRM_UD_F::is_url($echo_value)) {
                      echo "<a href='$echo_value'>$echovalue</a>";
                    } else {
                      echo $echo_value;
                    }
                  ?></li>
              <?php endforeach; ?>
            </ul>

          <?php

          $content = ob_get_contents();
          ob_end_clean();
          $r .= $content;

        break;

        case 'messages':

          $total_messages = $object['total_messages'];
          $additional_messages = ($total_messages - 1);
          ob_start();

          ?>

            <ul>
              <li><?php echo  CRM_UD_F::parse_urls(nl2br($object['text']), 100,'_blank'); ?></li>
              <li><?php echo human_time_diff(strtotime($object['time'])); ?> <?php _e('ago'); ?>.
                <?php if($additional_messages) { echo '<a href="' . admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id") . '">' . $additional_messages . ' ' . __('other messages.') . '</a>'; }  ?>
              </li>
            </ul>

            <?php $row_actions = apply_filters('wp_crm_message_quick_actions', array(
              'archive_message'=>__('Archive'),
              'trash_message'=>__('Trash'),
              'trash_message_and_user'=>__('Trash Message and User'))
              ); ?>
            <?php if($row_actions): ?>
            <div class="row-actions">
               <?php foreach($row_actions as $action => $title): ?>
                  <span  wp_crm_action="<?php echo $action; ?>" object_id="<?php echo $object['ID']; ?>" class="<?php echo $action; ?> wp_crm_message_quick_action"><?php echo $title; ?></span>
               <?php endforeach; ?>
              </div>
              <?php endif; ?>


           <?php
          $content = ob_get_contents();
          ob_end_clean();
          $r .= $content;

        break;

        case 'other_messages':

        break;

      }

      return $r;

    }


    /**
     * Add notification actions for contact message.
     *
     * @since 0.1
     */
    function default_wp_crm_actions($current) {
        global $wp_crm;

        if(is_array($wp_crm['wp_crm_contact_system_data'])) {
            foreach($wp_crm['wp_crm_contact_system_data'] as $contact_form_slug => $form_data) {
                $current[$contact_form_slug] = $form_data['title'];
            }
        }

        return $current;
    }


  function admin_enqueue_scripts() {
      global $current_screen, $wp_properties, $wp_crm;

      // Load scripts on specific pages
      switch($current_screen->id)  {

        case 'crm_page_wp_crm_contact_messages':
          wp_enqueue_style('wp_crm_global');
          wp_enqueue_script('wp-crm-data-tables');
          wp_enqueue_style('wp-crm-data-tables');
        break;


       }


  }

  function settings_page_nav($current) {
    $current['contact_messages']['slug'] = 'contact_messages';
    $current['contact_messages']['title'] = __('Contact Forms', 'wpp');

    return $current;

  }

  function shortcode_wp_crm_form($atts, $content = null, $code = '') {
    global $wp_crm;

    $a = shortcode_atts( array(
      'form' => false,
      'submit_text' => __('Submit')
    ), $atts );


    if(!$a['form']) {
      return;
    }

    //** Find form based on name */
    foreach($wp_crm['wp_crm_contact_system_data'] as $this_slug => $form_data) {

      //** Check to see if passed form tag matches either the name of the current slug */
      if($form_data['title'] == $a['form'] || $a['form'] == $form_data['current_form_slug']) {
        $form_slug = $this_slug;
        break;
      }

    }

     if($form_slug) {

      ob_start();
      class_contact_messages::draw_form(array('form_slug' => $form_slug,  'submit_text' => $a['submit_text']));
      $form = ob_get_contents();
      ob_end_clean();
      return $form;

     } else {
        return;
     }

  }


  /**
   * Echos out contact form
   *
   *
   * @todo add provision to not display fields that no longer exist
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function draw_form($form_settings) {
    global $wp_crm;

    extract($form_settings);


    $form = $wp_crm['wp_crm_contact_system_data'][$form_slug];

    $wp_crm_nonce = md5(NONCE_KEY);


  ?>

  <form id="<?php echo md5($wp_crm_nonce . '_form'); ?>" class="wp_crm_contact_form">
  <ul class='wp_crm_contact_form'>
    <li class='wp_crm_<?php echo $wp_crm_nonce; ?>_first'>
      <?php /* Span Prevention */ ?>
        <input type='hidden' name='action' value='process_crm_message' />
        <input type='text' name='wp_crm_nonce' value='<?php echo $wp_crm_nonce; ?>' />
        <input type='text' name="email" />
        <input type='text' name="name" />
        <input type='text' name="url" />
        <input type='text' name="comment" />
      <?php /* Span Prevention */ ?>
    </li>
  <?php
    $tabindex = 1;
    foreach($form['fields'] as $field):

    $this_attribute = $wp_crm['data_structure']['attributes'][$field];
    $this_attribute['autocomplete'] = 'false';

    ?>
    <li class='wp_crm_form_element'>

      <label class='wp_crm_input_label'><?php echo $this_attribute['title']; ?></label>
      <div class="wp_crm_input_wrapper">
      <?php echo WP_CRM_F::user_input_field($field, false, $this_attribute, false, "tabindex=$tabindex"); ?>
      </div>
    </li>

  <?php $tabindex++; endforeach; ?>

  <?php  if($form['message_field'] == 'on'): ?>
  <li class='wp_crm_form_element wp_crm_message_field '>
      <div class="wp_crm_input_wrapper">
        <?php echo WP_CRM_F::user_input_field('message_field', false,  array('input_type' => 'textarea'),false, "tabindex=$tabindex"); ?>
    </div>
  </li>
  <?php endif; ?>

    <li class='wp_crm_form_response' style='display:none;'><div></div></li>

    <li class='wp_crm_submit_row'>
      <div class="wp_crm_input_wrapper">
        <input class='<?php echo md5($wp_crm_nonce . '_submit'); ?>' type='submit' value='<?php echo $submit_text; ?>' />
      </div>
      <input type='hidden' name="form_slug" value="<?php echo md5($form_slug); ?>" />
    </li>

  </ul>
  </form>

  <style type="text/css">
    .wp_crm_<?php echo $wp_crm_nonce; ?>_first {display:none;}
  </style>
  <script type="text/javascript">
    jQuery(document).ready(function() {

      jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?>").submit(function(event) {
        event.preventDefault();
        submit_<?php echo md5($wp_crm_nonce . '_form'); ?>();
      });


      jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").click(function(event) {
        event.preventDefault();
        submit_<?php echo md5($wp_crm_nonce . '_form'); ?>();
      });
    });


    function submit_<?php echo md5($wp_crm_nonce . '_form'); ?>() {
      jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response').show();
      jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div').removeClass();
      jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div').text('<?php _e('Processing...'); ?>');

      //* Don't disable for development jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").attr('disabled',true);

      jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?>").serialize(), function(result) {

        if(result.success == 'true') {
          jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div').addClass('success');
          jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").attr('disabled',false);
        } else {
          jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div').addClass('failure');
          jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").attr('disabled',false);
        }

        jQuery('#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div').text(result.message);
      }, 'json');

      };

  </script>


  <?php


  }


  /**
   * Insert message into log
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function insert_message($user_id, $message, $form_slug) {
      $insert_id = WP_CRM_F::insert_event("object_id={$user_id}&user_id={$user_id}&attribute=contact_form_message&text={$message}&value=new&other={$form_slug}");
  }


  /**
   * Processes contact form via ajax request.
   *
   * @todo add security precautions to filter out potential SQL injections or bad data (such as account escalation)
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function process_crm_message() {
    global $wp_crm;

    //** watch for spam */
    if(!empty($_REQUEST['comment']) ||
          !empty($_REQUEST['email']) ||
            !empty($_REQUEST['name']) ||
              !empty($_REQUEST['url'])) {
      die(json_encode(array('success' => 'false', 'message' => __('If you see this message, WP-CRM through you were a robot.  Please contact admin if you do not think are you one.','wp_crm'))));
    }

    $data = $_REQUEST['wp_crm'];

    if(empty($data)) {
      die();
    }

    $md5_form_slug = $_REQUEST['form_slug'];
    foreach($wp_crm['wp_crm_contact_system_data'] as $form_slug => $form_data) {

      if($md5_form_slug == md5($form_slug)) {

        $confirmed_form_slug = $form_slug;
        $confirmed_form_data = $form_data;
        continue;
      }
    }

    if(!$confirmed_form_slug) {
      die();
    }

    $user_id = wp_crm_save_user_data($_REQUEST['wp_crm']['user_data'], 'default_role='.$wp_crm['configuration']['new_contact_role'].'&use_global_messages=false&match_login=true');

    if(!$user_id) {
      if($confirmed_form_data['message_field'] == 'on') {
        //** If contact form includes a message, notify that message could not be sent */
        die(json_encode(array('success' => 'false', 'message' => __('Message could not be sent. Please make sure you have entered your information properly.','wp_crm'))));
      } else {
        //** If contact form DOES NOT include a message, notify that it could not be submitted */
        die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted. Please make sure you have entered your information properly.','wp_crm'))));
      }
    }

    $message = WP_CRM_F::get_first_value($_REQUEST['wp_crm']['user_data']['message_field']);

    if(empty($message)) {
      //** No message submitted */

    }
    $message_id = class_contact_messages::insert_message($user_id, $message, $confirmed_form_slug);
 
    $notification_info = (array) wp_crm_get_user($user_id);
    $notification_info['message_content'] = stripslashes($message);
    $notification_info['profile_link'] = admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id");

    wp_crm_send_notification($confirmed_form_slug,$notification_info);

    $result = array('success' => 'true','message' => __('Your message has been sent. Thank you.','wp_crm'));

    echo json_encode($result);
    die();
  }


  /**
   * Adds content to the Messages tab on the settings page
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function settings_page_tab_content($wp_crm) {

    if(empty($wp_crm['wp_crm_contact_system_data'])) {
      $wp_crm['wp_crm_contact_system_data']['example_form']['title'] = 'Example Contact Form';
      $wp_crm['wp_crm_contact_system_data']['example_form']['full_shortcode'] = '[wp_crm_form form=example_contact_form]';
      $wp_crm['wp_crm_contact_system_data']['example_form']['current_form_slug'] = 'example_contact_form';
    }
  ?>
  <script type="text/javascript">
    jQuery(document).ready(function() {

    jQuery("#wp_crm_wp_crm_contact_system_data .slug_setter").live('change', function() {
      var parent = jQuery(this).parents('.wp_crm_notification_main_configuration');
      jQuery('.wp_crm_contact_form_shortcode', parent).val('[wp_crm_form form=' + wp_crm_create_slug(jQuery(this).val()) + ']');
      jQuery('.wp_crm_contact_form_current_form_slug', parent).val(wp_crm_create_slug(jQuery(this).val()));
    });


    });
  </script>
  <div class="wp_crm_inner_tab">


      <p>
        <?php _e('Use this section to add and configure new contact forms.', 'wp_crm'); ?>
      </p>
     <table id="wp_crm_wp_crm_contact_system_data" class="form-table wp_crm_form_table ud_ui_dynamic_table widefat">
      <thead>
        <tr>
           <th class="wp_crm_contact_form_header_col"><?php _e('Form Settings','wpp') ?></th>
           <th class="wp_crm_contact_form_attributes_col"><?php _e('Fields','wpp') ?></th>
          <th class="wp_crm_settings_col"><?php _e('Trigger Actions','wpp') ?></th>
          <th class="wp_crm_delete_col">&nbsp;</th>
          </tr>
      </thead>
      <tbody>
      <?php  foreach($wp_crm['wp_crm_contact_system_data'] as $contact_form_slug => $data):  $row_hash = rand(100,999); ?>
        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $contact_form_slug; ?>"  new_row='false'>
          <td class='wp_crm_contact_form_header_col'>
            <ul class="wp_crm_notification_main_configuration">
              <li>
                <label for=""><?php _e('Title:', 'wp_crm'); ?></label>
                <input type="text" id="title_<?php echo $row_hash; ?>" class="slug_setter regular-text" name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][title]" value="<?php echo $data['title']; ?>" />
              </li>

              <li>
                <label><?php _e('Shortcode:', 'wp_crm'); ?></label>
                <input type="text" READONLY class='regular-text wp_crm_contact_form_shortcode'   name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][full_shortcode]"   value="<?php echo $data['full_shortcode']; ?>" />
                <input type="hidden" class='regular-text wp_crm_contact_form_current_form_slug'   name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][current_form_slug]"   value="<?php echo $data['current_form_slug']; ?>" />
              </li>

              <li class="wp_crm_checkbox_on_left">
                <input <?php checked($data['message_field'], 'on'); ?> id="message_<?php echo $row_hash; ?>" type="checkbox"  name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][message_field]"  value="on"  value="<?php echo $data['message_field']; ?>" />
                <label for="message_<?php echo $row_hash; ?>"><?php _e('Display message field textarea.', 'wp_crm'); ?></label>
              <li>


            </ul>
          </td>
          <td>
           <?php if(is_array($wp_crm['data_structure']['attributes'])): ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['data_structure']['attributes'] as $attribute_slug => $attribute_data):

              if(empty( $attribute_data['title'])) {
                continue;
              }

              ?>
                <li>
                  <input id="field_<?php echo $attribute_slug; ?>_<?php echo $row_hash; ?>" type="checkbox" <?php CRM_UD_UI::checked_in_array($attribute_slug, $data['fields']); ?> name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][fields][]"  value="<?php echo $attribute_slug; ?>" />
                  <label for="field_<?php echo $attribute_slug; ?>_<?php echo $row_hash; ?>"><?php echo $attribute_data['title']; ?></label>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

          </td>
          <td class="wp_crm_settings_col">

            <?php if(is_array($wp_crm['notification_actions'])): ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['notification_actions'] as $action_slug => $action_title):

              if(empty($action_title)) {
                continue;
              }
              ?>
                <li>
                  <input <?php if( $action_slug == $contact_form_slug) echo ' DISABLED checked=true ' ; ?> id="field_<?php echo $action_slug; ?>_<?php echo $row_hash; ?>"  type="checkbox" <?php CRM_UD_UI::checked_in_array($action_slug, $data['fire_on_action']); ?> name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][fire_on_action][]"  value="<?php echo $action_slug; ?>" />
                  <label for="field_<?php echo $action_slug; ?>_<?php echo $row_hash; ?>"><?php echo $action_title; ?></label>
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
      <p><?php _e('To see list of variables you can use in wp_crm_contact_system_data open up the "Help" tab and view the user data structure.  Any variable you see in there can be used in the subject field, to field, BCC field, and the message body. Example: [user_email] would include the recipient\'s e-mail.', 'wp_crm'); ?></p>
      <p><?php _e('To add notification actions use the <b>wp_crm_notification_actions</b> filter, then call the action within <b>wp_crm_send_notification()</b> function, and the messages association with the given action will be fired off.', 'wp_crm'); ?></p>


      <table class='form-table'>
        <tr>
          <th><?php _e('Options'); ?></th>
          <td>
            <ul>
              <li>
                <label for="wp_crm_new_contact_role"><?php _e('Role to use for new contacts'); ?>
                 <select id="wp_crm_new_contact_role" name="wp_crm[configuration][new_contact_role]"><option value=""> - </option><?php wp_dropdown_roles($wp_crm['configuration']['new_contact_role']); ?></select>
                 <div class="description"><?php _e('WP-CRM creates user profiles, if only temporary, to store inquiries and messages from contact forms.  ', 'wp_crm'); ?></div>

              </li>
            </ul>
          </td>
      </table>

      <?php do_action('wp_crm_settings_notification_tab'); ?>


    </div>

<?php
  }


  /**
   * Ad contact message specific capabilities
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function add_capabilities() {
    global $wp_crm;

    $wp_crm['capabilities']['view_messages'] = __('View messages from contact forms.', 'wp_crm');

  }


  /**
   * Modify admin navigational menu to include link(s) for contact message viewing.
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function admin_menu() {
    global $wp_crm;

    //** Check how many message exist */
    $message_count = count(class_contact_messages::get_messages());


    //** Only show message section if messages exist */
    if($message_count) {
      $wp_crm['pages']['contact_messages']['overview'] = add_submenu_page('wp_crm','Messages', 'Messages', 'wp_crm_view_messages', 'wp_crm_contact_messages', array('class_contact_messages', 'page_loader'), '', 30);
    }

    // Add columns to overview page
    add_filter("manage_{$wp_crm['pages']['contact_messages']['overview']}_columns", array('class_contact_messages', "overview_columns"));

  }


 /**
  * Returns columns for specific person type based on $_GET[page] variable
  *
  * @since 0.1
  */
  function overview_columns($columns) {
    global $wp_crm;

    $columns['cb'] = '<input type="checkbox" />';
    $columns['messages'] = __('Message', 'wp_crm');
    $columns['user_card'] = __('Sender', 'wp_crm');


    return $columns;
  }



    /**
    * Used for loading back-end UI
    *
    * All back-end pages call this function, which then determines that UI to load below the headers.
    *
    * @since 0.1
   */
  function page_loader() {
    global $wp_crm, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID;

    echo "<script type='text/javascript'>console.log('screen id: {$current_screen->id}');</script>";

    // Figure out what object we are working with
    $object_slug = $current_screen->base;



    if(method_exists('class_contact_messages',$object_slug))
      call_user_func(array('class_contact_messages', $object_slug));
    else
      echo "<div class='wrap'><h2>Template Error</h2><p>Template via method <b>class_contact_messages::{$object_slug}()</b> not found.</p><div>";

    //print_r(func_get_args());


  }

  /**
   * Contact for the contact message overview page
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function crm_page_wp_crm_contact_messages() {
    global $current_screen, $wp_crm;

    $wp_list_table = new WP_CMR_List_Table("table_scope=wp_crm_contact_messages&per_page=25&ajax_action=wp_crm_messages_table");

    //** Load items into table class */
    $wp_list_table->all_items = class_contact_messages::get_messages();

    //** Items are only loaded, prepare_items() only paginates them */
    $wp_list_table->prepare_items();

    $wp_list_table->data_tables_script();

    //** Determine if sidebar filter shuold be displayed */

    $show_filter = false;

    if(count($wp_crm['wp_crm_contact_system_data']) > 1) {
      $show_filter = true;
    }

    ?>



<div class="wp_crm_overview_wrapper wrap">
    <h2><?php _e('Contact Messages', 'wp_crm'); ?></h2>

    <div id="poststuff" class="<?php echo $current_screen->id; ?>_table metabox-holder <?php if($show_filter){ ?>has-right-sidebar<?php } ?>">
      <form id="wp-crm-filter" action="#" method="POST">

      <?php if($show_filter){ ?>
        <div class="wp_crm_sidebar inner-sidebar">
          <div class="meta-box-sortables ui-sortable">
            <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
          </div>
        </div>
        <?php } ?>

        <div id="post-body">
          <div id="post-body-content">
            <?php $wp_list_table->display(); ?>
          </div> <?php /* .post-body-content */ ?>
        </div> <?php /* .post-body */ ?>

      </form>
      <br class="clear" />

  </div> <?php /* #poststuff */ ?>
</div> <?php /* .wp_crm_overview_wrapper */ ?>

    <?php
  }

  /**
   * Main function to query messages from log
   *
   * @version 1.0
   * Copyright 2010 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  function get_messages($args = false) {
    global $wpdb;

    if($args) {

      if(!empty($args['contact_form'])) {
        $where_query = " AND other = '{$args['contact_form']}' ";
      }

    }

    $messages = $wpdb->get_results("SELECT id as message_id, object_id as user_id, count(id) as total_messages, text, time FROM {$wpdb->prefix}crm_log WHERE attribute='contact_form_message' AND value = 'new' $where_query GROUP BY object_id ORDER BY time DESC");
    $messages = stripslashes_deep($messages);


    return $messages;

  }


/**
  * Draws table rows for ajax call
  *
  *
  * @since 0.1
  *
  */
  function ajax_table_rows() {

    include WP_CRM_Path . '/core/class_user_list_table.php';

    //** Get the paramters we care about */
    $sEcho = $_REQUEST['sEcho'];
    $per_page = $_REQUEST['iDisplayLength'];
    $iDisplayStart = $_REQUEST['iDisplayStart'];
    $iColumns = $_REQUEST['iColumns'];


    //* Init table object */
    $wp_list_table = new WP_CMR_List_Table("current_screen=crm_page_wp_crm_contact_messages&table_scope=wp_crm_contact_messages&ajax=true&per_page={$per_page}&iDisplayStart={$iDisplayStart}&iColumns={$iColumns}");

    //** Load items into table class */
    $wp_list_table->all_items = class_contact_messages::get_messages();

    $wp_list_table->prepare_items();

      if ( $wp_list_table->has_items() ) {


        foreach ( $wp_list_table->items as $count => $item ) {
          $data[] = $wp_list_table->single_row( $item );
        }

      } else {
          $data[] = $wp_list_table->no_items();
      }

    return json_encode(array(
      'sEcho' => $sEcho,
      'iTotalRecords' => count($wp_list_table->all_items),
      'iTotalDisplayRecords' =>count($wp_list_table->all_items),
      'aaData' => $data
      ));

  }

}

