<?php
global $out;

$templates->module_ignore_fields[] = get('message_name');
$replace_fields['_CONTINUE'] = _CONTINUE;
$replace_fields['_CERTIFICATE'] = _CERTIFICATE;

switch (get('message_name')){
	case "no_answer":		
		$replace_fields['_RETURN'] = _RETURN;
		$replace_fields['_NO_ANSWER'] = _NO_ANSWER;		
		break;
	case "go_and_register":
		$templates->module_ignore_fields[] = "login_title";
		$replace_fields['_GO_AND_REGISTER'] = _GO_AND_REGISTER;
		$replace_fields['_REGISTRATION'] = _REGISTRATION;
		$replace_fields['_RESTORE_PASSWORD'] = _RESTORE_PASSWORD;
		$module = "users";
		include("modules/users/files/login_main_page.php");
		$replace_fields['registration_form'] = $out;
		$module = load_module(get('module'));
		break;
	case "finished_skill":
		$test_info = $math->test_info(get_int('other'), "literacy");
		$award_info = $query->select_ar_sql("math_children_awards", "id", "award_id = 1 AND text_id = ".(int)$test_info['text_id']." AND skill_id = ".(int)$test_info['skill_id']." AND child_id = ".(int)$user_class->current_child_id);
		$child_award_id = $award_info['id'];
		
		$replace_fields['child_award_id'] = $child_award_id;
		$replace_fields['_FINISHED_SKILL'] = strip_tags((int)$test_info['text_id'] == 0 ? _FINISHED_SKILL : _FINISHED_SKILL_LITERACY_LOGICSTAR);
		break;
	case "finished_skill_literacy":
		$test_info = $math->test_info(get_int('other'), "literacy");
		$point_info = $math->get_user_smart_point_literacy($test_info['text_id']);
		
		$replace_fields['child_award_id'] = $child_award_id;
		$replace_fields['_FINISHED_SKILL'] = str_replace("{{point}}", $point_info['smart_point'], _FINISHED_SKILL_LITERACY);
		break;
}	
	

$out = $templates->gen_module_html($replace_fields, "popup_message");