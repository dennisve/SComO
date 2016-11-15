<?php
	//set page var in order to adapt navbar and functions
	$_GLOBALS['page'] = "registreren";

	//include header
	require '../templates/header.php';
?>

		<script src="./js/livesearch.js"></script>
	</head>

	<body>		
		<?php
			//include navbar
			require '../templates/navbar.php';
		?>
		
		<div class="jumbotron text-center">
			<h1>
				Registreren
			</h1>
			<p>
				Maak een account aan.
			</p>
		</div>
		<div class="container register">
			<?php
				if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["voornaam"]) && isset($_POST["naam"]) && isset($_POST["rnummer"]) && isset($_POST["email"]) && validateDomain($_POST["email"], $_GLOBALS["settings"]->Whitelist["mail"]) && isset($_POST["wachtwoord"]))
				{
					//new Data Access Layer object
					$dal = new DAL($_GLOBALS['settings']->Folders['root']);
					$conn = $dal->getConn();
					
					//validate input for html injection & check vs REGEX, counter mysql injection
					$voornaam = mysqli_real_escape_string($conn, validateNaam($_POST["voornaam"]));
					$naam = mysqli_real_escape_string($conn, validateNaam($_POST["naam"]));
					$rnummer = mysqli_real_escape_string($conn, validateRNummer($_POST["rnummer"], $_GLOBALS["settings"]->Whitelist["idletters"]));
					$mail = mysqli_real_escape_string($conn, validateMail($_POST["email"]));
					$wachtwoord = mysqli_real_escape_string($conn, validateWachtWoord($_POST["wachtwoord"]));
					$fullmail = $rnummer."@".$mail;
					
					//test if user already exists with rnummer
					$sql = "SELECT rnummer FROM gebruiker WHERE rnummer='".$rnummer."'";
					$records = $dal->QueryDB($sql);
					
					//if user already exists, numrows >= 1, if not we can continue
					if(!($dal->getNumResults()>=1))
					{
						//prepare for timestamp
						date_default_timezone_set('Europe/Brussels');
						
						//write to db (still need to add date & time)
						$sql = "INSERT INTO gebruiker (rnummer, voornaam, achternaam, email, wachtwoord, machtigingsniveau, aanmaakdatum) VALUES ('".$rnummer."', '".$voornaam."', '".$naam."', '".$fullmail."', '".$wachtwoord."', '0', '".date("j/n/Y H:i:s")."')";
						$dal->WriteDB($sql);
						
						echo '<div class="row">
							<p>Registratie succesvol</p>
							<p>mail verzonden naar '.$fullmail.'</p>
							</div>';
					}
					else
					{
						echo "<p>Fout bij het aanmaken van de gebruiker. Probeer opnieuw.</p>";
					}
					
					$dal->closeConn();
				}
				else
				{
					echo '<form action="registreren.php" method="post">
						<div class="form-group row">
							<label for="Voornaam" class="col-sm-2">Voornaam</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="voornaam" name="voornaam" placeholder="voornaam" />
							</div>
						</div>
						<div class="form-group row">
							<label for="naam" class="col-sm-2">Naam</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="naam" name="naam" placeholder="naam" />
							</div>
						</div>
						<div class="form-group row">
							<label for="rnummer" class="col-sm-2">rnummer en email</label>
							<div class="col-sm-5">
								<input type="text" class="form-control" id="rnummer" name="rnummer" placeholder="r0123456" />
							</div>
							<div class="col-sm-5">
								<select class="form-control"  id="email" name="email">
									<option value="Selecteer keuze">Selecteer keuze</option>';
									//integrate mail whitelist
									foreach ($_GLOBALS['settings']->Whitelist["mail"] as $mailaddress)
									{
										echo '<option value="'.$mailaddress.'">@'.$mailaddress.'</option>';
									}
					echo'		</select> 
							</div>
						</div>
						<div class="form-group row">
							<label for="wachtwoord" class="col-sm-2">Wachtwoord</label>
							<div class="col-sm-10">
								<input type="password" class="form-control"  id="wachtwoord" name="wachtwoord" placeholder="password" />
							</div>
						</div>
						<button class="btn btn-primary" type="submit">Registreer</button>
					</form>';
				}
			?>
		</div>
		<!-- footer -->
		<?php require '../templates/footer.php'; ?>