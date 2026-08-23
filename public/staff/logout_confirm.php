<?php
require_once('../../private/config.php');

log_out_staff($staff);

redirect_to(url_wrap('/staff/login.php'));

?>
