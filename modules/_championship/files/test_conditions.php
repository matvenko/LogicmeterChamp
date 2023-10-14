<?php
global $out, $math;

if((int)$test_id == 0){
	$test_id = (int)$champ->get_random_test_ids();
	//$test_id = 193398;
	if(!is_array($_SESSION['math']['test_ids'])) $_SESSION['math']['test_ids'] = array();
	$_SESSION['math']['test_ids'][$test_id] = $test_id;
	$templates->module_ignore_fields[] = "answer_button";
	$disabled = "";
}
else{
	$disabled = "disabled";
	$answer_fields['disabled'] = "disabled";
}

if(!is_array($test_info)){
	$test_info = $champ->test_info($test_id);
	if((int)$test_info['id'] == 0){
		unset($_SESSION['math']['tests'][get_int('skill_id')]);
		$test_id = (int)$champ->get_random_test_ids(get_int('skill_id'));
		$test_info = $champ->test_info($test_id);
	}
}
$test_text_info = $champ->test_info($test_id, "full");

//**** show test code
if($user_class->group_perm("math", "all")){
	$templates->module_ignore_fields[] = "admin";
	$replace_fields['test_code'] = $test_id;
	$user_info = $user_class->user_info($test_info['add_user_id']);
	$replace_fields['test_author'] = $user_info['name'];
}

$replace_fields['module'] = $module;
$replace_fields['test_id'] = $champ->test_id_encrypt($test_id);
$replace_fields['question'] = $test_text_info['test_text_cache'];
$replace_fields['_ANSWER'] = _ANSWER;
$replace_fields['_SKIP'] = _SKIP;

//**** answers
$answer_type['1'] = "test";
$answer_type['2'] = "manual";
$answer_type['4'] = "hidden";
if((int)$test_info['horizontal'] == 1){
	$answer_type['1'] = "horizontal";
}
//$answer_type = (int)$test_info['horizontal'] == 1 ? "horizontal" : $answer_type;
$answer_tmpl = $templates->split_template("answer_".$answer_type[$test_info['answer_type']], "test_conditions");

$result = $query_l->select_sql("math_tests_answers", "*", "del = 0 AND test_id = ".(int)$test_id, "priority ASC");
$test_answers = $champ->test_answers($test_id, $test_info);
$replace_fields['answer'] = "";
foreach($test_answers as $i => $answer_info){
    $answer_fields['answer_n'] = $i;
    if((int)$test_info['answer_type'] == 1){
        $answer_fields['answer_input'] = (int)$test_info['multi_answer'] == 0 ? input_form("answer", "single_radio", "", $i, "", "", $disabled) : input_form("answer_".$i, "checkbox", "", "", "", "", $disabled);
        $answer_fields['answer'] = $answer_info['answer'];
        if((int)$test_info['horizontal'] == 0){
            $replace_fields['answer'] .= $templates->gen_html($answer_fields, $answer_tmpl['answer_test'], 0);
            $templates->module_ignore_fields[] = "answers_test";
        }
        else{
        	$answer_text_fields['answer'] = "<label for=\"answer_".$i."\">".$answer_fields['answer']."</label>\n";
        	$answer_input_fields['answer'] = "<div class=\"styled_checkbox".$disabled."\" style=\"margin-left: 20px\">".$answer_fields['answer_input']."<label for=\"answer\"></label></div>";
        	$replace_fields['answer_text'] .= $templates->gen_html($answer_text_fields, $answer_tmpl['answer_horizontal'], 0);
        	$replace_fields['answer'] .= $templates->gen_html($answer_input_fields, $answer_tmpl['answer_horizontal'], 0);
        	$templates->module_ignore_fields[] = "answers_horizontal";
        }        
    }
    elseif((int)$test_info['answer_type'] == 2){
        $left_text = $answer_info['left_text'];
        $right_text = $answer_info['right_text'];
        $answer_input = input_form("answer_".$i, "textbox", "", "", "width: ".$champ->test_manual_aswer_width($answer_info['answer'])."px", "", "autocomplete=\"off\" ".$disabled);
        if((int)$test_info['draggable'] == 1){
            $x = $answer_info['position_x'];
            $y = $answer_info['position_y'];
            $answer_fields['answer'] = "<span id=\"drag_answer_".$i."\" style=\"position: absolute; left: ".$x."px; top: ".$y."px\">";
            $answer_fields['answer'] .= $left_text.$answer_input.$right_text;
            $answer_fields['answer'] .= "</span>";
        }
        else{
            $answer_fields['answer'] = $left_text.$answer_input.$right_text;
        }
        $replace_fields['answer'] .= $templates->gen_html($answer_fields, $answer_tmpl['answer_manual'], 0);

        $replace_fields['question'] = str_replace("|answer_".$i."|", $answer_fields['answer'], $replace_fields['question'], $replace_count);
        if((int)$replace_count > 0){
            $replace_fields['answer'] = "";
        }
        $templates->module_ignore_fields[] = "answers_manual";
        //$answer_tmpl = $answer_tmpl_manual;
    }
    else{
    	$replace_fields['answer'] .= input_form("answer_".$i, "hidden");
    	$templates->module_ignore_fields[] = "answers_hidden";
    }

    //$templates->gen_loop_html($answer_fields, $answer_tmpl);
}

$finished_test = $champ->finished_test();

$out = $finished_test !== false ? "" : $templates->gen_module_html($replace_fields, "test_conditions");
$out = str_replace("upload/math/", $global_conf['location_logicmeter']."upload/math/", $out);
$out = str_replace("\$\$frac", "\$\$\\frac", $out);
//$champ->set_spent_time();