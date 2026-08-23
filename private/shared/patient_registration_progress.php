<?php

$currentStep = $currentStep??1;

$totalSteps = 6;

$percent = ($currentStep/$totalSteps)*100;

?>

<div class = "registration-progress">

<div class = "progress-header">

<p>

Step <?php echo $currentStep;?> of <?php echo $totalSteps;?>

</p>

</div>

<div class = "progress">

<div class = "progress-fill" style = "width:<?php echo $percent;?>%">

</div>

</div>

</div>