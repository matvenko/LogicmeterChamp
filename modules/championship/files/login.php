<?php
global $out;
exit;
$champ_date = "2016-12-19";
//**** login ******
if(is_post('login')){
	$child_point_info = $query->select_ar_sql("championship_children_stage_points", "next_stage", "champ_date = '".$champ_date."' AND child_id = ".post_int('child_code'));
	if((int)$child_point_info['next_stage'] == 0){
		echo request_callback("error", _CHILD_NOT_IN_LIST);
		exit;
	}
	if(post('password') !== "1985"){
		echo request_callback("error", _PASSWORD_INCORRECT);
		exit;
	}

	//**** user info
	$child_info = $query_l->select_ar_sql("math_children", "*", "id = ".post_int('child_code'));
	$user_info = $query_l->select_ar_sql("users", "*", "id = ".(int)$child_info['user_id']);
	session_unset();
	$user_class->set_login_sessions($user_info);

	//*** child info
	$_SESSION['login']['child_id'] = $child_info['id'];
	$_SESSION['login']['name'] = $child_info['name'];
	$_SESSION['login']['surname'] = $child_info['surname'];
	$_SESSION['login']['choosed_profile'] = 1;

	echo request_callback("ok", "index.php?module=".$module);
	exit;
}

//**** login from school
if(is_post('school_login')){

	//**** user info
	$child_info = $query_l->select_ar_sql("math_children", "*", "id = ".post_int('child_id'));
	$user_info = $query_l->select_ar_sql("users", "*", "id = ".(int)$child_info['user_id']);
	session_unset();
	$user_class->set_login_sessions($user_info);

	//*** child info
	$_SESSION['login']['child_id'] = $child_info['id'];
	$_SESSION['login']['name'] = $child_info['name'];
	$_SESSION['login']['surname'] = $child_info['surname'];
	$_SESSION['login']['choosed_profile'] = 1;

	echo request_callback("ok", "index.php?module=".$module);
	exit;
}


if($user_class->login_action()){
	header("Location: ".(get('referer') == false ? "index.php" : str_replace("/", "", base64_decode(get('referer')))));
	exit;
}

$replace_fields['_LOGIN_TO_SYSTEM'] = _LOGIN_TO_SYSTEM;
$replace_fields['_CHILD_CODE'] = _CHILD_CODE;
$replace_fields['_PASSWORD'] = _PASSWORD;
$replace_fields['lang'] = $lang;
$replace_fields['module'] = $module;

$replace_fields['module'] = $module;


$out .= $templates->gen_module_html($replace_fields, "login");



?>
