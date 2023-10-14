<?php
global $tmpl, $out;

if(current_time() > '2016-12-17 23:59:59'){
	header("Location: text-9.html");
	exit;
}

/*if($user_class->login_action()){
	header("Location: index.php");
	exit;
}*/

if(get('member') == false && !$user_class->login_action()){
	$templates->module_ignore_fields[] = "choose_membership";
	$replace_fields['_ARE_YOU_MEMBER'] = _ARE_YOU_MEMBER;
	$replace_fields['_YES'] = _YES;
	$replace_fields['_NO'] = _NO;
	$replace_fields['referrer_url'] = base64_encode("index.php?module=".$module."&page=register&member=yes");
}
elseif (get('member') === "yes" && !$user_class->login_action()){
	header("Location: index.php?module=".$module."&page=login");
	exit;
}
else{
	$templates->module_ignore_fields[] = "registration";
}

//**** gen schools
if(get('action') == "gen_schools"){
	$result_schools = $query_l->select_sql("teachers_school", "*", "region_id != 0 AND region_id = ".(int)get_int('region'), "school_name ASC");
	$data['schools'] = $data['schools_id'] = array();
	while($row_schools = $query_l->assoc($result_schools)){
		$data['schools'][] = $row_schools['school_name'];
		$data['schools_id'][] = $row_schools['id'];
	}

	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;
}

//**** search_school
if(get('action') == "search_school"){
	$school_info = $query->select_ar_sql("teachers_school", "id, school_name", "school_code = '".get_int('school_code')."' AND active = 1");
	if((int)$school_info['id'] !== 0){
		echo $school_info['school_name'];
	}
	else{
		echo "not_found";
	}
	exit;
}

$replace_fields['module'] = $module;
$replace_fields['star'] = star();
$replace_fields['_BE_MEMBER'] = _BE_MEMBER;
$replace_fields['_MEMBERSHIP'] = _MEMBERSHIP;
$replace_fields['_CHOOSE_PERIOD'] = _CHOOSE_PERIOD;
$replace_fields['_CHILDREN_AMOUNT'] = _CHILDREN_AMOUNT;
$replace_fields['_PARENT_INFO'] = _PARENT_INFO;
$replace_fields['_NAME'] = _NAME;
$replace_fields['_SURNAME'] = _SURNAME;
$replace_fields['_EMAIL_PARENTS'] = _EMAIL_PARENTS;
$replace_fields['_RETYPE_EMAIL_PARENTS'] = _RETYPE_EMAIL;
$replace_fields['_MOBILE'] = _MOBILE;
$replace_fields['_PASSWORD'] = _PASSWORD;
$replace_fields['_RE_PASSWORD'] = _RE_PASSWORD;
$replace_fields['_CHILDREN_INFO'] = _CHILDREN_INFO;
$replace_fields['_CHOOSE_PACKAGE'] = _CHOOSE_PACKAGE;
$replace_fields['_REGISTRATION'] = _REGISTRATION;
$replace_fields['_SCHOOL_NOT_FOUND'] = _SCHOOL_NOT_FOUND;
$replace_fields['_SCHOOL_CODE_LENGTH_ERROR'] = _SCHOOL_NOT_FOUND;
$replace_fields['_NO_CHILDREN_AMOUNT'] = _NO_CHILDREN_AMOUNT;
$replace_fields['_ADD_CHILD'] = _ADD_CHILD;
$replace_fields['_GEO_KEYBOARD'] = _GEO_KEYBOARD;

//**** parent info
if($user_class->login_action()){
	$user_info = $user_class->user_info($user_class->current_user_id);
	$edit_value['parent_name'] = $user_info['name'];
	$edit_value['parent_surname'] = $user_info['surname'];
	$edit_value['parent_mail'] = $user_info['mail'];
	$edit_value['parent_mobile'] = $user_info['tel'];

	//***** children
	$result = $query_l->select_sql("math_children", "*", "user_id = ".(int)$user_class->current_user_id." AND `disabled` = 0");
	$n = 0; $children_info = array();
	while($row = $query->assoc($result)){
		$n++;
		$children_info[$n]['id'] = $row['id'];
		$children_info[$n]['name'] = $edit_value['child_name_'.$n] = $row['name'];
		$children_info[$n]['surname'] = $edit_value['child_surname_'.$n] = $row['surname'];
		$date_parts = explode("-", $row['birthdate']);
		$children_info[$n]['year'] = $edit_value['child_birthdate_year_'.$n] = $date_parts[0];
		$children_info[$n]['month'] = $edit_value['child_birthdate_month_'.$n] = (int)$date_parts[1];
		$children_info[$n]['day'] = $edit_value['child_birthdate_day_'.$n] = (int)$date_parts[2];

		//**** champ info
		$child_champ_info = $champ->champ_child_info($row['id'], "all");
		$children_info[$n]['skip_child'] = $edit_value['skip_child_'.$n] = (int)$child_champ_info['status'] == 2 ? 1 : 0;
		$children_info[$n]['grade'] = $edit_value['grade_'.$n] = $child_champ_info['grade'];
		$children_info[$n]['region_id'] = $edit_value['region_id_'.$n] = $child_champ_info['region_id'];
		$children_info[$n]['school_id'] = $edit_value['school_id_'.$n] = $child_champ_info['school_id'];
	}
}
else{
	$templates->module_ignore_fields[] = "unregistered";
}
$replace_fields['parent_name'] = input_form("parent_name", $edit_value['parent_name'] == "" ? "text" : "view", $edit_value, "", "", "georgian");
$replace_fields['parent_surname'] = input_form("parent_surname", $edit_value['parent_surname'] == "" ? "text" : "view", $edit_value, "", "", "georgian");
$replace_fields['parent_mail'] = input_form("parent_mail", $edit_value['parent_mail'] == "" ? "text" : "view", $edit_value);
$replace_fields['parent_mobile'] = input_form("parent_mobile", "text", $edit_value);
$replace_fields['retype_parent_mail'] = input_form("retype_parent_mail", "text", $edit_value);
$replace_fields['parent_password'] = input_form("parent_password", "password", $edit_value);
$replace_fields['parent_re_passowrd'] = input_form("parent_re_passowrd", "password", $edit_value);

