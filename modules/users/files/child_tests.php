<?php
global $out, $sql_db, $sql_db_l, $query_ch, $math;

//*** check child_id
if(!$user_class->login_action()){
	header("Location: index.php?module=".$module."&page=login&type=for_school&referer=".base64_encode("index.php?module=".$module."&page=school_profile"));
	exit;
}

//**** check result time
if($champ->config['result_time'] > current_time()){
	header("Location: index.php?module=".$module."&page=school_profile");
	exit;
}

$school_id = $champ->user_champ_school_id();
$query->where_vars['champ_date'] = $champ->config['champ_date'];
$child_info = $query->select_ar_sql("championship_children", "child_id, user_id, grade", "school_id = ".(int)$school_id." AND `date` = '{{champ_date}}' AND child_id = ".get_int('child_id'));
if((int)$school_id * (int)$child_info['child_id'] == 0){
	exit;
}
$user_class->current_user_id = $child_info['user_id'];

if(get('view_type') !== "ajax"){
	$templates->module_ignore_fields[] = "no_ajax";
}

$child_name_info = $champ->child_info($child_info['child_id']);
$replace_fields['child_name'] = $child_name_info['name'];
$replace_fields['child_surname'] = $child_name_info['surname'];
$replace_fields['child_grade'] = $child_info['grade'];
$replace_fields['_CLASS'] = _CLASS;

//*** change db
$query_ch = new sql_func();
$query = new sql_func(['db' => $sql_db_l]);

$child_id = $child_info['child_id'];
$child_tests = $champ->child_tests($child_id);
$test_id = in_array(get_int('champ_test_id'), array_keys($child_tests)) ? $child_tests[get_int('champ_test_id')] : current($child_tests);
$replace_fields['champ_test_id'] = get_int('champ_test_id') == 0 ? key($child_tests) : get_int('champ_test_id');
$replace_fields['child_id'] = $child_id;

$champ_test_full_info = $query_ch->select_ar_sql("championship_children_stage_points", "*", "champ_date = '".$champ->config['start_date']."' AND child_id = ".(int)$child_id);

$module = "math_user";
require_once($global_conf['logicmeter_src']."modules/math_user/files/functions.php");
$math = new math();
include($global_conf['logicmeter_src']."modules/".$module."/files/test_conditions.php");
$replace_fields['question'] = $out;
$out = "";

$module = "users";

//***** champ_tests
$champ_test_tmpl = $templates->split_template("champ_tests", "child_tests");
$n = 0;
foreach($child_tests as $champ_child_test_id => $champ_test_id){
	$n++;
	$champ_test_fields['champ_test_id'] = $champ_child_test_id;
	$champ_test_fields['test_n'] = $n;
	$answer_info = $query_ch->select_ar_sql("championship_children_tests", "true_answer", "child_id = ".(int)$child_id." AND test_id = ".(int)$champ_test_id);

	$champ_test_fields['test_n_border'] = (int)$answer_info['true_answer'] == 1 ? "" : "red";

	$templates->gen_loop_html($champ_test_fields, $champ_test_tmpl);
}

if(!is_array($test_info)){
	$test_info = $math->test_info($test_id);
}
$test_text_info = $query->select_ar_sql("math_tests_texts", "*", "test_id = ".(int)$test_id);

$replace_fields['module'] = $module;
$replace_fields['skill_id'] = $test_info['skill_id'];
//$replace_fields['test_id'] = $math->test_id_encrypt($test_id);
$replace_fields['test_id'] = $test_id;
//$replace_fields['point'] = $test_info['level'] + 2;
$replace_fields['point'] = $test_info['level'];
$replace_fields['sum_point'] = $champ_test_full_info['point'];
$minute = (int)($champ_test_full_info['duration']/60);
$seconds = $champ_test_full_info['duration'] - $minute * 60;
$replace_fields['spent_time'] = $minute." "._MINUTE." ".$seconds.""._SECONDS;
$replace_fields['_YOU_GAIN'] = _YOU_GAIN;
$replace_fields['_SPENT_TIME'] = _SPENT_TIME;
$replace_fields['_TEST_POINT'] = _TEST_POINT;
$replace_fields['_POINT'] = _POINT;
$replace_fields['_INCORRECT_ANSWER'] = _INCORRECT_ANSWER;
$replace_fields['_CORRECT_ANSWER'] = _CORRECT_ANSWER;
$replace_fields['_QUESTION_REVIEW'] = _QUESTION_REVIEW;
$replace_fields['_YOUR_ANSWER'] = _YOUR_ANSWER;
$replace_fields['_ANSWER_EXPLAIN'] = _ANSWER_EXPLAIN;
$replace_fields['_OVER_20_LETTER'] = _OVER_20_LETTER;
$replace_fields['_NEXT'] = _NEXT;

