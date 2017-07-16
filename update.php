<?php
	if (!isset($_POST["QID"]) || !intval($_POST["QID"])) {
		die("Invalid request");
	}
	
	header("Content-Type: application/json; charset=utf-8");
	include "db.php";
    $resp = [];
    $QID = intval($_POST["QID"]);
    $resp["Queue"] = $db->getQueue($QID);
    $resp["AvgTime"] = $db->getAvgTime($QID);
    $resp["Time"] = time();
	$res = $db->getOptions("Break"); $resp["Break"] = $res === false ? 0 : $res["value"];	
	echo json_encode($resp);
?>