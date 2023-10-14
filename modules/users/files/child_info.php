<?php
global $out;

if(!$user_class->login_action()){
	header("Location: index.php?module=".$module."&page=login&type=for_school&referer=".base64_encode("index.php?module=".$module."&page=school_profile"));
	exit;
}

$school_id = $champ->user_champ_school_id();
if((int)$school_id == 0){
	exit;
}
//**** champ info
$query->where_vars['start_date'] = $champ->config['start_date'];
$child_champ_info = $query->select_ar_sql("championship_children_stage_points", "id, last_answer_time, finished", "champ_date = '{{start_date}}' AND child_id = ".get_int('child_id'));
if((int)$child_champ_info['id'] !== 0){
	$templates->module_ignore_fields[] = "caution";
	$replace_fields['_ALREADY_STARTED'] = (int)$child_champ_info['finished'] == 0 ? _ALREADY_STARTED : _ALREADY_FINISHED;
}
if((int)$child_champ_info['finished'] == 0){
	$templates->module_ignore_fields[] = "can_start";
}

$champ_child_info = $query->select_ar_sql("championship_children", "*", "`date` = '{{champ_date}}' AND school_id = ".(int)$school_id." AND child_id = ".get_int('child_id'));
$child_info = $query_l->select_ar_sql("math_children", "*", "id = ".(int)$champ_child_info['child_id']);

$replace_fields['module'] = $module;
$replace_fields['child_id'] = $child_info['id'];
$replace_fields['grade'] = $champ_child_info['grade'];
$replace_fields['name'] = $child_info['name'];
$replace_fields['surname'] = $child_info['surname'];
$replace_fields['_GRADE'] = _CLASS;
$replace_fields['_START_TEST'] = _START_TEST;
$replace_fields['_RETURN'] = _RETURN;

$out = $templates->gen_module_html($replace_fields, "child_info");
