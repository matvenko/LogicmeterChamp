<?php
global $out;

if(!$user_class->login_action()){
	header("Location: index.php?module=".$module."&page=login&type=for_school&referer=".base64_encode("index.php?module=".$module."&page=school_profile"));
	exit;
}

$school_id = $champ->user_champ_school_id();
if((int)$school_id == 0){
	$templates->module_ignore_fields[] = "no_school";
	$replace_fields['_YOU_HAVE_NO_SCHOOL'] = _YOU_HAVE_NO_SCHOOL;
}
else{
	$templates->module_ignore_fields[] = "for_school";
}

//**** check test champ start time
if(current_time() >= $champ->config['start_date']." 09:00:00" && current_time() <= $champ->config['end_date']." 21:00:00"){
	$templates->module_ignore_fields[] = "start_test";
}

//**** check result time
if($champ->config['result_time'] < current_time()){
	$templates->module_ignore_fields[] = "show_results";
}

$school_info = $query_l->select_ar_sql("teachers_school", "*", "id = ".(int)$school_id);
$replace_fields['school_name'] = $school_info['school_full_name'];


$page = set_var(get('pg'), 1);
$limit = 20;

$replace_fields['module'] = $module;
$replace_fields['_NAME'] = _NAME;
$replace_fields['_SURNAME'] = _SURNAME;
$replace_fields['_CLASS'] = _CLASS;
$replace_fields['_SCHOOLCHILD'] = _SCHOOLCHILD;
$replace_fields['_SCHOOL_CHILDREN'] = _SCHOOL_CHILDREN;
$replace_fields['_BIRTHDATE'] = _BIRTHDATE;
$replace_fields['_CODE'] = _CODE;
$replace_fields['_SEARCH'] = _SEARCH;
$replace_fields['_INSTRUCTION'] = _INSTRUCTION;
$replace_fields['_POINT'] = _POINT;
$replace_fields['_TIME'] = _SPENT_TIME;
$replace_fields['active_grade'.get_int('grade')] = "grades_search_active";
$replace_fields['grade'] = get_int('grade');

$replace_fields['name'] = input_form("name", "text", $_GET);
$classes = range($champ->config['class_from'], $champ->config['class_to']);
$grades = array_combine($classes, $classes);
//$replace_fields['grade'] = input_form("grade", "select", $_GET, $grades);

//***** search *******
$query->where_vars['champ_date'] = $champ->config['champ_date'];
$query->where_vars['start_date'] = $champ->config['start_date'];
$where = "`champ_date` = '{{start_date}}' AND `date` = '{{champ_date}}' AND status IN(0,1) AND school_id = ".(int)$school_id;
if(get('name') !== false){
	$name_surname = explode(" ", get('name'));
	$query->where_vars['name'] = $name_surname[0];
	$query->where_vars['surname'] = $name_surname[1];
	$where .= " AND ((child_name LIKE '%{{name}}%' AND child_surname LIKE '%{{surname}}%') OR
				(child_name LIKE '%{{surname}}%' AND child_surname LIKE '%{{name}}%'))";
}
if(get_int('grade') > 0){
	$where .= " AND grade = ".get_int('grade');
}

$order_by = $champ->config['result_time'] < current_time() ? "grade ASC, point DESC, duration ASC, id DESC" : "grade ASC, id DESC";
$result = $query->select_sql("users_championship", "*", $where, $order_by, ($page - 1)*$limit.", ".$limit);
$children_tmpl = $templates->split_template("children", "school_profile");
$n = ($page - 1)*$limit;
while($row = $query->assoc($result)){
	$n++;
	$children_fields['n'] = $n;
	$children_fields['module'] = $module;
	$children_fields['child_id'] = $row['child_id'];
	$children_fields['name'] = $row['child_name'];
	$children_fields['surname'] = $row['child_surname'];
	$children_fields['birthdate'] = $row['child_birthdate'];
	$children_fields['grade'] = $row['grade'];

	$children_fields['_TESTS'] = (int)$row['duration'] > 0 ? _TESTS : "";
	$children_fields['point'] = $row['point'];
	$minute = (int)($row['duration']/60);
	$seconds = $row['duration'] - $minute * 60;
	$children_fields['time'] = (int)$row['duration'] > 0 ? $minute." "._MINUTE." ".$seconds." "._SECONDS : "";
	$children_fields['final'] = (int)$row['next_stage'] == 1 ? "("._FINALIST.")" : "";

	//**** champ info
	$child_champ_info = $query->select_ar_sql("championship_children_stage_points", "id, last_answer_time, finished", "champ_date = '{{start_date}}' AND child_id = ".(int)$row['child_id']);
	if((int)$child_champ_info['id'] == 0){
		$children_fields['_START'] = _START;
		$children_fields['button_status'] = "";
	}
	else{
		$children_fields['_START'] = (int)$child_champ_info['finished'] == 0 ? _STARTED : _FINISHED;
		$children_fields['button_status'] = (int)$child_champ_info['finished'] == 0 ? "started" : "finished";
	}

	//**** show start test button
	$children_fields['start_test_status'] = $row['grade'] >= 2 ? "inline-block" : "none";

	$templates->gen_loop_html($children_fields, $children_tmpl);
}

$replace_fields['children_amount'] = $query->amount_fields("users_championship", $where);
$replace_fields['split_page'] = split_page("users_championship", $where, $limit);

$out = $templates->gen_module_html($replace_fields, "school_profile");
