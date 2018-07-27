<?php
error_reporting(E_ALL);
include_once('checklogin.php');
if($loggedIn === true){
	header("Location: panel/index.php");
}
$error = "";
if(empty($_POST) == false){
	
	include('connect_dbsql.php');
	
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	
	$username = stripslashes($username);
	$password = stripslashes($password);
	
	$username = strip_tags($username);
	$password = strip_tags($password);
	
	$pass = $password;
	
	$consulta="SELECT * FROM `tblusua` WHERE usuemail = '$username' && usupasswd = '$pass' LIMIT 1";
	$query = mysqli_query($cmysqli, $consulta);
	$number = mysqli_num_rows($query);
	
	if($number == 1){
		while($row = mysqli_fetch_array($query)){
			$id = $row['Usuario_id'];
			$username2 = $row['usunombre'];
			$usuemail = $row['usuEmail'];
			$uniqid = sha1($row['Usuario_id']);
			$usunivel = $row['usunivel'];
		}
		mysqli_close($cmysqli);
		$_SESSION['id'] = $id;
		$_SESSION['username'] = $username2;
		$_SESSION['uid'] = $uniqid;
		$_SESSION['usunivel'] = $usunivel;
		$_SESSION['usuemail'] = $usuemail;
		header("Location: panel/index.php");
	} else {
		
		$error = '<div class="alert alert-danger" role="alert">El usuario y/o el password es incorrecto.</div>';
	}
}
/*
//Find out if users are allowed to register
$settingTitleOne = "Anonymous Register";
$allowedToRegister = mysql_query("SELECT * FROM `settings` WHERE `SettingTitle` = '$settingTitleOne'");
$registerValue = mysql_fetch_array($allowedToRegister);*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="login pedimento">
    <meta name="author" content="Abisai Cruz">
    <!--link rel="icon" href="../../favicon.ico"-->

    <title>Monitor de referencias</title>

	<!-- Bootstrap core CSS -->
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./bootstrap/css/signin.css" rel="stylesheet">
	
	<link rel="icon" type="image/ico" href="favicon.ico" />
    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">

      <form name="loginForm" class="form-signin" role="form" action="login.php" method="POST">
        <center><h3><font color="#585756">Monitor de referencias</font></h3></center>
		<center><img src="./images/logo.png" alt="logo" width='150' higth='75'></center>
		<br>
<?php
	if ($error != "") 
	{
		echo '<p class="bg-danger">'.$error.'</p>';
	}
?>
        <input type="email" name="username" class="form-control" placeholder="Usuario" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Acceder</button>
      </form>

    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--script src="./bootstrap/js/ie10-viewport-bug-workaround.js"></script-->
</body>
</html>