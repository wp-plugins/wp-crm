<?php
/**
 * WP-CRM Default API
 *
 * @version 0.1
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
 */

/**
 * Plugin Hooks and Filters
 * wp_crm_init: Run on plugin initialization
 * wp_crm_admin_menu: run before plugin admin menu setup
 */
add_filter('wp_crm_notification_actions', 'default_wp_crm_actions');
add_filter('wp_crm_user_card_keys', 'wpp_crm_card_keys_default');
add_filter('wp_crm_display_phone_number', 'wpp_crm_format_phone_number');
add_filter('wp_crm_display_company', 'wp_crm_display_company', 0,4);
add_filter('wp_crm_display_user_email', 'wp_crm_display_user_email', 0,4);


add_action('wp_crm_pre_load', 'wp_crm_load_connections');

function wp_crm_load_connections() {
  if(class_exists('WPI_Core')) {
    include_once WP_CRM_Path . '/core/connections/wp-invoice.php';
  }
  include_once WP_CRM_Path . '/core/connections/bb_press.php';
}


//add_action('added_user_meta', 'wp_crm_add_user_metasearch', 0,4);
//add_action('deleted_user_meta', 'wp_crm_delete_user_metasearch', 0,4);

/**
 * Add default notification actions.
 *
 * @since 0.1
 */
function default_wp_crm_actions($current) {
  $current['new_user_registration'] = __("User Registration", 'wp_crm');
  $current['support_request'] = __("Support Request", 'wp_crm');
  return $current;
}
  /**
   * Add attribute to overview "User Card" selection for settings page.
   *
   * @since 0.1
   */
  function wpp_crm_card_keys_default($current) {
    return array('display_name'=> array('title' => "Display Name <span class='description'>Generated automatically by WordPress.</span>")) + $current; ;
  }

 /**
   * Format company on overview page in the main_view cell
   *
   * @todo add link to filter down by company
   * @since 0.1
   */
 function wp_crm_display_company($current, $user_id, $user_object, $scope) {

  if($scope == 'main_view') {
    return (WP_CRM_F::get_first_value($user_object['title']) ? WP_CRM_F::get_first_value($user_object['title']) . ' at ' : '') . '<a href="">' . WP_CRM_F::get_first_value($user_object['company']) . '</a>';
  }

  return $current;

 }


 /**
   * Format user_email on overview page in the main_view cell and add a new filter to potentially allow e-mails to be sent via CRM
   *
   * @since 0.1
   */
 function wp_crm_display_user_email($current, $user_id, $user_object, $scope) {

  if($scope == 'main_view') {
    return apply_filters('wp_crm_contact_link', " <a href='mailto:{$current}'>{$current}</a>", $user_object);
  }

  return $current;

 }


if(!function_exists('wpp_crm_format_phone_number')) {
  /**
   * Converts a string into a readable phone number
   *
   * @since 0.1
   */
  function wpp_crm_format_phone_number($phone) {

    $phone = preg_replace("/[^0-9]/", "", $phone);

    if(strlen($phone) == 7)
    return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
    elseif(strlen($phone) == 10)
    return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
    else
    return $phone;

  }
}

