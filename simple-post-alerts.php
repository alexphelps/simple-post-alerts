<?php
/*
Plugin Name: Simple Post Alerts
Plugin URI: http://wordpress.org/extend/plugins/simple-post-alerts
Description: Allows users to easily get alerts for new posts pending review and published.
Version: 0.1
Author: Alex Phelps
Author URI: http://www.alexphelps.me/
*/

function spa_show_settings( $user ) {
  if (esc_attr( get_the_author_meta( "spa_pending_review", $user->ID )) == "yes"){
    $check_spa_pending_review = "checked";
  }
  if (esc_attr( get_the_author_meta( "spa_published", $user->ID )) == "yes") {
    $check_spa_published = "checked";
  }
  echo '<h3>Post Notifications</h3>';
  $spa_settings_form = '<table class="form-table"><tr>';
  if (user_can( $user, 'publish_posts' )) {
    $spa_settings_form .= '<th><label for="spa_pending_review">New posts pending review</label></th>';
    $spa_settings_form .= '<td><input type="checkbox" name="spa_pending_review" value="yes" ' . $check_spa_pending_review . ' class="regular-checkbox" /></td>';
  }
  $spa_settings_form .= '</tr><tr>';
  $spa_settings_form .= '<th><label for="spa_published">New posts published</label></th>';
  $spa_settings_form .= '<td><input type="checkbox" name="spa_published" value="yes" ' . $check_spa_published . ' class="regular-checkbox" /></td>';
  $spa_settings_form .= '</tr></table>';
  echo $spa_settings_form;
}
add_action('show_user_profile', 'spa_show_settings');
add_action('edit_user_profile', 'spa_show_settings');

function spa_save_settings($user_id) {
    update_user_meta($user_id,'spa_pending_review', $_POST['spa_pending_review']);
    update_user_meta($user_id,'spa_published', $_POST['spa_published']);
}
add_action('personal_options_update', 'spa_save_settings');
add_action('edit_user_profile_update', 'spa_save_settings');

function spa_pending_review_send( $post_id ){
  $spa_pending_review_subscribers = get_users( array( 'meta_key' => 'spa_pending_review', 'meta_value' => 'yes' ) );
  foreach ( $spa_pending_review_subscribers as $user ) {
      $post_title = get_the_title( $post_id );
      $subject = 'New Post Pending Review';
      $message = "There is a new post pending review on your website.\n\n";
      $message .= $post_title . "\n\n";
      $message .= get_edit_post_link( $post_id, '' );
      wp_mail( $user->user_email, $subject, $message );
    }
}
add_action('pending_post', 'spa_pending_review_send');

function spa_published_send( $post_id ){
  $spa_published_subscribers = get_users( array( 'meta_key' => 'spa_published', 'meta_value' => 'yes' ) );
  foreach ( $spa_published_subscribers as $user ) {
      $post_title = get_the_title( $post_id );
      $subject = 'New Post Published';
      $message = "There is a new post published on your website.\n\n";
      $message .= $post_title . "\n\n";
      $message .= get_permalink( $post_id );
      wp_mail( $user->user_email, $subject, $message );
    }
}
add_action('publish_post', 'spa_published_send');
