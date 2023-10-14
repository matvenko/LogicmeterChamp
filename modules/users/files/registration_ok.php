<?php
$user_class->permission_end($module, 'user');
global $out;

$replace_fields[''] = "";

$out = $templates->gen_module_html($replace_fields, "registration_ok");