<?php
/**
 * Navigation Include Template
 *
 * @package GetSimple
 */
 
$debugInfoUrl = 'http://get-simple.info/wiki/debugging';

if (cookie_check()) { 
	echo '<ul id="pill"><li class="leftnav"><a href="logout.php" accesskey="'. find_accesskey(i18n_r('TAB_LOGOUT')).'" >'.i18n_r('TAB_LOGOUT').'</a></li>';
	if (isDebug()) {
		echo '<li class="debug"><a href="'.$debugInfoUrl.'" target="_blank">'.i18n_r('DEBUG_MODE').'</a></li>';
	}
	echo '<li class="rightnav" ><a href="settings.php#profile">'.i18n_r('WELCOME').' <strong>'.$USR.'</strong>!</a></li></ul>';
} 

//determine page type if plugin is being shown
if (get_filename_id() == 'load') {
	
	if(isset($_GET['id'])){
		
		//checking for custom tabs
		$pageArray = array(
			"dominion-jcart" =>	"products"
		);

		//checking if page is in custom tabs
		if(array_key_exists($_GET['id'], $pageArray)){

			//if page is present then retunr the array value
			$plugin_class = $pageArray[$_GET['id']];

		} else {
			$plugin_class = $plugin_info[$plugin_id]['page_type'];
		}
		

	} else {
		$plugin_class = $plugin_info[$plugin_id]['page_type'];	
	}
	
} else {
	$plugin_class = '';
}
echo get_filename_id();
?>
<h1><a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a></h1>
<ul class="nav <?php echo $plugin_class; ?>">
	<li id="nav_pages" ><a class="pages" href="pages.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_PAGES'));?>" ><?php i18n('TAB_PAGES');?></a></li>
	<li id="nav_upload" ><a class="files" href="upload.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_FILES'));?>" ><?php i18n('TAB_FILES');?></a></li>
	<li id="nav_theme" ><a class="theme" href="theme.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_THEME'));?>" ><?php i18n('TAB_THEME');?></a></li>
	<li id="nav_backups" ><a class="backups" href="backups.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_BACKUPS'));?>" ><?php i18n('TAB_BACKUPS');?></a></li>
	<li id="nav_products" ><a class="products" href="load.php?id=dominion-jcart"><?php i18n('TAB_PRODUCTS');?></a></li>
	<li id="nav_plugins" ><a class="plugins" href="plugins.php" accesskey="<?php echo find_accesskey(i18n_r('PLUGINS_NAV'));?>" ><?php i18n('PLUGINS_NAV');?></a></li>

	
	<?php exec_action('nav-tab');	?>
	
	<li id="nav_loaderimg" ><img class="toggle" id="loader" src="template/images/ajax.gif" alt="" /></li>
	<li class="rightnav" ><a class="settings first" href="settings.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_SETTINGS'));?>" ><?php i18n('TAB_SETTINGS');?></a></li>
	<li class="rightnav" ><a class="support last" href="support.php" accesskey="<?php echo find_accesskey(i18n_r('TAB_SUPPORT'));?>" ><?php i18n('TAB_SUPPORT');?></a></li>

</ul>

</div>
</div>
	
<div class="wrapper">

	<?php include('template/error_checking.php'); ?>
