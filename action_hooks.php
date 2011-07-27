<?php
/**
 * WP-CRM Actions and Hooks File
 *
 * Do not modify arrays found in these files, use the filters to modify them in your functions.php file
 * Sets up default settings and loads a few actions.
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @link http://twincitiestech.com/plugins/wp-crm/
 * @version 0.1
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
*/


  // Load settings out of database to overwrite defaults from action_hooks.
  $wp_crm_db = get_option('wp_crm_settings');

  /*
    Default configuration
  */
  $wp_crm['version'] = '0.1';

  $wp_crm['configuration'] = array(
    'replace_default_user_management_screen' => 'false',
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
      
  $wp_crm['configuration']['overview_table_options']['main_view'] = array(
    'display_name',
    'user_email',
    'company',
    'phone_number'
  );
     

  /*
    User types to be used for CRM. Roles and capabilities are created for these.

    Not sure if this is the best way of handling companies, may need special provision just for companies

  */
  $wp_crm['user_types'] = array(
    'company' => array(
      'title' => "Company",
      'capabilities' => array()
    ),
    'prospect' => array(
      'title' => "Prospect",
      'capabilities' => array()
    ),
    'customer' => array(
      'title' => "Customer",
      'capabilities' => array()
    )
  );

  /*
    Permissions to be utilized thruogh the plugin.  We will need a UI to manage these, and add new ones.
    wp_crm_* prefix will automatically be added for these capabilities
  */
  $wp_crm['capabilities'] = array(
    'add_prospects' => __('Add prospects to the CRM database.', 'wp_crm'),
    'manage_settings' => __('View and edit plugin settings.', 'wp_crm'),
    'add_users' => __('Add new users.', 'wp_crm'),
    'view_main_overview' => __('View individual prospects and the overview page.', 'wp_crm')
  );


  // Overwrite $wp_crm with database setting
  if(!empty($wp_crm_db)) {
    $wp_crm = CRM_UD_F::array_merge_recursive_distinct($wp_crm, $wp_crm_db);
  }

