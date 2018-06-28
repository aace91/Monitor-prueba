<?php
$mysqlserver="216.150.40.163";
$mysqldb="bodega";
$mysqluser="ejecutivo";
$mysqlpass="ejebra01";
$rutabodegamdb='\\\\192.168.2.33\dbdata\bodega\bodega.mdb';
$rutabodegamexmdb='\\\\192.168.1.107\gabdata\bodega\bodega.mdb';
$rutaremisionesmdb='\\\\192.168.1.107\gabdata\bodega\remisiones.mdb';
$rutaexposmdb='\\\\192.168.1.107\gabdata\bodega\expos.mdb';
$ip_out2="www.delbravoapps.tk";
$port_out2="80";

/********************************************/
/* ..:: Base de datos Contabilidad ::.. */
$mysqlserver_sab07="192.168.1.107:3309";
$mysqlserver2_sab07="192.168.1.107";
$mysqlport_sab07=3309;
$mysqldb_sab07="contagab";
$mysqluser_sab07="root";
$mysqlpass_sab07="Marianar0117c";

/********************************************/
/* ..:: Base de datos expedientes ::.. */
$mysqlserver_exp="192.168.1.107:3309";
$mysqldb_exp="expedientes";
// $mysqluser_exp="root";
// $mysqlpass_exp="Marianar0117c";
$mysqluser_exp="expedientes";
$mysqlpass_exp="r0117c";

//CASA
$pdo_casa_cnn = "firebird:dbname=192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.gdb";//;charset=utf8
$pdo_casa_usu = "SYSDBA";
$pdo_casa_psw = "masterkey";
//iBase :: Actualizar CASA
$host_ibase_casa = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
$username_ibase_casa='SYSDBA';
$password_ibase_casa='masterkey';

$pdo_mysql_sconn = 'mysql:host='.$mysqlserver.';dbname='.$mysqldb;
$pdo_accss_sconn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=".$rutabodegamdb.";Uid=; Pwd=;";

/********************************************/
/* ..:: Base de datos GABSQL1 ::.. */
$mysqlserver_gab1="192.168.1.233";
$mysqldb_gab1="bodega";
$mysqluser_gab1="ejecutivo";
$mysqlpass_gab1="ejebra01";

/********************************************/
/* ..:: Base de datos Steris Regla8va ::.. */
$mysqldb_sterisr8va = 'steris_regla8va';
/*$mysqlserver_steris8va = "delbravoapps.com";
$mysqlport_steris8va = 3309;
$mysqldb_steris8va = "contagab";
$mysqluser_steris8va = "root";
$mysqlpass_steris8va = "Marianar0117c";*/