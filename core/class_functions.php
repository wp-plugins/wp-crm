<?php
/**
 * WP-CRM General Functions
 *
 * Contains all the general functions used by the plugin.
 *
 * @version 0.01
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-CRM
 * @subpackage Functions
 */

class WP_CRM_F {


/**
   * Handle version-specific updates
   *
   * Ran if version in DB is older than version of THIS code right before the DB version is updated.
   *
   * @since 0.1
   *
   */
   function handle_update($old_version) {
    global $wp_roles;


    if($old_version < 0.17) {
        /*
          Version prior to 0.17 used poorly labeled and structured capabilities.
          We remove all old capabilities
        */

        $roles = $wp_roles->get_names();

        foreach($roles as $role => $role_label) {
          $wp_roles->remove_cap( $role, 'wp_crm_manage_settings' );
          $wp_roles->remove_cap( $role, 'wp_crm_add_prospects' );
          $wp_roles->remove_cap( $role, 'wp_crm_view_main_overview' );
          $wp_roles->remove_cap( $role, 'wp_crm_manage_settings' );
          $wp_roles->remove_cap( $role, 'wp_crm_view_messages' );
          $wp_roles->remove_cap( $role, 'wp_crm_add_users' );
          $wp_roles->remove_cap( $role, 'wp_crm_Manage Settings' );


        }

      }

   }


/**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @since 0.1
   *
   */
    static function get_notification_template($slug = '') {
      global $wp_crm;
      
      if(!empty($wp_crm['notifications'][$slug])) {
        return json_encode($wp_crm['notifications'][$slug]);;
      } else {
        return json_encode(array('error' => __('Notification template not found.')));
      }
    }
    
    
    
/**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @since 0.1
   *
   */
    static function csv_export($wp_crm_search = '') {
      global $wpdb, $wp_crm;

      $file_name = "wp-crm-export-".date("Y-m-d").".csv";

      $meta_keys = $wp_crm['data_structure']['meta_keys'];

      $primary_columns = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->users}");
            
      $results = WP_CRM_F::user_search($wp_crm_search); 

      foreach($results as $result) {
      
        $primary = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$result->ID}", ARRAY_A);

        foreach($meta_keys as $meta_key => $meta_label) {
        
          $meta_key_labels[] = $meta_label;
          
          if(in_array($meta_key, $primary_columns)) {
            $value = $primary[$meta_key];
          } else {        
            $value = get_user_meta($result->ID, $meta_key, true);
          }
          
          if(!empty($value)) {
            $display_columns[$meta_key] = $meta_label;
          }
            
          $user[trim($meta_key)] = trim($value);
          
        }

        $users[] = $user;

      }      
 
      header("Content-type: application/csv");
      header("Content-Disposition: attachment; filename=$file_name");
      header("Pragma: no-cache");
      header("Expires: 0");
 
      echo implode(',', $display_columns) . "\n";
      
      foreach($users as $user) {
        unset($this_row);
        foreach($display_columns as $meta_key => $meta_label) {
          $this_row[] = '"' . $user[$meta_key] . '"';
        }
        echo implode(",", $this_row) . "\n";
      }   
      
 

    }