//************ children
$result_regions = $query_l->select_sql("teachers_regions");
while($row_regions = $query_l->assoc($result_regions)){
	$regions[$row_regions['id']] = $row_regions['name'];
}

$replace_fields['children_amount'] = count($children_info) > 0 ? count($children_info) : 1;
//$schools['0'] = _CHOOSE_REGION;
$classes = range($champ->config['class_from'], $champ->config['class_to']);

$children_birthdate_years = range(date("Y") - 3, date("Y") - 20);
$children_birthdate_years = array_combine($children_birthdate_years, $children_birthdate_years);
$children_birthdate_years[0] = _YEAR;
$birthdate_monthes = array(1 => _JANUARY, 2 => _FABRUARY, 3 => _MARCH, 4 => _APRIL, 5 => _MAY, 6 => _JUNE, 7 => _JULY, 8 => _AUGUST, 9 => _SEPTEMBER, 10 => _OCTOMBER, 11 => _NOVEMBER, 12 => _DECEMBER);
$birthdate_monthes[0] = _MONTH;
$birthdate_dayes = range(0, 31);
$birthdate_dayes[0] = _DAY;

$children_tmpl = $templates->split_template("children", "register");
for($i = 1; $i <= 4; $i++){
	//**** schools ***
	$schools = array();
	if((int)$edit_value['region_id_'.$i] !== 0) {
		$result_schools = $query_l->select_sql("teachers_school", "*", "region_id = ".(int)$edit_value['region_id_'.$i], "school_name ASC");
		while ($row_schools = $query_l->assoc($result_schools)) {
			$schools[$row_schools['id']] = $row_schools['school_name'];
		}
	}

	$children_fields['child_id'] = $children_info[$i]['id'];
	$children_fields['child_display'] = $i > 1 ? "none" : "block";
	$children_fields['_NAME'] = _NAME;
	$children_fields['_SURNAME'] = _SURNAME;
	$children_fields['_BIRTHDATE'] = _BIRTHDATE;
	$children_fields['_REGION'] = _REGION;
	$children_fields['_SCHOOL'] = _SCHOOL;
	$children_fields['_CLASS'] = _CLASS;
	$children_fields['_SKIP_CHILD'] = _SKIP_CHILD;
	$children_fields['star'] = star();
	$children_fields['child_n'] = $i;
	$children_fields['skip_child_style'] = ((int)$children_info[$i]['id'] !== 0 && count($children_info) > 1) || $i > 1 ? "block" : "none";
	$children_fields['skip_child'] = input_form("skip_child_".$i, "checkbox", $edit_value);
	$children_fields['skip_child_class'] = (int)$edit_value['skip_child_'.$i] == 1 ? "skip_child_block" : "";
	$children_fields['child_name'] = input_form("child_name_".$i, "text", $edit_value, "", "", "georgian");
	$children_fields['child_surname'] = input_form("child_surname_".$i, "text", $edit_value, "", "", "georgian");
	$children_fields['child_birthdate_year'] = input_form("child_birthdate_year_".$i, "select", $edit_value, $children_birthdate_years, "width: 65px", "year_month year", "data-num=\"".$i."\"");
	$children_fields['child_birthdate_month'] = input_form("child_birthdate_month_".$i, "select", $edit_value, $birthdate_monthes, "width: 105px", "year_month month", "data-num=\"".$i."\"");
	$children_fields['child_birthdate_day'] = input_form("child_birthdate_day_".$i, "select", $edit_value, $birthdate_dayes, "width: 60px", "day");
	$children_fields['school'] = input_form("school_id_".$i, "select", $edit_value, $schools, "width: 245px", "chosen-select school_id");
	$children_fields['region'] = input_form("region_id_".$i, "select", $edit_value, $regions, "width: 245px");
	$children_fields['grade'] = input_form("grade_".$i, "select", $edit_value, array_combine($classes, $classes), "width: 100px");
	
	$templates->gen_loop_html($children_fields, $children_tmpl);
}

$replace_fields['terms_and_rules'] = input_form("terms_and_rules", "checkbox");
$replace_fields['_I_AGREE'] = _I_AGREE;
$replace_fields['_TERMS_AND_RULES'] = _TERMS_AND_RULES;

$out = $templates->gen_module_html($replace_fields, "register");