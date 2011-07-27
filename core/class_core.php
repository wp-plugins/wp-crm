<?php
/**
 * WP-CRM Core Framework
 *
 * @version 0.1
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
*/

/**
 * WP-CRM Core Framework Class
 *
 * Contains primary functions for setting up the framework of the plugin.
 *
 * @version 0.01
 * @package WP-CRM
 * @subpackage Main
 */
class WP_CRM_Core {

  /**
   * First function of WP_CRM_Core to be loaded, call by: after_setup_theme hook.
   *
   * Load premium features.
   *
   * @since 0.01
   *
   * @uses $wp_crm WP-CRM configuration array
   *
   */
  function WP_CRM_Core(){
    global $wp_crm, $wp_roles, $wpdb;

    do_action('wp_crm_pre_load');
    
    // Process settings updates
    WP_CRM_F::settings_action();

    // Load premium features
    WP_CRM_F::load_premium();

    add_action('init', array('WP_CRM_Core', 'init'));

  }

  /**
   * Primary init of WP_CRM_Core, gets called by after_setup_theme.
   *
   * Register scripts.
   * Register styles.
   * Load premium features.
   *
   * @since 0.01
   *
   * @uses $wp_crm WP-CRM configuration array
   *
   */
  function init() {
    global $wpdb, $wp_crm, $wp_roles;

    /** Loads all the class for handling all plugin tables */
    include_once WP_CRM_Path . '/core/class_list_table.php';


    wp_register_script('jquery-cookie', WP_CRM_URL. '/third-party/jquery.cookie.js', array('jquery'), '1.7.3' );
    wp_register_script('jquery-position', WP_CRM_URL. '/third-party/jquery.ui.position.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-slider', WP_CRM_URL. '/third-party/jquery.ui.slider.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-widget', WP_CRM_URL. '/third-party/jquery.ui.widget.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-datepicker', WP_CRM_URL. '/third-party/jquery.ui.datepicker.js', array('jquery-ui-core'));
    wp_register_script('jquery-autocomplete', WP_CRM_URL. '/third-party/jquery.ui.autocomplete.min.js', array('jquery-widget','jquery-position'));
    wp_register_script('wp-crm-global', WP_CRM_URL. '/js/wp-crm-global.js', array('jquery'));
    
    wp_register_script('wp-crm-data-tables', WP_CRM_URL. '/third-party/dataTables/jquery.dataTables.min.js', array('jquery'));
 
    // Find and register theme-specific style if a custom wp_properties.css does not exist in theme
    $theme_slug = get_option('stylesheet');
    if(file_exists( WP_CRM_Templates . "/theme-specific/{$theme_slug}.css")) {
      wp_register_style('wp-crm-theme-specific', WP_CRM_URL . "/templates/theme-specific/{$theme_slug}.css",  array(),WPP_Version);
    }
    
    if(file_exists( WP_CRM_Path . "/css/wp_crm_global.css")) {    
      wp_register_style('wp_crm_global', WP_CRM_URL . "/css/wp_crm_global.css",  array(),WPP_Version);
    }
    
    wp_register_style('wp-crm-data-tables', WP_CRM_URL . "/css/crm-data-tables.css",  array(),WPP_Version);
      
    // Plug page actions -> Add Settings Link to plugin overview page
    add_filter('plugin_action_links', array('WP_CRM_Core', 'plugin_action_links'), 10, 2 );

    // Setup pages and overview columns
    add_action("admin_menu", array('WP_CRM_Core', "admin_menu"));

    add_filter("manage_toplevel_page_wp_crm_sortable_columns", array('WP_CRM_Core', "sortable_columns"));
    add_filter("admin_body_class", array('WP_CRM_Core', "admin_body_class"));

     // Load back-end scripts
    add_action("admin_enqueue_scripts", array('WP_CRM_Core', "admin_enqueue_scripts"));

    add_action("wp_ajax_wp_crm_user_object", create_function('',' echo "CRM Object Report: \n" . print_r(wp_crm_get_user($_REQUEST[user_id]), true) . "\nRaw Meta Report: \n" .  print_r(WP_CRM_F::show_user_meta_report($_REQUEST[user_id]), true); '));
    add_action("wp_ajax_wp_crm_show_meta_report", create_function('',' die(print_r(WP_CRM_F::show_user_meta_report(), true)); '));
    add_action("wp_ajax_wp_crm_get_user_activity_stream", create_function('',' echo WP_CRM_F::get_user_activity_stream("user_id={$_REQUEST[user_id]}"); die; '));
    add_action("wp_ajax_wp_crm_insert_activity_message", create_function('',' echo WP_CRM_F::insert_event("attribute=note&object_id={$_REQUEST[user_id]}&text={$_REQUEST[content]}&ajax=true"); die; '));
        
    add_action("wp_ajax_wp_crm_do_fake_users", create_function('',' echo WP_CRM_F::do_fake_users("number={$_REQUEST[number]}&do_what={$_REQUEST[do_what]}"); die; '));
    
    //* Returns table rows for overview tbale */
    add_action("wp_ajax_wp_crm_list_table", create_function('',' echo WP_CRM_F::ajax_table_rows(); die; '));
    
    add_action("wp_ajax_wp_crm_quick_action", create_function('',' echo WP_CRM_F::quick_action(); die; '));

    // Used for processing back-end functions
    add_action("admin_head", array('WP_CRM_Core', "admin_head"));
    
    add_action("admin_init", array('WP_CRM_Core', "admin_init"));

    // Init action hook
    do_action('wp_crm_init');
    
    
    add_action('load-toplevel_page_wp_crm',  array('WP_CRM_Core', 'toplevel_page_wp_crm'));
    add_action('load-crm_page_wp_crm_settings', array('WP_CRM_Core',  'crm_page_wp_crm_settings'));

    add_action("template_redirect", array('WP_CRM_Core', "template_redirect"));
    add_action("deleted_user", array('WP_CRM_F', "deleted_user"));
     

 
    // Filers are applied
    $wp_crm['configuration']       = apply_filters('wp_crm_configuration', $wp_crm['configuration']);
    $wp_crm['notification_actions']       = apply_filters('wp_crm_notification_actions', $wp_crm['notification_actions']);

  }
  /**
   * Listens for WP-CRM shortcodes
   * 
   * @todo Enure displayed settings are being honored when saved.
   * @since 0.1
   *
   */
  function template_redirect() {
      global $post, $property, $wp, $wp_query, $wp_styles;

      if(!strpos($post->post_content, "wp_crm_form"))
        return;
        
       // Load theme-specific stylesheet if it exists
      wp_enqueue_script('jquery');
      wp_enqueue_style('wp-crm-theme-specific');
  }
  
