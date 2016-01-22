<?php
/*
Plugin Name: Image Upload Series
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Used to demonstrate how to work with the media uploader in WordPress.
Author: Bobby Bryant
Version: 1.0.0
Author URI: twitter.com/mrbobbybryant
*/

namespace image_uploader;

function register_metaboxes() {
	add_meta_box('image_metabox', 'Image Uploader', __NAMESPACE__ . '\image_uploader_callback');
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_metaboxes' );

function register_admin_script() {
	wp_enqueue_script( 'wp_img_upload', plugin_dir_url( __FILE__ ) . '/image-upload.js', array('jquery', 'media-upload'), '0.0.2', true );
	wp_enqueue_style( 'img_upload-css', plugin_dir_url( __FILE__ ) . '/image-upload.css', '0.0.2' );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_admin_script' );

function image_uploader_callback( $post_id ) {
	wp_nonce_field( basename( __FILE__ ), 'custom_image_nonce' );
	$image_data = get_post_meta( get_the_ID(), 'custom_image_data', true );

	$img_style = '';
	$delete_style = 'display: none;';
	if ( isset( $image_data ) ) {
		$img_style = 'width: 100%;';
		$delete_style = '';
	}
	?>

	<div id="image_wrapper">
		<?php printf( '<img id="image-upload-tag" src="%s" style="%s">', esc_url( $image_data['src'] ), esc_attr( $img_style ) ); ?>
		<?php printf( '<input type="hidden" id="img-hidden-field" name="custom_image_data" value="%s">', esc_attr( $image_data ) ); ?>

		<input type="button" id="image-upload-button" class="button" value="Add Image">
		<input type="button" id="image-delete-button" class="button" value="Delete Image" style="<?php esc_attr_e( $delete_style ); ?>">
	</div>


	<?php
}

function save_custom_image( $post_id ) {
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'custom_image_nonce' ] ) && wp_verify_nonce( $_POST[ 'custom_image_nonce' ], basename( __FILE__ ) ) );

	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	if ( isset( $_POST[ 'custom_image_data' ] ) ) {
		$image_data = json_decode( stripslashes( $_POST[ 'custom_image_data' ] ) );
		$img_id = $image_data[0]->id;
		$img_src = $image_data[0]->url;

		update_post_meta( $post_id, 'custom_image_data', array( 'id' => intval( $img_id ), 'src' => esc_url_raw( $img_src ) ) );
	}


}
add_action( 'save_post', __NAMESPACE__ . '\save_custom_image' );
