<?php
	include "db.php";
	//$pass = password_hash("",  PASSWORD_BCRYPT, ["cost" => 11]);
	//$db->query("INSERT INTO `users` (`Login`, `Password`, `status`) VALUES ('', '$pass', 0)");
	//$db->query("INSERT INTO `registars` (`UserID`, `QueueID`) VALUES (".mysqli_insert_id($db).", 1)");
	echo $db->error;

	$infoMsg = '';
	if (isset($_POST["Act"])) {
        $res = [];
		switch ($_POST["Act"]) {
			case "auth.login":
				if (isset($_POST["Login"]) && isset($_POST["Password"])) {
					$res = $db->login($_POST["Login"], $_POST["Password"]);
					if ($res === true) {
						$infoMsg = $db->user["status"] ? "Kabinetā: ".$db->user["Login"] : "Gaitenī: ".$db->user["Login"];
					} else {
						$infoMsg = "Nepareizi pieslēgšanās dati" ;
					}					
				}
			break;
            case "auth.logout":
                $res = $db->logout();
            break;
		}
	} else if($db->isLoggedIn()) {
		$infoMsg = $db->user["status"] ? "Kabinetā: ".$db->user["Login"] : "Gaitenī: ".$db->user["Login"];
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>E-rinda ADM</title>
        <link rel="stylesheet" type="text/css" href="/styles/reset.css">
        <link rel="stylesheet" type="text/css" href="/styles/styles.adm.css">		
		<script language="JavaScript">
			var logged = <?php echo $db->isLoggedIn() ? 1 : 0 ; ?>;
			var serverTime = <?php echo time(); ?>;
			setInterval(function(){serverTime++;},1000);
			var purgeTimeout = <?php echo $db->purgeTimeout(1); ?>;
			var userStatus = <?php echo ($db->isLoggedIn() && $db->user["status"]) ? 1 : 0; ?>;
			var _break = <?php $res = $db->getOptions("Break"); echo $res === false ? 0 : $res["value"]; ?>;
		</script>
        <script language="JavaScript" src="/scripts/jquery-2.1.4.min.js"></script>
        <script language="JavaScript" src="/scripts/admin.panel.scripts.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class="header"></div>
        <div class="column left">
			<?php
				if (!empty($infoMsg)) {
					echo '<div class="infoMsg">'.$infoMsg.'</div>';
				}
			?>
            <?php if($db->isLoggedIn()) { 
					if(!$db->user["status"]) { 
			?><div class="block">
                <header>Pievienot studentu</header>
                <form id="formEnqueue">
                    <input type="hidden" name="Act" value="queue.enqueue">
                    <!--input type="text" name="QID" placeholder="Queue ID"-->
                    <input type="hidden" name="QID" value="1">
                    <input type="text" name="Name" placeholder="Vārds un uzvārds">
                    <input type="submit" value="Ok">
                </form>
            </div>
			<?php 	} else { ?>
            <div class="block">
                <header>Izsaukt studentus</header>
                <form id="formCall">
                    <input type="hidden" name="Act" value="queue.callnext">
                    <input type="hidden" name="QID" value="1">					
					<select name="Count">
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
					</select>					
                    <input type="submit" value="Ok">
                </form>
            </div>
			
			<div class="block" id="breakBlock">
                <header class="paddingTop">Pievienot pārtraukumu</header>
                <form id="formBreak">
                    <input type="hidden" name="Act" value="queue.break">
                    <input type="hidden" name="QID" value="1">					
					<select name="Minutes">						
						<option value="10">10 min</option>
						<option value="20">20 min</option>
						<option value="30">30 min</option>
						<option value="40">40 min</option>
						<option value="50">50 min</option>
						<option value="60">60 min</option>
						<option value="70">70 min</option>
						<option value="80">80 min</option>
						<option value="90">90 min</option>
					</select>					
                    <input type="submit" value="Ok">
                </form>
            </div>
			<div class="block hidden" id="unbreakBlock">
                <header class="paddingTop">Pārtraukt pārtraukumu</header>
                <form id="formUnbreak">
                    <input type="hidden" name="Act" value="queue.unbreak">
                    <input type="hidden" name="QID" value="1">
                    <input type="submit" value="Ok">
                </form>
            </div>
			
			<?php 		} ?>
			
			<div class="block hidden" id="breakTimeBox">
                <header class="paddingTop">Pārtraukums</header>
                <div class="infoMsg" id="breakTime"></div>
            </div>			
			
			<div class="block">                
                <form method="post" action="#" class="paddingTop paddingBottom">
                    <input type="hidden" name="Act" value="auth.logout">
                    <input type="submit" value="Izlogoties">
                </form>
            </div>
			<?php }else{ ?>
            <div class="block">
                <form method="post" action="#" class="paddingTop paddingBottom">
                    <input type="hidden" name="Act" value="auth.login">
                    <input type="text" name="Login" placeholder="Lietotājvārds">
                    <input type="password" name="Password" placeholder="Parole">
                    <input type="submit" value="Pieslēgties">
                </form>
            </div>
            <?php } ?>
        </div>
        <div class="column right">
        	<div id="queuerList" data-qid="1"></div>
			
			
			
        	<div class="queuerList paddingTop">
				<table>
					<tr><th>ID</th><th>Vārds un uzvārds</th><th>Pieteicās</th><th>Izsauca</th><th>Noslēdzās</th><th>Darbības</th></tr>
					<tr class="waiting"><td>1</td><td>Pieteicies</td><td></td><td></td><td></td><td></td></tr>	
					<tr class="called"><td>2</td><td>Izsaukts</td><td></td><td></td><td></td><td></td></tr>
					<tr class="dequeued"><td>3</td><td>Dokumenti parakstīti</td><td></td><td></td><td></td><td></td></tr>
					<tr class="out"><td>4</td><td>Viss OK</td><td></td><td></td><td></td><td></td></tr>
					<tr class="purged"><td>5</td><td>Izmests</td><td></td><td></td><td></td><td></td></tr>					
				</table>
			</div>
        
			
			
        </div>
    </body>
</html>
