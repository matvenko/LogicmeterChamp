<?php

class champ {

    var $config = array();
    var $spent_time = 0;
    var $point = 0;
    
    function __construct () {
        $this->config = select_items("championship_config", "name", "value");
    }
    
    function is_participant(){
    	global $query, $user_class;
    	$champ_child_info = $this->champ_child_info($user_class->current_child_id);
    	
    	return (int)$champ_child_info['id'] == 0 ? false : true;
    }
    
    function is_participant_end(){
    	global $module;
    	
    	if($this->is_participant() == false){
    		header("Location: index.php?module=".$module."&page=check");
    		exit;
    	}
    }
    
    function check_payment(){
    	global $query_l, $user_class, $module;
    	 
    	$payment_module = "math";
    	 
    	if(!$user_class->login_action()){
    		return true;
    	}
    	elseif((int)$user_class->current_child_id !== 0){
    		$child_info = $this->child_info($user_class->current_child_id);
    
    		return $child_info['paid_to'] >= $this->config['start_date'] && (int)$child_info[$payment_module] == 1 ? true : false;
    	}
    	else{
    		return $query_l->record_exist("math_children", "user_id = ".(int)$user_class->current_user_id." AND `".$payment_module."` = 1 AND disabled = 0 AND paid_to >= '".$this->config['start_date']."'");
    	}
    }
    
    function champ_child_info($child_id, $type = ""){
    	global $query, $user_class;

    	$where = $type === "all" ? "" : " AND `status` = 1";
	    $query->where_vars['champ_date'] = $this->config['champ_date'];
    	$child_info = $query->select_ar_sql("championship_children", "*", "`date` = '{{champ_date}}' AND child_id != 0 AND child_id = ".(int)$child_id.$where);
    	
    	if((int)$child_info['id'] !== 0){
    		return $child_info;
    	}
    	else{
    		return false;
    	}
    }
    
    function child_info($child_id, $type = "user"){
    	global $query_l, $user_class;
    	 
    	$where = $type == "admin" ? "" : " AND user_id = ".(int)$user_class->current_user_id;
    	$child_info = $query_l->select_ar_sql("math_children", "*", "id = ".(int)$child_id.$where);
    	$child_info['image_src'] = $child_info['image'];
    	$child_image = "upload/users/thumb/".$child_info['image'];
    	$child_info['image'] = is_file($child_image) ? $child_image : "images/user_no_image.jpg";
    	 
    	if((int)$child_info['id'] !== 0){
    		return $child_info;
    	}
    	else{
    		return false;
    	}
    }

    function test_info ($test_id, $type = "full") {
        global $query_l;
        $test_info1 = $query_l->select_ar_sql("math_tests", "*", "id = ".(int)$test_id." AND del = 0");
        if((int)$test_info1['id'] == 0){
        	return false;
        }
        
    	if($type == "full"){
        	$test_info2 = $query_l->select_ar_sql("math_tests_texts", "*", "test_id = " . (int)$test_id);
        	$result = array_merge((array)$test_info1, (array)$test_info2);
        }
        else{
        	$result = $test_info1;
        }
        
        return $result;
    }

    function test_answers ($test_id, $test_info = "", $true_answer = 0) {
        global $query_l;
        
        $test_info = !is_array($test_info) ? $this->test_info($test_id) : $test_info;
        
        $where = (int)$true_answer == 1 && (int)$test_info['answer_type'] == 1 ? " AND true_answer = 1" : "";
        $result = $query_l->select_sql("math_tests_answers", "*", "del = 0 AND test_id = ".(int)$test_id.$where, "priority ASC");
        
        while($row = $query_l->assoc($result)){
            $i = $row['priority'];
            $answer_fields['answer_n'] = $i;
            if((int)$test_info['answer_type'] == 1){
                $answers[$i]['answer'] = $row['answer_cache'];
                $answers[$i]['true_answer'] = (int)$row['true_answer'];
            }
            else{
                $answers[$i]['left_text'] = $row['left_text'];
                $answers[$i]['right_text'] = $row['right_text'];
                $answers[$i]['position_x'] = $row['position_x'];
                $answers[$i]['position_y'] = $row['position_y'];
                $answers[$i]['answer'] = $row['answer_cache'];
            }
        }
        return (array)$answers;
    }
    
