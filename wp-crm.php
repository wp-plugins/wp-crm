<?php
/*
Plugin Name: WP-CRM - Customer Relationship Management
Plugin URI: http://twincitiestech.com/plugins/
Description: Integrated Customer Relationship Management for WordPress. 
Author: Usability Dynamics, Inc.
Version: 0.16
Author URI: http://twincitiestech.com

Copyright 2010  Usability Dynamics, Inc.    (email : andy.potanin@twincitiestech.com)

Created by Usability Dynamics, Inc (website: twincitiestech.com       email : support@twincitiestech.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/** Path for Includes */
define('WP_CRM_Path', WP_PLUGIN_DIR . '/wp-crm');

/** Path for Includes */
define('WP_CRM_Templates', WP_CRM_Path . '/templates');

/** Path for front-end links */
define('WP_CRM_URL', WP_PLUGIN_URL . '/wp-crm');

/** Plugin Version */
define('WP_CRM_Version', '0.16');

/** Directory path for include_onces of template files  */
define('WP_CRM_Premium', WP_PLUGIN_DIR . '/wp-crm/core/premium');

function WP_CRM_load_textdomain() {
	$locale = get_locale();
	$mofile = WP_CRM_Path . "/langs/wp-crm-$locale.mo";

	if ( file_exists( $mofile ) ) {
		load_textdomain( 'WP_CRM', $mofile );
  }
}
add_action ( 'plugins_loaded', 'WP_CRM_load_textdomain', 2 );
	
// Global Usability Dynamics / TwinCitiesTech.com, Inc. Functions - customized for WP-CRM
include_once WP_CRM_Path . '/core/class_ud.php';
	
/** Loads built-in plugin metadata and allows for third-party modification to hook into the filters. Has to be include_onced here to run after template functions.php */
include_once WP_CRM_Path . '/action_hooks.php';
	
/** Defaults filters and hooks */
include_once WP_CRM_Path . '/default_api.php';

/** Loads general functions used by WP-crm */
include_once WP_CRM_Path . '/core/class_functions.php';

 /** Loads all the metaboxes for the crm page */
include_once WP_CRM_Path . '/core/ui/crm_metaboxes.php';
 
/** Loads all the metaboxes for the crm page */
include_once WP_CRM_Path . '/core/class_core.php';

    
// Register activation hook -> has to be in the main plugin file
register_activation_hook(__FILE__,array('WP_CRM_F', 'activation'));

// Register activation hook -> has to be in the main plugin file
register_deactivation_hook(__FILE__,array('WP_CRM_F', 'deactivation'));
			
// Initiate the plugin
add_action("after_setup_theme", create_function('', 'new WP_CRM_Core;'));

