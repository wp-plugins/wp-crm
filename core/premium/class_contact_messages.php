<?php
/*
Name: Shortcode Contact Forms
Class: class_contact_messages
Version: 0.1
Minimum WPC Version: 0.16
Description: Create contact forms using shortcodes and keep track of messages in your dashboard.
Feature ID: 12
*/

add_action('wp_crm_init', array('class_contact_messages', 'init'));


/**
 * class_contact_messages Class
 *
 *
 * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
 *
 * @version 1.0
 * @author Andy Potanin <andy.potanin@usabilitydynamics.com>
 * @package WP-CRM
 * @subpackage Contact Forms
 */
class class_contact_messages {

  /**
   * Init level functions for email syncronziation management
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
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
        $return['message'] = __('Message trashed.', 'wp_crm');
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

      <ul class="wp_crm_overview_filters">
      <?php do_action('wp_crm_messages_metabox_filter_before'); ?>

        <li class="wpp_crm_filter_section_title"><?php _e('Status', 'wp_crm'); ?></li>
        <li>
          <input id="wp_crm_attribute_value_new" checked="true" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="new" />
          <label for="wp_crm_attribute_value_new"><?php _e('New', 'wp_crm'); ?></label>
        </li>
        
        
        <li>
          <input id="wp_crm_attribute_value_archived" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="archived" />
          <label for="wp_crm_attribute_value_archived"><?php _e('Archived', 'wp_crm'); ?></label>
        </li>
        <li>
          <input id="wp_crm_attribute_value_all" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="all" />
          <label for="wp_crm_attribute_value_all"><?php _e('All', 'wp_crm'); ?></label>
        </li>

      <?php if(is_array($contact_forms)) {  ?>
        <li class="wpp_crm_filter_section_title"><?php _e('Originating Form'); ?></li>
      <?php foreach($contact_forms as $form_slug => $form_data) { ?>

        <li>
          <input id="wp_crm_cf_<?php echo $form_slug; ?>" type="checkbox" name="wp_crm_message_search[form_name][]" value="<?php echo $form_slug; ?>" <?php (is_array($search[$form_slug]) && in_array($option_slug, $search[$form_slug]) ? "checked" : ""); ?>/>
          <label for="wp_crm_cf_<?php echo $form_slug; ?>"><?php echo $form_data['title']; ?></label>
        </li>

      <?php } ?>
      <?php }  ?>

      <?php do_action('wp_crm_messages_metabox_filter_after'); ?>
      </ul>



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


      $return_data = $object;

      //** Rename some keys for convinience */
      $return_data['ID'] = $object['message_id'];
      $return_data['status'] = $object['value'];

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
      global $wp_crm, $wpdb;

      if($cell_data['table_scope'] != 'wp_crm_contact_messages') {
        return $cell_data;
      }

      $object = $cell_data['object'];
      $user_id = $object['user_id'];
      
      if($associated_object = $object['associated_object']) {      
        $associated_object = get_post($associated_object);
        
        //** Only allow specific post types to be "associated "*/
        if(apply_filters('wp_crm_associated_post_types', false, $associated_object->post_type)) {
          $post_type = get_post_type_object($associated_object->post_type);
        } else {
          unset($associated_object);
        }
      }
 

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

          //print_r($object);
          ?>

            <ul>
              <li><?php echo  CRM_UD_F::parse_urls(nl2br($object['text']), 100,'_blank'); ?></li>
              
              <?php if($associated_object) { ?>
              <li><?php echo sprintf(__('Related %s:','wp_crm'), $post_type->labels->singular_name); ?> <a href="<?php echo admin_url("post.php?post={$associated_object->post_ID}&action=edit"); ?>" target="_blank"><?php echo $associated_object->post_title; ?></a></li>
              <?php } ?>
              
