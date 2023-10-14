<?php
global $out;

if(!$user_class->login_action()){
	header("Location: index.php");
	exit;
}

$replace_fields['module'] = $module;
$replace_fields['_FINAL_SUBMISSION'] = _FINAL_SUBMISSION;
$replace_fields['_FINISH'] = _FINISH;
$replace_fields['_BACK'] = _BACK;
$replace_fields['_CKECK_INFO_AND_CONTINUE'] = _CKECK_INFO_AND_CONTINUE;
$replace_fields['_PARENT_INFO'] = _PARENT_INFO;
$replace_fields['_NAME'] = _NAME;
$replace_fields['_SURNAME'] = _SURNAME;
$replace_fields['_GRADE'] = _CLASS;
$replace_fields['_BIRTHDATE'] = _BIRTHDATE;
$replace_fields['_SCHOOL'] = _SCHOOL;
$replace_fields['_EMAIL_PARENTS'] = _EMAIL_PARENTS;
$replace_fields['_MOBILE'] = _MOBILE;
$replace_fields['_CHILDREN_INFO'] = _CHILDREN_INFO;

//**** parent info
$user_info = $user_class->user_info($user_class->current_user_id);
$replace_fields['parent_name'] = $user_info['name'];
$replace_fields['parent_surname'] = $user_info['surname'];
$replace_fields['parent_mail'] = $user_info['mail'];
$replace_fields['parent_mobile'] = $user_info['tel'];

//**** children
$result_regions = $query_l->select_sql("teachers_regions");
while($row_regions = $query_l->assoc($result_regions)){
	$regions[$row_regions['id']] = $row_regions['name'];
}

$children_tmpl = $templates->split_template("children", "final_submission");
$query->where_vars['champ_date'] = $champ->config['champ_date'];
$result_children = $query->select_sql("championship_children", "*", "`date` = '{{champ_date}}' AND user_id = ".(int)$user_class->current_user_id." AND `status` IN(0,1)");
$n = 0;
while($row_children = $query->assoc($result_children)){
	$n++;
	$child_info = $champ->child_info($row_children['child_id']);
	$school_info = $query_l->select_ar_sql("teachers_school", "*", "id = ".(int)$row_children['school_id']);

	$children_fields['n'] = $n;
	$children_fields['child_name'] = $child_info['name'];
	$children_fields['child_surname'] = $child_info['surname'];
	$children_fields['child_birthdate'] = $child_info['birthdate'];
	$children_fields['school'] = $school_info['school_name'];
	$children_fields['region'] = $regions[$school_info['region_id']];
	$children_fields['grade'] = $row_children['grade'];

	$templates->gen_loop_html($children_fields, $children_tmpl);
}

$out = $templates->gen_module_html($replace_fields, "final_submission");
