=== WP-CRM - Customer Relations Management for WordPress ===
Contributors: Usability Dynamics, andypotanin
Donate link: http://twincitiestech.com/plugins/
Tags: CRM, user management, contact form, email, feedback, form, contact form plugin
Requires at least: 3.0
Tested up to: 3.2.2
Stable tag: trunk


== Description ==

This plugin is intended to significantly improve user management, easily create contact forms, and keep track of incoming contact form messages.

WordPress already comes with some basic user management functions - WP-CRM expands on those functionalities by allowing you to organize your users using custom attributes, find them using filters, and keep track of correspondence.

Your WP control panel can effectively be used to manage all your customers, vendors, partners, affiliates, etc.

This plugin is in beta stages.

= Core Features =
* Excellent user organization, filtering and editing.
* Ability to easily add new user data attributes (i.e. Company Name).
* Dynamic charts representing attributes with quantifiable data.
* Form Creation and Contact Message Management.
* User CSV Exporting.* 
* User activity and note tracking.

= Contact Forms =

[vimeo http://vimeo.com/26983459]

= Attribute Management =

[vimeo http://vimeo.com/26984134]


= Premium Features =

* Newsletter Management - ability to send newsletters to users, or user groups. (in development)
* Email Synchronization - synchronized email management. (in development)

== Installation ==

Immediately after activation you will see a new "CRM" section which will list your existing users.
Default data will be loaded on the first install, which can then be configured further on the CRM -> Settings page.
For additional help, please reference the contextual help dropdowns the different plugin pages.

== Frequently Asked Questions == 

= How do I add a new attribute?  =

Visit CRM -> Settings and click on the "Data" tab.  There you will  be able to add a new and configure new attributes.

== Screenshots ==

1. Main view and filter
2. User editing
3. Incoming contact messages overview
4. Example of the contact form in action
5. Graphs and charts.

== Upgrade Notice ==

= 0.08 =
* Initial public release.
 
== Changelog ==

= 0.20 =
* Added hook to add new user password reset link to user profile stream.
* Associated objects, such as properties, are now associated with messages when the contact form shortcode is filled out from a property page.

= 0.19 =
* Added graphs for displaying quantifiable attributes.
* Fixes to avoid deletion of existing users when reviewing received messages.
* Added default data that is installed on first run to include contact messages and notifications.
* Added filtering options to contact messages screen.
* Setup default UI settings for metabox layout.
* Added a setting for default "system" e-mail address.
* Added contextual help to user editing page.

= 0.18 =
* Added check to prevent self-deletion. 
* Added hook to check if bbPress exists, and display user statistics in their CRM profile.
* Improved connection with WPI where "Total Sales" can not be seen for users from overview screen.

= 0.17 =
* Much improved capability management. New capabilities: View Profiles, View Overview, Manage Settings, Add User Messages, Send Group Message (premium)
* Fixed issue with capabilities not being added automatically on plugin activation (a refresh was necessary before

= 0.16.2 =
* When a user is deleted their posts and pages are kept by being reassigned to the user doing the deleting action, and then being trashed.
* Invoices from WP-Invoice are displayed in a metabox in the CRM profile.

= 0.16.1 =
* Added ability to force download premium features.
* Styling fixes to Settings page "Plugin" tab.

= 0.16 =
* User CSV Export.
* WP-Invoice items can be viewed on the user's CRM profile page.
* Minor UI Improvements.

= 0.15 =
* Renamed jquery.cookies.js to jquery.smookie.js to avoid issues with certain hosts that blocks files with the word 'cookie' in the name.
* Many updates to Contact Forms feature.  Shortcode can now have use_current_user and success_message arguments. use_current_user argument pre-fills the form with currently logged in user's information, and success_message sets the message to display upon successful submittal of form.
* Added ability to mark attributes as required. 
* If a Contact Form does not have a message, it is not displayed on the "Messages" screen after submitted.  Contact Forms can effectively be used as front-end profile updating tools.
* Fields can be set to be "uneditable".  This can be used for adding a field such as "User Login" which is set by WordPress, and in most cases should not be directly editable.
* Added "Description" field, which allows descriptions to be displayed next to input fields on profile screen.
* Added function to delete log entries.
* Added wp_crm_after_{$slug}_input filter which is ran after an input field is rendered.
* Added wp_crm_render_input action which can be used for custom input types.
* Fixed Entry Log date picker styles.
* Minor UI Improvements on overview and settings pages. 

= 0.14 =
* UI fixes to overview screen (roles are now collapsed too)
* Ability to build predefined attribute lists from taxonomies.
* Ability to add JavaScript callback function to shortcode forms.
* Ability to call shortcode forms via AJAX
* Few fixes to DataTable sorting.
* UI improvements to the way checkboxes are displayed.
* Minor change to collapsible filters UI on overview page.

= 0.13 =
* Added option to disable "All" users instead of paginating.
* Added a wp_crm_add_to_user_log() function for easy user log modification.
* Improved wp_crm_save_user_data() for better API.

= 0.12 =
* Fixed some issues with contact form user creation.
* Fixed issue with user deletion.
* Added option to have WP-CRM replace default User Management menu.

= 0.11 =
* Minor fix with message notifications. 
* Better screenshots included. 

= 0.10 =
* Added screenshots. 

= 0.09 =
* Minor release.
* Adding tutorial videos to WordPress plugin page.
* Few UI fixes.

= 0.08 =
* Initial public release.