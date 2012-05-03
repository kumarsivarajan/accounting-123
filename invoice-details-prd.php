<?
#This program is copyright by Andre Coetzee email: ac@main.me
#and is licensed under the GPL v3
#
#
#
#
#Please add yourself to: http://www.accounting-123.com
#Developers, Software Vendors, Support, Accountants, Users
#
#
#The full software license can be found here:
#http://www.accounting-123.com/a.php?a=153/GPLv3
#
#
#
#
#
#
#
#
#
#
#
# get settings
require("settings.php");
require("core-settings.php");
require("libs/ext.lib.php");

# decide what to do
if (isset($_GET["invid"]) && isset($_GET["prd"])) {
	$OUTPUT = details($_GET);
} else {
	$OUTPUT = "<li class='err'>Invalid use of module.</li>";
}

# get templete
require("template.php");




# details
function details($_GET)
{

	# get vars
	extract ($_GET);

	# validate input
	require_lib("validate");
	$v = new  validate ();
	$v->isOk ($invid, "num", 1, 20, "Invalid invoice number.");
	// $v->isOk ($prd, "num", 1, 20, "Invalid period Database number.");

	# display errors, if any
	if ($v->isError ()) {
		$err = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$err .= "<li class='err'>".$e["msg"]."</li>";
		}
		$confirm = "$err<p><input type='button' onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirm;
	}

	# Get invoice info
	db_conn($prd);
	$sql = "SELECT * FROM invoices WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$invRslt = db_exec ($sql) or errDie ("Unable to get invoice information");
	if (pg_numrows ($invRslt) < 1) {
		return "<i class='err'>Not Found</i>";
	}
	$inv = pg_fetch_array($invRslt);

	# Keep the charge vat option stable
	if($inv['chrgvat'] == "inc"){
		$inv['chrgvat'] = "Yes";
	}elseif($inv['chrgvat'] == "exc"){
		$inv['chrgvat'] = "No";
	}else{
		$inv['chrgvat'] = "Non VAT";
	}

	/* --- Start Products Display --- */

	# Products layout
	$products = "
		<table ".TMPL_tblDflts." width='100%'>
			<tr>
				<th>WAREHOUSE</th>
				<th>ITEM NUMBER</th>
				<th>DESCRIPTION</th>
				<th>QTY</th>
				<th>UNIT PRICE</th>
				<th>UNIT DISCOUNT</th>
				<th>AMOUNT</th>
			<tr>";

	# get selected stock in this invoice
	db_conn($prd);

	$sql = "SELECT * FROM inv_items  WHERE invid = '$invid' AND div = '".USER_DIV."'";
	$stkdRslt = db_exec($sql);

	while($stkd = pg_fetch_array($stkdRslt)){

		if($stkd['account'] == 0) {

			# get warehouse name
			db_conn("exten");
			$sql = "SELECT whname FROM warehouses WHERE whid = '$stkd[whid]' AND div = '".USER_DIV."'";
			$whRslt = db_exec($sql);
			$wh = pg_fetch_array($whRslt);

			# get selected stock in this warehouse
			db_connect();
			$sql = "SELECT * FROM stock WHERE stkid = '$stkd[stkid]' AND div = '".USER_DIV."'";
			$stkRslt = db_exec($sql);
			$stk = pg_fetch_array($stkRslt);
		} else {
			$wh['whname'] = "";
			$stk['stkcod'] = "";
			$stk['stkdes'] = $stkd['description'];
		}

		if (strlen($stkd['serno']) > 0){
			$showser = "<br>".trim($stkd['serno']);
		}else {
			$showser = "";
		}

		# put in product
		$products .= "
			<tr class='".bg_class()."'>
				<td>$wh[whname]</td>
				<td>$stk[stkcod]</td>
				<td>$stk[stkdes] $showser</td>
				<td>$stkd[qty]</td>
				<td>$stkd[unitcost]</td>
				<td>".CUR." $stkd[disc] &nbsp;&nbsp; OR &nbsp;&nbsp; $stkd[discp]%</td>
				<td>".CUR." $stkd[amt]</td>
			</tr>";
	}
	$products .= "</table>";

