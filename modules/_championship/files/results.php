<?php
global $out;
if(!$user_class->group_perm("math", "all")){
	header("location: index.php");
	exit;
}

$out .= "<div style=\"width: 100px; display: inline-block\">Test ID</div>";
$out .= "<div style=\"width: 50px; display: inline-block\">True</div>";
$out .= "<div style=\"width: 70px; display: inline-block\">Answer N</div>";
$out .= "<div style=\"width: 100px; display: inline-block\">Answer</div>";
$out .= "<div style=\"clear: both\"></div>";

$result = $query->select_sql("championship_children_tests", "*", "child_id = ".(int)$user_class->current_child_id, "id ASC");

while($row = $query->assoc($result)){
	$answer_info = $query->select_ar_sql("championship_children_test_answers", "*", "child_test_id = ".(int)$row['id']);
	$out .= "<div style=\"width: 100px; display: inline-block\">".$row['test_id']."</div>";
	$out .= "<div style=\"width: 50px; display: inline-block\">".$row['true_answer']."</div>";
	$out .= "<div style=\"width: 70px; display: inline-block\">".$answer_info['answer_n']."</div>";
	$out .= "<div style=\"width: 100px; display: inline-block\">".$answer_info['answer']."</div>";
	$out .= "<div style=\"clear: both\"></div>";
}