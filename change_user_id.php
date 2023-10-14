<?php
include("init.php");

if($user_class->user_admin() !== "ADMIN"){
	exit;
}

$user_info = $query_l->select_ar_sql("users", "*", "id = ".get_int('user_id'));
@session_start();
session_unset();
$user_class->set_login_sessions($user_info);
header("Location: index.php");