if(!function_exists('wp_crm_get_user')) {
  /**
   * Get user object based on the CRM data hierarchy
   *
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_get_user($user_id, $args = '') {
    global $wp_crm, $wpdb, $current_user;

    $defaults = array(
      'return_type' => 'object'
    );
    $args = wp_parse_args( $args, $defaults );

    //** Check if user exists */
    if(!$user_table = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = $user_id", ARRAY_A)) {
      return false;
    }

    //** Get all values from user table */
    foreach($user_table as $key => $value) {
      if($value) {
        $user_data[$key]['default'][0] = $value;
      }
    }

    //** Get data from meta table */
    if($wp_crm['data_structure']['attributes']) {
      foreach($wp_crm['data_structure']['attributes'] as $key => $data) {

        //* Get default value */
        $default_values = get_user_meta($user_id, $key);

        //** Add default value to array, taking into account that it may already have been populated from user table */
        if($default_values) {
          foreach($default_values as $default_value) {
          $user_data[$key]['default'][]  = $default_value;
          }
        }

        if($data['has_options']) {
          //** If key has options, we check all meta keys for values */
          foreach($data['option_keys'] as $option_key) {

            if($option_values = get_user_meta($user_id, $option_key)) {

              foreach($option_values as $option_count => $option_value) {
                $option_annex = str_replace($key . '_option_', '', $option_key);
                $user_data[$key][$option_annex][$option_count] = $option_value;
              }

            }

          }

        }
      }
    }


    //** Handle roles and capabilities */
    $capabilities = unserialize($wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = $user_id AND meta_key = '{$wpdb->prefix}capabilities'"));
    if (!empty ($capabilities)) {
      foreach($capabilities as $cap_slug => $cap_active) {
        if($cap_active) {
          $user_data['role']['default'][0] = $cap_slug;
        }
      }
    }


    //* Fix up certain attributes */
    //$user_data['display_name'][0]['value'] = $user_data['first_name'][0]['value'] . ' ' . $user_data['last_name'][0]['value'];

    if($return_type == 'object') {
      $user_data = (object)$user_data;
    }

    if($return_type == 'array') {
      $user_data = (array)$user_data;
    }

    return $user_data;
  }
}

if(!function_exists('wp_crm_send_notification')) {

  /**
   * Send an e-mail or a text message to a recipient .
   *
   * @since 0.1
   */
  function wp_crm_send_notification($action = false, $args = false) {
    global $wp_crm, $wpdb;

    if(!$action) {
      return false;
    }

    $defaults = array(
      'force' => false
    );

    if(!is_array($args)) {
      $args = wp_parse_args( $args, $defaults );
    }

    if(empty($args)) {
      return false;
    }

    $notifications = WP_CRM_F::get_trigger_action_notification($action, $args['force']);

    if(!$notifications) {
      return false;
    }


     // Act upon every notification one at a time
     foreach($notifications as $notification) {

      $message = WP_CRM_F::replace_notification_values($notification, $args);

      if(!$message) {
        continue;
      }

      $headers = "From: {$message[send_from]} \r\n\\";

      add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
      
      $message['message'] = nl2br($message['message']);
      $result = wp_mail($message['to'], $message['subject'], $message['message'], $headers, ($args['attachments'] ? $args['attachments'] : false));


     }


  }
} /* wp_crm_send_notification */

