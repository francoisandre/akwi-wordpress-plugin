<?php
/*
 * Plugin Name: akwi-wordpress-plugin
 */
function set_default_tagline($blog_id) {
	switch_to_blog ( $blog_id );
	update_option ( 'blogdescription', 'Sch&eacute;ma d\'Am&eacute;nagement et de Gestion de l\'Eau' );
	set_theme_mod ( 'theme_aeris_copyright', 'Akwi.fr ' . date ( 'Y' ) );
	restore_current_blog ();
}



add_action ( 'wpmu_new_blog', 'set_default_tagline' );

function akwi_commons_addLocalFile($localFile) {
	$aux = str_replace ( '\\', '/', $localFile);
	$upload_dir = wp_upload_dir ();
	$image_data = file_get_contents ( $aux );
	$filename = basename ( $aux );
	
	$upload_file = wp_upload_bits ( $filename, null, file_get_contents ( $aux ) );
	if (! $upload_file ['error']) {
		$parent_post_id='1';
		$wp_filetype = wp_check_filetype ( $filename, null );
		$attachment = array (
				'post_mime_type' => $wp_filetype ['type'],
				'post_parent' => $parent_post_id,
				'post_title' => preg_replace ( '/\.[^.]+$/', '', $filename ),
				'post_content' => '',
				'post_status' => 'inherit'
		);
		$attachment_id = wp_insert_attachment ( $attachment, $upload_file ['file'], $parent_post_id );
		if (! is_wp_error ( $attachment_id )) {
			require_once (ABSPATH . "wp-admin" . '/includes/image.php');
			$attachment_data = wp_generate_attachment_metadata ( $attachment_id, $upload_file ['file'] );
			wp_update_attachment_metadata ( $attachment_id, $attachment_data );
			return $attachment_id;
		}
		else {
			return -1;
		}
	} 
	else {
		return -1;
	}
}

function akwi_commons_addLogo($localFile) {
	$result = akwi_commons_addLocalFile($localFile);
	if ($result!= -1) {
		set_theme_mod( 'custom_logo', $result);
	}
}

function akwi_commons_addFavicon($localFile) {
	$result = akwi_commons_addLocalFile($localFile);
	if ($result!= -1) {
		update_option( 'site_icon',$result);
	}
}



function akwi_commons_addDefaultFavicon() {
	$defaultFavicon = ABSPATH . 'wp-content/plugins/akwi-wordpress-plugin/images/default-favicon.png';
	akwi_commons_addFavicon($defaultFavicon);
}

//add_action ( 'init', 'addLogo' );

add_action('wp_dashboard_setup', 'akwi_dashboard_widgets');

function akwi_dashboard_widgets() {
	global $wp_meta_boxes;
	
	wp_add_dashboard_widget('akwi_dashboard_site_widget', 'Akwi site', 'akwi_dashboard_site_widget');
}

function akwi_dashboard_site_widget() {
	echo '<a href="' . admin_url( 'admin-post.php?action=build_akwi_site' ) . '">Créer site</a>';
}



function akwi_commons_addWelcomePage() {
		$new_page_title = 'bienvenue';
		$new_page_content = 'Contenu de la page';
		$new_page_template = 'template-home.php'; 
		$page_check = get_page_by_title($new_page_title);
		$new_page = array(
				'post_type' => 'page',
				'post_title' => $new_page_title,
				'post_content' => $new_page_content,
				'post_status' => 'publish',
				'post_author' => 1,
		);
		if(!isset($page_check->ID)){
			$new_page_id = wp_insert_post($new_page);
			if(!empty($new_page_template)){
				update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
				akwi_commons_setWelcomePage();
			}
		}
}

function llog($content) {
	file_put_contents('C:/tmp/log/log_'.date("j.n.Y").'.txt', $content.PHP_EOL, FILE_APPEND);
}

function buildSite()
{
	//addLogo(ABSPATH . 'wp-content/plugins/akwi-wordpress-plugin/images/default-logo.png');
	//addFavicon();
	//addWelcomePage();
	setWelcomePage();
	wp_redirect( admin_url( 'index.php' ) );
	exit;
}

function akwi_commons_setWelcomePage() {
	llog("XXX");
	$homepage = get_page_by_title( 'bienvenue' );
	
	if ( $homepage )
	{
		update_option( 'page_on_front', $homepage->ID );
		update_option( 'show_on_front', 'page' );
	}
}

add_action( 'admin_post_build_akwi_site', 'buildSite' );
?>