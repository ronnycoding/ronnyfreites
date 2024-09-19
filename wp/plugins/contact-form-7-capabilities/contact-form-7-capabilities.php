<?php
/**
 * Plugin Name: Contact Form 7 Subscriber Role Capabilities
 * Plugin URI: https://ronnyfreites.com/
 * Description: This plugin is used to add capabilities to subscriber user role to the contact form 7 plugin.
 * Version: 0.1
 * Author: Ronny Freites
 * Author URI: https://ronnyfreites.com/
 **/
add_filter("wpcf7_map_meta_cap", "custom_wpcf7_map_meta_cap", 20);

function custom_wpcf7_map_meta_cap($meta_caps)
{
  // Allow subscribers to read contact forms and submit them
  $meta_caps["wpcf7_read_contact_forms"] = "read";
  $meta_caps["wpcf7_edit_contact_form"] = "read";
  $meta_caps["wpcf7_submit"] = "read";
  return $meta_caps;
}
