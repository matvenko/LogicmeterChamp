<?php
require_once("modules/championship/files/encryption.php");
require_once($global_conf['logicmeter_src']."modules/math_user/files/functions.php");

//****** registration ****************
if(is_post('register')){
	if(current_time() > '2016-12-17 23:59:59'){
		echo request_callback("ok_message", "", "reload");
		exit;
	}
	$check_fields = (int)$user_class->current_user_id == 0 ?
		array('parent_name', 'parent_surname', 'parent_mail', 'parent_mobile', 'parent_password', 'parent_re_passowrd') :
		array('parent_mobile');
	foreach($check_fields as $field_name){
		if(post($field_name) == false){
			echo request_callback("error", _FILL_ALL_FIELDS, "form_field_error", array("field_id" => $field_name, "good_fields" => (array)$good_fields));
			exit;
		}
		$good_fields[] = $field_name;
	}

	if((int)$user_class->current_user_id == 0) {
		//**** check mail
		$check_mail = check_mail(post('parent_mail'));
		if ($check_mail !== 'ok') {
			echo request_callback("error", $check_mail, "form_field_error", array(
				"field_id"    => "parent_mail",
				"good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "parent_mail";
		$good_fields[] = "retype_parent_mail";


		//***** check password
		if (mb_strlen(post('parent_password'), 'utf-8') < 6) {
			echo request_callback("error", _PASSWORD_ERROR, "form_field_error", array("field_id"    => "parent_password",
			                                                                          "good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "parent_password";
		if (post('parent_password') !== post('parent_re_passowrd')) {
			echo request_callback("error", _RE_PASSWORD_ERROR, "form_field_error", array("field_id"    => "parent_re_passowrd",
			                                                                             "good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "parent_re_passowrd";
	}

	//***** children
	$children_amount = post_int('children_amount') > 12 ? 12 : post_int('children_amount');
	$check_fields = array('child_name', 'child_surname', 'child_birthdate_year', 'child_birthdate_month', 'child_birthdate_day', 'grade', 'region_id', 'school_id');
	$check_fields_champ = array('grade', 'region_id', 'school_id');
	for($i = 1; $i <= $children_amount; $i ++){
		//**** skip child
		if(post_int('child_id_'.$i) == 0 && post_int('skip_child_'.$i) == 1){
			continue;
		}

		foreach($check_fields as $field_name) {
			$field_input_name = $field_name == "school_id" ? "school_id_".$i."_chosen" : $field_name.'_'.$i;
			if (post($field_name.'_'.$i) == false){
				if(!(in_array($field_name, $check_fields_champ) && post_int('skip_child_'.$i) == 1)) {
					echo request_callback("error", _FILL_ALL_FIELDS, "form_field_error", array("field_id"    => $field_input_name,
					                                                                           "good_fields" => (array)$good_fields));
					exit;
				}
			}
			$good_fields[] = $field_input_name;
		}
		
		//**** check latin chars
		if(post('child_name_'.$i) !== convert_text(post('child_name_'.$i), "lat", "geo")){
			echo request_callback("error", _LATIN_CHARS, "form_field_error", array("field_id"    => "child_name_".$i,
			                                                                           "good_fields" => (array)$good_fields));
			exit;
		}
		if(post('child_surname_'.$i) !== convert_text(post('child_surname_'.$i), "lat", "geo")){
			echo request_callback("error", _LATIN_CHARS, "form_field_error", array("field_id"    => "child_surname_".$i,
			                                                                           "good_fields" => (array)$good_fields));
			exit;
		}
		
		$fields_children[$i]['name'] = post('child_name_'.$i);
		$fields_children[$i]['surname'] = post('child_surname_'.$i);
		$fields_children[$i]['birthdate'] = post('child_birthdate_year_'.$i).'-'.post('child_birthdate_month_'.$i).'-'.post('child_birthdate_day_'.$i);

		//***** insert school
		$school_info = $query_l->select_ar_sql("teachers_school", "*", "id = ".post_int('school_id_'.$i));
		$fields_children[$i]['school_code'] = (int)$school_info['id'] !== 0 ? $school_info['school_code'] : "";
		$fields_championship[$i]['school_id'] = $school_info['id'];
		$fields_championship[$i]['region_id'] = $school_info['region_id'];
		$fields_championship[$i]['grade'] = post_int('grade_'.$i);
		$fields_championship[$i]['status'] = post_int('skip_child_'.$i) == 1 ? 2 : 0;
	}

	//****** terms and rules
	if(post_int('terms_and_rules') == 0){
		echo request_callback("error", _MUST_AGREE_TERMS, "form_field_error", array("field_id" => "terms_and_rules_block", "good_fields" => (array)$good_fields));
		exit;
	}

	//***** registration
	if((int)$user_class->current_user_id == 0) {
		$fields_user['name'] = post('parent_name');
		$fields_user['surname'] = post('parent_surname');
		$fields_user['mail'] = post('parent_mail');
		$fields_user['password'] = md5(post('parent_password'));
		$fields_user['add_time'] = current_time();
		$fields_user['register_ip'] = $_SERVER['REMOTE_ADDR'];
		$fields_user['activation_code'] = md5(generatePassword(6, 2));
		$fields_user['status'] = 1;
		$user_id = $query_l->insert_sql("users", $fields_user);


		//***** user other info
		$fields_user_other_info['user_id'] = $user_id;
		$fields_user_other_info['tel'] = only_numbers(post('parent_mobile'));
		$query_l->insert_sql("users_other_info", $fields_user_other_info);

		//**** insert to parents group
		$fields_group['user_id'] = $user_id;
		$fields_group['group_id'] = 9;
		$query_l->insert_sql("user_groups", $fields_group);
	}
	else{
		$user_id = $user_class->current_user_id;
		$fields_user_other_info['tel'] = only_numbers(post('parent_mobile'));
		$query_l->update_sql("users_other_info", $fields_user_other_info, "user_id = ".(int)$user_class->current_user_id);
	}

	//**** insert children
	$children_ids = array();
	$n = 0;
	foreach ($fields_children as $fields){
		$n++;
		$fields_child = $fields;
		$child_info = $champ->child_info(post_int('child_id_'.$n));
		if((int)$child_info['id'] == 0) {
			$fields_child['math'] = 1;
			$fields_child['user_id'] = $user_id;
			$children_ids[] = $child_id = $query_l->insert_sql("math_children", $fields_child);
		}
		else{
			$child_id = post_int('child_id_'.$n);
			$query_l->update_sql("math_children", $fields_child, "user_id = ".(int)$user_class->current_user_id." AND id = ".(int)$child_id);
		}

		//**** insert children to championship
		$fields_champ['school_id'] = $fields_championship[$n]['school_id'];
		$fields_champ['region_id'] = $fields_championship[$n]['region_id'];
		$fields_champ['grade'] = $fields_championship[$n]['grade'];
		$fields_champ['status'] = $fields_championship[$n]['status'];
		
		$champ_child_info = $champ->champ_child_info($child_id, "all");
		if((int)$champ_child_info['id'] == 0) {
			$fields_champ['date'] = $champ->config['champ_date'];
			$fields_champ['user_id'] = $user_id;
			$fields_champ['child_id'] = $child_id;
			$fields_champ['add_time'] = current_time();
			$query->insert_sql("championship_children", $fields_champ);
		}
		else{
			$query->where_vars['champ_date'] = $champ->config['champ_date'];
			$fields_champ['update_time'] = current_time();
			$query->update_sql("championship_children", $fields_champ, "date = '{{champ_date}}' AND user_id = ".(int)$user_class->current_user_id." AND child_id = ".(int)$child_id);
		}
	}


	if((int)$user_class->current_user_id == 0) {
		//***** login *******
		$user_info = $user_class->user_info($user_id);
		$user_class->set_login_sessions($user_info);


		//**** insert package
		/*$user_class->current_user_id = $user_id;
		$query_temp = $query;
		$query = $query_l;
		require_once($global_conf['logicmeter_src']."modules/math_user/files/functions.php");
		$math = new math();
		$math->generate_package(1, 1, 0);
		$query = $query_temp;*/
	}

	echo request_callback("ok", "index.php?module=".$module."&page=final_submission");
	exit;
}
//************************************

//******** final submission **********
if(is_post('final_submission')){
	if(!$user_class->login_action())exit;

	$query->where_vars['champ_date'] = $champ->config['champ_date'];
	$query->update_sql("championship_children", array("status" => 1), "date = '{{champ_date}}' AND user_id = ".(int)$user_class->current_user_id." AND `status` = 0");

	//**** send email
	$global_conf['reply_to'] = 'info@logicmeter.com';
	$user_info = $user_class->user_info($user_class->current_user_id);
	$replace_fields = $user_info;

	//*** regions
	$result_regions = $query_l->select_sql("teachers_regions");
	while($row_regions = $query_l->assoc($result_regions)){
		$regions[$row_regions['id']] = $row_regions['name'];
	}
	
	//**** children
	$children_tmpl = $templates->split_template("children", "registration_mail");
	$result_children = $query->select_sql("championship_children", "*", "`date` = '{{champ_date}}' AND user_id = ".(int)$user_class->current_user_id." AND `status` IN(0,1)");
	while($row_children = $query->assoc($result_children)){
		$child_info = $champ->child_info($row_children['child_id']);
		$school_info = $query_l->select_ar_sql("teachers_school", "*", "id = ".(int)$row_children['school_id']);

		$children_fields['name'] = $child_info['name'];
		$children_fields['surname'] = $child_info['surname'];
		$children_fields['school'] = $school_info['school_name'];
		$children_fields['region'] = $regions[$school_info['region_id']];
		$children_fields['grade'] = $row_children['grade'];

		$templates->gen_loop_html($children_fields, $children_tmpl);
	}

	$mail_text = $templates->gen_module_html($replace_fields, "registration_mail");
	send_mail($user_info['mail'], _MATH_CHAMPIONSHIP, email_formated_text($mail_text));

	echo request_callback("ok", "index.php?module=".$module."&page=registration_ok");
	exit;
}
//************************************

//******* school registration ********
if(is_post('school_register')){
	$check_fields = !$user_class->login_action() ? array('name', 'surname', 'tel', 'mail') : array('tel');
	foreach($check_fields as $field_name){
		if(post($field_name) == false){
			echo request_callback("error", _FILL_ALL_FIELDS, "form_field_error", array("field_id" => $field_name, "good_fields" => (array)$good_fields));
			exit;
		}
		$good_fields[] = $field_name;
	}
	
	//***** check school
	$query_l->where_vars['school_hash'] = post('school_hash');
	$school_info = $query_l->select_ar_sql("teachers_school", "*", "school_hash = '{{school_hash}}'");
	if((int)$school_info['id'] == 0){
		echo request_callback("error", _SCHOOL_HASH_ERROR, "form_field_error", array(
			"field_id"    => "password",
			"good_fields" => (array)$good_fields
		));
		exit;
	}

	if(!$user_class->login_action()) {
		//**** check mail
		$check_mail = check_mail(post('mail'));
		if ($check_mail !== 'ok') {
			$check_mail = $check_mail == _MAIL_EXIST ? _MAIL_EXIST.". <span>"._GO_TO_AUTH_PAGE."</span>" : $check_mail;
			$check_mail = str_replace("{{referrer_url}}", base64_encode("index.php?module=".$module."&page=register_school&school_hash=".post('school_hash')), $check_mail);
			echo request_callback("error", $check_mail, "form_field_error", array(
				"field_id"    => "mail",
				"good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "mail";

		//***** check password
		if(mb_strlen(post('password'), 'utf-8') < 6) {
			echo request_callback("error", _PASSWORD_ERROR, "form_field_error", array(
				"field_id"    => "password",
				"good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "password";
		if (post('password') !== post('re_passowrd')) {
			echo request_callback("error", _RE_PASSWORD_ERROR, "form_field_error", array(
				"field_id"    => "re_passowrd",
				"good_fields" => (array)$good_fields
			));
			exit;
		}
		$good_fields[] = "re_passowrd";
	}

	//***** registration
	if((int)$user_class->current_user_id == 0) {
		$fields_user['name'] = post('name');
		$fields_user['surname'] = post('surname');
		$fields_user['mail'] = post('mail');
		$fields_user['password'] = md5(post('password'));
		$fields_user['add_time'] = current_time();
		$fields_user['register_ip'] = $_SERVER['REMOTE_ADDR'];
		$fields_user['activation_code'] = md5(generatePassword(6, 2));
		$user_id = $query_l->insert_sql("users", $fields_user);


		//***** user other info
		$fields_user_other_info['user_id'] = $user_id;
		$fields_user_other_info['tel'] = only_numbers(post('tel'));
		$query_l->insert_sql("users_other_info", $fields_user_other_info);

		//**** insert to parents group
		$fields_group['user_id'] = $user_id;
		$fields_group['group_id'] = 9;
		$query_l->insert_sql("user_groups", $fields_group);

		//***** login *******
		$user_info = $user_class->user_info($user_id);
		$user_class->set_login_sessions($user_info);
	}
	else{
		$user_id = $user_class->current_user_id;
		$fields_user_other_info['tel'] = only_numbers(post('tel'));
		$query_l->update_sql("users_other_info", $fields_user_other_info, "user_id = ".(int)$user_class->current_user_id);
	}
	
	//**** school registration
	$champ_school_info = $champ->champ_school_info($school_info['id']);
	if((int)$champ_school_info['id'] == 0){
		$fields_champ_school['school_id'] = $school_info['id'];
		$fields_champ_school['user_id'] = $user_id;
		$fields_champ_school['champ_date'] = $champ->config['champ_date'];
		//$fields_champ_school['status'] = 1;
		$fields_champ_school['add_time'] = current_time();

		$query->insert_sql("championship_schools", $fields_champ_school);
	}

	//***** send mail
	$user_info = $user_class->user_info($user_id);
	$mail_text = $query->select_ar_sql("mail_templates", "*", "name = 'IST_REGISTRATION_MAIL'");
	send_mail($user_info['mail'], $mail_text['title'], $mail_text['tamplate']);

	echo request_callback("ok", "index.php?module=".$module."&page=school_profile");
	exit;
}
//************************************


$pages = array (
		'register',
		'profile',
		'login',
		'logout',
		'chng_pass',
		'login_main_page',
		'auth_main_page',
		'choose_profile_main',
		'registration_ok',
		'register_school',
		'final_submission',
		'school_profile',
		'child_info',
		'child_tests'
);
load_page(get('page'), $pages, "login");
?>