              <li><?php echo human_time_diff(strtotime($object['time'])); ?> <?php _e('ago'); ?>.
                <?php if($additional_messages) { echo '<a href="' . admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id") . '">' . $additional_messages . ' ' . __('other messages.') . '</a>'; }  ?>
              </li>
            </ul>

            <?php

            $row_actions = array(
              'trash_message'=>__('Trash')
            );

            if($object['status'] != 'archived') {
              $row_actions['archive_message'] = __('Archive', 'wp_crm');
            }

            //** Only allow Trashing of recently registered users */
            $week_ago = date('Y-m-d', strtotime('-3 days'));
            if($wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE ID = {$user_id} AND user_registered  > '{$week_ago}'") && get_user_meta($user_id, 'wpc_cm_generated_account')) {
              $row_actions['trash_message_and_user'] = __('Trash Message and User', 'wp_crm');
              $verify_actions['trash_message_and_user'] = true;
            }

            $row_actions = apply_filters('wp_crm_message_quick_actions', $row_actions);
            $verify_actions = apply_filters('wp_crm_message_quick_actions_verification', $verify_actions);

              ?>
            <?php if($row_actions) { ?>
            <div class="row-actions">
               <?php foreach($row_actions as $action => $title) { ?>
                  <span  wp_crm_action="<?php echo $action; ?>" <?php echo ($verify_actions[$action] ? 'verify_action="true"' : '');?> object_id="<?php echo $object['ID']; ?>" class="<?php echo $action; ?> wp_crm_message_quick_action"><?php echo $title; ?></span>
               <?php } ?>
              </div>
              <?php } ?>


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
    $current['contact_messages']['title'] = __('Shortcode Forms', 'wpp');

    return $current;

  }

