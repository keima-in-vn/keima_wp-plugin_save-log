<?php

function ksl_add_jquery() {
  wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'ksl_add_jquery' );

add_action( 'wp_print_footer_scripts', 'ksl_insert_vimeo_log', 10 );

function ksl_insert_vimeo_log() {
  $admin_url = admin_url();
  $args = ksl_create_args();

  $user_caps        = $args['user_caps'];
  $user_id          = $args['user_id'];
  $user_ip          = $args['user_ip'];
  $user_agent       = $args['user_agent'];
  $page_path        = $args['page_path'];
  $page_title       = $args['page_title'];

  $code = <<< EOD
<script src="https://player.vimeo.com/api/player.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var vimeoIframes = document.querySelectorAll('iframe[src*="vimeo"]');
    //console.log(vimeoIframes);
    vimeoIframes.forEach(function (vimeoIframe) {
      //console.log(vimeoIframe);
      var src = vimeoIframe.getAttribute('src');
      var searchWords = 'video/';
      var videoNumber = src.slice(src.lastIndexOf(searchWords) + searchWords.length);
      var videoId = 'id-' + videoNumber;
      vimeoIframe.setAttribute('id', videoId);
      var player = new Vimeo.Player(videoId);
  
      var videoTitle = null;
      player.getVideoTitle().then(function(title) {
        videoTitle = title;
      });
      var insert_log = function (eventName, eventObj, param = {}) {
        var _param = {
          'video_provider': 'Vimeo',
          'video_id': videoNumber,
          'video_title': videoTitle,
          'video_url': 'https://vimeo.com/' + videoNumber,
          'visible': 1,
        }
  
        if ( eventObj.percent !== undefined ) {
          _param.video_duration = eventObj.duration;
        }
        if ( eventObj.percent !== undefined ) {
          _param.video_current_time = eventObj.seconds;
        }
        if ( param.video_percent !== undefined ) {
          _param.video_percent = param.video_percent;
        } else if ( eventObj.percent !== undefined ) {
          var percent = eventObj.percent;
          percent = String(percent.toFixed(2));
          percent = percent * 100;
          _param.video_percent = percent;
        }
        jQuery.ajax({
          type: 'POST',
          url: '{$admin_url}admin-ajax.php',
          dataType : 'json',
          data: {
            action: 'ksl_insert_vimeo_log_ajax',
            user_caps: '{$user_caps}',
            user_id: '{$user_id}',
            user_ip: '{$user_ip}',
            user_agent: '{$user_agent}',
            event_name: eventName,
            page_path: '{$page_path}',
            page_title: '{$page_title}',
            video_id: _param.video_id,
            video_progress: _param.video_percent
          },
        }).done(function( data, textStatus ) {
          console.log('log saved:', eventName);
        }).fail(function( xhr, textStatus, errorThrown ) {
          console.log('fail saving log');
        });
      };
      player.on('loaded', function (e) {
        insert_log('video_loaded', e);
      });
      player.on('play', function (e) {
        if ( e.percent === 0 ) {
          // This event is when user start Video the first time in the page.
          // If user play again after pausing, It's "video_play".
          insert_log('video_start', e);
        }
      });
      player.on('playing', function (e) {
        insert_log('video_play', e);
      });
      player.on('pause', function (e) {
        insert_log('video_pause', e);
      });
      player.on('seeking', function (e) {
        insert_log('video_seeking', e);
      });
      player.on('seeked', function (e) {
        insert_log('video_seeked', e);
      });
      player.on('ended', function (e) {
        insert_log('video_complete', e);
      });
  
      var timeUpdateFlag = {}
      player.on('timeupdate', function(e) {
        if ( e.percent > 0.95 ) {
          if ( ! timeUpdateFlag.t95 ) {
            timeUpdateFlag.t95 = true;
            insert_log('video_progress' , e, { 'video_percent': 95 });
          }
        } else if ( e.percent > 0.90 ) {
          if ( ! timeUpdateFlag.t90 ) {
            timeUpdateFlag.t90 = true;
            insert_log('video_progress', e, { 'video_percent': 90 });
          }
        } else if ( e.percent > 0.85 ) {
          if ( ! timeUpdateFlag.t85 ) {
            timeUpdateFlag.t85 = true;
            insert_log('video_progress', e, { 'video_percent': 85 });
          }
        } else if ( e.percent > 0.80 ) {
          if ( ! timeUpdateFlag.t80 ) {
            timeUpdateFlag.t80 = true;
            insert_log('video_progress', e, { 'video_percent': 80 });
          }
        } else if ( e.percent > 0.75 ) {
          if ( ! timeUpdateFlag.t75 ) {
            timeUpdateFlag.t75 = true;
            insert_log('video_progress', e, { 'video_percent': 75 });
          }
        } else if ( e.percent > 0.5 ) {
          if ( ! timeUpdateFlag.t50 ) {
            timeUpdateFlag.t50 = true;
            insert_log('video_progress', e, { 'video_percent': 50 });
          }
        } else if ( e.percent > 0.25 ) {
          if ( ! timeUpdateFlag.t25 ) {
            timeUpdateFlag.t25 = true;
            insert_log('video_progress', e, { 'video_percent': 25 });
          }
        } else if ( e.percent > 0.1 ) {
          if ( ! timeUpdateFlag.t10 ) {
            timeUpdateFlag.t10 = true;
            insert_log('video_progress', e, { 'video_percent': 10 });
          }
        }
      });
    });
  });
</script>
EOD;

  echo $code;
}

function ksl_insert_vimeo_log_ajax ()
{
  if( empty($_POST) ) {
    echo 'error: no post';
    return;
  }

  $args = array(
    'user_caps'        => $_POST['user_caps'],
    'user_id'          => $_POST['user_id'],
    'user_ip'          => $_POST['user_ip'],
    'user_agent'       => $_POST['user_agent'],
    'event_name'       => $_POST['event_name'],
    'page_path'        => $_POST['page_path'],
    'page_title'       => $_POST['page_title'],
    'video_id'         => $_POST['video_id'],
    'video_progress'   => $_POST['video_progress'],
  );
  $args = ksl_create_args( $args );

  if ( ksl_check_duplicate( $args ) === 'duplicated' )
    return;

  ksl_insert_log( $args );

}
add_action( 'wp_ajax_nopriv_ksl_insert_vimeo_log_ajax', 'ksl_insert_vimeo_log_ajax' );
add_action( 'wp_ajax_ksl_insert_vimeo_log_ajax', 'ksl_insert_vimeo_log_ajax' );
