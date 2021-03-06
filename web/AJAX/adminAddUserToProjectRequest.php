<?php
	//load config, typecast to object for easy access
	$GLOBALS['settings'] = (object) parse_ini_file('../../config/config.ini', true);

	//include classes BEFORE session_start because we might need them in session
	//include DAL (DAL & login always go on top since classes depend on them)
	require $GLOBALS['settings']->Folders['root'].'/lib/database/classes/DAL.php';

	//include login class
	require $GLOBALS['settings']->Folders['root'].'/lib/users/classes/Login.php';

	//include ProductPrice class
	require $GLOBALS['settings']->Folders['root'].'/lib/project/classes/Project.php';

	//inputchecks
	require $GLOBALS['settings']->Folders['root'].'/lib/database/functions/validateInputs.php';

	session_start();

	//redirect if user is not logged in as admin
	if(!isset($_SESSION["user"]) OR $_SESSION["user"]->__get("loggedIn") != TRUE OR $_SESSION["user"]->__get("permissionLevel") != 2)
	{
		header("location: ../index.php");
	}

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_POST["id"]) && isset($_POST["rnummer"]))
	{
		$dal = new DAL();

		//sanitize DB input
		$projectid = mysqli_real_escape_string($dal->getConn(), $_POST["id"]);
		$rnummer = mysqli_real_escape_string($dal->getConn(), $_POST["rnummer"]);

		//create array of parameters
		//first item = parameter types
		//i = integer
		//d = double
		//b = blob
		//s = string
		$parameters[0] = "si";
		$parameters[1] = $rnummer;
		$parameters[2] = (int)$projectid;

		//prepare statement
		$dal->setStatement("SELECT * FROM gebruikerproject WHERE rnummer=? AND idproject= ?");
		$records = $dal->queryDB($parameters);
		unset($parameters);

		//if not exists, add row
		if($dal->getNumResults() == 0)
		{
			//create array of parameters
			//first item = parameter types
			//i = integer
			//d = double
			//b = blob
			//s = string
			$parameters[0] = "sii";
			$parameters[1] = $rnummer;
			$parameters[2] = (int)$projectid;
			$parameters[3] = 0;

			//prepare statement
			$dal->setStatement("INSERT INTO gebruikerproject (rnummer, idproject, is_beheerder) VALUES (?, ?, ?)");
			$dal->writeDB($parameters);
			unset($parameters);
		}

		$dal->closeConn();
	}
?>