<?php

function ksl_create_table() {
  global $wpdb;

  $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ksl_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
        `user_id` int(11) NOT NULL DEFAULT '0',
        `user_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
        `user_agent` varchar(512) NOT NULL DEFAULT '',
        `event_name` varchar(126) NOT NULL,
        `page_path` varchar(512) NOT NULL,
        `page_title` varchar(512) NOT NULL,
        `video_id` varchar(512) DEFAULT NULL,
        `video_progress` int(11) DEFAULT NULL,
        `timestamp` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY  (id)
    ) CHARSET=utf8;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

  add_option( 'activity_log_db_version', '1.0' );
}
register_activation_hook( KEIMA_SAVE_LOG_FILE, 'ksl_create_table' );
