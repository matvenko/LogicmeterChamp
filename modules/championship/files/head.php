<?php
global $out, $tmpl;

$child_info = $champ->child_info($user_class->current_child_id);
$replace_fields['child_name'] = $child_info['name']." ".$child_info['surname'];

$champ_info = $champ->champ_child_info($user_class->current_child_id, "all");
$replace_fields['class'] = $champ_info['grade'];

$replace_fields['_LOGOUT'] = _LOGOUT;
$replace_fields['_E'] = _E;
$replace_fields['_CLASS'] = _CLASS;

if((int)$champ_info['status'] == 1){
	$templates->module_ignore_fields[] = "head_line";
}

$out .= $templates->gen_module_html($replace_fields, "head");