/* --- Start Some calculations --- */

	# Calculate subtotal
	$SUBTOT = sprint($inv['subtot']);

	# Calculate tradediscm
	if($inv['traddisc'] > 0){
		$traddiscm = sprint((($inv['traddisc']/100) * $SUBTOT));
	}else{
		$traddiscm = "0.00";
	}

	$VATP = TAX_VAT;

	# Calculate subtotal
	$SUBTOT = sprint($inv['subtot']);
 	$VAT = sprint($inv['vat']);
	$TOTAL = sprint($inv['total']);
	$inv['delchrg'] = sprint($inv['delchrg']);

/* --- End Some calculations --- */

	/* -- Final Layout -- */
	$details = "
		<center>
		<h3>Invoice Details</h3>
		<table ".TMPL_tblDflts." width='95%'>
			<tr>
				<td valign='top'>
					<table ".TMPL_tblDflts." width='60%'>
						<tr>
							<th colspan='2'> Customer Details </th>
						</tr>
						<tr class='".bg_class()."'>
							<td>Department</td>
							<td valign='center'>$inv[deptname]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Customer</td>
							<td valign='center'>$inv[cusname] $inv[surname]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td valign='top'>Customer Address</td>
							<td valign='center'>".nl2br($inv['cusaddr'])."</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Customer Order number</td>
							<td valign='center'>$inv[cordno]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Customer VAT Number</td>
							<td>$inv[cusvatno]</td>
						</tr>
						<tr>
							<th colspan='2' valign='top'>Comments</th>
						</tr>
						<tr class='".bg_class()."'>
							<td colspan='2' align='center'>".nl2br($inv['comm'])."</pre></td>
						</tr>
					</table>
				</td>
				<td valign='top' align='right'>
					<table ".TMPL_tblDflts.">
						<tr>
							<th colspan='2'> Invoice Details </th>
						</tr>
						<tr class='".bg_class()."'>
							<td>Invoice No.</td>
							<td valign='center'>$inv[invnum]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Proforma Inv No.</td>
							<td valign='center'>$inv[docref]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Order No.</td>
							<td valign='center'>$inv[ordno]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>VAT Inclusive</td>
							<td valign='center'>$inv[chrgvat]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Terms</td>
							<td valign='center'>$inv[terms] Days</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Sales Person</td>
							<td valign='center'>$inv[salespn]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Invoice Date</td>
							<td valign='center'>$inv[odate]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Trade Discount</td>
							<td valign='center'>$inv[traddisc]%</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Delivery Charge</td>
							<td valign='center'>$inv[delchrg]</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td><br></td></tr>
			<tr><td colspan='2'>$products</td></tr>
			<tr>
				<td>
					<table ".TMPL_tblDflts.">
						<p>
						<tr>
							<th>Quick Links</th>
						</tr>
						<tr class='".bg_class()."'>
							<td><a href='cust-credit-stockinv.php'>New Invoice</a></td>
						</tr>
						<tr class='".bg_class()."'>
							<td><a href='invoice-view.php'>View Invoices</a></td>
						</tr>
						<script>document.write(getQuicklinkSpecial());</script>
					</table>
				</td>
				<td align='right'>
					<table ".TMPL_tblDflts." width='80%'>
						<tr class='".bg_class()."'>
							<td>SUBTOTAL</td>
							<td align='right'>".CUR." $SUBTOT</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Trade Discount</td>
							<td align='right'>".CUR." $inv[discount]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td>Delivery Charge</td>
							<td align='right'>".CUR." $inv[delivery]</td>
						</tr>
						<tr class='".bg_class()."'>
							<td><b>VAT @ $VATP%</b></td>
							<td align='right'>".CUR." $VAT</td>
						</tr>
						<tr class='".bg_class()."'>
							<th>GRAND TOTAL</th>
							<td align='right'>".CUR." $TOTAL</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		</form>
		</center>";
	return $details;

}



?>