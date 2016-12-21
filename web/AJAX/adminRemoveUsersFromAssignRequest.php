<?php
	//this script processes the AJAX request & updates the user's shopping cart

	//load config, typecast to object for easy access
	$GLOBALS['settings'] = (object) parse_ini_file('../../config/config.ini', true);

	//include classes BEFORE session_start because we might need them in session
	//include DAL (DAL & login always go on top since classes depend on them)
	require $GLOBALS['settings']->Folders['root'].'../lib/database/classes/DAL.php';

	//include login class
	require $GLOBALS['settings']->Folders['root'].'../lib/users/classes/Login.php';

	//include ProductPrice class
	require $GLOBALS['settings']->Folders['root'].'../lib/project/classes/Project.php';

	session_start();

	//redirect if user is not logged in as admin
	if(!isset($_SESSION["user"]) OR $_SESSION["user"]->__get("loggedIn") != TRUE OR $_SESSION["user"]->__get("permissionLevel") != 2)
	{
		header("location: ../index.php");
	}

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_POST["array"]) && isset($_SESSION["adminAddUsersToAssignRequest"]))
	{
		//remove array from session array
		foreach($_POST["array"] as $key => $assigneduser)
		{
			//remove matching elements from session array
			for($i = 0; $i < count($_SESSION["adminAddUsersToAssignRequest"]); $i++)
			{
				if($_SESSION["adminAddUsersToAssignRequest"][$i]["email"] == $_POST["array"][$key]["email"])
				{
					array_splice($_SESSION["adminAddUsersToAssignRequest"], $i, 1);
				}
			}
		}
	}
?>