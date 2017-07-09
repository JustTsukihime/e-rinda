<?php
	include "db.php";
	$QID = 1;
	$data = $db->getQueueMetadata($QID);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $data["Name"]; ?> :: E-rinda</title>
	<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="/styles/reset.css">
	<link rel="stylesheet" type="text/css" href="/styles/styles.css">
	<script type="text/javascript" src="/scripts/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="/scripts/main.js"></script>
</head>
<body>
	<header><?php echo $data["Name"]; ?></header>
	<div id="data" data-id="<?php echo $QID; ?>" data-avgWait="<?php echo 1/*$db->getAvgTime($QID)*/; ?>" data-loadTime="<?php echo time(); ?>">
	<?php
		$queuers = $db->getQueue($QID);
		if ($queuers !== false) {
            $i=0;
			foreach ($queuers as $queuer) {
                $i++;
				echo "<div data-id=\"{$queuer["ID"]}\"".($queuer["Status"] == "Called" ? "class=\"next\"" : "")."><span>$i.</span><span>{$queuer["Name"]}</span></div>";
			}
		}
	?>
	</div>
    <footer>
        <div class="updateInterval">Informācija atjaunojas automātiski</div>
    </footer>
</body>
</html>