    function test_manual_aswer_width($answer){
        $string_length = mb_strlen($answer, 'utf8');
        $string_length = (int)$string_length == 0 ? 1 : $string_length;
        $length[1] = 20;
        $length[2] = 15;
        return $string_length <= 2 ? $string_length * $length[$string_length] : $string_length * 12;
    }
    
    function gen_answer_type ($answer_type, $answer_n, $edit_value = "", $new_answer = 0) {
        global $templates, $module;
        
        if($new_answer == 0){
            $result_head = "<div id=\"answer_block_" . $answer_n . "\" style=\"clear: both\">\n";
            $result_footer = "</div>\n";
        }
        $answer_types = array(
                1 => 'test',
                2 => 'manual'
        );
        $answer_tmpl = $templates->split_template($answer_types[$answer_type], "answer_types");
        
        $replace_fields['_ANSWER'] = _ANSWER;
        
        settype($edit_value, "array");
        $edit_value['answer_' . $answer_n] = $edit_value['answer'];
        $edit_value['true_answer_' . $answer_n] = $edit_value['true_answer'];
        $edit_value['answer_left_' . $answer_n] = $edit_value['left_text'];
        $edit_value['answer_right_' . $answer_n] = $edit_value['right_text'];
        
        if($answer_types[$answer_type] == "test"){
            $replace_fields['answer'] = input_form("answer_" . $answer_n, "textarea", $edit_value, "", "width: 300px; height: 60px", "test_field");
            $replace_fields['true_answer'] = input_form("true_answer_" . $answer_n, "checkbox", $edit_value, "", "", "test_field");
            $replace_fields['answer_n'] = $answer_n;
            $replace_fields['_TRUE_ANSWER'] = _TRUE_ANSWER;
        }elseif($answer_types[$answer_type] == "manual"){
            $replace_fields['answer_n'] = $answer_n;
            $replace_fields['answer'] = input_form("answer_" . $answer_n, "textbox", $edit_value, "", "width: 40px", "test_field");
            $replace_fields['_LEFT_TEXT'] = _LEFT_TEXT;
            $replace_fields['_RIGHT_TEXT'] = _RIGHT_TEXT;
            $replace_fields['answer_left'] = input_form("answer_left_" . $answer_n, "textbox", $edit_value, "", "width: 90px", "test_field");
            $replace_fields['answer_right'] = input_form("answer_right_" . $answer_n, "textbox", $edit_value, "", "width: 90px", "test_field");
        }
        
        $result = $templates->gen_html($replace_fields, $answer_tmpl[$answer_types[$answer_type]], 0);
        
        return $result_head . $result . $result_footer;
    }
    
    function test_id_encrypt($id){
        global $global_conf;
        return encrypt($id, $global_conf['private_key']);
    }
    
    function test_id_decrypt($id){
        global $global_conf;
        return decrypt($id, $global_conf['private_key']);
    }

