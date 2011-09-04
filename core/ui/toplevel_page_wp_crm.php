 <?php
 
  if($_REQUEST['message'] == 'user_deleted') {
    WP_CRM_F::add_message(__('User has been deleted and all associatd posts have been trashed.'));
  }

  include WP_CRM_Path . '/core/class_user_list_table.php';

  $wp_list_table = new CRM_User_List_Table("per_page=25");

  $wp_list_table->prepare_items();

  $wp_list_table->data_tables_script();
 ?>


<div class="wp_crm_overview_wrapper wrap">
<div class="wp_crm_ajax_result"></div>
    <?php screen_icon(); ?>
    <h2><?php _e('CRM - All People'); ?> <a href="<?php echo admin_url('admin.php?page=wp_crm_add_new'); ?>" class="button add-new-h2"><?php _e('Add New'); ?></a></h2>
    <?php WP_CRM_F::print_messages(); ?>
    
    <div id="poststuff" class="<?php echo $current_screen->id; ?>_table metabox-holder has-right-sidebar">
    <form id="wp-crm-filter" action="#" method="POST">

    <div class="wp_crm_sidebar inner-sidebar">
      <div class="meta-box-sortables ui-sortable">
        <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
      </div>
    </div>

    <div id="post-body">
      <div id="post-body-content">
        <?php $wp_list_table->display(); ?>
      </div> <?php /* .post-body-content */ ?>
    </div> <?php /* .post-body */ ?>

    </form>
    <br class="clear" />

  </div> <?php /* #poststuff */ ?>
</div> <?php /* .wp_crm_overview_wrapper */ ?>
