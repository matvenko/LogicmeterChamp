<?php
$user_class->permission_end("math", "all");

$test_id = get_int('test_id');
$_SESSION['champ']['tests'][get_int('skill_id')] = $test_id;
unset($_SESSION['champ']['skills']);
$_SESSION['champ']['skills'] = array(get_int('skill_id') => get_int('skill_id'));

include("modules/".$module."/files/tests.php");
