<?php

require_once('../../../private/config.php');

require_password_reset();

if(!hasPermission('create_patient')){
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$_SESSION['patient_registration']=[];

redirect_to(
url_wrap('/modules/patients/basic_information.php')
);

?>
