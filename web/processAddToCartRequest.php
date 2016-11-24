<?php
	//this script processes the AJAX request & updates the user's shopping cart

	//load config, typecast to object for easy access
	$GLOBALS['settings'] = (object) parse_ini_file('../config/config.ini', true);

	//include classes BEFORE session_start because we might need them in session
	//include DAL (DAL & login always go on top since classes depend on them)
	require $GLOBALS['settings']->Folders['root'].'../lib/database/classes/DAL.php';

	//include login class
	require $GLOBALS['settings']->Folders['root'].'../lib/users/classes/Login.php';

	//include ProductPrice class
	require $GLOBALS['settings']->Folders['root'].'../lib/products/classes/ProductPrice.php';

	//include Product class
	require $GLOBALS['settings']->Folders['root'].'../lib/products/classes/Product.php';

	//include MouserProduct
	require $GLOBALS['settings']->Folders['root'].'../lib/products/classes/MouserProduct.php';

	//include FarnellProduct
	require $GLOBALS['settings']->Folders['root'].'../lib/products/classes/FarnellProduct.php';

	//include functions
	require $GLOBALS['settings']->Folders['root'].'../lib/products/functions/getfarnellproducts.php';
	require $GLOBALS['settings']->Folders['root'].'../lib/products/functions/getmouserproducts.php';
	require $GLOBALS['settings']->Folders['root'].'../lib/shoppingcart/functions/addToCart.php';

	session_start();

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_POST["productid"]) && isset($_POST["supplier"]) && isset($_POST["amount"]))
	{
		//we search product to add to database (this causes overhead, but allows us to validate the data)
		//new Data Access Layer object
		$dal = new DAL();

		if($_POST["supplier"]=="Farnell")
		{
			//send request to Farnell API, returns array of one FarnellProduct
			//what if there are no results? --> send error to page & terminate script (still needs work)
			$product = getFarnellProducts($_POST["productid"], 0, 1);

			//add product to cart (typecast amount string to int)
			addToCart($_SESSION["user"]->__get("userId"), $product, (int) $_POST["amount"], $dal);
		}
		elseif($_POST["supplier"]=="Mouser")
		{
			//send request to Mouser API, returns array of one MouserProduct
			$product = getMouserProducts($_POST["productid"], 0, 1);

			//add product to cart (typecast amount string to int)
			addToCart($_SESSION["user"]->__get("userId"), $product, (int) $_POST["amount"], $dal);
		}

		//close connection
		$dal->closeConn();
	}
?>