if(!function_exists('wp_crm_save_user_data')) {
  /**
   * Saves user data
   *
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_save_user_data($user_data, $args = '') {
    global $wpdb, $wp_crm;

    $insert_data = array();
    $insert_custom_data = array();

    $defaults = array(
      'use_global_messages' => 'true',
      'match_login' => 'false',
      'no_errors' => 'false',
      'default_role' => get_option('default_role'),
      'no_redirect' => 'false'
    );
    $args = wp_parse_args( $args, $defaults );

    $wp_insert_user_vars = array(
      'user_pass',
      'user_email',
      'user_login',
      'user_url',
      'role',
      'user_nicename',
      'display_name',
      'user_registered',
      'first_name',
      'last_name',
      'nickname'
    );

    //** Get custom meta attributes */
    $wp_user_meta_data = array();
    if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
      foreach($wp_crm['data_structure']['attributes'] as $slug => $value) {
        if(!in_array($slug, $wp_insert_user_vars)) {
          $wp_user_meta_data[] = $slug;
        }
      }
    }

   $temp_data['user_id'] = WP_CRM_F::get_first_value($user_data['user_id']);


    // Prepare Data
    foreach($user_data as $meta_key => $values) {

 

      //** Fix up values if they are not passed in the crazy CRM format */
      if(!empty($values) && !is_array($values)) {

        //** Check if Attribute TITLE was passed intead of the slug */
        foreach($wp_crm['data_structure']['attributes'] as $attribute_slug => $attribute_data) {
          if($attribute_data['title'] == $meta_key) {

            //** Actual slug / meta_key found, we overwrite the passed one */
            $meta_key = $attribute_slug;
            break;
          }
        }

        //** Check if this is an option key, and value needs to be convered to 'on' */
        if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
          if(in_array($values, $wp_crm['data_structure']['attributes'][$meta_key]['option_labels'] )) {

            //** Get option key from passed option title */

            $option_key = array_search($values, $wp_crm['data_structure']['attributes'][$meta_key]['option_labels']);
            //** Restet $values, and update with checkbox friendly data entry */
            $values = array(
              rand(10000, 99999) => array (
                  'value' => 'on',
                  'option' => $option_key
              )
            );

          }
        } else {
          //** Handle Regular values */
            $values = array(
              rand(10000, 99999) => array (
                  'value' => $values
              )
            );

        }

      }


      foreach((array)$values as $temp_key => $data) {

        if(in_array($meta_key, $wp_insert_user_vars)) {
          //** If this attribute is in the main user table, we store it here */
          $insert_data[$meta_key] = $data['value'];

        } elseif (in_array($meta_key, $wp_user_meta_data)) {
          //** If the attribute is a meta key created  by WP-CRM, we store it here */
                    
          switch ($wp_crm['data_structure']['attributes'][$meta_key]['input_type']) {

            case 'checkbox':
              if(!empty($data['option']) && $data['value'] == 'on') {

                //** get full meta key of option */
                $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

                if(empty($full_meta_key)) {
                  $full_meta_key= $meta_key;
                }

                $insert_custom_data[$full_meta_key][] = 'on';

              }
            break;

            case 'dropdown':

              //** get full meta key of option */
              $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

              if(empty($full_meta_key)) {
                $full_meta_key= $meta_key;
              }

              if(!empty($data['option'])) {
                $insert_custom_data[$full_meta_key][] = 'on';
              }


            break;
  
            default:

              //* if element exists but no value was passed, continue */
              if ( isset($data['value']) ) {
                if (empty($data['value'])) {
                  continue;
                }
              }

              if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
                $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

                if(empty($full_meta_key)) {
                  $full_meta_key= $meta_key;
                }

                $insert_custom_data[$full_meta_key][] = $data['value'];
              } else {
                $insert_custom_data[$meta_key][] = $data['value'];
              }


              break;
          }

        }
      }
    }
 

   if(empty($temp_data['user_id'])) {
      // Determine user_id and if new or old user
      if ($args['match_login'] == 'true' && (isset($user_data['user_login']) || isset($user_data['user_email']))) {

        $temp_data['user_login'] = WP_CRM_F::get_first_value($user_data['user_login']);
        $temp_data['user_email'] = WP_CRM_F::get_first_value($user_data['user_email']);

        //* Try to get ID based on login and email */
        if($temp_data['user_email']) {
          $insert_data['ID'] = username_exists($temp_data['user_email']);
        }
        // Validate e-mail
        if(empty($insert_data['ID'])) {
          $insert_data['ID'] = email_exists($temp_data['user_email']);
        }


      }
    } else {
      //** User ID was passed */
      $insert_data['ID'] = $temp_data['user_id'];

    }


    if(empty($insert_data['ID'])) {
      $new_user = true;
    }




    //** Set user_login from user_email or a guessed value if this is a new usr and user_login is not passed */
    if($new_user && !isset($insert_data['user_login'])) {

      if(empty($insert_data['user_login'])) {
        if(!empty($insert_data['user_email'])) {
          $insert_data['user_login'] = $insert_data['user_email'];
        } else {
          //** Try to guess user_login from first passed user value */
          if($first_value = WP_CRM_F::get_primary_display_value($user_data)) {
            if($first_value['value']) {
              $insert_data['user_login'] = $first_value['value'];
            }
          }
        }
      }
    }

    //** Always update display name if its blank */
    if(empty($insert_data['display_name']) && isset($insert_data['user_email'])) {
      $insert_data['display_name'] = $insert_data['user_email'];
    }

    //** If password is passed, we hash it */
    if(empty($insert_data['user_pass'])) {
      //** Unset password to prevent it being cleared out */
      unset($insert_data['user_pass']);
    } else {
      if($new_user) {
        $insert_data['user_pass'] = wp_hash_password($insert_data['user_pass']);
      }
    }

    //** Set default role if no role set and this isn't a new user */
    if(empty($insert_data['role']) && !isset($insert_data['ID'])) {
      $insert_data['role'] = $args['default_role'];
    }
    
     //echo "<pre>" . print_r($user_id, true) . print_r($insert_data, true) . print_r($insert_custom_data, true). print_r($_REQUEST, true);die();
    
    if($new_user) {
      $user_id = wp_insert_user($insert_data);
    } else {
      $user_id = wp_update_user($insert_data);
    }



    if(is_numeric($user_id)) {

      //** Remove all old meta values if field is set (to avoid deleting unpasssed valued */
      if(is_array($wp_crm['data_structure']['meta_keys'])) {
        foreach($wp_crm['data_structure']['meta_keys'] as $meta_key => $meta_label) {

          if(isset($insert_custom_data[$meta_key])) {

          //echo "$meta_key is set deleting <br />";
            delete_user_meta($user_id, $meta_key);
          }

          //** Delete old option meta keys for this meta_key  */
          if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
            
            //** Delete "holder" meta key (this may not be necessary */
            delete_user_meta($user_id, $meta_key);
            foreach($wp_crm['data_structure']['attributes'][$meta_key]['option_keys'] as $old_meta_key) {
              //** Delete individual long (optional) meta keys */
              delete_user_meta($user_id, $old_meta_key);
            }
          }

        }
      }

      //** Add meta values */
      if(is_array($insert_custom_data) && !empty($insert_custom_data)) {
        foreach((array)$insert_custom_data as $meta_key => $meta_value) {
          foreach($meta_value as $single_value)  {
           add_user_meta($user_id, $meta_key, $single_value);
          }
        }
      }
      


      $display_name = WP_CRM_F::get_primary_display_value($user_id);

      if($display_name) {
        $wpdb->update($wpdb->users, array('display_name' => $display_name), array('ID' => $user_id));
      }

      if($new_user) {
        if($args['use_global_messages'] == 'true') {
          WP_CRM_F::add_message(__('New user added.', 'wp_crm'));
        }
      } else {
        if($args['use_global_messages'] == 'true') {
          WP_CRM_F::add_message(__('User updated.', 'wp_crm'));
        }
      }

      // Don't redirect if data was passed
      if($args['no_redirect'] != 'true') {
        wp_redirect(admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id&message=" . ($new_user ? 'created' : 'updated')));
      }

    } else {
      if($args['use_global_messages'] == 'true') {
        switch($user_id->get_error_code()) {
          case 'existing_user_email':
            $existing_id = email_exists($insert_data['user_email']);
            WP_CRM_F::add_message(sprintf(__('Error saving user: %s', 'wp_crm'), $user_id->get_error_message() . ' <a href="' . admin_url("admin.php?page=wp_crm_add_new&user_id={$existing_id}"). '">'. ('Go to user profile') . '</a>'), 'bad');
          break;

          default:
            WP_CRM_F::add_message(sprintf(__('Error saving user: %s', 'wp_crm'), $user_id->get_error_message()), 'bad');
          break;
        }
      }
    }

    if($args['no_errors'] && is_wp_error($user_id)) {
      return false;
    }

    return $user_id;
  }
}  /* wp_crm_save_user_data */


if(!function_exists('wp_crm_add_to_user_log')) {
  /**
   * Saves user data
   *
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_add_to_user_log($user_id, $message, $time = false) {

    $insert_data['object_id'] = $user_id;
    $insert_data['attribute'] = 'note';
    $insert_data['text'] = $message;

    if($time) {
      $insert_data['time'] = $time;
    }

    if(WP_CRM_F::insert_event($insert_data)) {
      return true;
    }

    return false;
  }

}