    /**
   * Runs pre-header functions on admin-side only for the overview page
   * 
   * @todo Enure displayed settings are being honored when saved.
   * @since 0.1
   *
   */
  function toplevel_page_wp_crm() {
    add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
    add_screen_option('per_page', array('label' => __( 'Users', 'wp_crm' )) );
  }
    
  /**
   * Runs pre-header functions for settings page
   * 
   * 
   * @since 0.1
   *
   */
  function crm_page_wp_crm_settings() {
  
     // Download backup of configuration
    if($_REQUEST['wp_crm_action'] == 'download-wp_crm-backup'
      && wp_verify_nonce($_REQUEST['_wpnonce'], 'download-wp_crm-backup')) {
        global $wp_crm;
        
        $sitename = sanitize_key( get_bloginfo( 'name' ) );
        $filename = $sitename . '-wp-crm.' . date( 'Y-m-d' ) . '.txt';

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Transfer-Encoding: binary");
        header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );        

        echo json_encode($wp_crm);
        
      die();
    }
  }
  
  /**
   * Runs pre-header functions on admin-side only - ran on ALL admin pages
   * 
   * Checks if plugin has been updated.
   * 
   * @since 0.1
   *
   */
  function admin_init() {
    global $wp_rewrite, $wp_roles, $wp_crm; 
 
    do_action('wp_crm_metaboxes');  
    
 
    
    // Add overview table rows. Static because admin_menu is not loaded on ajax calls.
    add_filter("manage_toplevel_page_wp_crm_columns", array('WP_CRM_Core', "overview_columns"));        

    add_action('admin_print_scripts-' . $wp_crm['system']['pages']['settings'], create_function('', "wp_enqueue_script('jquery-ui-tabs');wp_enqueue_script('jquery-cookie');"));
    

    // Add columns number to edit page
    add_filter('screen_layout_columns',array('WP_CRM_Core','screen_layout_columns'));

    // Add metaboxes
    
    if(is_array($wp_crm['system']['pages'])) {
      foreach($wp_crm['system']['pages'] as $screen) {

        if(!class_exists($screen))
          continue;

        $location_prefixes = array('side_', 'normal_', 'advanced_');

        foreach(get_class_methods($screen) as $box) {

          // Set context and priority if specified for box


          $context = 'normal';

          if(strpos($box, "side_") === 0)
            $context = 'side';

          if(strpos($box, "advanced_") === 0)
            $context = 'advanced';

          // Get name from slug
          $label = CRM_UD_F::slug_to_label(str_replace($location_prefixes, '', $box));


          add_meta_box( $box, $label , array($screen,$box), $screen, $context, 'default');
        }
      }
    }

    
    WP_CRM_F::manual_activation();    
    
    if(is_array($wp_crm['capabilities']) && $wp_roles) {
        foreach($wp_crm['capabilities'] as $capability => $description) {
          $wp_roles->add_cap('administrator','wp_crm_' . $capability,true);
        }
    }
          
    add_filter('admin_title', array('WP_CRM_F', 'admin_title'));
  }
  
    /**
     * Sets up sortable columns columns
     *
     * @since 0.01
     *
     */
    function sortable_columns($columns) {
        global $wp_crm;
        
        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            foreach($wp_crm['data_structure']['attributes'] as $slug => $data) {
                if(isset($data['overview_column']) && $data['overview_column'] == 'true')
                    $columns[$slug] = $slug;
            }
        }
        
        $columns = apply_filters('wp_crm_admin_sortable_columns', $columns);
        
        return $columns;
    }

  /**
   * Pre-Header functions
   *
   * Loads after admin_enqueue_scripts, admin_print_styles, and admin_head.
   * Loads before: favorite_actions, screen_meta
   *
   * @since 0.1
   */
    function admin_head() {
      global $current_screen;

      do_action("wp_crm_header_{$current_screen->id}", $current_screen->id);

    }

    /**
     * Returns columns for specific person type based on $_GET[page] variable
     *
     * @since 0.1
     */
    function overview_columns($columns = false) {
        global $wp_crm;
        
        $columns['cb'] = '<input type="checkbox" />';
        $columns['user_card'] = 'Information';
    
        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            foreach($wp_crm['data_structure']['attributes'] as $slug => $data) {
                if(isset($data['overview_column']) && $data['overview_column'] == 'true')
                $columns[$slug] = $data['title'];
            }
        }
        
        return $columns;
    }


  /**
   * Sets up plugin pages and loads their scripts
   *
   * @since 0.01
   * @todo Make position incriment by one to not override anything
   *
   */
  function admin_menu() {
    global $wp_crm;
 
    do_action('wp_crm_admin_menu');

    // Replace default user management screen if set
    $position = ($wp_crm['configuration']['replace_default_user_management_screen'] == 'true' ? '30' : '33');

    // Setup main overview page
    $wp_crm['system']['pages']['core'] = add_menu_page('CRM', 'CRM', 'wp_crm_view_main_overview', 'wp_crm', array('WP_CRM_Core', 'page_loader'), '', $position);

    // Setup child pages (first one is used to be loaded in place of 'CRM'
    $wp_crm['system']['pages']['overview'] = add_submenu_page('wp_crm', 'All People', 'All People', 'wp_crm_view_main_overview', 'wp_crm', array('WP_CRM_Core', 'page_loader'));
    $wp_crm['system']['pages']['add_new'] = add_submenu_page('wp_crm', 'New Person', 'New Person', 'add_users', 'wp_crm_add_new', array('WP_CRM_Core', 'page_loader'));
    $wp_crm['system']['pages']['settings'] = add_submenu_page('wp_crm', 'Settings', 'Settings', 'wp_crm_manage_settings', 'wp_crm_settings', array('WP_CRM_Core', 'page_loader'));


  }

   /**
   * Adds columns to page layout
    *
   * Can't be called via create_function(), $columns have to be passed, otherwise all other column settings will be overwritten
    *
   * @since 0.1
   */
  function screen_layout_columns($columns) {
    $columns['crm_page_wp_crm_add_new'] = 2;
    return $columns;
  }


   /**
   * Used for loading back-end UI
    *
    * All back-end pages call this function, which then determines that UI to load below the headers.
    *
   * @since 0.01
   */
  function page_loader() {
    global $wp_crm, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID;

    echo "<script type='text/javascript'>console.log('screen id: {$current_screen->base}');</script>";


    $file_path = WP_CRM_Path . "/core/ui/{$current_screen->base}.php";


    if(file_exists($file_path))
      include $file_path;
    else
      echo "<div class='wrap'><h2>Error</h2><p>Template not found:" . $file_path. "</p></div>";

    //print_r(func_get_args());


  }



  /**
   * Can enqueue scripts on specific pages, and print content into head
   *
   *  @uses $current_screen global variable
   * @since 0.01
   *
   */
  function admin_enqueue_scripts() {
    global $current_screen, $wp_properties, $wp_crm;


    // Load scripts on specific pages
    switch($current_screen->id)  {

      case 'toplevel_page_wp_crm':
        wp_enqueue_script('wp-crm-data-tables');
        wp_enqueue_style('wp-crm-data-tables');
       break;

      case 'crm_page_wp_crm_add_new':
        wp_enqueue_script('post');
        wp_enqueue_script('jquery-autocomplete');
        wp_enqueue_script('jquery-datepicker');
      break;

      case 'crm_page_wp_crm_settings':
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-mouse');
        
        $contextual['settings'][] = __('<h3>Contact Forms</h3>');
        $contextual['settings'][] = __('<p>Contact forms are setup here, and then inserted using a shortcode into a page, or a widget. The available contact form attributes are taken from the WP-CRM attributes, and when filled out by a user, are mapped over directly into their profile. User profiles are created based on the e-mail address, if one does not already exist, for keeping track of users. </p>');
        $contextual['settings'][] = __('<h3>Cellular Notifications</h3>');
        $contextual['settings'][] = __('<p>You can send notifications to cellphone numbers.  Instead of entering an e-mail address, add the receipient\'s number using the following rules:</p><ul><li>AT&T – cellnumber@txt.att.net</li><li>Verizon – cellnumber@vtext.com</li><li>T-Mobile – cellnumber@tmomail.net</li><li>Sprint PCS - cellnumber@messaging.sprintpcs.com</li><li>Virgin Mobile – cellnumber@vmobl.com</li><li>US Cellular – cellnumber@email.uscc.net</li><li>Nextel - cellnumber@messaging.nextel.com</li><li>Boost - cellnumber@myboostmobile.com</li><li>Alltel – cellnumber@message.alltel.com</li></ul>');
        
        add_contextual_help($current_screen->id, implode("\n", $contextual['settings']));
      break;

    }

    // Load our scripts after the third-party scripts

    // Include on all pages
    wp_enqueue_script('wp-crm-global');

    // Automatically insert styles sheet if one exists with $current_screen->ID name
    if(file_exists(WP_CRM_Path . "/css/{$current_screen->id}.css")) {
      wp_enqueue_style($current_screen->id . '-style', WP_CRM_URL . "/css/{$current_screen->id}.css", array(), $wp_crm['version'], 'screen');
    }

    // Automatically insert JS sheet if one exists with $current_screen->ID name
    if(file_exists(WP_CRM_Path . "/js/{$current_screen->id}.js")) {
      wp_enqueue_script($current_screen->id . '-js', WP_CRM_URL . "/js/{$current_screen->id}.js", array('jquery'), $wp_crm['version']);
    }



  }

  /**
   * Modify admin body class on property pages for CSS
   *
   * @return string|$request a modified request to query listings
   * @since 0.5
   *
   */
   function admin_body_class($content) {
    global $current_screen;
 
    // Load scripts on specific pages
    switch($current_screen->id)  {

      case 'toplevel_page_wp_crm':
      case 'crm_page_wp_crm_add_new':
      case 'crm_page_wp_crm_settings':
        return 'wp_crm';
      break;

    }
  }


  /**
   * Adds "Settings" link to the plugin overview page
   *
   *
    * @since 0.60
   *
   */
   function plugin_action_links( $links, $file ){

     if ( $file == 'wp-crm/wp-crm.php' ){
      $settings_link =  '<a href="'.admin_url("edit.php?post_type=property&page=property_settings").'">' . __('Settings','wp_crm') . '</a>';
      array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
  }
}
?>