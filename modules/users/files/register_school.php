<?php
global $tmpl, $out;

if(!$user_class->login_action()){
	$templates->module_ignore_fields[] = "unregistered";
}

$query_l->where_vars['school_hash'] = get('school_hash');
$school_info = $query_l->select_ar_sql("teachers_school", "*", "school_hash = '{{school_hash}}'");
if((int)$school_info['id'] == 0){
	$templates->module_ignore_fields[] = "no_hash";
	$replace_fields['_SCHOOL_HASH_ERROR'] = _SCHOOL_HASH_ERROR;
}
else{
	$templates->module_ignore_fields[] = "registration";
	$replace_fields['school_name'] = $school_info['school_full_name'];
}

//***** check if already registered
$champ_school_info = $champ->champ_school_info($school_info['id']);
if((int)$champ_school_info['id'] !== 0){
	header("Location: index.php?module=".$module."&page=".($user_class->login_action() ? "school_profile" : "login&referer=".base64_encode("index.php?module=".$module."&page=school_profile")));
	exit;
}

//**** user info
if($user_class->login_action()) {
	$edit_value = $user_info = $user_class->user_info($user_class->current_user_id);
	$edit_value['name'] = $user_info['name'];
	$edit_value['surname'] = $user_info['surname'];
	$edit_value['mail'] = $user_info['mail'];
	$edit_value['mobile'] = $user_info['tel'];
}

$replace_fields['module'] = $module;
$replace_fields['school_hash'] = get('school_hash');
$replace_fields['star'] = star();
$replace_fields['_CONTACT_INFO'] = _CONTACT_INFO;
$replace_fields['_ENTER_ICT'] = _ENTER_ICT;
$replace_fields['_NAME'] = _NAME;
$replace_fields['_SURNAME'] = _SURNAME;
$replace_fields['_MOBILE'] = _MOBILE;
$replace_fields['_EMAIL'] = _EMAIL;
$replace_fields['_PASSWORD'] = _PASSWORD;
$replace_fields['_RE_PASSWORD'] = _RE_PASSWORD;
$replace_fields['_TECHNICAL_INFO'] = _TECHNICAL_INFO;
$replace_fields['_COMPUTER_AMOUNT'] = _COMPUTER_AMOUNT;
$replace_fields['_REGISTRATION'] = _REGISTRATION;

$replace_fields['name'] = input_form("name", $edit_value['name'] == "" ? "text" : "view", $edit_value, "", "", "georgian");
$replace_fields['surname'] = input_form("surname", $edit_value['surname'] == "" ? "text" : "view", $edit_value, "", "", "georgian");
$replace_fields['tel'] = input_form("tel", "text", $edit_value);
$replace_fields['mail'] = input_form("mail", $edit_value['mail'] == "" ? "text" : "view", $edit_value);
$replace_fields['password'] = input_form("password", "password", $edit_value);
$replace_fields['re_passowrd'] = input_form("re_passowrd", "password", $edit_value);
$replace_fields['comp_amount'] = input_form("comp_amount", "text", $edit_value);

$out = $templates->gen_module_html($replace_fields, "register_school");