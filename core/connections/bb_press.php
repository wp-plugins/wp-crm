<?php
/**
   * BB Press Connector
   *
   */

   //** Load user invoices into global user */
   //add_action('wp_crm_user_loaded', array('WPC_BB_Press', 'wp_crm_user_loaded'));


   class WPC_BB_Press {

    function wp_crm_user_loaded($wp_crm_user) {
      global $wp_crm_user, $bb_table_prefix;
      
      //** Check if BB Press exists */
      
      $user_id = $wp_crm_user['ID']['default'][0];

      
      //$user_posts = new BB_Query( 'post', array( 'post_author_id' => $user_id) );
    
      add_action('wp_crm_metaboxes', array('WPC_BB_Press', 'wp_crm_metaboxes'));
    }


    function wp_crm_metaboxes() {
      global $wp_crm_user;
      //add_meta_box("Invoices", "Invoices" , array('WPC_BB_Press', 'metabox'), 'crm_page_wp_crm_add_new', 'normal', 'default');
    }

    function metabox($user_object) {
      global $wpi_settings;

    }

}