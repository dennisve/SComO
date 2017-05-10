<?php

	class OrderProduct
	{
		private $orderId;
		private $productId;
		private $productSupplier;
		private $productAmount;
		private $productPrice;
		private $productStatus;
		private $productFinalOrderNumber;

		private $product;

		public function __construct($oid, $pid, $psupplier, $pamount, $pprice, $pstatus='0', $pfinalOrderNumber='')
		{
			$this->orderId = $oid;
			$this->productId = $pid;
			$this->productSupplier = $psupplier;
			$this->productAmount = $pamount;
			$this->productPrice = $pprice;
			$this->productStatus = $pstatus;
			$this->productFinalOrderNumber = $pfinalOrderNumber;

			//get extra info on product
			$this->getProductInfo();
		}

		public function writeDB()
		{
			$dal = new DAL();

			//prevent SQL injection
			$this->orderId = mysqli_real_escape_string($dal->getConn(), $this->orderId);
			$this->productId = mysqli_real_escape_string($dal->getConn(), $this->productId);
			$this->productSupplier = mysqli_real_escape_string($dal->getConn(), $this->productSupplier);
			$this->productAmount = mysqli_real_escape_string($dal->getConn(), $this->productAmount);
			$this->productPrice = mysqli_real_escape_string($dal->getConn(), $this->productPrice);
			$this->productStatus = mysqli_real_escape_string($dal->getConn(), $this->productStatus);
			$this->productFinalOrderNumber = mysqli_real_escape_string($dal->getConn(), $this->productFinalOrderNumber);

			//create array of parameters
			//first item = parameter types
			//i = integer
			//d = double
			//b = blob
			//s = string
			$parameters[0] = "issidis";
			$parameters[1] = $this->orderId;
			$parameters[2] = $this->productId;
			$parameters[3] = $this->productSupplier;
			$parameters[4] = $this->productAmount;
			$parameters[5] = $this->productPrice;
			$parameters[6] = (int) $this->productStatus;
			$parameters[7] = $this->productFinalOrderNumber;

			//add order to DB (still to complete)
			$dal->setStatement("INSERT INTO bestellingproduct (bestelnummer, idproduct, leverancier, aantal, prijs, status, defbestelnummer) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$dal->writeDB($parameters);
			unset($parameters);

			$dal->closeConn();
		}

		public function getProductInfo()
		{
			$this->product = new Product();
			$this->product->fillFromDB($this->productId, $this->productSupplier);
		}

		public function printOrderProduct()
		{
			//print orders in "table row" style
			echo '
				<td>'
				.$this->product->__get("Name").
				'</td>
				<td>'
				.$this->product->__get("Supplier").
				'</td>
				<td>'
				.$this->product->__get("Id").
				'</td>
				<td>'
				.$this->product->__get("Vendor").
				'</td>		
				<td>
					<a href="'.$this->product->__get("DataSheet").'" target="_blank">Link</a>
				</td>
				<td>
					<img class="img img-responsive" src="'.$this->product->__get("Image").'" />
				</td>
				<td>'
					.$this->productPrice.
				'</td>
				<td>'
					.$this->productAmount.
				'</td>
			</div>';
		}
	}