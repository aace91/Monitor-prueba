<?php
	$mysqlserver="192.168.1.107:3309";
	$mysqldb="casa";
	$mysqluser="root";
	$mysqlpass="Marianar0117c";

	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb) or die("Error al conectarse a la base de datos de expedientes");;
	
	/*$mysqlserverbod="www.delbravoapps.com";
	$mysqluserbod="clienteweb";
	$mysqlpassbod="clibra01";

	$cmysqlbod = mysql_connect($mysqlserverbod,$mysqluserbod,$mysqlpassbod);
	mysql_select_db($cmysqlbod) or die("Error al conectarse a la base de datos de bodega");
	;*/
	
?>