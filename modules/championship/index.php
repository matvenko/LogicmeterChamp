<?php
require_once("modules/".$module."/files/encryption.php");

//**** registration
if(is_post('register')){
	if((int)$user_class->current_child_id == 0){
		session_unset();
		echo request_callback("ok", "index.php?module=".$module."&page=check");
		exit;
	}
	$good_fields = array();
	if(post_int('grade') < $champ->config['class_from'] || post_int('class') > $champ->config['class_to']){
		echo request_callback("error", _CHOOSE_CLASS, "form_field_error", array("field_id" => "grade_block", "good_fields" => (array)$good_fields));
		exit;
	}
	$good_fields[] = "grade_block";
	
	if(post_int('region_id') == 0){
		echo request_callback("error", _CHOOSE_REGION, "form_field_error", array("field_id" => "region_block", "good_fields" => (array)$good_fields));
		exit;
	}
	$good_fields[] = "region_block";
	
	if($query_l->record_exist("teachers_school", "region_id != 0 AND id = ".post_int('school_id')) == false){
		echo request_callback("error", _CHOOSE_SCHOOL, "form_field_error", array("field_id" => "school_chosen", "good_fields" => (array)$good_fields));
		exit;
	}
	
	$champ_child_info = $champ->champ_child_info($user_class->current_child_id, "all");
	
	$fields['user_id'] = $user_class->current_user_id;
	$fields['child_id'] = $user_class->current_child_id;
	$fields['school_id'] = post_int('school_id');
	$fields['region_id'] = post_int('region_id');
	$fields['grade'] = post('grade');
	
	if((int)$champ_child_info['id'] == 0){
		$fields['add_time'] = current_time();
		$fields['date'] = $champ->config['start_date'];
	
		$query->insert_sql("championship_children", $fields);
	}
	else{
		$fields['update_time'] = current_time();
		
		$query->update_sql("championship_children", $fields, "id = ".(int)$champ_child_info['id']);
	}
	
	echo request_callback("ok", "index.php?module=".$module."&page=check&action=view_info");
	exit;
}

//***** final_submission
if(is_post('final_submission')){
	if((int)$user_class->current_child_id == 0){
		session_unset();
		echo request_callback("ok", "index.php?module=".$module."&page=check");
		exit;
	}
	$champ_child_info = $champ->champ_child_info($user_class->current_child_id, "all");

	if((int)$champ_child_info['id'] !== 0){
		$fields['status'] = 1;
		$fields['update_time'] = current_time();

		$query->update_sql("championship_children", $fields, "id = ".(int)$champ_child_info['id']);
	}

	echo request_callback("ok", "index.php?module=".$module."&page=tests");
	exit;
}

//****** reset ****
if(get('action') == "reset"){
	//exit;
	$user_class->permission_end("math", "all");

	$query->where_vars['start_date'] = $champ->config['start_date'];
	$query->where_vars['champ_date'] = $champ->config['champ_date'];
	$where = "child_id = ".(int)$user_class->current_child_id." AND champ_date = '{{start_date}}'";
	$query->delete_sql("championship_children_stage_points", $where);
	$query->delete_sql("championship_children_tests", $where);
	//$query->delete_sql("championship_children", "child_id = ".(int)$user_class->current_child_id);
	$query->update_sql("championship_children", array("status" => 0), "child_id = ".(int)$user_class->current_child_id." AND date = '{{champ_date}}'");
	
	session_unset();
	
	header("location: index.php?module=".$module);
	exit;
}

$pages = array (
        'tests',
        'head',
        'test_conditions',
        'test_incorrect_answer',
		'popup_message',
		'check',
		'registration',
		'tests_admin',
		'fb_share',
		'results',
        'login'
);
load_page(get('page'), $pages, 'tests');
