<?php
	//this script processes the AJAX request

	//load config, typecast to object for easy access
	$GLOBALS['settings'] = (object) parse_ini_file('../../config/config.ini', true);

	//include classes BEFORE session_start because we might need them in session
	//include DAL (DAL & login always go on top since classes depend on them)
	require $GLOBALS['settings']->Folders['root'].'/lib/database/classes/DAL.php';

	//include login class
	require $GLOBALS['settings']->Folders['root'].'/lib/users/classes/Login.php';

	//include Product class
	require $GLOBALS['settings']->Folders['root'].'/lib/products/classes/Product.php';

	session_start();

	//redirect if user is not logged in as admin
	if(!isset($_SESSION["user"]) OR $_SESSION["user"]->__get("loggedIn") != TRUE OR $_SESSION["user"]->__get("permissionLevel") != 2)
	{
		header("location: ../index.php");
	}

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_GET["rnummer"]))
	{
		$dal = new DAL();

		//prevent SQL injection
		$rnummer= mysqli_real_escape_string($dal->getConn(), $_GET["rnummer"]);

		//create array of parameters
		//first item = parameter types
		//i = integer
		//d = double
		//b = blob
		//s = string
		$parameters[0] = "s";
		$parameters[1] = $rnummer;

		//prepare statement
		$dal->setStatement("SELECT project.idproject, project.titel, gebruikerproject.is_beheerder as beheerder
			FROM project
			INNER JOIN gebruikerproject ON project.idproject=gebruikerproject.idproject
			WHERE gebruikerproject.rnummer=?");

		$records = $dal->queryDB($parameters);
		unset($parameters);

		$dal->closeConn();

		//Lumino admin panel requires a JSON to process
		echo json_encode($records);
	}
?>