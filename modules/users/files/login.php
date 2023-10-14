<?php
global $out;
//**** login ******
if(is_post('login')){
	//$query = new sql_func();
	if($query_l->amount_fields("users", "mail = '".post('username')."' AND password = '".md5(post('password'))."' AND `status` != 2") == 1){
		$user_info = $query_l->select_ar_sql("users", "*", "mail = '".post('username')."' AND password = '".md5(post('password'))."' AND `status` != 2");
		//@session_start();

		//***** for school
		$school_id = $champ->user_champ_school_id($user_info['id']);
		if((int)$school_id == 0 && get('type') == "for_school"){
			echo _LOGIN_ERROR;
			exit;
		}

		session_unset();
		$user_class->set_login_sessions($user_info);
		$_SESSION['login']['child_id'] = 0;
		$_SESSION['login']['choosed_profile'] = 1;


		$query_l->update_sql("users", array("last_login" => time(), 'last_login_ip' => $_SERVER['REMOTE_ADDR']), "id = ".(int)$user_info['id']);

		if(post('login_type') == 'ajax'){
			echo 'ok';
		}
		else{
			header("Location: index.php");
		}
        exit;
	}
	else{
		if(post('login_type') == 'ajax'){
			echo _LOGIN_ERROR;
		}
		else{
			header("Location: index.php?module=".$module."&page=login&error=login_error");
		}
        exit;
	}
}

if(get('type') == "for_school"){
	$templates->module_ignore_fields[] = "for_school";
	$replace_fields['_FOR_SCHOOL_ADMIN'] = _FOR_SCHOOL_ADMIN;
	$replace_fields['_IF_SCHOOL_NOT_REGISTERED'] = _IF_SCHOOL_NOT_REGISTERED;
}

//****** choose profile ******
if(is_post('choose_profile')){
	if(post_int('child_id') !== 0){
		$child_info = $champ->child_info(post_int('child_id'));
		if($child_info !== false){
			$_SESSION['login']['child_id'] = post_int('child_id');
			$_SESSION['login']['name'] = $child_info['name'];
			$_SESSION['login']['surname'] = $child_info['surname'];
			$_SESSION['login']['image'] = $child_info['image_src'];
			$_SESSION['login']['choosed_profile'] = 1;
		}
	}
	else{
		$user_info = $user_class->user_info($user_class->current_user_id);
		$_SESSION['login']['name'] = $user_info['name'];
		$_SESSION['login']['surname'] = $user_info['surname'];
		$_SESSION['login']['image'] = $user_info['image'];
		$_SESSION['login']['child_id'] = 0;
		$_SESSION['login']['choosed_profile'] = 1;
	}
	echo "ok";
	exit;
}

if($user_class->login_action()){
	header("Location: ".(get('referer') == false ? "index.php" : str_replace("/", "", base64_decode(get('referer')))));
	exit;
}

if(get('error') !== false){
	$replace_fields['error_message'] = _LOGIN_ERROR;
}

$replace_fields['_AUTHORIZATION'] = _AUTHORIZATION;
$replace_fields['_LOGIN_TO_SYSTEM'] = _LOGIN_TO_SYSTEM;
$replace_fields['_USERNAME'] = _USERNAME;
$replace_fields['_PASSWORD'] = _PASSWORD;
$replace_fields['_RESTORE_PASSWORD'] = _RESTORE_PASSWORD;
$replace_fields['_LOGIN'] = _LOGIN;
$replace_fields['lang'] = $lang;
$replace_fields['type'] = get('type');
$replace_fields['module'] = $module;
$replace_fields['location_logicmeter'] = $global_conf['location_logicmeter'];

$replace_fields['module'] = $module;


$out .= $templates->gen_module_html($replace_fields, "login");



?>
