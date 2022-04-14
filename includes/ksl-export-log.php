<?php

add_action('admin_menu', function () {
  // tools.php は不要。
  add_submenu_page( 'ksl_log_list',
    __('Log data export', 'keima-save-log'),
    __('Log data export', 'keima-save-log'),
    'manage_options',
    'ksl_log_data_export2',
    'ksl_admin_log_export_page');
});

function ksl_admin_log_export_page () {
?>
<div class="wrap">
  <h1><?php _e('Export log', 'keima-save-log') ?></h1>
  <form method="post">
    <p>
      <?php _e('Specify the period: ', 'keima-save-log'); ?>
      <input name="date_from" type="datetime-local">
      ～
      <input name="date_until" type="datetime-local">
    </p>
    <p class="description">
      <?php _e('* If you do not specify it, you can leave it blank. If not entered, the entire period will be covered.', 'keima-save-log'); ?>
    </p>

    <p>
      <input type="submit" name="export_log" class="button button-primary" value="<?php _e('Export Log', 'keima-save-log'); ?>">
    </p>
  </form>
</div>
<?php
}

function ksl_export_log() {
  if( isset( $_POST['export_log'] ) ) {

    $conditions = 'WHERE 1 = 1 ';

    if ( $_POST['date_from'] ) {
      $conditions .= 'AND timestamp >= ' . strtotime($_POST['date_from']) . ' ';
    }
    if ( $_POST['date_until'] ) {
      $conditions .= 'AND timestamp <= ' . strtotime($_POST['date_until']) . ' ';
    }

    $conditions .= 'ORDER BY id ASC';

    $list = ksl_get_list($conditions);

    if( $list ) {
      header('Content-type: text/csv');
      header('Content-Disposition: attachment; filename="log-' . date('YmdHms') . '.csv"');
      header('Pragma: no-cache');
      header('Expires: 0');

      $file = fopen('php://output', 'w');

      $fields = array('log_id', 'user_caps', 'user_id', 'user_email', 'user_display_name', 'user_ip', 'user_agent', 'event_name', 'page_path', 'page_title', 'video_id', 'video_progress', 'timestamp');
      fputcsv( $file, $fields );

      $users = get_users(array(
        'orderby' => 'ID',
        'order' => 'ASC',
      ));

      foreach($list as $item) {

        $keys = array_column($users, 'ID');
        $index = array_search($item->user_id, $keys);
        $user_email = '';
        $display_name = '';
        if ($index !== false) {
          $user_email = $users[$index]->user_email;
          $display_name = $users[$index]->display_name;
        }

        $log_id = $item->id;
        $user_caps = $item->user_caps;
        $user_id = $item->user_id;
        $user_ip = $item->user_ip;
        $user_agent = $item->user_agent;
        $event_name = $item->event_name;
        $page_path = $item->page_path;
        $page_title = $item->page_title;
        $video_id = $item->video_id;
        $video_progress = $item->video_progress;
        $timestamp = date('Y-m-d H:i:s', $item->timestamp);

        $data = array(
          $log_id,
          $user_caps,
          $user_id,
          $user_email,
          $display_name,
          $user_ip,
          $user_agent,
          $event_name,
          $page_path,
          $page_title,
          $video_id,
          $video_progress,
          $timestamp,
        );
        fputcsv($file, $data, ',', '"');
      }

      exit();
    }
  }
}
add_action('init', 'ksl_export_log');

