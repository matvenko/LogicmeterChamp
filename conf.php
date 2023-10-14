<?php
$sql_host = "localhost";
$sql_user = "logicmeter_user";
$sql_pass = "yL8coANDaJRn3Vh2";
$sql_db = "logicmeter_champ";
$sql_db_l = "logicmeter";

//**** sessions
$global_conf['session_db'] = "sessions";

$global_conf['private_key'] = '5YU58z3v4waEUhmU';

$global_conf['editor_upload_directory'] = "/upload/editor";
$global_conf['literacy_upload_directory'] = "/upload/literacy";

$global_conf['table_pref'] = 'gt_';

$global_conf['location'] = 'https://champ.logicmeter.com/';
$global_conf['location_logicmeter'] = 'https://logicmeter.com/';
$global_conf['lang_ar'] = array('geo', 'eng');
$global_conf['logicmeter_src'] = "../main/";

$global_conf['debug_ip'] = array("::1", "127.0.0.1", "188.129.208.61", "85.114.249.110", "212.72.159.74");

define(DOC_ROOT, "");
define(MANAGE_DIR, "");

$global_conf['default_lang'] = 'geo';

$global_conf['admin_email'] = "admin@logicmeter.com";

$global_conf['contac_email'] = "info@logicmeter.com";

$send_mail['sendgrid_key'] = "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";
$send_mail['host'] = "ssl://localhost";
$send_mail['port'] = "465";
$send_mail['from_name'] = "Logicmeter";
$send_mail['username'] = "info@logicmeter.com";
$send_mail['password'] = "avarjishegoneba55aaaa";
$send_mail['from'] = "Logicmeter <info@logicmeter.com>"
?>