<?php
	//this script processes the AJAX request & writes the user's shopping cart as an order to the database

	//load config, typecast to object for easy access
	$GLOBALS['settings'] = (object) parse_ini_file('../../config/config.ini', true);

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

	session_start();

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_POST["orderpersonal"]) && isset($_POST["projectid"]))
	{
		//shopping cart object including all articles
		$shoppingCart = new ShoppingCart($_SESSION["user"]->__get("userId"));

		//add products from cart to order, indicate if it's for personal use or not (admin can assign project if not personal)
		$shoppingCart->addCartToOrders($_POST["orderpersonal"]);

		//empty cart
		$shoppingCart->emptyCart();
	}
?>