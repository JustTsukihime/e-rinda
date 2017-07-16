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
	<link rel="stylesheet" type="text/css" href="/styles/reset.css">
	<link rel="stylesheet" type="text/css" href="/styles/styles.proj.css">
	<script language="JavaScript">
		var serverTime = <?php echo time(); ?>;
			setInterval(function(){serverTime++;},1000);
		var _break = <?php $res = $db->getOptions("Break"); echo $res === false ? 0 : $res["value"]; ?>;
	</script>
	<script type="text/javascript" src="/scripts/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="/scripts/proj.scripts.js"></script>
</head>
<body>
	<!--header><div><?php echo $data["Name"]; ?></div></header-->
	<div class="content">
        <div class="queuers">
            <div class="infoTitle">Rindā esošie cilvēki</div>
            <div id="data" data-id="<?php echo $QID; ?>" data-avgWait="<?php echo $db->getAvgTime($QID); ?>" data-loadTime="<?php echo time(); ?>"></div>
            <div class="newQueuer"></div>
        </div><div class="info">
                <div class="infoTitle">&nbsp;</div>
                <img src="/images/sejiens_2016.png" style="width: 100%"/>
	    </div>
	</div>
</body>
</html>