$replace_fields['answer_explain'] = $out = str_replace("\$\$frac", "\$\$\\frac", $test_text_info['answer_explain_cache']);

//**** javascript
if((int)strpos($test_text_info['test_text_cache'], "<script") !== 0){
	$templates->module_ignore_fields[] = "javascript";
	$answer_paramters_user_tmpl = $templates->split_template("true_answer_parameters_user", "test_incorrect_answer");
	$answer_paramters_true_tmpl = $templates->split_template("true_answer_parameters_true", "test_incorrect_answer");
}


//**** true answer
$true_answers = $math->test_answers($test_id, $test_info, 1);
foreach($true_answers as $i => $answer_info){
	if((int)$test_info['answer_type'] == 1){
		$answer_info['answer'] = str_replace("\"canvas", "\"canvas_true_answer", $answer_info['answer']);
		$replace_fields['true_answer'] .= $answer_info['answer']."<div class=\"split_user_ansers\"></div>";
	}
	elseif((int)$test_info['answer_type'] == 2){
		$replace_fields['true_answer'] .= $test_answers[$i]['left_text'].input_form("true_answer", "textbox", $answer_info['answer'], "", "width: ".$math->test_manual_aswer_width($answer_info['answer'])."px", "", "disabled").$test_answers[$i]['right_text']."<div class=\"split_user_ansers\"></div>";
	}
	else{
		//**** javascript
		if(strpos("<script", $test_text_info['test_text_cache']) !== 0){
			$answer_parameters_true[] = "'".$answer_info['answer']."'";
		}
	}
}
if(is_array($answer_parameters_true)){
	$replace_fields['answer_parameters_true'] = implode(',', $answer_parameters_true);
}

//**** user answer
$child_test_id = $query_ch->select_ar_sql("championship_children_tests", "id", "child_id = ".(int)$child_id." AND test_id = ".(int)$test_id);
$user_answers = $champ->user_answers($test_id, $child_test_id['id'], $test_info);
foreach($user_answers as $i => $answer){
	if((int)$test_info['answer_type'] == 1){
		$answer = str_replace("\"canvas", "\"canvas_user_answer", $answer);
		$replace_fields['user_answer'] .= $answer."<div class=\"split_user_ansers\"></div>";
	}
	elseif((int)$test_info['answer_type'] == 2){
		$replace_fields['user_answer'] .= $test_answers[$i]['left_text'].input_form("true_answer", "textbox", $answer, "", "width: ".$math->test_manual_aswer_width($answer)."px", "", "disabled").$test_answers[$i]['right_text']."<div class=\"split_user_ansers\"></div>";
		if(mb_strlen($answer) >= 20 && strpos($answer, "...")){
			$templates->module_ignore_fields[] = "over_20_letter";
		}
	}
	else{
		//**** javascript
		if(strpos("<script", $test_text_info['test_text_cache']) !== 0){
			$answer_parameters_user[] = "'".$answer."'";
		}
	}
}
if(is_array($answer_parameters_user)){
	$replace_fields['answer_parameters_user'] = implode(',', $answer_parameters_user);
}

//**** container
$container_info = $query_ch->select_ar_sql("championship_children_test_answers", "container", "answer_n = 1 AND child_test_id = ".(int)$child_test_id['id']);
$replace_fields['container'] = input_form("container", "hidden", $container_info['container']);

if($champ->config['result_time'] > current_date()){
	$out = "";
}
else {
	$out = (int)$child_id == 0 ? "<h4>"._NO_INFO_FINAL."<h4>" : $templates->gen_module_html($replace_fields, "child_tests");
	$out = str_replace("upload/math/", $global_conf['location_logicmeter']."upload/math/", $out);
}

$query = new sql_func(['db' => $sql_db]);
