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

	//include project class
	require $GLOBALS['settings']->Folders['root'].'/lib/project/classes/Project.php';

	//include project class
	require $GLOBALS['settings']->Folders['root'].'/lib/database/functions/validateInputs.php';

	//add PHPMailer mail functionality
	require $GLOBALS['settings']->Folders['root'].'/lib/PHPMailer/PHPMailerAutoload.php';

	//include function to remove projects
	require $GLOBALS['settings']->Folders['root'].'/lib/orders/functions/markOrderArrived.php';

	//add PHPMailer mail functionality
	require $GLOBALS['settings']->Folders['root'].'/lib/FPDF/fpdf.php';

	//include DAL (DAL & login always go on top since classes depend on them)
	require $GLOBALS['settings']->Folders['root'].'/lib/barcodegenerator/src/BarcodeGenerator.php';
	require $GLOBALS['settings']->Folders['root'].'/lib/barcodegenerator/src/BarcodeGeneratorJPG.php';

	require $GLOBALS['settings']->Folders['root'].'/lib/orders/classes/generateInvoice.php';

	session_start();

	//redirect if user is not logged in as admin
	if(!isset($_SESSION["user"]) OR $_SESSION["user"]->__get("loggedIn") != TRUE OR $_SESSION["user"]->__get("permissionLevel") != 2)
	{
		header("location: ../index.php");
	}

	//check login condition and if the request contains all info
	if(isset($_SESSION["user"]) && $_SESSION["user"]->__get("loggedIn") && isset($_POST["array"]) && isset($_POST["status"]) && !empty($_POST["array"]) && !empty($_POST["status"]))
	{
		//validate input
		$status = validateInput($_POST["status"]);

		$dal = new DAL();

		//if status is approved, set orderstatus to 2 (approved)
		if($status == "approved")
		{
			foreach($_POST["array"] as $orderapproved)
			{
				//validate input
				$orderid = validateInput($orderapproved["id"]);
				$orderid = mysqli_real_escape_string($dal->getConn(), $orderid);

				//create array of parameters
				//first item = parameter types
				//i = integer
				//d = double
				//b = blob
				//s = string
				$parameters[0] = "i";
				$parameters[1] = $orderid;

				//prepare statement
				//get user email
				$dal->setStatement("SELECT product.leverancier
						FROM bestellingproduct
 						INNER JOIN product
 						ON bestellingproduct.idproduct = product.idproduct
 						WHERE bestellingproduct.bestelnummer=?");
				$records = $dal->queryDB($parameters);
				unset($parameters);

				//create array of parameters
				//first item = parameter types
				//i = integer
				//d = double
				//b = blob
				//s = string
				$parameters[0] = "i";
				$parameters[1] = $orderid;

				//prepare statement
				//get user email
				$dal->setStatement("SELECT gebruiker.email FROM gebruiker
 						INNER JOIN bestelling
 						ON bestelling.rnummer = gebruiker.rnummer
 						WHERE bestelling.bestelnummer=?");
				$emails = $dal->queryDB($parameters);
				unset($parameters);

				$fullmail = $emails[0]->email;

				if($records[0]->leverancier == 'EMSYS')
				{
					//create array of parameters
					//first item = parameter types
					//i = integer
					//d = double
					//b = blob
					//s = string
					$parameters[0] = "i";
					$parameters[1] = $orderid;

					//prepare statement
					//update orderstatus in database
					$dal->setStatement("UPDATE bestelling SET status='4' WHERE bestelnummer=?");
					$dal->writeDB($parameters);
					unset($parameters);

					// Instanciation of inherited class
					$pdf = new Invoice($orderid);
					$pdf->AliasNbPages();
					$pdf->AddPage();
					$pdf->SetFont('Times','',12);
					//for($i=1;$i<=20;$i++)
					//$pdf->Cell(0,10,'Printing line number '.$i,0,1);
					//$pdf->FancyTable($header,$data);
					$pdf->ProductTable();

					$pdf_filename = "factuur".$orderid.".pdf";

					if(!file_exists($pdf_filename) || is_writable($pdf_filename)){
						$pdf->Output($pdf_filename, "F");
					} else {
						exit("Path Not Writable");
					}

					//send mail to inform person
					$mail = new PHPMailer;

					//$mail->SMTPDebug = 3;

					//PHPMailer settings
					$mail->isSMTP();
					$mail->Host = $GLOBALS['settings']->SMTP['host'];
					$mail->SMTPAuth = TRUE;
					$mail->Username = $GLOBALS['settings']->SMTP['username'];
					$mail->Password = $GLOBALS['settings']->SMTP['password'];
					$mail->SMTPSecure = $GLOBALS['settings']->SMTP['connection'];
					$mail->Port = $GLOBALS['settings']->SMTP['port'];

					$mail->From = $GLOBALS['settings']->SMTP['username'];
					$mail->FromName = $GLOBALS['settings']->SMTP['name'];
					$mail->addAddress($fullmail);

					$mail->isHTML(TRUE);

					$mail->Subject = "bestelling #" . $orderid . " is toegekomen.";
					$mail->Body = '<html><p>Uw bestelling is aangekomen. Deze kan afgehaald worden aan het secretariaat.</p><p>Gelieve een afgedrukte versie van de factuur en uw studentenkaart mee te brengen als afhaalbewijs.</p></html>';
					$mail->addAttachment($pdf_filename);

					if (!$mail->send())
					{
						echo "<p>Fout bij het verzenden van de mail.</p>";
					}
					else
					{
						echo '<div class="row">
						<p>mail verzonden naar ' . $fullmail . '</p>
						</div>';
					}

					unlink($pdf_filename);
				}
				else
				{
					//create array of parameters
					//first item = parameter types
					//i = integer
					//d = double
					//b = blob
					//s = string
					$parameters[0] = "i";
					$parameters[1] = $orderid;

					//prepare statement
					//update orderstatus in database
					$dal->setStatement("UPDATE bestelling SET status='2' WHERE bestelnummer=?");
					$dal->writeDB($parameters);
					unset($parameters);

					//send mail to inform person
					$mail = new PHPMailer;

					//$mail->SMTPDebug = 3;

					//PHPMailer settings
					$mail->isSMTP();
					$mail->Host = $GLOBALS['settings']->SMTP['host'];
					$mail->SMTPAuth = TRUE;
					$mail->Username = $GLOBALS['settings']->SMTP['username'];
					$mail->Password = $GLOBALS['settings']->SMTP['password'];
					$mail->SMTPSecure = $GLOBALS['settings']->SMTP['connection'];
					$mail->Port = $GLOBALS['settings']->SMTP['port'];

					$mail->From = $GLOBALS['settings']->SMTP['username'];
					$mail->FromName = $GLOBALS['settings']->SMTP['name'];
					$mail->addAddress($fullmail);

					$mail->isHTML(TRUE);

					$mail->Subject = "bestelling #" . $orderid . " is goedgekeurd.";
					$mail->Body = '<html><p>Uw bestelling werd goedgekeurd. Nu moet u afwachten tot de bestelling door de school geplaatst wordt.</p></html>';

					if (!$mail->send())
					{
						echo "<p>Fout bij het verzenden van de mail.</p>";
					}
					else
					{
						echo '<div class="row">
					<p>mail verzonden naar ' . $fullmail . '</p>
					</div>';
					}
				}
			}
		}
		//else set orderstatus to 0 (denied)
		elseif($status = "denied")
		{
			foreach($_POST["array"] as $orderdenied)
			{
				//validate input
				$orderid = validateInput($orderdenied["id"]);
				$message = validateInput($orderdenied["message"]);

				//update orderstatus in database
				$orderid = mysqli_real_escape_string($dal->getConn(), $orderid);
				$message = mysqli_real_escape_string($dal->getConn(), $message);

				//create array of parameters
				//first item = parameter types
				//i = integer
				//d = double
				//b = blob
				//s = string
				$parameters[0] = "si";
				$parameters[1] = $message;
				$parameters[2] = (int) $orderid;


				//prepare statement
				//update orderstatus
				$dal->setStatement("UPDATE bestelling SET status='0', bericht=? WHERE bestelnummer=?");
				$dal->writeDB($parameters);
				unset($parameters);

                $parameters[0] = "i";
                $parameters[1] = $orderid;

                //prepare statement
                //update orderstatus
                $dal->setStatement("SELECT bestelling.rnummer, bestellingproduct.idproduct, bestellingproduct.leverancier, bestellingproduct.aantal, bestellingproduct.prijs
                FROM bestellingproduct
                INNER JOIN bestelling
                ON bestellingproduct.bestelnummer=bestelling.bestelnummer
                WHERE bestellingproduct.bestelnummer=?");
                $records = $dal->queryDB($parameters);
                unset($parameters);

				$z = 0;

				//add articles back to shopping cart
               foreach($records as $record)
			   {
					$parameters[0] = "sssid";
					$parameters[1] = $record->rnummer;
                    $parameters[2] = $record->idproduct;
                    $parameters[3] = $record->leverancier;
                    $parameters[4] = $record->aantal;
                    $parameters[5] = $record->prijs;

                    $dal->setStatement("INSERT INTO winkelwagen (rnummer,idproduct,leverancier,aantal,prijs) VALUES (?,?,?,?,?)");

                    $dal->writeDB($parameters);
                    unset($parameters);
			   }

				//create array of parameters
				//first item = parameter types
				//i = integer
				//d = double
				//b = blob
				//s = string
				$parameters[0] = "i";
				$parameters[1] = $orderid;

				//prepare statement
				//get user email
				$dal->setStatement("SELECT gebruiker.email FROM gebruiker
 						INNER JOIN bestelling
 						ON bestelling.rnummer = gebruiker.rnummer
 						WHERE bestelling.bestelnummer=?");
				$records = $dal->queryDB($parameters);
				unset($parameters);

				$fullmail = $records[0]->email;

				//send mail to inform person
				$mail = new PHPMailer;

				//$mail->SMTPDebug = 3;

				//PHPMailer settings
				$mail->isSMTP();
				$mail->Host = $GLOBALS['settings']->SMTP['host'];
				$mail->SMTPAuth = TRUE;
				$mail->Username = $GLOBALS['settings']->SMTP['username'];
				$mail->Password = $GLOBALS['settings']->SMTP['password'];
				$mail->SMTPSecure = $GLOBALS['settings']->SMTP['connection'];
				$mail->Port = $GLOBALS['settings']->SMTP['port'];

				$mail->From = $GLOBALS['settings']->SMTP['username'];
				$mail->FromName = $GLOBALS['settings']->SMTP['name'];
				$mail->addAddress($fullmail);

				$mail->isHTML(TRUE);

				$mail->Subject = "bestelling #" . $orderid . " is afgekeurd.";
				$mail->Body = '<html><p>Uw bestelling werd afgekeurd. Vraag na bij uw begeleidend docent waarom.</p></html>';

				if (!$mail->send())
				{
					echo "<p>Fout bij het verzenden van de mail.</p>";
				}
				else
				{
					echo '<div class="row">
					<p>mail verzonden naar ' . $fullmail . '</p>
					</div>';
				}
			}
		}

		$dal->closeConn();
	}
?>