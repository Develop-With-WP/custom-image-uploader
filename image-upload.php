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
	wp_localize_script( 'wp_img_upload', 'customUploads', array( 'imageData' => get_post_meta( get_the_ID(), 'custom_image_data', true ) ) );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_admin_script' );

function image_uploader_callback( $post_id ) {
	wp_nonce_field( basename( __FILE__ ), 'custom_image_nonce' ); ?>

	<div id="metabox_wrapper">
		<img id="image-tag">
		<input type="hidden" id="img-hidden-field" name="custom_image_data">
		<input type="button" id="image-upload-button" class="button" value="Add Image">
		<input type="button" id="image-delete-button" class="button" value="Delete Image">
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
		if ( is_object( $image_data[0] ) ) {
			$image_data = array( 'id' => intval( $image_data[0]->id ), 'src' => esc_url_raw( $image_data[0]->url
			) );
		} else {
			$image_data = [];
		}

		update_post_meta( $post_id, 'custom_image_data', $image_data );
	}

}
add_action( 'save_post', __NAMESPACE__ . '\save_custom_image' );