/**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @since 0.1
   *
   */
    static function maybe_load_profile($args = '') {
      global $wp_crm_user;

      if($_GET['page'] == 'wp_crm_add_new' && !empty($_REQUEST['user_id']))  {
        $maybe_user = wp_crm_get_user($_REQUEST['user_id']);

        if($maybe_user) {
          $wp_crm_user = $maybe_user;
          do_action('wp_crm_user_loaded', $wp_crm_user);
        } else {
          $wp_crm_user = false;
        }
        
      }
      
    }


 /**
    * Show user creation UI (mostly for ajax calls)
    *
    * @todo Prone to breaking because of the way values are passed.
    * @since 0.1
    *
    */
    static function display_shortcode_form($args = '') {

      if(!empty($args['shortcode'])) {
        $atts = $args['atts'];

        echo do_shortcode("[{$args['shortcode']} {$atts}]");
      }
    }


    /**
     * Outputs user options as list.
     *
     * @since 0.1
     *
    */
    static function list_options($user_object, $column_name, $args = '') {
      global $wp_crm;

      if(!is_array($user_object[$column_name])) {
        return;
      }
      foreach($user_object[$column_name] as $option_type_slug => $option_type_values) {

        foreach($option_type_values as $single_option_value) {
          if($single_option_value == 'on') {
            $return[] = $wp_crm['data_structure']['attributes'][$column_name]['option_labels'][$option_type_slug];
          }
        }

      }

      return $return;
    }


    /**
     * Generate fake users
     *
     * This function is mostly for development.
     *
     * @todo Add function to remove dummy users.
     * @since 0.1
     *
    */
    static function do_fake_users($args = '') {
      global $wp_crm, $wpdb;

      $defaults = array(
          'number' => 5,
          'do_what' => 'generate'
      );

      $args = wp_parse_args( $args, $defaults );
      $count = 0;

      if($args['do_what'] == 'generate') {


        $names = array('John', 'Bill', 'Randy', 'Mary', 'Jenna', 'Beth', 'Allyson', 'Samantha');
        $emails = array('gmail.com', 'yahoo.com', 'msn.com', 'acme.com', 'xyz.com', 'mac.com', 'microsoft.com', 'google.com');


        while ($count <= $args['number']) {
          $count++;

          $user_data['first_name'] = $names[array_rand($names, 1)];
          $user_data['last_name'] = $names[array_rand($names, 1)];
          $user_data['display_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
          $user_data['user_email'] = $user_data['first_name'] . '.' . $user_data['last_name'] . '@' . $emails[array_rand($emails, 1)];
          $user_data['user_login'] = $user_data['user_email'];


         $user_id = wp_insert_user( $user_data ) ;

          if($user_id && !is_wp_error($user_id)) {
            update_user_meta($user_id,  'wp_crm_fake_user', true);
            update_user_meta($user_id,  'company_name', $names[array_rand($names, 1)] . ' Inc.');
            update_user_meta($user_id,  'phone_number', rand(1000000000,9999999999));
            $generated_users[] = $user_id;
          }  else {
            print_r($user_id);
          }

        }

        echo 'Generated ' . count($generated_users) . ' fake users. User IDs: ' . print_r($generated_users, true);

      }

      if($args['do_what'] == 'remove') {


      }

    }


/**
   * Performs a user search
   *
   *
   * @since 0.1
   *
   */
    static function user_search($search_vars = false, $args = array()) {
      global $wp_crm, $wpdb;


      $sort_by = ' ORDER BY user_registered DESC ';
      /** Start our SQL, we include the 'WHERE 1' to avoid complex statements later */
      $sql = "SELECT * FROM {$wpdb->prefix}users AS u WHERE 1";

      if(!empty($search_vars)) {
        foreach($search_vars as $primary_key => $key_terms) {

          //** Handle search_string differently, it applies to all meta values */
          if($primary_key == 'search_string'){
            /* First, go through the users table */
            $tofind = strtolower($key_terms);
            $sql .= " AND (";
            $sql .= " u.ID IN (SELECT ID FROM {$wpdb->prefix}users WHERE LOWER(display_name) LIKE '%$tofind%' OR LOWER(user_email) LIKE '%$tofind%')";
            /* Now go through the users meta table */
            $sql .= " OR u.ID IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE LOWER(meta_value) LIKE '%$tofind%')";
            $sql .= ")";
            continue;
          }

          //** Handle role filtering differently too*/
          if($primary_key == 'wp_role') {
            $sql .= " AND (";
            unset($or);
            foreach($key_terms as $single_term) {
              $or = (isset($or) ? " OR " : "");
              $sql .= "{$or}u.ID IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%$single_term%')";
            }
            $sql .= ")";
            continue;
          }

          //** Build array of actual meta keys and values ot look for */
          if(is_array($key_terms)) {
            /** Anything in here is required for the user (it's an AND), so we enclose our statement with () */
            $sql .= " AND (1";
            foreach($key_terms as $single_term) {
              $meta_key = $wp_crm['data_structure']['attributes'][$primary_key]['option_keys'][$single_term];
              $sql .= " AND u.ID IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = '$meta_key' AND (meta_value = 'on' OR meta_value = 'true'))";
            }
            $sql .= ")";
          }

        }
      }

      $sql = $sql . $sort_by;

      $results = $wpdb->get_results($sql);


      return $results;
    }


 /**
    * Draws table rows for ajax call
    *
    *
    * @since 0.1
    *
    */
    static function ajax_table_rows($wp_settings = false) {

      include WP_CRM_Path . '/core/class_user_list_table.php';

      //** Get the paramters we care about */
      $sEcho = $_REQUEST['sEcho'];
      $per_page = $_REQUEST['iDisplayLength'];
      $iDisplayStart = $_REQUEST['iDisplayStart'];
      $iColumns = $_REQUEST['iColumns'];

      //** Parse the serialized filters array */
      parse_str($_REQUEST['wp_crm_filter_vars'], $wp_crm_filter_vars);
      $wp_crm_search = $wp_crm_filter_vars['wp_crm_search'];


      //* Init table object */
      $wp_list_table = new CRM_User_List_Table("ajax=true&per_page={$per_page}&iDisplayStart={$iDisplayStart}&iColumns={$iColumns}");

      $wp_list_table->prepare_items($wp_crm_search);


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
        'user_ids' => $wp_list_table->user_ids,
        'page_user_ids' => $wp_list_table->page_user_ids,
        'aaData' => $data
        ));

    }


    /**
     * Generates all possible meta keys given the data structure
     *
     *
     * @since 0.1
     *
    */
    static function build_meta_keys($wp_settings = false) {
      global $wpdb;

      if(!$wp_settings) {
        global $wp_crm;
      } else {
        $wp_crm = $wp_settings;
      }

       foreach($wp_crm['data_structure']['attributes'] as $main_key => $attribute_data) {

         $meta_keys[$main_key] = $attribute_data['title'];


        if(!empty($attribute_data['options'])) {

          //** Watch for taxonomy: slug */
          if(strpos($attribute_data['options'], 'taxonomy:') !== false) {
            $source_taxonomy = trim(str_replace('taxonomy:', '', $attribute_data['options']));

            //** Load all taxonomy terms.  Cannot use get_terms() because this function is ran before most others run register_taxonomy() */
            $taxonomy_terms = $wpdb->get_results("SELECT tt.term_id, name, slug, description FROM {$wpdb->prefix}term_taxonomy tt LEFT JOIN {$wpdb->prefix}terms t ON tt.term_id = t.term_id WHERE taxonomy = '{$source_taxonomy}' AND name != ''");

            if($taxonomy_terms) {
              foreach($taxonomy_terms as $term_data) {
                 $exploded_array[] = $term_data->name;
              }
            }

          } else {

            if(strpos($attribute_data['options'], ',')) {
              $exploded_array = explode(',', $attribute_data['options']);
            } else {
              $exploded_array = array($attribute_data['options']);
            }

          }

          //** Go through every option and identify what meta_key it will use */
          foreach($exploded_array as $option_title) {
            $option_key = $main_key . '_option_' . sanitize_title_with_dashes($option_title);
            $meta_keys[$option_key] = $option_title;


            $wp_crm['data_structure']['attributes'][$main_key]['option_keys'][sanitize_title_with_dashes($option_title)] = $option_key;
            $wp_crm['data_structure']['attributes'][$main_key]['option_labels'][sanitize_title_with_dashes($option_title)] = trim($option_title);
          }

          if(!empty($wp_crm['data_structure']['attributes'][$main_key]['option_keys'])) {
            $wp_crm['data_structure']['attributes'][$main_key]['has_options'] = true;
          }


        } else {
          unset($wp_crm['data_structure']['attributes'][$main_key]['options']);
        }

       }

       $wp_crm['data_structure']['meta_keys'] = $meta_keys;


       return $wp_crm['data_structure'];


    }


  /**
   * Handle "quick actions" via ajax
   *
   * Return json instructions on next action
   *
   * @since 0.1
   *
   */
  function show_user_meta_report($user_id = false) {
    global $wpdb;

    if($user_id = intval($user_id)) {
      $user_specific_query = " AND user_id = '$user_id' ";
    }

    $exclude_prefices = array('screen_layout_', 'meta-box-order_', 'metaboxhidden', 'closedpostboxes_', 'managetoplevel_', 'manageedi');
    $excluded_keys = implode("%' AND meta_key NOT LIKE '" ,  $exclude_prefices);

    //* get all user meta keys */
    $meta_keys = $wpdb->get_col("SELECT DISTINCT(meta_key) FROM {$wpdb->usermeta} WHERE (meta_key NOT LIKE '{$excluded_keys}%') GROUP BY meta_key");

    foreach($meta_keys as $key) {


      if(!$typical_options = $wpdb->get_col("SELECT DISTINCT(meta_value) FROM {$wpdb->usermeta} WHERE meta_key = '$key'  AND meta_value != '' $user_specific_query LIMIT 0, 3 ")) {
        continue;
      }

      $return[$key] = implode(',', $typical_options);



    }

    return $return;


  }



  /**
   * Handle "quick actions" via ajax
   *
   * Return json instructions on next action
   *
   * @since 0.1
   *
   */
  function quick_action($array = false) {
    global $wpdb;

    $action = $_REQUEST['wp_crm_quick_action'];
    $object_id = $_REQUEST['object_id'];

    switch ($action) {


      case 'archive_message':

        $wpdb->update($wpdb->prefix . 'crm_log', array('value' => 'archived'), array('id' =>$object_id));
        $return['success'] = 'true';
        $return['message'] = _('Message archived.', 'wp_crm');
        $return['action'] = 'hide_element';

      break;


      case 'delete_log_entry':

        do_action('wp_crm_delete_log_entry', $object_id);

        if($wpdb->query("DELETE FROM {$wpdb->prefix}crm_log WHERE id = {$object_id}")) {
          $return['success'] = 'true';
          $return['message'] = _('Message deleted.', 'wp_crm');
          $return['action'] = 'hide_element';
        }


      break;


      case 'trash_message_and_user':

        if( current_user_can( 'delete_users' ) ) {
         $user_id = $wpdb->get_var("SELECT object_id FROM {$wpdb->prefix}crm_log WHERE id = $object_id AND object_type = 'user' ");

         if($user_id) {
          wp_delete_user($user_id);
        }

        $return['success'] = 'true';
        $return['message'] = _('Sender trashed.', 'wp_crm');
        $return['action'] = 'hide_element';
        }

      break;

      default:
        $return = apply_filters('wp_crm_quick_action', array(
          'action' => $action,
          'object_id' => $object_id
        ));

      break;


    }



    if(is_array($return)) {
      return json_encode($return);
    } else {
      return false;
    }

  }





  /**
   * Delete WP-CRM related user things
   *
   *
   * @since 0.1
   *
   */
   function deleted_user($object_id) {
    global $wpdb;

    $wpdb->query("DELETE FROM {$wpdb->prefix}crm_log WHERE object_type = 'user' AND object_id = $object_id");


   }


  /**
   * Returns first value from an array
   *
   *
   * @since 0.1
   *
   */
   function get_first_value($array = false) {

    if(!$array) {
      return false;
    }

    if(!is_array($array)) {
      return $array;
    }

    if(isset($arary['default'][0])) {
      return $arary['default'][0];
    }

    foreach($array as $key => $data) {

      if(isset($data['value'])) {
        return $data['value'];
      }

      if(isset($data[0])) {
        return $data[0];
      }

    }


   }

   /**
   * Returns notifications for a given trigger action
   *
   *
   * @since 0.1
   *
   */
   function get_trigger_action_notification($action = false, $force = false) {
    global $wp_crm;

    if(!$action) {
      return;
    }

    foreach($wp_crm['notifications'] as $slug => $notification_data){
      if(is_array($notification_data['fire_on_action']) && in_array($action, $notification_data['fire_on_action']) || $force) {
        $notifications[$slug] = $notification_data;
      }

    }

   return $notifications;

   }


   /**
   * Returns user values in an array by keys set in the WP_CRM meta keys (data tab)
   *
   * @since 0.16
   *
   */
   function get_user_replacable_values($user_id = false) {
    global $wp_crm, $wpdb;    
 
    $meta_keys = $wp_crm['data_structure']['meta_keys'];

    $primary_columns = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->users}");
            
    $primary = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$user_id}", ARRAY_A);

    foreach($meta_keys as $meta_key => $meta_label) {    
       
      if(in_array($meta_key, $primary_columns)) {
        $value = $primary[$meta_key];
      } else {        
        $value = get_user_meta($user_id, $meta_key, true);
      }
      
      if(!empty($value)) {
        $display_columns[$meta_key] = $meta_label;
      }
        
      $user[trim($meta_key)] = trim($value);
      
    }

    return $user;
    
   
   }

   /**
   * Replaced notification variables with actual values
   *
   *
   * @since 0.1
   *
   */
   function replace_notification_values($notification_data = false, $replace_with = false) {
    global $wp_crm;

    if(!is_array($replace_with)) {
      return;
    }

    $notification_keys = array_keys($notification_data);

    foreach($replace_with as $key => $value) {

      if(is_array($value))
        $value = WP_CRM_F::get_first_value($value);

      foreach($notification_data as $n_key => $n_value)
        $notification_data[$n_key] = str_replace('[' . $key . ']', $value, $n_value);

    }


    return $notification_data;

   }



    /**
     * Tries to determine what the main display value of the user should be
     * Cycles through in attribute order to find first with value
     *
     * @since 0.1
     *
    */
    static function get_primary_display_value($user_object) {
        global $wp_crm;

        if(!empty($user_object) && is_numeric($user_object)) {
            $user_object = wp_crm_get_user($user_object);
        }

        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            $attribute_keys = array_keys($wp_crm['data_structure']['attributes']);
            foreach($attribute_keys as $key) {

                if(!empty($user_object[$key]['default'])) {
                  if(is_array($user_object[$key]['default'])) {
                    return $user_object[$key]['default'][0];
                  } else {
                    return $user_object[$key]['default'];
                  }
                } else {
                  //** Default is empty */
                  if(is_array($user_object[$key])) {
                    foreach($user_object[$key] as $some_value) {
                      if(!empty($some_value)) {
                        return $some_value;
                      }
                    }
                  }
                }
            }
        }

        return false;
    }


    /**
     * Get first value -> used to guess "default" user value
     *
     * @since 0.1
     *
    */
    static function get_first_user_data_key()  {
      global $wp_crm;

      foreach($wp_crm['data_structure']['attributes'] as $key => $data) {
        return $key;
      }

    }

    /**
     * Get user data structure.  May be depreciated.
     *
     * @since 0.1
     *
    */
    static function user_object_structure($args = '') {

        global $wp_crm, $wpdb;

        $defaults = array(
            'table_cols' => 'false',
            'root_only' => 'false'
        );

        $args = wp_parse_args( $args, $defaults );

        foreach($wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users}") as $column) {
            $a[$column->Field] = CRM_UD_F::de_slug($column->Field);
            $table_cols[] = $column->Field;
        }

        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            foreach($wp_crm['data_structure']['attributes'] as $attribute => $attribute_data){

                $a[$attribute] = $attribute_data['title'];

                if($args['root_only'] == 'true') {
                    continue;
                }

                if(!empty($attribute_data['options'])) {
                    foreach(explode(',', $attribute_data['options']) as $this_option) {
                        $a[$attribute .'_' . CRM_UD_F::create_slug($this_option)] = $this_option;
                    }
                }
            }
        }

        if($args['table_cols'] == 'true') {
            return $table_cols;
        }

        return $a;
    }

  /**
   * Fixes admin titles.
   *
   *
   * @since 0.1
   *
   */
  static function admin_title($current_title) {
    global $current_screen, $wpdb;
    switch($current_screen->id) {

      case 'crm_page_wp_crm_add_new':

        if(isset($_REQUEST['user_id']))
          if($user_object = get_userdata($_REQUEST['user_id']))
            return str_replace("New Person", "Editing {$user_object->display_name}", $current_title);

      break;
    }

    return $current_title;

  }



  /**
   * Run manually when a version mismatch is detected.
   *
   * Called in admin_init and on activation hook.
   *
   * @since 0.1
   *
    */
  static function manual_activation( $args = '' ) {
    global $wp_crm, $wp_roles;

    $defaults = array(
      'auto_redirect' => 'true'
    );

    $args = wp_parse_args( $args, $defaults );

    $installed_ver = get_option( "wp_crm_version" );


    if(@version_compare($installed_ver, WP_CRM_Version) == '-1') {

      if(!empty($installed_ver)) {
        //** Handle any updates related to version changes */
        WP_CRM_F::handle_update($installed_ver);
      }

      // Unschedule event
      $timestamp = wp_next_scheduled( 'wp_crm_premium_feature_check' );
      wp_unschedule_event($timestamp, 'wp_crm_premium_feature_check' );
      wp_clear_scheduled_hook('wp_crm_premium_feature_check');

      // Schedule event
      wp_schedule_event(time(), 'daily', 'wp_crm_premium_feature_check');

      // Update option to latest version so this isn't run on next admin page load
      update_option( "wp_crm_version", WP_CRM_Version );

      //** Get premium features on activation */
      @WP_CRM_F::feature_check();

      //** Add capabilities */
      if(is_array($wp_crm['capabilities']) && $wp_roles) {
        foreach($wp_crm['capabilities'] as $capability => $description) {
          $wp_roles->add_cap('administrator','WP-CRM: ' . $capability,true);
        }
      }

      if($args['auto_redirect'] == 'true') {         
        //** Redirect to overview page so all updates take affect on page reload. Not done on activation() */
        wp_redirect(admin_url('admin.php?page=wp_crm&message=plugin_updated'));
        die();
      }


    }


    return;



  }


  /**
   * Draws default user input field
   *
   * Values are always in always in array format.  A string may be passed, but it will be converted into an array and placed into the 'default' holder.
   * Just because mutliple "types" of values are passed does not mean they will be rendered, WP-CRM data settings are checked first to see which attributes have predefined values
   *
   *  Array
   *   (
   *      [default] => Array
   *           (
   *               [0] => 555-default-number
   *           )
   *
   *       [home] => Array
   *           (
   *               [0] => 444-home number
   *               [1] => 445- secondary home number
   *           )
   *
   *       [cell] => Array
   *           (
   *               [0] => 651-my only cell
   *           )
   *
   *   )
   *
   *
   * @since 0.01
   *
   */
  function user_input_field($slug, $values = false, $attribute = false, $user_object = false, $args = '') {
    global $wp_crm, $wpdb;

    $defaults = array(
      'default_input_type' => 'text'
    );
    $args = wp_parse_args( $args, $defaults );

    if(isset($args['tabindex'])) {
      $tabindex = " TABINDEX={$args['tabindex']} ";
    }

    //** Load attribute data if it isn't passed */
    if(empty($attribute)) {
      $attribute = $wp_crm['data_structure']['attributes'][$slug];
    }


    //** If value array is not passed, we create an array */
    if(!is_array($values)) {
      $values = array('default' => array($values));
    }

    //** Calculate total values passed and convert to loop-ready format */
    if($attribute['input_type'] != 'checkbox' && $attribute['input_type'] != 'dropdown') {

      foreach($values as $type_slug => $type_values) {

      //** Check if this type exists in data structure if this is a non-default type slug */
      if($type_slug != 'default' && $attribute['option_keys'] && !in_array("{$slug}_option_{$type_slug}", $attribute['option_keys'])) {
        //* If this type does not exist in option_keys, discard data */
        continue;
      }

      //** Cycle through individual values for this type */
      foreach($type_values as $single_value) {

        //** Set random ID now as meta key for later use for DOM association */
        $rand_id =  rand(10000,99999);
        $loop_ready_values[$rand_id]['value'] = $single_value;
        $loop_ready_values[$rand_id]['option'] = $type_slug;
        $loop_ready_values[$rand_id]['label'] = $attribute['option_labels'][$type_slug];

      }

      }
    }

    //** Checkbox options are handled differently because they all need to be displayed, and we don't cycle through the values but through the available options */
    if($attribute['input_type'] == 'checkbox') {

      if($attribute['has_options']) {
        foreach($attribute['option_labels'] as $option_key => $option_label) {

          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $option_key;
          $loop_ready_values[$rand_id]['label'] = $option_label;

          if($values[$option_key] && (in_array('on', $values[$option_key]) || in_array('true', $values[$option_key]))) {
            $loop_ready_values[$rand_id]['enabled'] = true;
          }

        }
      } else {
          //** In case checkbox doesn't have options  we don't cycle through them but only check the primary key */

          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $slug;
          $loop_ready_values[$rand_id]['label'] = $attribute['title'];

          if(in_array('on', $values['default']) || in_array('true', $values['default'])) {
            $loop_ready_values[$rand_id]['enabled'] = true;
          }

      }

    }

    if($attribute['input_type'] == 'dropdown') {

        foreach($values as $type_slug => $type_values) {
          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $type_slug;
          //** only the first value for an option will be selected. we assume that there will not be situations of same dropdown having same value twice */
          $loop_ready_values[$rand_id]['label'] = $type_values[0];

      }

    }


    $values = $loop_ready_values;
    $total_values = count($values);

    if($total_values > 1) {
      $multiple_values = true;
    }



    if(empty($attribute['input_type'])) {
      $attribute['input_type'] = $default_input_type;
    }

    if($attribute['input_type'] == 'text') {
      $class = 'regular-text';
    }

    ob_start();
    ?>


    <div class="input_div <?php echo ($attribute['allow_multiple'] == 'true' ? 'allow_multiple' : ''); ?> wp_crm_<?php echo $slug; ?>_div">

    <?php


    switch ($attribute['input_type']) {

      case 'password':
      case 'text':
        foreach($values as $rand => $value_data) { ?>
        <div class="wp_crm_input_wrap">

        <input <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type="<?php echo $attribute['input_type']; ?>" value="<?php echo esc_attr($value_data['value']); ?>" />

        <?php if($attribute['has_options']) { ?>
          <select <?php echo $tabindex; ?>  random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
          <option></option>
          <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
            <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
          <?php endforeach; ?>
          </select>
        </div>

        <?php } //* end: has_options */?>

    <?php
        }
      break;
    ?>

    <?php case 'textarea': foreach($values as $rand => $value_data) { ?>
      <div class="wp_crm_input_wrap">

       <textarea <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]" class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>"><?php echo $value_data['value']; ?></textarea>

        <?php if($attribute['has_options']) { ?>
          <select <?php echo $tabindex; ?>  random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
          <option></option>
          <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
            <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
          <?php endforeach; ?>
          </select>
        <?php } //* end: has_options */?>

      </div>

    <?php } break; ?>

    <?php case 'checkbox': ?>
       <div class="wp_crm_input_wrap wp_checkbox_input wp-tab-panel">
         <ul class='wp_crm_checkbox_list'>
         <?php foreach($values as $rand => $value_data) { ?>

         <li>
            <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  type='hidden' value="" />
            <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]"  type='hidden' value="<?php echo esc_attr($value_data['option']); ?>" />
            <input id="wpi_checkbox_<?php echo $rand; ?>" <?php checked($value_data['enabled'], true); ?> <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type='<?php echo $attribute['input_type']; ?>' value="on" />
            <label for="wpi_checkbox_<?php echo $rand; ?>"><?php echo $value_data['label']; ?></label>
          </li>

        <?php } ?>
        </ul>
      </div>

    <?php break; case 'dropdown':  foreach($values as $rand => $value_data) { ?>
      <div class="wp_crm_input_wrap wp_dropdown_input">

      <select <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
        <option value=""></option>
        <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
          <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
        <?php endforeach; ?>
      </select>

      </div>
   <?php }  break;

    default:
      do_action('wp_crm_render_input',  array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
    break;

 }

  //** API Access for data after the field *'
  do_action("wp_crm_after_{$slug}_input", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
 ?>



      <script type="text/javascript">
      <?php /* if($attribute['autocomplete'] == 'true'): ?>


         <?php
          // get data
          $values = $wpdb->get_col("SELECT DISTINCT(meta_value) FROM {$wpdb->usermeta} WHERE meta_key = '{$slug}'");

          if(!empty($values)) {
         ?>

          var <?php echo $slug; ?>_autocomplete_vars = ["<?php echo implode('","', $values); ?>"];

         jQuery(document).ready(function() {

          jQuery("input.wp_crm_<?php echo $slug; ?>_field").click(function() {
            var input = jQuery(this);
            input.autocomplete({source: <?php echo $slug; ?>_autocomplete_vars, appendTo: jQuery(input).parents('.input_div')});

          });

          });


      <?php } endif; /* autocomplete */ ?>
      </script>

    </div>
    <?php



    $content = ob_get_contents();
    ob_end_clean();



    $content = apply_filters('wp_crm_user_input_field', $content, $slug, $value);

    return $content;



   }

  /**
   * Return user object
   *
    * @since 0.01
   *
    */
  function get_user($user_id) {
    return get_userdata($user_id);
  }

  /**
   * Draws table cell for overview table
   *
    * @since 0.01
   *
    */
  function overview_table_cell($column_name, $user_object) {
    global $wpdb, $current_user;

    if(is_object($user_object))
      $user_id = $user_object->ID;
    else
      $user_id = $user_object;

    $edit_link = admin_url("admin.php?page=add_helpdesk_case&object_id={$user_id}");

    $user = WP_CRM_F::get_user($user_object->ID);

    $column_name = trim($column_name);

     switch ($column_name) {


      default:

         $result = 'test';

        $result = apply_filters("erp_h_meta_display_$column_name",$result);
      break;

    }

        //$result .= $result . " ($column_name) ";



    return nl2br($result);



  }

  /**
   * Saves settings, applies filters, and loads settings into global variable
   *
   * Run from WP_CRM_C::WP_CRM_C()
   *
    * @since 0.01
   *
    */
  function settings_action($force_db = false) {
    global $wp_crm;

    // Process saving settings
    if(isset($_REQUEST['wp_crm']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'wp_crm_setting_save') ) {

      // Handle backup
      if($backup_file = $_FILES['wp_crm']['tmp_name']['settings_from_backup']) {
        $backup_contents = file_get_contents($backup_file);

        if(!empty($backup_contents))
          $decoded_settings = json_decode($backup_contents, true);

        if(!empty($decoded_settings))
          $_REQUEST['wp_crm'] = $decoded_settings;
      }

      $wp_crm_settings = apply_filters('wp_crm_settings_save', $_REQUEST['wp_crm'], $wp_crm);

      // Prevent removal of featured settings configurations if they are not present
      if($wp_crm['configuration']['feature_settings'])
      foreach($wp_crm['configuration']['feature_settings'] as $feature_type => $preserved_settings) {

        if(empty($_REQUEST['wp_crm']['configuration']['feature_settings'][$feature_type])) {
          $wp_crm_settings['configuration']['feature_settings'][$feature_type] = $preserved_settings;
         }
      }


      //* Regenerate possible meta keys */
      $wp_crm_settings['data_structure'] = WP_CRM_F::build_meta_keys( $wp_crm_settings);

      update_option('wp_crm_settings', $wp_crm_settings);

      // Load settings out of database to overwrite defaults from action_hooks.
      $wp_crm_db = get_option('wp_crm_settings');

      // Overwrite $wp_crm with database setting
      $wp_crm = array_merge($wp_crm, $wp_crm_db);

      // Reload page to make sure higher-end functions take affect of new settings
      // The filters below will be ran on reload, but the saving functions won't
      if($_REQUEST['page'] == 'wp_crm_settings'); {
        unset($_REQUEST);
        wp_redirect(admin_url("admin.php?page=wp_crm_settings&message=updated"));
        exit;
      }

    } else {

      //** Check if this is a new install */
      $check_crm_settings = get_option('wp_crm_settings');

      if(empty($check_crm_settings)) {

        //* Load some basic data structure (need better place to put this) */
        $wp_crm['data_structure']['attributes']['display_name']['title'] = 'Display Name';
        $wp_crm['data_structure']['attributes']['display_name']['primary'] = 'true';
        $wp_crm['data_structure']['attributes']['display_name']['input_type'] = 'text';

        $wp_crm['data_structure']['attributes']['user_email']['title'] = 'User Email';
        $wp_crm['data_structure']['attributes']['user_email']['primary'] = 'true';
        $wp_crm['data_structure']['attributes']['user_email']['input_type'] = 'text';

        $wp_crm['data_structure']['attributes']['company']['title'] = 'Company';
        $wp_crm['data_structure']['attributes']['company']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['company']['primary'] = 'true';

        $wp_crm['data_structure']['attributes']['phone_number']['title'] = 'Phone Number';
        $wp_crm['data_structure']['attributes']['phone_number']['input_type'] = 'text';

        $wp_crm['data_structure']['attributes']['user_type']['title'] = 'User Type';
        $wp_crm['data_structure']['attributes']['user_type']['options'] = 'Customer,Vendor,Employee';
        $wp_crm['data_structure']['attributes']['user_type']['input_type'] = 'checkbox';

        $wp_crm['data_structure']['attributes']['instant_messenger']['title'] = 'IM';
        $wp_crm['data_structure']['attributes']['instant_messenger']['options'] = 'Skype,Google Talk,AIM';
        $wp_crm['data_structure']['attributes']['instant_messenger']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['instant_messenger']['allow_multiple'] = 'true';

        $wp_crm['data_structure']['attributes']['description']['title'] = 'Description';
        $wp_crm['data_structure']['attributes']['description']['input_type'] = 'textarea';

        $wp_crm['configuration']['overview_table_options']['main_view'][] = 'display_name';
        $wp_crm['configuration']['overview_table_options']['main_view'][] = 'user_email';

        $wp_crm['data_structure'] = WP_CRM_F::build_meta_keys( $wp_crm);
       }

    }

    if($force_db) {

      // Load settings out of database to overwrite defaults from action_hooks.
      $wp_crm_db = get_option('wp_crm_settings');

      // Overwrite $wp_crm with database setting
      $wp_crm = array_merge($wp_crm, $wp_crm_db);


    }


    $wp_crm = stripslashes_deep($wp_crm);

    return $wp_crm;
  }

  /**
   * Check plugin updates - typically for AJAX use
   *
    * @since 0.01
   *
    */
  function check_plugin_updates() {
    global $wp_crm;

    echo WP_CRM_F::feature_check(true);

  }

  /**
   * Minify JavaScript
   *
    * Uses third-party JSMin if class isn't declared.
    *
    * @since 0.01
   *
    */
  function minify_js($data) {

    if(!class_exists('W3_Plugin'))
      include_once wp_crm_Path. '/third-party/jsmin.php';
    elseif(file_exists(WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php'))
      include_once WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php';
    else
      include_once wp_crm_Path. '/third-party/jsmin.php';

    if(class_exists('JSMin'))
      $data = JSMin::minify($data);

    return $data;
  }

  /**
   * Checks for updates against TwinCitiesTech.com Server
   *
    *
    * @since 0.01
   *
    */
  static function feature_check($return = false) {
    global $wp_crm;
 
    $blogname = get_bloginfo('url');
    $blogname = urlencode(str_replace(array('http://', 'https://'), '', $blogname));
    $system = 'wp_crm';
    $wp_crm_version = get_option( "wp_crm_version" );

    $check_url = "http://updates.usabilitydynamics.com/?system=$system&site=$blogname&system_version=$wp_crm_version";
    $response = @wp_remote_get($check_url);

     if(!$response) {
      return;
    }

    // Check for errors
    if(is_object($response) && !empty($response->errors)) {

      foreach($response->errors as $update_errrors) {
        $error_string .= implode(",", $update_errrors);
        CRM_UD_F::log("Feature Update Error: " . $error_string);
      }

      if($return) {
        return sprintf(__('An error occured during premium feature check: <b> %s </b>.','wp_crm'), $error_string);
      }

      return;
    }

    // Quit if failture
    if($response['response']['code'] != '200') {
      return;
    }

   $response = @json_decode($response['body']);

    if(is_object($response->available_features)) {

      $response->available_features = CRM_UD_F::objectToArray($response->available_features);

      // Updata database
      $wp_crm_settings = get_option('wp_crm_settings');
      $wp_crm_settings['available_features'] =  CRM_UD_F::objectToArray($response->available_features);
       update_option('wp_crm_settings', $wp_crm_settings);


    } // available_features


    if($response->features == 'eligible' && $wp_crm['configuration']['disable_automatic_feature_update'] != 'true') {

      // Try to create directory if it doesn't exist
      if(!is_dir(WP_CRM_Premium)) {
        @mkdir(WP_CRM_Premium, 0755);
      }

      // If didn't work, we quit
      if(!is_dir(WP_CRM_Premium)) {
        continue;
      }
      
      // Save code
      if(is_object($response->code)) {
        foreach($response->code as $code) {

          $filename = $code->filename;
          $php_code = $code->code;
          $version = $code->version;

          // Check version

          $default_headers = array(
          'Name' => __('Feature Name','wp_crm'),
          'Version' => __('Version','wp_crm'),
          'Description' => __('Description','wp_crm')
          );

          $current_file = @get_file_data( WP_CRM_Premium . "/" . $filename, $default_headers, 'plugin' );
          //echo "$filename - new version: $version , old version:$current_file[Version] |  " .  @version_compare($current_file[Version], $version) . "<br />";

          if(@version_compare($current_file['Version'], $version) == '-1') {
            $this_file = WP_CRM_Premium . "/" . $filename;
            $fh = @fopen($this_file, 'w');            
            if($fh) {
              fwrite($fh, $php_code);
              fclose($fh);

              if($current_file[Version])
                CRM_UD_F::log(sprintf(__('WP-CRM Premium Feature: %s updated to version %s from %s.','wp_crm'), $code->name, $version, $current_file['Version']));
              else
                CRM_UD_F::log(sprintf(__('WP-CRM Premium Feature: %s updated to version %s.','wp_crm'), $code->name, $version));

              $updated_features[] = $code->name;
            }
          } else {

          }


        }
      }
    }

    // Update settings
    WP_CRM_F::settings_action(true);


    if($return && $wp_crm['configuration']['disable_automatic_feature_update'] == 'true') {
      return __('Update ran successfully but no features were downloaded because the setting is disabled.','wp_crm');

    } elseif($return) {
      return __('Update ran successfully.','wp_crm');
    } 
  }


  /**
   * Check for premium features and load them
   *
   * @since 0.01
   *
    */
  function load_premium() {
    global $wp_crm;

    $default_headers = array(
      'Name' => __('Name','wp_crm'),
      'Version' => __('Version','wp_crm'),
      'Description' => __('Description','wp_crm')
    );

    if(!is_dir(WP_CRM_Premium)) {
      return;
    }

    if ($premium_dir = opendir(WP_CRM_Premium)) {

      if(file_exists(WP_CRM_Premium . "/index.php")) {

        if(WP_DEBUG) {
          include_once(WP_CRM_Premium . "/index.php");
        } else {
          @include_once(WP_CRM_Premium . "/index.php");
        }
      }

      while (false !== ($file = readdir($premium_dir))) {

        if($file == 'index.php')
          continue;

        if(end(explode(".", $file)) == 'php') {

          $plugin_slug = str_replace(array('.php'), '', $file);


          if(WP_DEBUG) {
            $plugin_data = get_file_data( WP_CRM_Premium . "/" . $file, $default_headers, 'plugin' );
          } else {
            $plugin_data = @get_file_data( WP_CRM_Premium . "/" . $file, $default_headers, 'plugin' );
          }
          $wp_crm['installed_features'][$plugin_slug]['name'] = $plugin_data['Name'];
          $wp_crm['installed_features'][$plugin_slug]['version'] = $plugin_data['Version'];
          $wp_crm['installed_features'][$plugin_slug]['description'] = $plugin_data['Description'];

          // Check if the plugin is disabled
          if($wp_crm['installed_features'][$plugin_slug]['disabled'] != 'true') {

            if(WP_DEBUG) {
              include_once(WP_CRM_Premium . "/" . $file);
            } else {
              @include_once(WP_CRM_Premium . "/" . $file);
            }

             // Disable plugin if class does not exists - file is empty
            if(!class_exists($plugin_slug))
              unset($wp_crm['installed_features'][$plugin_slug]);

            $wp_crm['installed_features'][$plugin_slug]['disabled'] = 'false';
          }

        }
      }
    }

  }




  /**
   * Installs tables and runs WP_CRM_F::manual_activation() which actually handles the upgrades
   *
   * @since 0.01
   *
    */
  function activation() {
    global $current_user, $wp_crm, $wp_roles;

    //WP_CRM_F::manual_activation('auto_redirect=false');

    WP_CRM_F::maybe_install_tables();

  }

  /**
   * Install DB tables.
   *
   * @since 0.01
   * @uses $wpdb
   *
    */
  function maybe_install_tables() {
    global $wpdb;

    // Array to store SQL queries
    $sql = array();

    if(!$wpdb->crm_log) {
      $wpdb->crm_log = $wpdb->prefix . 'crm_log';
    }

    $sql[] = "CREATE TABLE {$wpdb->crm_log} (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      object_type VARCHAR(11),
      user_id mediumint(9) NOT NULL,
      action VARCHAR(255),
      attribute VARCHAR(255),
      value VARCHAR(255),
      msgno VARCHAR(255),
      email_to VARCHAR(255),
      email_from VARCHAR(255),
      subject VARCHAR(255),
      text TEXT,
      email_references VARCHAR(255),
      time DATETIME,
      other VARCHAR(255),
      UNIQUE KEY id (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( implode(' ', $sql) );



  }

  /**
   * Displays user activity stream for display.
   *
   * @since 0.1
   */
  function get_user_activity_stream($args = '') {
    global $wpdb, $current_user;

    $defaults = array(
     );

    $args = wp_parse_args( $args, $defaults );

    if(empty($args['user_id'])) {
      return;
    }

    $result = WP_CRM_F::get_events('object_id=' . $args['user_id']);

    
    
    if(!$result) {
      return;
    }

    $result = stripslashes_deep($result);

    foreach($result as $entry): 
    
    //echo "<pre>";print_r($entry);echo "<pre>";
    
    $entry_classes[] = $entry->attribute;
    $entry_classes[] = $entry->object_type;
    $entry_classes[] = $entry->action;
    
    $entry_classes = apply_filters('wp_crm_entry_classes', $entry_classes, $entry);
    
    $entry_type = apply_filters('wp_crm_entry_type_label', $entry->attribute, $entry);
    
    ?>
    <?php $left_by = $wpdb->get_var("SELECT display_name FROM {$wpdb->users} WHERE ID = '{$entry->user_id}'"); ?>
    <tr class="wp_crm_activity_single <?php echo @implode(' ', $entry_classes); ?>">

    <td class="left">
      <ul class='message_meta'>
        <li class='timestamp'>
          <span class='time'><?php echo date(get_option('time_format'), strtotime($entry->time)); ?></span>
          <span class='date'><?php echo date(get_option('date_format'), strtotime($entry->time)); ?></span>
        </li>
        <?php if($entry_type): ?><li class='entry_type'><?php echo $entry_type; ?> </li><?php endif; ?>
        <?php if($left_by): ?><li class='by_user'>by <?php echo $left_by; ?> </li><?php endif; ?>
        <li class="wp_crm_log_actions">
          <span verify_action="true" instant_hide="true" class="wp_crm_message_quick_action wp_crm_subtle_link" object_id="<?php echo $entry->id; ?>" wp_crm_action="delete_log_entry"><?php _e('Delete'); ?></span>
        </li>
      </ul>
    </td>

    <td class="right">
      <p class="wp_crm_entry_content"><?php echo apply_filters('wp_crm_activity_single_content', nl2br($entry->text), array('entry' => $entry, 'args' => $args)); ?></p>
    </td>



    </tr>
    <?php endforeach;

  }



  /**
   * Logs an action
   *
   * @since 0.1
   */
  function insert_event($args = '') {
    global $wpdb, $current_user;

 
    $defaults = array(
        'object_type' => 'user',
        'user_id' => $current_user->data->ID,
        'attribute' => 'general_message',
        'action' => 'insert',
        'ajax' => 'false',
        'time' => date('Y-m-d H:i:s')
     );


    if(is_array($args)) {
      $args = array_merge($defaults, $args);
    } else {
      $args = wp_parse_args( $args, $defaults );
    }
   

    //** Convert time - just in case */
    if(empty($args['time'])) {
      $time_stamp = time();
    } else {
      $time_stamp = strtotime($args['time']);
    }

    $args['time'] = date('Y-m-d H:i:s', $time_stamp);


    $wpdb->insert($wpdb->prefix . 'crm_log', array(
      'object_id' => $args['object_id'],
      'object_type' => $args['object_type'],
      'user_id' => $args['user_id'],
      'attribute' => $args['attribute'],
      'action' => $args['action'],
      'value' => $args['value'],
      'email_from' => $args['email_from'],
      'email_to' => $args['email_to'],
      'text' => $args['text'],
      'other' => $args['other'],
      'time' => $args['time']
    ));


    if($args['ajax'] == 'true')  {
      if($wpdb->insert_id)
        return json_encode(array('success' => 'true', 'insert_id' => $wpdb->insert_id));
      else
        return json_encode(array('success' => 'false'));
    }

    return $wpdb->insert_id;

  }


  /**
    * Get events from log.
    *
    * @since 0.1
   */
  function get_events($args = '') {
    global $wpdb, $current_user;

    $defaults = array(
        'object_type' => 'user',
        'order_by' => 'time',
        'start' => '0',
        'import_count' => '10',
        'get_count' => 'false',
     );
         

    $args = wp_parse_args( $args, $defaults );

    if($args['import_count'])
      $limit = " LIMIT {$args[start]}, {$args[import_count]} ";

    if($args['object_id'])
      $query[] = " (object_id = '{$args['object_id']}') ";

    if($args['object_type'])
      $query[] = " object_type = '{$args['object_type']}' ";

    if($query)
      $query = " WHERE " . implode(' AND ', $query);

    if($args['order_by'])
      $order_by = " ORDER BY {$args[order_by]} DESC ";

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}crm_log $query $order_by $limit");

    if($args['get_count'] == 'true') {
      return count($results);
    }

    return $results;

  }



  /**
   * Dectivate plugin, stop crons.
   *
   * @since 0.01
   *
    */
  function deactivation() {
    global $wp_crm, $wp_roles;

    /*
    if(is_array($wp_crm['capabilities']))
      foreach($wp_crm['capabilities'] as $capability => $description)
        $wp_roles->remove_cap('administrator','wp_crm_' . $capability);
    */

    $timestamp = wp_next_scheduled( 'wp_crm_premium_feature_check' );
    wp_unschedule_event($timestamp, 'wp_crm_premium_feature_check' );
    wp_clear_scheduled_hook('wp_crm_premium_feature_check');


  }


  /**
   * Adds message to global message holder variable.
   *
   * @since 0.01
   *
    */
    function add_message($message, $type = 'good') {
        global $wp_crm_messages;
        if (!is_array($wp_crm_messages))
            $wp_crm_messages = array();

        array_push($wp_crm_messages, array('message' => $message, 'type' => $type));
    }


  /**
   * Prints out global messages and styles them accordingly.
   *
   * @since 0.01
   *
    */
    function print_messages() {
        global $wp_crm_messages;
        
        echo '<div class="wp_crm_ajax_update_message"></div>';

        if (count($wp_crm_messages) < 1) {
            return;
        }

        $update_messages = array();
        $warning_messages = array();

        echo "<div id='wp_crm_message_stack'>";

        foreach ($wp_crm_messages as $message) {

            if ($message['type'] == 'good')
                array_push($update_messages, $message['message']);

            if ($message['type'] == 'bad')
                array_push($warning_messages, $message['message']);
        }

        if (count($update_messages) > 0) {
            echo "<div class='wp_crm_message wp_crm_yellow_notification updated fade'><p>";
            foreach ($update_messages as $u_message)
                echo $u_message . "<br />";
            echo "</p></div>";
        }

        if (count($warning_messages) > 0) {
            echo "<div class='wp_crm_message wp_crm_red_notification error'><p>";
            foreach ($warning_messages as $w_message)
                echo $w_message . "<br />";
            echo "</p></div>";
        }

        echo "</div>";
    }


}
