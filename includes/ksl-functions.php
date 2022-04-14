<?php

function ksl_create_args ( $args = array() ) {
  $args = wp_parse_args(
    $args,
    array(
      'user_caps'        => ksl_get_user_caps(),
      'user_id'          => get_current_user_id(),
      'user_ip'          => ksl_get_ip_address(),
      'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
      'event_name'       => 'page_view',
      'page_path'        => $_SERVER['REQUEST_URI'],
      'page_title'       => wp_get_document_title(),
      'video_id'         => NULL,
      'video_progress'   => NULL,
      'timestamp'        => current_time( 'timestamp' ),
    )
  );

  return $args;
}

function ksl_check_favicon ( $args ) {
  $is_favicon = strpos($args['page_path'], '/favicon.ico');
  return is_numeric($is_favicon);
}

function ksl_check_duplicate ( $args ) {
  global $wpdb;
  $check_duplicate = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT `id` FROM `{$wpdb->prefix}ksl_log`
					WHERE `user_caps` = %s
						AND `user_id` = %d
						AND `user_ip` = %s
						AND `user_agent` = %s
						AND `event_name` = %s
						AND `page_path` = %s
						AND `page_title` = %s
						AND `video_id` = %s
						AND `video_progress` = %d
						AND `timestamp` = %d
				;",
      $args['user_caps'],
      $args['user_id'],
      $args['user_ip'],
      $args['user_agent'],
      $args['event_name'],
      $args['page_path'],
      $args['page_title'],
      $args['video_id'],
      $args['video_progress'],
      $args['timestamp']
    )
  );

  if ( $check_duplicate )
    return 'duplicated';
  else
    return 'not duplicated';
}

function ksl_insert_log ( $args ) {
  global $wpdb;
  $wpdb->insert($wpdb->prefix . 'ksl_log', array(
    'user_caps'      => $args['user_caps'],
    'user_id'        => $args['user_id'],
    'user_ip'        => $args['user_ip'],
    'user_agent'     => $args['user_agent'],
    'event_name'     => $args['event_name'],
    'page_path'      => $args['page_path'],
    'page_title'     => $args['page_title'],
    'video_id'       => $args['video_id'],
    'video_progress' => $args['video_progress'],
    'timestamp'      => $args['timestamp'],
  ));
}

function ksl_get_list ( $conditions = 'ORDER BY timestamp DESC LIMITE 10' ) {
  global $wpdb;

  $query = "SELECT * FROM {$wpdb->prefix}ksl_log {$conditions}";
  $results = $wpdb->get_results( $wpdb->prepare( $query, 0, ARRAY_A ) );

  return $results;
}

function ksl_get_user_caps () {
  $user = get_user_by( 'id', get_current_user_id() );
  if ( $user ) {
    $user_caps = strtolower( key( $user->caps ) );
  } else {
    $user_caps = 'guest';
  }
  return $user_caps;
}

function ksl_get_ip_address () {
  $server_ip_keys = array(
    'HTTP_CF_CONNECTING_IP', // CloudFlare
    'HTTP_TRUE_CLIENT_IP', // CloudFlare Enterprise header
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR',
  );

  foreach ( $server_ip_keys as $key ) {
    if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
      return $_SERVER[ $key ];
    }
  }

  // Fallback local ip.
  return '127.0.0.1';
}
