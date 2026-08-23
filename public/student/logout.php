<?php
require_once('../../private/config.php');
require_once('../../private/student_auth.php');

log_out_student();
redirect_to(url_wrap('/index.php'));
?>
