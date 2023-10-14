<?php
$user_class->login_action_end();
global $out, $input2view;

//***** check child registration info
if($champ->is_participant() == true){
	header("Location: index.php?module=".$module);
	exit;
}

//**** gen schools
if(get('action') == "gen_schools"){
	$result_schools = $query_l->select_sql("teachers_school", "*", "region_id != 0 AND region_id = ".(int)get_int('region'));
	while($row_schools = $query_l->assoc($result_schools)){
		$data['schools'][] = $row_schools['school_name'];
		$data['schools_id'][] = $row_schools['id'];
	}
	
	echo json_encode($data);
	exit;
}

//***** change child
if(get('action') == "change_child"){
	$_SESSION['login']['child_id'] = 0;
	header("Location: index.php?module=".$module);
	exit;
}

$replace_fields['style_select'] = "style_select";
$edit_value = $champ->champ_child_info($user_class->current_child_id, "all");

//**** view mode
if(get('action') == "view_info" && (int)$edit_value['id'] !== 0){
	$templates->module_ignore_fields[] = "view_info";
	$input2view = "view";
	$replace_fields['style_select'] = "";
}
else{
	$templates->module_ignore_fields[] = "registration";
}

if(get('action') !== "view_info" && $query_l->amount_fields("math_children", "user_id = ".(int)$user_class->current_user_id." AND `disabled` = 0") > 1){
	$templates->module_ignore_fields[] = "multi_children";
}

$replace_fields['module'] = $module;
$replace_fields['_CKECK_INFO_AND_CONTINUE'] = _CKECK_INFO_AND_CONTINUE;
$replace_fields['_CHILDREN_INFO'] = _CHILDREN_INFO;
$replace_fields['_NAME'] = _NAME;
$replace_fields['_SURNAME'] = _SURNAME;
$replace_fields['_CLASS'] = _CLASS;
$replace_fields['_REGION'] = _REGION;
$replace_fields['_SCHOOL'] = _SCHOOL;
$replace_fields['_CONTINUE'] = $input2view == "view" ? _START_TEST : _CONTINUE;
$replace_fields['_BACK'] = _BACK;
$replace_fields['_CHANGE'] = _CHANGE;
$replace_fields['_LOADING'] = _LOADING;

$child_info = $champ->child_info($user_class->current_child_id);
$replace_fields['child_name'] = $child_info['name'];
$replace_fields['child_surname'] = $child_info['surname'];

$classes = range($champ->config['class_from'], $champ->config['class_to']);
$replace_fields['grade'] = input_form("grade", "select", $edit_value, array_combine($classes, $classes), "width: 100px");

$result_regions = $query_l->select_sql("teachers_regions");
while($row_regions = $query_l->assoc($result_regions)){
	$regions[$row_regions['id']] = $row_regions['name'];
}
$replace_fields['region'] = input_form("region_id", "select", $edit_value, $regions, "width: 230px");

$result_schools = $query_l->select_sql("teachers_school", "*", "region_id != 0 AND region_id = ".(int)$edit_value['region_id']);
while($row_schools = $query_l->assoc($result_schools)){
	$schools[$row_schools['id']] = $row_schools['school_name'];
}
//$replace_fields['school'] = input_form("school_id", "select", $edit_value, $schools, "width: 350px", "chosen-select");
$replace_fields['school'] = input_form("school_id", "hidden", 1)."Logicmeter";

$out = $templates->gen_module_html($replace_fields, "registration");