  function shortcode_wp_crm_form($atts, $content = null, $code = '') {
    global $wp_crm;


    $a = shortcode_atts( array(
      'js_callback_function' => false,
      'form' => false,
      'use_current_user' => 'true',
      'success_message' => __('Your message has been sent. Thank you.'),
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

    $form_vars = array(
      'form_slug' => $form_slug,
      'success_message' => $a['success_message'],
      'submit_text' => $a['submit_text']
    );

    if(isset($a['use_current_user'])) {
      $form_vars['use_current_user'] = $a['use_current_user'];
    }

    if($a['js_callback_function']) {
      $form_vars['js_callback_function']  = $a['js_callback_function'];
    }

    if($form_slug) {

    ob_start();
    class_contact_messages::draw_form($form_vars);

    $form = ob_get_contents();
    ob_end_clean();
    return preg_replace('(\r|\n|\t)', '', $form);

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
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function draw_form($form_settings) {
    global $wp_crm, $post;

     extract($form_settings);

    $form = $wp_crm['wp_crm_contact_system_data'][$form_slug];

    $wp_crm_nonce = md5(NONCE_KEY);

    //** Load user object if passed */
    if($use_current_user == 'true') {
      $current_user = wp_get_current_user();

      if ( 0 == $current_user->ID ) {
        $user_data = false;
      } else {
        $user_data = wp_crm_get_user($current_user->ID);
      }
    }

  ?>
  <form id="<?php echo md5($wp_crm_nonce . '_form'); ?>" class="wp_crm_contact_form">
  <ul class="wp_crm_contact_form">
    <li class="wp_crm_<?php echo $wp_crm_nonce; ?>_first">
      <?php /* Span Prevention */ ?>
        <input type="hidden" name="action" value="process_crm_message" />
        <input type="text" name="wp_crm_nonce" value="<?php echo $wp_crm_nonce; ?>" />
        <input type="text" name="email" />
        <input type="text" name="name" />
        <input type="text" name="url" />
        <input type="text" name="comment" />
        <input type="hidden" name="wp_crm[success_message]" value="<?php echo esc_attr($success_message); ?>" />
        <?php if($user_data) { ?>
        <input type="hidden" name="wp_crm[user_id]" value="<?php echo $current_user->ID; ?>" />
        <?php } ?>
      <?php /* Span Prevention */ ?>
    </li>
  <?php
    $tabindex = 1;

    foreach($form['fields'] as $field) {
    $this_attribute = $wp_crm['data_structure']['attributes'][$field];
    $this_attribute['autocomplete'] = 'false';

    if($user_data && $user_data[$field]) {
      $values = $user_data[$field];
    } else {
      $values = false;
    }

    ?>
    <li class="wp_crm_form_element <?php echo ($this_attribute['required'] == 'true' ? 'wp_crm_required_field' : ''); ?>">
      <label class="wp_crm_input_label"><?php echo $this_attribute['title']; ?></label>
      <div class="wp_crm_input_wrapper">
      <?php echo WP_CRM_F::user_input_field($field, $values, $this_attribute, false, "tabindex=$tabindex"); ?>
      </div>
    </li>
  <?php $tabindex++; } ?>

  <?php  if($form['message_field'] == 'on') { ?>
  <li class="wp_crm_form_element wp_crm_message_field ">
      <div class="wp_crm_input_wrapper">
        <?php echo WP_CRM_F::user_input_field('message_field', false,  array('input_type' => 'textarea'),false, "tabindex=$tabindex"); ?>
    </div>
  </li>
  <?php } ?>
    <li class="wp_crm_form_response" style="display:none;"><div></div></li>
    <li class="wp_crm_submit_row">
      <div class="wp_crm_input_wrapper">
        <input class="<?php echo md5($wp_crm_nonce . '_submit'); ?>" type="submit" value="<?php echo $submit_text; ?>" />
      </div>
      <input type="hidden" name="form_slug" value="<?php echo md5($form_slug); ?>" />
      <input type="hidden" name="associated_object" value="<?php echo $post->ID; ?>" />
    </li>
  </ul>
  </form>

  <style type="text/css">.wp_crm_<?php echo $wp_crm_nonce; ?>_first {display:none;}</style>
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

      var validation_error = false;
      var form = jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> ");

      jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").attr("disabled", true);
      
      jQuery("*", form).removeClass(form).removeClass("wp_crm_input_error");

      <?php /* Front End validation */ ?>
      jQuery("li.wp_crm_required_field", form).each(function() {

        var wrapper = this;

        if(jQuery("input.regular-text:first", wrapper).val() == '') {
          validation_error = true;
          jQuery("input.regular-text", wrapper).addClass("wp_crm_input_error");
        }

      });

      if(validation_error) {
        return false;
      }

      jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response").show();
      jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div").removeClass();
      jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div").text("<?php _e("Processing..."); ?>");

      jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?>").serialize(), function(result) {
        if(result.success == "true") {
          jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div").addClass("success");
        } else {
          jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div").addClass("failure");
          jQuery(".<?php echo md5($wp_crm_nonce . '_submit'); ?>").attr("disabled",false);
        }
        <?php if($js_callback_function) { ?>
        callback_data = {};
        callback_data.form =  jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?>");
        callback_data.result =  result;
        <?php echo $js_callback_function; ?>(callback_data);
        <?php } ?>
        jQuery("#<?php echo md5($wp_crm_nonce . '_form'); ?> .wp_crm_form_response div").text(result.message);
      }, "json");
      };
  </script>
  <?php


  }


  /**
   * Insert message into log
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function insert_message($user_id, $message, $form_slug) {
      $insert_id = WP_CRM_F::insert_event("object_id={$user_id}&user_id={$user_id}&attribute=contact_form_message&text={$message}&value=new&other={$form_slug}");
      
      if($insert_id) {
        return $insert_id;      
      }
      
      return false;
  }

  /**
   * Insert message meta into log meta
   *
   * @version 0.20
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function insert_message_meta($message_id, $meta_key, $meta_value, $args = false) {
    global $wpdb;
    
    $defaults = array(
      'meta_group' => ''
     );

    $args = wp_parse_args( $args, $defaults );
    
    
    $insert['message_id'] = $message_id;
    $insert['meta_key'] = $meta_key;
    $insert['meta_value'] = $meta_value;
    
    if(!empty($meta_group)) {
      $insert['meta_group'] = $args['meta_group'];
    }
    
    $wpdb->insert($wpdb->crm_log_meta, $insert);
   
    return $wpdb->insert_id;
    
  }


  /**
   * Processes contact form via ajax request.
   *
   * @todo add security precautions to filter out potential SQL injections or bad data (such as account escalation)
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function process_crm_message() {
    global $wp_crm;

    //** Server seems to return nothing somethines, adding space in beginning seems to solve */
    /** This needs to be removed - it causes a warning when the header items are set later in the code, when then causes the form NOT to work echo ' '; */

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

    //** Some other security */
    if(isset($data['user_data']['user_id'])) {
      //** Fail - user_id will never be passed in this manner unless somebody is screwing around */
      die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted.','wp_crm'))));
    }

    $md5_form_slug = $_REQUEST['form_slug'];
    $associated_object = $_REQUEST['associated_object'];

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

    if(isset($data['user_id'])) {
      //** User ID was passsed. Verify that current user is logged in */
     $current_user = wp_get_current_user();

      if ( 0 == $current_user->ID || $data['user_id'] != $current_user->ID) {
        //** User ID not found, or passed doesn't match. Either way, fail with ambigous messages.
        die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted.','wp_crm'))));
      } else {
        //** We have User ID, we are updating an existing profile */
        $data['user_data']['user_id']['default'][] = $current_user->ID;
      }

    }

    $user_data = @wp_crm_save_user_data($data['user_data'], 'default_role='.$wp_crm['configuration']['new_contact_role'].'&use_global_messages=false&match_login=true&no_redirect=true&return_detail=true');

    if(!$user_data) {
      if($confirmed_form_data['message_field'] == 'on') {
        //** If contact form includes a message, notify that message could not be sent */
        die(json_encode(array('success' => 'false', 'message' => __('Message could not be sent. Please make sure you have entered your information properly.','wp_crm'))));
      } else {
        //** If contact form DOES NOT include a message, notify that it could not be submitted */
        die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted. Please make sure you have entered your information properly.','wp_crm'))));
      }
    } else {
      $user_id = $user_data['user_id'];

      if($user_data['new_user']) {
        //** Log in DB that this account was created automatically via contact form */
        update_user_meta($user_id,'wpc_cm_generated_account', true);
      }

    }

    $message = WP_CRM_F::get_first_value($_REQUEST['wp_crm']['user_data']['message_field']);

    if(empty($message)) {
      //** No message submitted */
    } else {
      //** Message is submitted. Do stuff. */
      $message_id = class_contact_messages::insert_message($user_id, $message, $confirmed_form_slug);
      
      if($associated_object) {
        class_contact_messages::insert_message_meta($message_id, 'associated_object', $associated_object);
      }

      $notification_info = (array) wp_crm_get_user($user_id);
      $notification_info['message_content'] = stripslashes($message);
      $notification_info['trigger_action'] = $confirmed_form_data['title'];
      $notification_info['profile_link'] = admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id");
      wp_crm_send_notification($confirmed_form_slug,$notification_info);

    }

    $result = array('success' => 'true','message' => $data['success_message']);

    if( current_user_can('manage_options') ) {
      $result['user_id'] = $user_id;
    }

    echo json_encode($result);
    die();
  }


