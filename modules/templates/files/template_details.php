<?php
$user_class->permission_end($module, 'admin');
global $html_out;

$replace_fields['module'] = $module;
$replace_fields['add'] = icon('add');
$replace_fields['tmpl_id'] = get_int('tmpl_id');

//**** page list *******
$pages_tmpl = $templates->split_template("pages_list", "template_details");
$result = $query->select_sql("templates_sources", "DISTINCT page_type", "template_id = ".get_int('tmpl_id'), "page_type ASC");
$page_types = select_items("templates_page_types", "id", "page_type");

while($row = $query->assoc($result)){
	$pages_fields['page_type'] = $page_types[$row['page_type']];
	$pages_fields['page_type_id'] = $row['page_type'];
	$pages_fields['module'] = $module;
	
	$templates->gen_loop_html($pages_fields, $pages_tmpl);
}

//**** page details *******
$page_type = get('page_type') == false ? 1 : get('page_type');
$page_lang = get('page_lang') == false ? $lang : get('page_lang');
$page_details = $query->select_ar_sql("templates_sources", "*", "page_type = '".$page_type."'");
$replace_fields['lang'] = $page_lang;
$replace_fields['template_file'] = $page_details['template_file'];
$replace_fields['last_cache_time'] = $page_details['last_cache_time'];
$replace_fields['tmpl_page_id'] = $page_details['id'];


$out .= $templates->gen_module_html($replace_fields, "template_details");
$html_out['module'] = $out;