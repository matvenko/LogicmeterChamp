<?php
//$user_class->permission_end($module, "user");
global $out;

$user_info = $user_class->user_info($user_class->current_user_id);

$replace_fields['module'] = $module;
$replace_fields['name_surname'] = $user_info['profile_name'];
$replace_fields['_LOGOUT'] = _LOGOUT;
$replace_fields['_CHOOSE_PROFILE'] = _CHOOSE_PROFILE;


$profiles_tmpl = $templates->split_template("profiles", "choose_profile_main");
$result = $query_l->select_sql("math_children", "*", "user_id = ".(int)$user_class->current_user_id." AND `disabled` = 0");
$n = 0;
while($row = $query->assoc($result)){
	$n++;
	$profiles_fields['name_surname'] = $row['name'];
	$profiles_fields['surname'] = $row['surname'];
	$profiles_fields['child_id'] = $row['id'];
	$user_image = $global_conf['location_logicmeter']."/upload/users/thumb/".$row['image'];
	$profiles_fields['user_image'] = is_file($user_image) ? $user_image : "images/user_no_image.jpg";

	$templates->gen_loop_html($profiles_fields, $profiles_tmpl);
}

//**** if one child
if($n == 1){
	$_SESSION['login']['child_id'] = $profiles_fields['child_id'];
	$_SESSION['login']['name'] = $profiles_fields['name_surname'];
	$_SESSION['login']['surname'] = $profiles_fields['surname'] = $row['surname'];;
	$_SESSION['login']['image'] = $user_image;
	$_SESSION['login']['choosed_profile'] = 1;
	
	echo "success";
	exit;
}

$out = $templates->gen_module_html($replace_fields, "choose_profile_main");