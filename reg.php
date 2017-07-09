<?php
	include "db.php";
	//$pass = password_hash("",  PASSWORD_BCRYPT, ["cost" => 11]);
	//$db->query("INSERT INTO `users` (`Login`, `Password`) VALUES ('Dencel', '$pass')");
	echo $db->error;

	if (isset($_POST["Act"])) {
        $res = [];
		switch ($_POST["Act"]) {
			case "auth.login":
				if (isset($_POST["Login"]) && isset($_POST["Password"])) {
					$res = $db->login($_POST["Login"], $_POST["Password"]);
					echo $res === true ? "OK" : "Wrong login/password" ;
				}
			break;
            case "auth.logout":
                $res = $db->logout();
            break;
		}
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>E-rinda ADM</title>
        <link rel="stylesheet" type="text/css" href="/styles/reset.css">
        <link rel="stylesheet" type="text/css" href="/styles/styles.adm.css">
        <script language="JavaScript" src="/scripts/jquery-2.1.4.min.js"></script>
        <script language="JavaScript" src="/scripts/admin.panel.scripts.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class="header"></div>
        <div class="column left">
            <div class="block">
                <header>Enqueue</header>
                <form id="formEnqueue">
                    <input type="hidden" name="Act" value="queue.enqueue">
                    <!--input type="text" name="QID" placeholder="Queue ID"-->
                    <input type="hidden" name="QID" value="1">
                    <input type="text" name="Name" placeholder="Person's name">
                    <input type="submit">
                </form>
            </div>
            <div class="block">
                <header>Next</header>
                <form id="formCall">
                    <input type="hidden" name="Act" value="queue.callnext">
                    <input type="hidden" name="QID" value="1">
                    <input type="text" name="Count" placeholder="Count">
                    <input type="submit" value="Call">
                </form>
            </div>
            <div class="block">
                <header>Log in</header>
                <form method="post" action="#">
                    <input type="hidden" name="Act" value="auth.login">
                    <input type="text" name="Login" placeholder="Login">
                    <input type="password" name="Password" placeholder="Password">
                    <input type="submit">
                </form>
            </div>
            <div class="block">
                <header>Log out</header>
                <form method="post" action="#">
                    <input type="hidden" name="Act" value="auth.logout">
                    <input type="submit">
                </form>
            </div>
        </div>
        <div class="column right">
        	<div id="queuerList" data-qid="1"></div>
        </div>
    </body>
</html>
