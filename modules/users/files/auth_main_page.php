<?php
global $out;

if($user_class->login_action()){
	$user_info = $user_class->user_info($user_class->current_user_id);

	$replace_fields['name'] = $_SESSION['login']['name'];
	$replace_fields['surname'] = $user_info['surname'];
	$replace_fields['name_surname'] = $user_info['profile_full_name'];
	$replace_fields['_GO_TO_PROFILE'] = _GO_TO_PROFILE;
	$replace_fields['_LOGOUT'] = _LOGOUT;
	$replace_fields['logicmeter_link'] = $global_conf['location_logicmeter'];
	$replace_fields['session_id'] = session_id();

	$user_image = $global_conf['location_logicmeter']."/upload/".$module."/".$user_info['image'];
	$replace_fields['user_image'] = is_file($user_image) ? $user_image : "images/user_no_image.jpg";
	
	$out = $templates->gen_module_html($replace_fields, "auth_main_page");
}