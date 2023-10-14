<?php
global $out, $facebook_loginUrl;

if((int)$user_class->choosed_profile == 0 && $user_class->login_action()){
	include("modules/".$module."/files/choose_profile_main.php");
}
elseif($user_class->login_action()){
	include("modules/".$module."/files/auth_main_page.php");
}
else{
	$replace_fields['module'] = $module;
	$replace_fields['_AUTHORIZATION'] = _AUTHORIZATION;
	$replace_fields['_USERNAME'] = _USERNAME;
	$replace_fields['_PASSWORD'] = _PASSWORD;
	$replace_fields['_RESTORE_PASSWORD'] = _RESTORE_PASSWORD;
	$replace_fields['_LOGIN_TO_SYSTEM'] = _LOGIN_TO_SYSTEM;
	$replace_fields['_LOGIN'] = _LOGIN;
	$replace_fields['location_logicmeter'] = $global_conf['location_logicmeter'];

	$out .= $templates->gen_module_html($replace_fields, "login_main_page");
}