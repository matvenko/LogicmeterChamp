<?php
global $out;

//***** check user authorisation
if($user_class->login_action() == false){
	//$module = "users";
	//include("language/geo/".$module.".php");
	include("modules/".$module."/files/login.php");

}

/* elseif(!$user_class->group_perm("math", "all")){
	header("location: index.php");
	exit;
} */

//***** check child id
elseif((int)$user_class->current_child_id == 0){
	$module = "users";
	include("language/geo/".$module.".php");
	include("modules/".$module."/files/choose_profile_main.php");

}

//***** check payment
/*
elseif($champ->check_payment() == false){
	$replace_fields['_NOT_PAID'] = _NOT_PAID;
	$replace_fields['session_id'] = session_id();
	$replace_fields['location_logicmeter'] = $global_conf['location_logicmeter'];
	$not_paid_tmpl = $templates->split_template("not_paid", "check");
	
	$out = $templates->gen_html($replace_fields, $not_paid_tmpl['not_paid'], 0);	
}
*/
//***** check child registration info
elseif($champ->is_participant() == false){
	include("modules/".$module."/files/registration.php");
}

else{
	header("Location: index.php?module=".$module);
	exit;
}