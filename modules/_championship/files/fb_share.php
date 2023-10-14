<?php
global $out;

if(strpos($_SERVER['HTTP_REFERER'], "facebook")){
	header("Location: index.php");
	exit;
}

if(get('sc') == false) exit;

$query->where_vars['share_code'] = get('sc');
$stage_info = $query->select_ar_sql("championship_children_stage_points", "*", "share_code = '{{share_code}}'");
$child_info = $child_info = $query_l->select_ar_sql("math_children", "*", "id = ".(int)$stage_info['child_id']);

$replace_fields['url'] = $global_conf['location']."body.php?module=".$module."&page=fb_share&sc=".get('sc');
$replace_fields['_WEB_TITLE'] = _WEB_TITLE;
$replace_fields['child_name'] = $child_info['name'];
$replace_fields['final_point'] = $stage_info['point'];

$out = $templates->gen_module_html($replace_fields, "fb_share");