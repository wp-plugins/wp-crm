<?php
if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
    $attribute_keys = array_keys($wp_crm['data_structure']['attributes']);
} else {
    $attribute_keys = array();
}

 if($_REQUEST['message'] == 'created') {
  WP_CRM_F::add_message(__('User created.'));
}elseif($_REQUEST['message'] == 'updated') {
  WP_CRM_F::add_message(__('User updated.'));
}

if($_REQUEST['user_id'])  {
	$user_id = $_REQUEST['user_id'];
	$object = wp_crm_get_user($user_id);
  
	$title =  WP_CRM_F::get_primary_display_value($object);
} else {
	$object = array();
	$object['new'] = true;
  $object['user_role']['default'][0] = get_option('default_role');
	$title = __('Add New Person');
}
 

//echo "<pre> ". print_r($object, true) . "</pre>";


?>
<script type="text/javascript">
  var wp_crm = {
    'user_id': '<?php echo $user_id; ?>'
  };
  
  var wp_crm_hidden_attributes = {<?php 
  $count = 0; $roles = count($wp_crm['hidden_attributes']); if(is_array($wp_crm['hidden_attributes'])) foreach($wp_crm['hidden_attributes']  as $role => $elements): $count++; ?>
 '<?php echo $role; ?>': ['<?php  echo implode("','", $elements); ?>']<?php echo ($count != $roles ? ',' : ''); ?>  
  <?php endforeach; ?>};
</script>


<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo $title; ?></h2>
<?php WP_CRM_F::print_messages(); ?>

<form name="crm_user" action="admin.php?page=wp_crm_add_new<?php echo ($user_id ? "&user_id=$user_id" : ''); ?>" method="post" id="post">
<input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>" />
<?php
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'wp_crm_update_user', 'wp_crm_update_user', false );
?>

<div id="poststuff"  class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
<div id="side-info-column" class="inner-sidebar">
	<?php $side_meta_boxes = do_meta_boxes($current_screen->id, 'side', $object); ?>
</div>

<div id="post-body">
<div id="post-body-content">	
<?php do_meta_boxes($current_screen->id, 'normal', $object); ?>
<?php do_meta_boxes($current_screen->id, 'advanced', $object); ?>
</div>
</div>
<br class="clear" />
</div><!-- /poststuff -->
</form>
</div>
