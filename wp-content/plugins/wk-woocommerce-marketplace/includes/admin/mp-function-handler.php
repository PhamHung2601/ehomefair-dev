<?php

function mp_admin_notices() {
  if ( isset( $_POST['oid'] ) && is_array( $_POST['oid'] ) && ! empty( $_POST['oid'] ) ) {
    echo '<div  class="notice notice-success is-dismissible">';
      echo '<p>Payment has been successfully done.</p>';
    echo '</div>';
  }
}