  /**
   * Adds content to the Messages tab on the settings page
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
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
      jQuery(".wp_crm_contact_form_shortcode", parent).val("[wp_crm_form form=" + wp_crm_create_slug(jQuery(this).val()) + "]");
      jQuery(".wp_crm_contact_form_current_form_slug", parent).val(wp_crm_create_slug(jQuery(this).val()));
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
          <?php /* <th class="wp_crm_settings_col"><?php _e('Trigger Actions','wpp') ?></th> */ ?>
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


              <li>
                <label for=""><?php _e('Role:'); ?></label>
                <select id="" name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][new_user_role]">
                  <option value=""> - </option>
                  <?php wp_dropdown_roles($data['new_user_role']); ?>
                </select>
                <span class="description"><?php _e('If new user created, assign this role.'); ?></span>
             </li>


              <li class="wp_crm_checkbox_on_left">
                <input <?php checked($data['message_field'], 'on'); ?> id="message_<?php echo $row_hash; ?>" type="checkbox"  name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][message_field]"  value="on"  value="<?php echo $data['message_field']; ?>" />
                <label for="message_<?php echo $row_hash; ?>"><?php _e('Display textarea for custom message.', 'wp_crm'); ?></label>
              </li>


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

          <?php /*
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
          */ ?>

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
                <label for="wp_crm_new_contact_role"><?php _e('Default role to use for new contacts: '); ?></label>
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
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function add_capabilities() {
    global $wp_crm;

    $wp_crm['capabilities']['View Messages'] = __('View messages from contact forms.', 'wp_crm');

  }


  /**
   * Modify admin navigational menu to include link(s) for contact message viewing.
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function admin_menu() {
    global $wp_crm;

    //** Check how many message exist */

    $new_message_count = count(class_contact_messages::get_messages());

    //** Only show message section if messages exist */
    /*
    if($message_count) {
    }
    */
    
    $wp_crm['pages']['contact_messages']['overview'] = add_submenu_page('wp_crm','Messages', 'Messages' . ($new_message_count ? ' (' . $new_message_count . ')' : ''), 'WP-CRM: View Messages', 'wp_crm_contact_messages', array('class_contact_messages', 'page_loader'), '', 30);

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

   // echo "<script type='text/javascript'>console.log('screen id: {$current_screen->id}');</script>";

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
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function crm_page_wp_crm_contact_messages() {
    global $current_screen, $wp_crm, $wpdb;

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
    
    //** Check if we have archived messaged*/
    if($wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}crm_log WHERE value = 'archived'")) {
      $show_filter = true;
    }


    $show_filter = apply_filters('wp_crm_messages_show_filter', $show_filter);

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
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function get_messages($args = false) {
    global $wpdb;

    $defaults = array(
      'value' => 'new',
      'attribute' => 'contact_form_message'
    );

    $args = wp_parse_args( $args, $defaults );

    //** Handle group message queries */
    if($args['attribute'] == 'group_message') {
      unset($args['value']);    
    }


    if(!empty($args['attribute'])) {
      $where_query[] = " attribute = '{$args['attribute']}' ";
    }

    //** Filter by type, unless 'all' is specified */
    if(!empty($args['value']) && $args['value'] != 'all') {
      $where_query[] = " value = '{$args['value']}' ";
    }

    $where_query  = 'WHERE (' . implode(" AND ", $where_query) . ") ";

    $messages = $wpdb->get_results("
      SELECT id as message_id, value, object_id as user_id, count(id) as total_messages, text, time
      FROM {$wpdb->prefix}crm_log
      $where_query
      GROUP BY object_id
      ORDER BY time DESC", ARRAY_A);
      


    //** Get messages meta */
    foreach($messages as $key => $message_data) {
      $meta_data = $wpdb->get_results("SELECT * FROM {$wpdb->crm_log_meta} WHERE message_id = {$message_data[message_id]}", ARRAY_A);      
      
      if(!empty($meta_data)) {
        foreach($meta_data as $meta_data) {
          $messages[$key][$meta_data['meta_key']] = $meta_data['meta_value'];          
        }
      }
    
    }
 
    //echo $wpdb->last_query;

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

    parse_str($_REQUEST['wp_crm_filter_vars'], $wp_crm_filter_vars);
    $wp_crm_message_search = $wp_crm_filter_vars['wp_crm_message_search'];


    //* Init table object */
    $wp_list_table = new WP_CMR_List_Table("current_screen=crm_page_wp_crm_contact_messages&table_scope=wp_crm_contact_messages&ajax=true&per_page={$per_page}&iDisplayStart={$iDisplayStart}&iColumns={$iColumns}");

    //** Load items into table class */
    $wp_list_table->all_items = class_contact_messages::get_messages($wp_crm_message_search);

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

