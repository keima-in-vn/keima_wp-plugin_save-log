<?php

add_action( 'init', 'ksl_session_start', 1 );

function ksl_session_start() {
  if( session_status() !== PHP_SESSION_ACTIVE ) {
    session_start();
  }
}

add_action( 'wp_print_footer_scripts', 'ksl_insert_page_view_log', 99 );

function ksl_insert_page_view_log() {
  if ( is_admin() ) {
    return;
  }

  $args = ksl_create_args();

  if ( ksl_check_favicon( $args ) )
    return;

  if ( ksl_check_duplicate( $args ) === 'duplicated' )
    return;

  ksl_insert_log( $args );
}

add_action( 'wp_print_footer_scripts', 'ksl_insert_login_log', 100 );

function ksl_insert_login_log() {
  if ( is_admin() ) {
    return;
  }

  // Check accessed user is logged in or not.
  $user = get_user_by( 'id', get_current_user_id() );
  if ( $user ) {
    if ( isset($_SESSION['ksl_logged_in']) ) {
      return;
    }
    $_SESSION['ksl_logged_in'] = $user->ID;
  } else {
    if ( isset($_SESSION['ksl_logged_in']) ) {
      unset($_SESSION['ksl_logged_in']);
    }
    return;
  }

  $args = array(
    'event_name' => 'login'
  );
  $args = ksl_create_args( $args );

  if ( ksl_check_duplicate( $args ) === 'duplicated' )
    return;

  ksl_insert_log( $args );
}
