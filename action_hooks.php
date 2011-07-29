<?php
/**
 * WP-CRM Actions and Hooks File
 *
 * Do not modify arrays found in these files, use the filters to modify them in your functions.php file
 * Sets up default settings and loads a few actions.
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 *
 * @link http://twincitiestech.com/plugins/
 * @version 0.1
 * @author Usability Dynamics, Inc. <info@usabilitydynamics.com>
 * @package WP-CRM
*/


  // Load settings out of database to overwrite defaults from action_hooks.
  $wp_crm_db = get_option('wp_crm_settings');

  /*
    Default configuration
  */
  $wp_crm['version'] = '0.1';

  $wp_crm['configuration'] = array(
    'default_user_capability' => 'prospect',
    'default_user_capability_permissions_base' => 'subscriber',
    'create_individual_pages_for_crm_capabilities' => 'true'
    );

  $wp_crm['configuration']['mail'] = array(
    'sender_name' => get_bloginfo(),
    'send_email' => get_bloginfo('admin_email')
  );
   $wp_crm['configuration']['input_types'] = array(
    'text' => __('Single Line Text', 'wp_crm'),
    'checkbox' => __("Checkbox", 'wp_crm'),
    'textarea' => __("Textarea", 'wp_crm'),
    'dropdown' => __("Dropdown", 'wp_crm'),
    'password' => __("Password", 'wp_crm')
  );
      
 
 
  /*
    Permissions to be utilized through the plugin. 
  */
  $wp_crm['capabilities'] = array(
    'manage_settings' => __('View and edit plugin settings.', 'wp_crm'),
    'view_main_overview' => __('View individual prospects and the overview page.', 'wp_crm')
  );


  // Overwrite $wp_crm with database setting
  if(!empty($wp_crm_db)) {
    $wp_crm = CRM_UD_F::array_merge_recursive_distinct($wp_crm, $wp_crm_db);
  }

