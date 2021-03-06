$(document).ready(function()
{
	//disable submit on enter & trigger button click instead
	$('#searchform').on('keyup keypress', function(e) {
		var keyCode = e.keyCode || e.which;
		if (keyCode === 13) {
			e.preventDefault();
			$("#searchproduct").trigger("click");
			return false;
		}
	});

	//when search button is clicked, process ajax request
	$("#searchproduct").click(function()
	{
		//document.getElementById("overlay").style.display = "block";

		var searchterm = $("#searchterm").val();

		$(".workspace").html('<noscript><div class="alert alert-success alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Opgelet!</strong> Zonder javascript werkt de webwinkel mogelijk niet.</div> </noscript>');

		$(".workspace").append('<table id="displayproducts" data-toggle="table" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">'
			+ '<thead>'
			+ '<tr>'
			+ '<th data-field="id" data-sortable="true">id</th>'
			+ '<th data-field="name" data-sortable="true">naam</th>'
			+ '<th data-field="supplier"  data-sortable="true">leverancier</th>'
			+ '<th data-field="vendor" data-sortable="true">verkoper</th>'
			+ '<th data-field="price">prijs</th>'
			+ '<th data-field="add">bestel</th>'
			+ '</tr>'
			+ '</thead>'
			+ '</table>');

		//remove old data from table
		$('#displayproducts').bootstrapTable('removeAll');

		//destroy table
		$('#displayproducts').bootstrapTable('destroy');

		//recreate table with new data
		$('#displayproducts').bootstrapTable({
			onPageChange:function() {
				colorLowInventoryRed();

				//when a product button is clicked, product id, supplier and amount are sent to the shopping cart
				$(".productbutton").click(function()
				{
					var DOMobj = $(this).parent().prev();

					//if amount is lower than minimal quantity, do not allow order of component
					if($(DOMobj).val() >= 0)
					{
						//prepare request
						$request = $.ajax({
							method:"POST",
							url:"AJAX/processAddToCartRequest.php?r=" + new Date().getTime(),
							data: {productid: $(DOMobj).data("productid"), supplier: $(DOMobj).data("supplier"), amount: $(DOMobj).val()}
						});

						$request.done(function()
						{
							//display message when product is successfully added
							$('<div class="navbar-fixed-bottom alert alert-success"> <strong>Toegevoegd!</strong> Het product is toegevoegd aan je winkelmandje.</div>').insertBefore($("footer")).fadeOut(2000, function()
							{
								$(this).remove();
							});
						});

						$request.fail(function()
						{
							//display message when product could not be added
							$('<div class="navbar-fixed-bottom alert alert-danger"> <strong>Fout!</strong> Het product kon niet worden toegevoegd aan je winkelmandje.</div>').insertBefore($("footer")).fadeOut(2000, function()
							{
								$(this).remove();
							});
						});
					}
				});
			},
			onLoadSuccess: function(){
				//when a product button is clicked, product id, supplier and amount are sent to the shopping cart
				$(".productbutton").click(function()
				{
					var DOMobj = $(this).parent().prev();

					//if amount is lower than minimal quantity, do not allow order of component
					if($(DOMobj).val() >= 0)
					{
						//prepare request
						$request = $.ajax({
							method:"POST",
							url:"AJAX/processAddToCartRequest.php?r=" + new Date().getTime(),
							data: {productid: $(DOMobj).data("productid"), supplier: $(DOMobj).data("supplier"), amount: $(DOMobj).val()}
						});

						$request.done(function()
						{
							//display message when product is successfully added
							$('<div class="navbar-fixed-bottom alert alert-success"> <strong>Toegevoegd!</strong> Het product is toegevoegd aan je winkelmandje.</div>').insertBefore($("footer")).fadeOut(2000, function()
							{
								$(this).remove();
							});
						});

						$request.fail(function()
						{
							//display message when product could not be added
							$('<div class="navbar-fixed-bottom alert alert-danger"> <strong>Fout!</strong> Het product kon niet worden toegevoegd aan je winkelmandje.</div>').insertBefore($("footer")).fadeOut(2000, function()
							{
								$(this).remove();
							});
						});
					}
				});
				/**document.getElementById("overlay").style.display = "none";**/
			},
			url: 'AJAX/processSearchEmSysProductRequest.php?searchproduct=' + searchterm + '&r=' + new Date().getTime()
		});
	});
});

function colorLowInventoryRed()
{
	//color inventory = red when under 100 items
	$('#displayproducts').find("tr").each(function(){
		if(Number($(this).find("td").eq(5).text()) < 100)
		{
			$(this).find("td").eq(5).css('color', 'red');
		}
	});
}