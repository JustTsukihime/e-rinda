<?php
	header("Content-Type: application/json; charset=utf-8");
	$resp = [];
	$resp["Time"] = time();
	$resp["Status"] = "OK";

	if (!isset($_POST["Act"])) {
		$resp["Status"] = "Fail";
		$resp["Error"] = "Invalid request";
		die(json_encode($resp));
	}
	
	include "db.php";
	//	$QID = intval($_POST["QID"]);

	switch ($_POST["Act"]) {
		case "queue.add":
			if (isset($_POST["Name"]) && strlen($_POST["Name"]) > 2) {
				$res = $db->createQueue($_POST["Name"]);
				//echo $res["Status"]." ".$res["ID"];
				if ($res["Status"] == "Error") {
					$resp["Error"] = $res["ID"];
				} else {
					$resp["ID"] = $res["ID"];
				}
			} else {
				$resp["Error"] = "Name not supplied";
			}
		break;
		case "queue.get":
			if (isset($_POST["QID"]) && intval($_POST["QID"])) {
				$res = $db->getQueueADM($_POST["QID"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				} else {
					$resp["Queue"] = $res;
				}
			} else {
				$resp["Error"] = "Queue ID not supplied";
			}
		break;
		case "queue.enqueue":
			if (isset($_POST["QID"]) && intval($_POST["QID"]) && isset($_POST["Name"]) && strlen($_POST["Name"]) > 2) {
				$res = $db->enqueue($_POST["QID"], $_POST["Name"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "queue.dequeue":
			if (isset($_POST["ID"]) && intval($_POST["ID"])) {
				$res = $db->dequeue($_POST["ID"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "queue.reenqueue":
			if (isset($_POST["ID"]) && intval($_POST["ID"])) {
				$res = $db->reenqueue($_POST["ID"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "queue.callid":
			if (isset($_POST["ID"]) && intval($_POST["ID"])) {
				$res = $db->callID($_POST["ID"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "queue.callnext":
			if (isset($_POST["QID"]) && intval($_POST["QID"]) && isset($_POST["Count"]) && intval($_POST["Count"])) {
				$res = $db->callNext($_POST["QID"], $_POST["Count"]);
				if ($res === false) {
					$resp["Error"] = $res;	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "auth.login":
			if (isset($_POST["Login"]) && isset($_POST["Password"])) {
				$res = $db->login($_POST["Login"], $_POST["Password"]);
				if ($res === false) {
					$resp["Error"] = "Wrong login/password";	
				}
			} else {
				$resp["Error"] = "Invalid data";
			}
		break;
		case "auth.logout":
			$res = $db->logout();
		break;
		default:
			$resp["Status"] = "Fail";
			$resp["Error"] = "Invalid request";
		break;
	}

	if (isset($resp["Error"])) {
		$resp["Status"] = "Fail";
	}
	/*
	$resp = [];
	$QID = intval($_POST["QID"]);
	$resp["Queue"] = $db->getQueue($QID);
	$resp["AvgTime"] = $db->getAvgTime($QID);
	$resp["Time"] = time();
	*/
	echo json_encode($resp);	
?>