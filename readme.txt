=== WP-CRM - Customer Relations Management for WordPress ===
Contributors: Usability Dynamics, andypotanin
Donate link: http://twincitiestech.com/plugins/
Tags: CRM, user management, contact form, email, feedback, form, contact form plugin
Requires at least: 3.0
Tested up to: 3.2.2
Stable tag: trunk


== Description ==

This plugin is in beta stages.  However, we are very interested in hearing any feedback you may have, feel free to suggest ideas on our [feedback site](http://feedback.twincitiestech.com/) or by visiting our [forums](http://forums.twincitiestech.com/).

This plugin is intended to improve user management, easily create contact forms, and keep track of incoming contact form messages.

= Core Features =
* Excellent user organization, filtering and editing.
* Ability to easily add new user data attributes (i.e. Company Name).
* Form Creation.
* Contact Message Management.
* Keep track of customer, client and vendor notes.

= Contact Forms =

[vimeo http://vimeo.com/26983459]

= Attribute Management =

[vimeo http://vimeo.com/26984134]


= Premium Features =

* Newsletter Management - ability to send newsletters to users, or user groups. (in development)
* Email Synchronization - synchronized email management. (in development)

== Installation ==

Immediately after activation you will see a new "CRM" section which will list your existing users.
To configure, visit CRM -> Settings.

== Frequently Asked Questions == 

= How do I add a new attribute?  =

Visit CRM -> Settings and click on the "Data" tab.  There you will  be able to add a new and configure new attributes.

== Screenshots ==

1. Main view and filter
2. User editing
4. Incoming contact messages overview
3. Example of the contact form in action

== Upgrade Notice ==

= 0.08 =
* Initial public release.
 
== Changelog ==

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