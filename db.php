<?php
    include_once('config.php');
    date_default_timezone_set("Europe/Riga");
	session_start();

	$db = new E_Rinda($_config['DB_host'], $_config['DB_user'], $_config['DB_password'], $_config['DB_db']);
	$db->set_charset("utf8");

	class E_Rinda extends mysqli {

		public $user = [];

		function __construct($h, $u, $p, $db) {
			parent::__construct($h, $u, $p, $db);
			if (isset($_SESSION["ID"])) {
				$res = $this->query("SELECT `ID`, `Login`, `status` FROM `users` WHERE `ID`=".intval($_SESSION["ID"]));
				if ($res->num_rows) {
					$this->user = $res->fetch_assoc();
				}
			}
		}

		function __destruct() {
			//
		}

		function createQueue($name) {
			// Check login
			$name = $this->real_escape_string($name);
			$this->query("INSERT INTO `queues` (`Name`, `Status`, `StartTime`, `EndTime`, `PurgeTimeout`) VALUES ('$name', 'Open', 0, 0, 3600)");
			if ($this->errno) return ["Status" => "Error", "ID" => $this->errno];
			return ["Status" => "OK", "ID" => $this->insert_id];
		}
		
		function createOptions($name,$value) {
			$name = $this->real_escape_string($name);
			$value = intval($value);			
			$res = $this->query("SELECT * FROM `options` WHERE `name`='$name'");
			if (!$res->num_rows) {
				$this->query("INSERT INTO `options` (`name`, `value`) VALUES ('$name', $value)");
				if ($this->errno) return ["Status" => "Error", "ID" => $this->errno];
				return ["Status" => "OK", "ID" => $value];
			} else {
				$this->query("UPDATE `options` SET `value`=$value WHERE `name`='$name'");
				if ($this->affected_rows && !$this->errno) return ["Status" => "OK", "ID" => $value];
				return ["Status" => "Error", "ID" => $this->errno];
			}
		}

		function getOptions($name) {
			$name = $this->real_escape_string($name);
			$res = $this->query("SELECT * FROM `options` WHERE `name`='$name'");
			if (!$res->num_rows) return false;
			return $res->fetch_assoc();
		}
		
		function getQueueMetadata($QID) {
			if (!intval($QID)) return false;
			$res = $this->query("SELECT * FROM `queues` WHERE `ID`=$QID");
			if (!$res->num_rows) return false;
			return $res->fetch_assoc();
		}

		function getQueue($QID) {
            if (!intval($QID)) return false;
			$res = $this->query("SELECT `ID`, `Name`, `Status`, `Enqueued` AS `T`,
			(
				CASE 
					WHEN Status = 'Called' THEN Called
					ELSE Enqueued + 100000000
				END
			) as orderTime
			FROM `queuers` WHERE `QueueID` = ".intval($QID)." AND `Enqueued` < ".(time()-5)." AND `Dequeued` IS NULL ORDER BY `orderTime` ASC");
			if (!$res->num_rows) return false;
			return $res->fetch_all(MYSQLI_ASSOC);
		}

        function getQueueADM($QID) {
            if (!intval($QID)) return false;
			$timeLimit = ($this->isLoggedIn() && $this->user["status"]) ? 5 : -1;			
            $res = $this->query("SELECT *,
			(
				CASE 
					WHEN Status = 'Waiting' THEN Enqueued + 100000000
					WHEN Status = 'Called' THEN Called
					WHEN Status = 'Handling' THEN Dequeued + 200000000
					WHEN Status = 'Out' THEN Dequeued + 300000000
					WHEN Status = 'Purged' THEN Dequeued + 400000000
					ELSE Dequeued + 500000000
				END
			) as orderTime
			FROM `queuers` WHERE `QueueID` = ".intval($QID)." AND `Enqueued` < ".(time()-$timeLimit)." ORDER BY `orderTime` ASC");
            if (!$res->num_rows) return false;
            return $res->fetch_all(MYSQLI_ASSOC);
        }

        function getAvgTime($QID) {
            if (!intval($QID)) return false;
            $res = $this->query("SELECT ROUND(AVG(`Dequeued` - `Enqueued`)) AS `Time` FROM `queuers` WHERE `QueueID` = $QID AND `Dequeued` IS NOT NULL");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();	
            return $row["Time"];
        }

		function enqueue($QID, $name) {
			$QID = intval($QID);
			if (!$QID) return false;
            if (!$this->hasRights($QID)) return false;
			$name = $this->real_escape_string($name);
			$this->query("INSERT INTO `queuers` (`QueueID`, `Enqueued`, `Name`, `Status`) VALUES ($QID, ".time().", '$name', 'Waiting')");
			if ($this->errno) {
				return $this->errno;
			}
			return true;
		}

		function dequeue($ID) {
			$ID = intval($ID);
            if (!$ID) return false;
            if (!$this->isLoggedIn()) return false;
            // Get queue rights
            $res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID ");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            if (!$this->hasRights($row["QueueID"])) return false;
            // Dequeue
			$this->query("UPDATE `queuers` SET `Dequeued`=".time().", `Status`='Handling' WHERE `ID`=$ID");
            return $this->affected_rows ? true : false;
		}

        function reenqueue($ID) {
            $ID = intval($ID);
            if (!$ID) return false;
            if (!$this->isLoggedIn()) return false;
            $res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID ");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
			
			$resEnqueued = $this->query("SELECT `Enqueued` FROM `queuers` WHERE `QueueID` = ".$row["QueueID"]." AND `Status` = 'Waiting' ORDER BY `Enqueued` ASC LIMIT 2,1");
			if (!$resEnqueued->num_rows) $rowEnqueued['Enqueued'] = time() - 1;
			else $rowEnqueued = $resEnqueued->fetch_assoc(); //Move 3 positions after waiting students
			
            if (!$this->hasRights($row["QueueID"])) return false;
            // Enqueue
            $this->query("UPDATE `queuers` SET `Enqueued` = ".($rowEnqueued['Enqueued']+1).", `Dequeued` = NULL, `Called` = NULL, `Status` = 'Waiting' WHERE `ID` = $ID");
            return $this->affected_rows ? true : false;
        }
		
		function purgeOne($ID) {
            $ID = intval($ID);
            if (!$ID) return false;
            if (!$this->isLoggedIn()) return false;
            $res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID ");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            if (!$this->hasRights($row["QueueID"])) return false;
            // purgeOne
            $res = $this->query("SELECT `PurgeTimeout` FROM `queues` WHERE `ID` = ".$row["QueueID"]);
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            $this->query("UPDATE `queuers` SET `Status` = 'Purged', `Dequeued`= ".time()." WHERE `ID` = $ID AND `Status` = 'Called' AND `Called` < ".(time() - $row["PurgeTimeout"]));
            return $this->affected_rows ? true : false;
        }

		function deleteID($ID) {
            $ID = intval($ID);
            if (!$ID) return false;
            if (!$this->isLoggedIn()) return false;
            $res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID ");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            if (!$this->hasRights($row["QueueID"])) return false;
            // delete
            $this->query("DELETE FROM `queuers` WHERE `ID` = $ID AND `Enqueued` > ".(time() - 5)." AND `Status` = 'Waiting'");
            return $this->affected_rows ? true : false;
		}
		
        function callID($ID) {
            $ID = intval($ID);
            if (!$ID) return false;
            if (!$this->isLoggedIn()) return false;
            $res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID ");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            if (!$this->hasRights($row["QueueID"])) return false;
            // Enqueue
            $this->query("UPDATE `queuers` SET `Status` = 'Called', `Called`=".time()." WHERE `ID` = $ID");
            return $this->affected_rows ? true : false;
        }

        function callNext($QID, $count) {
            $QID = intval($QID);
            $count = abs(intval($count));
            if (!$QID) return false;
            if (!$count) return false;
            if (!$this->hasRights($QID)) return false;
            //$this->purge($QID); do not purge automaticaly after next call
            $this->query("UPDATE `queuers` SET `Status` = 'Called', `Called`=".time()." WHERE `QueueID` = $QID AND `Status` = 'Waiting' ORDER BY `Enqueued` ASC LIMIT $count");
            return $this->affected_rows ? true : false;
        }

        function doneProcessing($ID) {
        	$ID = intval($ID);
        	if (!$ID) return false;
        	if (!$this->isLoggedIn()) return false;
        	$res = $this->query("SELECT `QueueID` FROM `queuers` WHERE `ID` = $ID");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            if (!$this->hasRights($row["QueueID"])) return false;
            $this->query("UPDATE `queuers` SET `Status` = 'Out' WHERE `ID` = $ID");
            return $this->affected_rows ? true : false;
        }

        function hasRights($QID) {
            if (!$this->isLoggedIn()) return false;
            $QID = intval($QID);
            $res = $this->query("SELECT * FROM `registars` WHERE `QueueID`=$QID AND `UserID`=".intval($this->user["ID"]));
            return $res->num_rows == 1 ? true : false;
        }

		function purge($QID) {
            $QID = intval($QID);
            if (!$QID) return false;
            if (!$this->hasRights($QID)) return false;
            $res = $this->query("SELECT `PurgeTimeout` FROM `queues` WHERE `ID` = $QID");
            if (!$res->num_rows) return false;
            $row = $res->fetch_assoc();
            $this->query("UPDATE `queuers` SET `Status` = 'Purged', `Dequeued`= ".time()." WHERE `QueueID` = $QID AND `Status` = 'Called' AND `Called` < ".(time() - $row["PurgeTimeout"]));
            return $this->affected_rows;
        }

		function purgeTimeout($QID) {
            $QID = intval($QID);
            if (!$QID) return 0;  
            $res = $this->query("SELECT `PurgeTimeout` FROM `queues` WHERE `ID` = $QID");
            if (!$res->num_rows) return 0;
            $row = $res->fetch_assoc();
            return $row["PurgeTimeout"];
        }
		
		function login($login, $password) {
			$login = $this->real_escape_string($login);
			$res = $this->query("SELECT * FROM `users` WHERE `Login`='$login'");
			if ($res->num_rows) {
				$row = $res->fetch_assoc();
				if (password_verify($password, $row["Password"])) {
					$_SESSION["ID"] = $row["ID"];
					$this->user["ID"] = $row["ID"];
					$this->user["Login"] = $row["Login"];
					$this->user["status"] = $row["status"];
					return true;
				}
			}
			return false;
		}

		function logout() {
			if (!$this->isLoggedIn()) return false;
			unset($_SESSION);
			unset($this->user);
			return true;
		}

		function register($login, $password, $repassword) {}

		function isLoggedIn() {
			return isset($this->user["ID"]);
		}
    }
?>