    function get_random_test_ids(){
    	global $query, $query_l, $user_class;
    	
    	if(!is_array($_SESSION['champ']['skills'])){
    		$champ_child_info = $this->champ_child_info($user_class->current_child_id);

    		$champ_skill = $query->select_ar_sql("championship_skills", "parent_skill_id", "grade = ".(int)$champ_child_info['grade']." AND champ_date = '".$this->config['start_date']."'");
    		
    		$complete_skills = select_items("championship_children_tests", "skill_id", "skill_id", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");
    		
    		$result_skills = $query_l->select_sql("math_grade_skills", "id", "parent_id = ".(int)$champ_skill['parent_skill_id'], "priority ASC");
    		while($row_skills = $query_l->assoc($result_skills)){
    			if(!in_array($row_skills['id'], $complete_skills)){
    				$skills[] = $row_skills['id'];
    			}
    		}
    		$_SESSION['champ']['skills'] = $skills;
    	}
    	
    	if(count($_SESSION['champ']['skills']) > 0){
    		reset($_SESSION['champ']['skills']);
    		$current_skill_id = current($_SESSION['champ']['skills']);
    	}
    	if((int)$_SESSION['champ']['tests'][$current_skill_id] == 0){
    		$test_id = $query_l->select_ar_sql("math_tests", "id", "del = 0 AND skill_id = ".(int)$current_skill_id." ORDER BY RAND() LIMIT 0, 1");
    		$_SESSION['champ']['tests'][$current_skill_id] = $test_id['id'];
    	}
    	

    	return (int)$_SESSION['champ']['tests'][$current_skill_id];
    }
    
    function set_spent_time(){
    	global $query, $user_class;
    	
    	$time = time();
    	if((int)$_SESSION['champ']['test_start_time'] == 0){
    		$test_start_time = $query->select_ar_sql("championship_children_stage_points", "last_answer_time", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");
    		if((int)$test_start_time['last_answer_time'] == 0){
    			$start_time = $time;    			
    		}
    		else{
    			$start_time = $time - (int)$test_start_time['last_answer_time'] > $this->config['test_max_spent_time'] ? $time - $this->config['test_max_spent_time'] : $test_start_time['last_answer_time'];
    		}
    		$_SESSION['champ']['test_start_time'] = $start_time;
    	}
    	else{
    		$start_time = $_SESSION['champ']['test_start_time'];
    		$_SESSION['champ']['test_start_time'] = $time;
    	}
    	
    	$spent_time = $time - (int)$start_time;
    	$spent_time = $spent_time < 0 ? 0 : $spent_time;
    	$spent_time = $spent_time > $this->config['test_max_spent_time'] ? $this->config['test_max_spent_time'] : $spent_time;    	 
    	
    	$this->spent_time = $spent_time;
    }
    
    function set_user_smart_point($grade_id, $skill_id, $true_answer, $level){
    	global $query, $user_class;
    	
    	$where = "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'";
    	$user_point = $query->select_ar_sql("championship_children_stage_points", "*", $where);
    	 
    	//*** if skill ended
    	if($user_point['point'] == 100){
    		$fields['point'] = 100;
    		return $fields;
    	}
    	 
    	if((int)$true_answer == 1){
    		$increase_point = $level;
    		$point = $user_point['point'] + $increase_point;
    		if($point > 100){
    			$point = 100;
    		}
    	}
    	else{
    		$increase_point = 0;
    		$point = $user_point['point'];
    	}
    	 
    	$this->point = $point;
    	$fields['point'] = $point;
    	$fields['tests_amount'] = (int)$user_point['tests_amount'] + 1;
    	$fields['true_answers'] = (int)$user_point['true_answers'] + $true_answer;
    	$fields['duration'] = (int)$user_point['duration'] + $this->spent_time;
    	$fields['last_answer_time'] = time();

   		if((int)$user_point['tests_amount'] == 0){
    		$fields['child_id'] = $user_class->current_child_id;
    		$fields['champ_date'] = $this->config['start_date'];
    		$fields['start_time'] = current_time();
    		
    		$query->insert_sql("championship_children_stage_points", $fields);
    	}
    	else{
    		$query->update_sql("championship_children_stage_points", $fields, $where);
    	}
    	
    	$fields['answer_point'] = $increase_point;
    	$fields['true_answer'] = $true_answer;
    
    	return $fields;
    }
    
	function get_user_smart_point(){
    	global $query, $user_class;
    	
        $info = $query->select_ar_sql("championship_children_stage_points", "point, tests_amount, true_answers", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");
    	
    	return $info;
    }
    
    function insert_user_test($grade_id, $skill_id, $test_id, $point_info, $user_answers, $container = ""){
    	global $query, $user_class;
    	if((int)$user_class->current_child_id !== 0){
    		$fields['champ_date'] = $this->config['start_date'];
    		$fields['user_id'] = $user_class->current_user_id;
    		$fields['child_id'] = $user_class->current_child_id;
    		$fields['grade_id'] = $grade_id;
    		$fields['skill_id'] = $skill_id;
    		$fields['test_id'] = $test_id;
    		$fields['answer_point'] = $point_info['answer_point'];
    		$fields['true_answer'] = $point_info['true_answer'];
    		$fields['spent_time'] = $this->spent_time;
    		$fields['answer_add_time'] = current_time();    		
    			
    		$user_test_id  = $query->insert_sql("championship_children_tests", $fields);

		    $fields_answer['container'] = $container == "" ? post('container') : $container; //**** drag&drop container
    		foreach($user_answers as $answer_n => $answer){
				if($answer_n > 1){
					unset($fields_answer['container']);
				}
    			$fields_answer['child_test_id'] = $user_test_id;
    			$fields_answer['user_id'] = $user_class->current_user_id;
    			$fields_answer['test_id'] = $test_id;
    			$fields_answer['answer_n'] = $answer_n;
    			$fields_answer['answer'] = substr(strip_tags($answer), 0, 100);
    
    			$query->insert_sql("championship_children_test_answers", $fields_answer);
    		}
    	}
    }
    
    function set_finished_test_status(){
    	global $query, $query_l, $user_class, $templates;
    	
    	$stage_info = $query->select_ar_sql("championship_children_stage_points", "*", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");
    	$this->set_spent_time();
    	
    	if((int)$stage_info['finished'] == 0 && ((int)$stage_info['tests_amount'] >= (int)$this->config['tests_amount'] || 
    			(int)$stage_info['duration'] + $this->spent_time >= (int)$this->config['step_1_test_duration'] * 60)){
    		$share_code = md5($user_class->current_child_id.$stage_info['last_answer_time']);
    		$query->update_sql("championship_children_stage_points", array("finished" => 1, "share_code" => $share_code, "end_time" => current_time()), "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");

    		//***** send mail
    		$template_name = $stage_info['point'] >= $this->config['step_1_point_limit'] ? "RESULTS_FOR_WINNER" : "RESULTS_FOR_LOOSER";
    		$mail_template = $query->select_ar_sql("mail_templates", "*", "name = '".$template_name."'");
    		
    		$replace_fields['answer_tests_amount'] = $stage_info['tests_amount'];
    		$replace_fields['true_answers_amount'] = $stage_info['true_answers'];
    		$replace_fields['wrong_tests_amount'] = $stage_info['tests_amount'] - $stage_info['true_answers'];
    		$replace_fields['final_point'] = $stage_info['point'];
    		
    		$send_mail_text = $templates->gen_html($replace_fields, $mail_template['tamplate'], 0);
    		$user_info = $query_l->select_ar_sql("users_info", "*", "id = ".(int)$user_class->current_user_id."");
    		//send_mail($user_info['mail'], $mail_template['title'], email_formated_text($send_mail_text));
    	}
    	elseif((int)$stage_info['finished'] !== 1){
    		echo javascript_echo("reload()");
    	}
    }

	function champ_school_info($school_id){
		global $query;

		$query->where_vars['champ_date'] = $this->config['champ_date'];
		$champ_school_info = $query->select_ar_sql("championship_schools", "*", "del = 0 AND champ_date = '{{champ_date}}' AND school_id = ".(int)$school_id);

		return (int)$champ_school_info['id'] == 0 ? false : $champ_school_info;
	}

	function user_champ_school_id($user_id = 0){
		global $query, $user_class;

		$user_id = $user_id == 0 ? $user_class->current_user_id : $user_id;

		$query->where_vars['champ_date'] = $this->config['champ_date'];
		$champ_school_info = $query->select_ar_sql("championship_schools", "school_id", "del = 0 AND champ_date = '{{champ_date}}' AND user_id = ".(int)$user_id);

		return (int)$champ_school_info['school_id'] == 0 ? false : $champ_school_info['school_id'];
	}
    
    function finished_test(){
    	global $query, $user_class, $templates, $global_conf, $module;
    	$stage_info = $query->select_ar_sql("championship_children_stage_points", "*", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$this->config['start_date']."'");
    
    	if((int)$stage_info['finished'] == 1){
    		$replace_fields['_TEST_IS_FINISHED'] = _TEST_IS_FINISHED;
    		$replace_fields['_YOU_ANSWERED'] = _YOU_ANSWERED;
    		$replace_fields['_TEST_S'] = _TEST_S;
    		$replace_fields['_BETWEEN_THEM'] = _BETWEEN_THEM;
    		$replace_fields['_WAS_TRUE'] = _WAS_TRUE;
    		$replace_fields['_AND2'] = _AND2;
    		$replace_fields['_WAS_WRONG'] = _WAS_WRONG;
    		$replace_fields['_FINAL_AMOUNT'] = _FINAL_AMOUNT;
    		$replace_fields['_POINT'] = _POINT;
    		$replace_fields['_YOU_GAIN_SECOND_STAGE'] = _YOU_GAIN_SECOND_STAGE;
    		$replace_fields['_S'] = _S;
    
    		$replace_fields['answer_tests_amount'] = $stage_info['tests_amount'];
    		$replace_fields['true_answers_amount'] = $stage_info['true_answers'];
    		$replace_fields['wrong_tests_amount'] = $stage_info['tests_amount'] - $stage_info['true_answers'];
    		$replace_fields['final_point'] = $stage_info['point'];
    		
    		if((int)$stage_info['point'] >= $this->config['step_1_point_limit']){
    			$templates->module_ignore_fields[] = "next_stage";
    			$replace_fields['_YOU_GAIN_SECOND_STAGE'] = _YOU_GAIN_SECOND_STAGE;
    			$replace_fields['result_class'] = "winner";
    			
    			//**** share
    			$replace_fields['share_url'] = ($global_conf['location']."body.php?module=".$module."&page=fb_share&sc=".rawurlencode($stage_info['share_code']));
    			$replace_fields['share_title'] = _WEB_TITLE;
    		}
    		else{
    			$replace_fields['_YOU_GAIN_SECOND_STAGE'] = _YOU_DONT_GAIN_SECOND_STAGE;
    			$replace_fields['result_class'] = "not_winner";
    		}
    
    		return $templates->gen_module_html($replace_fields, "finished_test");
    	}
    	else{
    		return false;
    	}
    }

	//*** for schools
	function champ_children(){
		global $query_ch, $user_class;

		$result = $query_ch->select_sql("championship_children", "child_id", "user_id = ".(int)$user_class->current_user_id." AND status = 1");
		$children = array();
		while($row = $query_ch->assoc($result)){
			$children[$row['child_id']] = $row['child_id'];
		}
		return $children;
	}

	function child_tests($child_id){
		global $query_ch, $user_class;

		$result_user_tests = $query_ch->select_sql("championship_children_tests", "id, test_id", "champ_date = '".$this->config['start_date']."' AND user_id = ".(int)$user_class->current_user_id." AND child_id = ".(int)$child_id);
		$user_tests = array();
		while($row_user_tests = $query_ch->assoc($result_user_tests)){
			$user_tests[$row_user_tests['id']] = $row_user_tests['test_id'];
		}
		return $user_tests;
	}

	function user_answers ($test_id, $child_test_id, $test_info) {
		global $query, $query_ch, $math;
		$test_info = !is_array($test_info) ? $math->test_info($test_id) : $test_info;
		$result = $query_ch->select_sql("championship_children_test_answers", "*", "child_test_id = ".(int)$child_test_id, "answer_n ASC");

		while($row = $query_ch->assoc($result)){
			if((int)$test_info['answer_type'] == 1){
				$answer_info = $query->select_ar_sql("math_tests_answers", "answer_cache", "del = 0 AND test_id = ".(int)$test_id." AND priority = ".(int)$row['answer_n']);
				$user_answers[$row['answer_n']] = $answer_info['answer_cache'];
			}
			else{
				$user_answers[$row['answer_n']] = $row['answer'];
			}
		}
		return (array)$user_answers;
	}
}