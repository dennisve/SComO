<?php
	//set page var in order to adapt navbar and functions
	$GLOBALS['page'] = "winkelmandje";

	//include header
	require '../templates/header.php';

	//redirect if user is not logged in
	if(!isset($_SESSION["user"]) OR $_SESSION["user"]->__get("loggedIn") != TRUE)
	{
		header("location: index.php");
	}

	//include Shopping Cart & ShoppingCartArticle
	require $GLOBALS['settings']->Folders['root'].'../lib/shoppingcart/classes/ShoppingCart.php';
	require $GLOBALS['settings']->Folders['root'].'../lib/shoppingcart/classes/ShoppingCartArticle.php';

?>
</head>

<body>
<?php
	//include navbar
	require '../templates/navbar.php';
?>

<!-- PROJECT TITLE and QUOTE -->
<div class="jumbotron text-center">
	<h1>
		<?php
			echo $_SESSION["user"]->__get("firstName")." ".$_SESSION["user"]->__get("lastName")
		?>
	</h1>
	<p>
		Controleer je bestelling
	</p>
</div>
<div class="container">
	<noscript>
		<div class="alert alert-warning alert-dismissible">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<strong>Opgelet!</strong> Zonder javascript werkt de webwinkel mogelijk niet.
		</div>
	</noscript>
	<?php
		$shoppingCart = new ShoppingCart($_SESSION["user"]->__get("userId"));
		$shoppingCart->printFinalShoppingCart();
	?>
</div>

<!-- footer -->
<?php require $GLOBALS['settings']->Folders['root'].'../templates/footer.php'; ?>