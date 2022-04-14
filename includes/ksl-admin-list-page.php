<?php

add_action('admin_menu', function (){
  if ( current_user_can('administrator','editor') ) {
    add_menu_page(
      __('Log', 'keima-save-log'),
      __('Log', 'keima-save-log'),
      'manage_options',
      'ksl_log_list',
      'ksl_admin_list_page'
    );
    add_submenu_page( 'ksl_log_list',
      __('Log list', 'keima-save-log'),
      __('Log list', 'keima-save-log'),
      'manage_options',
      'ksl_log_list',
      'ksl_admin_list_page');
  }
});

function ksl_admin_list_page () {
  $list = ksl_get_list('ORDER BY timestamp DESC LIMIT 10' );
  ?>
  <div class="wrap">
    <h1><?php _e('Log list', 'keima-save-log') ?></h1>
    <p>
      <?php _e('The latest 10 items are displayed (10 or more records are stored in the database).', 'keima-save-log') ?>
    </p>

    <?php if ( empty($list) ) : ?>
    <p><?php _e('There is no log data.', 'keima-save-log') ?></p>
    <?php else : ?>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
      <thead>
      <tr>
        <th width="40">log_id</th>
        <th width="70">user_caps</th>
        <th width="50">user_id</th>
        <th width="80">user_ip</th>
        <th width="180">user_agent</th>
        <th width="80">event_name</th>
        <th>page_path</th>
        <th>page_title</th>
        <th>video_id</th>
        <th width="60">video_progress</th>
        <th>timestamp</th>
      </tr>
      </thead>

      <tbody id="the-list">

      <?php
      $html = '';
      foreach($list as $item){
        $timestamp = date('Y-m-d H:i:s', $item->timestamp);
        $html .= '<tr>';
        $html .= '<td>' . $item->id . '</td>';
        $html .= '<td>' . $item->user_caps . '</td>';
        $html .= '<td>' . $item->user_id . '</td>';
        $html .= '<td>' . $item->user_ip . '</td>';
        $html .= '<td>' . $item->user_agent . '</td>';
        $html .= '<td>' . $item->event_name . '</td>';
        $html .= '<td>' . $item->page_path . '</td>';
        $html .= '<td>' . $item->page_title . '</td>';
        $html .= '<td>' . $item->video_id . '</td>';
        $html .= '<td>' . $item->video_progress . '</td>';
        $html .= '<td>' . $timestamp . '</td>';
        $html .= '</tr>';
      }
      echo $html;
      ?>

      </tbody>

      <tfoot>
      <tr>
        <th>log_id</th>
        <th>user_caps</th>
        <th>user_id</th>
        <th>user_ip</th>
        <th>user_agent</th>
        <th>event_name</th>
        <th>page_path</th>
        <th>page_title</th>
        <th>video_id</th>
        <th>video_progress</th>
        <th>timestamp</th>
      </tr>
      </tfoot>

    </table>

    <?php endif ?>

  </div>
  <style>

  </style>
  <?php
}
