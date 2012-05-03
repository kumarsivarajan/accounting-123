<?
# This program is copyright by Cubit Accounting Software CC
# Reg no 2002/099579/23
# Full e-mail support is available
# by sending an e-mail to andre@andre.co.za
#
# Rights to use, modify, change and all conditions related
# thereto can be found in the license.html file that is
# distributed along with this program.
# You may not use this program in any way or form without
# consenting to the terms and conditions contained in the
# license. If this program did not include the license.html
# file please contact us at +27834433455 or via email
# andre@andre.co.za (In South Africa: Tel. 0834433455)
#
# Our website is at http://www.cubit.co.za
# comments. suggestions and applications for free coding
# could be made via email to andre@andre.co.za
#
# Our banking details as follows:
# Banker: Nedbank
# Account Name: Cubit Accounting Software
# Account Number: 1357 082517
# Swift Code: NEDSZAJJ
# Branch Code: 135705
# Branch Name: Manager Direct
# Banker Address: 3rd Floor Nedcor Park, 6 Press Avenue, Johanesburg
#
#
# Fees due to integrators, will be paid into your account within 30 days
# of receipt of the relevant license fee.
#
# Please ensure that we have your correct banking details.
require ("settings.php");

// store the post vars in get vars, so that both vars can be accessed at once
// it is done this was around, so post vars get's higher priority and overwrites duplicated in get vars
if ( isset($_POST) ) {
	foreach( $_POST as $arr => $arrval ) {
		$_GET[$arr] = $arrval;
	}
}

// see what to do
if (isset ($_GET["key"])) {
	switch ($_GET["key"]) {
		case "delete":
		case "confirm_delete":
			$OUTPUT = deleteContact();
			break;
		default:
			$OUTPUT = viewContact ();
	}
} else {
	$OUTPUT = viewContact ();
}

# display output
require ("template.php");
# enter new data
function viewContact () {
	global $_GET;
	global $user_admin;

  foreach ($_GET as $key => $value) {
		$$key = $value;
	}
	# validate input
	require_lib("validate");
	$v = new  validate ();

        $v->isOk ($id,"num", 1,100, "Invalid num.");

        # display errors, if any
	if ($v->isError ()) {
		$confirmCust = "";
		$errors = $v->getErrors();
		foreach ($errors as $e) {
			$confirmCust .= "<li class=err>".$e["msg"];
		}
		$confirmCust .= "<p><input type=button onClick='JavaScript:history.back();' value='&laquo; Correct submission'>";
		return $confirmCust;
	}

  db_conn('cubit');
  $user=USER_ID;
  # write to db
  $Sql = "SELECT * FROM cons WHERE ((id='$id')and ((con='Yes' and assigned_to_id='$user') or(con='No')))";
  $Rslt = db_exec($Sql) or errDie ("Unable to access database.");
  if (pg_numrows($Rslt)<1) {return "Contact not found";}
  $Data = pg_fetch_array($Rslt);

  $date= $Data['date'];

  $mon=substr($date,5,2);

  if ($mon==1){$td=31;$M='January';}
  if ($mon==2){$td=28;$M='February';}
  if ($mon==3){$td=31;$M='March';}
  if ($mon==4){$td=30;$M='April';}
  if ($mon==5){$td=31;$M='May';}
  if ($mon==6){$td=30;$M='June';}
  if ($mon==7){$td=31;$M='July';}
  if ($mon==8){$td=31;$M='August';}
  if ($mon==9){$td=30;$M='September';}
  if ($mon==10){$td=31;$M='October';}
  if ($mon==11){$td=30;$M='November';}                             //        and substr(date,7,4)='$year'
  if ($mon==12){$td=31;$M='December';}

$Day=substr($date,8,2);
     $Day=$Day+0;
    $Year=substr($date,0,4);

    $Date=$Day." ".$M." "." ".$Year;

    $hadd=$Data['hadd'];
    $padd=$Data['padd'];

$busy_deleting = isset($_GET["key"]) && $_GET["key"] == "confirm_delete";

// only show this when not deleting
$viewContact = "";
if ( ! ($busy_deleting) )
	$viewContact .="<center><h3>Contact details</h3></center>";

	db_conn('cubit');


	$i=0;
	$conpers="";


	$Sl="SELECT * FROM conpers WHERE con='$Data[id]' ORDER BY name";
	$Ry=db_exec($Sl) or errDie("Unable to get contacts from db.");

	if(pg_num_rows($Ry)>0) {

		$conpers="<h3>Contact Persons</h3>
		<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
		<tr><th>Name</th><th>Position</th><th>Tel</th><th>Cell</th><th>Fax</th><th>Email</th><th>Notes</th><th colspan=2>Options</th></tr>";

		while($cp=pg_fetch_array($Ry)) {
			$i++;
			$bgcolor=($i%2) ? TMPL_tblDataColor1 : TMPL_tblDataColor2;

			$conpers.="<tr bgcolor='$bgcolor'><td>$cp[name]</td><td>$cp[pos]</td><td>$cp[tell]</td><td>$cp[cell]</td><td>$cp[fax]</td><td>$cp[email]</td>
			<td>$cp[notes]</td><td><a href='conper-edit.php?id=$cp[id]&type=edit'>Edit</a></td><td><a href='conper-rem.php?id=$cp[id]'>Delete</a></td></tr>";
		}

		$conpers.="</table>";
	}

	extract($Data);

	if (isset($birthdate)) {
		list($bf_year, $bf_month, $bf_day) = explode("-", $birthdate);
		$birthdate_description = date("d F Y", mktime(0, 0, 0, $bf_day, $bf_month, $bf_year));
	} else {
		$birthdate_description = "";
	}

	$viewContact .= "
	<br>
	<center>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th colspan=4>Contact Information</th></tr>
	<tr class='bg-even'>
		<td width=120>First Name</td>
		<td width=210>$name</td>

		<td width=120>Office Phone</td>
		<td width=210>$tell_office</td>
	</tr>
	<tr class='bg-odd'>
		<td>Company/Last Name</td>
		<td>$surname</td>

		<td>Mobile</td>
		<td>$cell</td>
	</tr>
	<tr class='bg-even'>
		<td>Reports To</td>
		<td>$reports_to</td>
		
		<td>Home Phone</td>
		<td>$tell</td>
	</tr>
	<tr class='bg-odd'>
		<td>Lead Source</td>
		<td>$lead_source</td>

		<td>Other Phone</td>
		<td>$tell_other</td>
	</tr>
	<tr class='bg-even'>
		<td>Title</td>
		<td>$title</td>

		<td>Fax</td>
		<td>$fax</td>
	</tr>
	<tr class='bg-odd'>
		<td>Department</td>
		<td>$department</td>

		<td>E-mail</td>
		<td>$email</td>
	</tr>
	<tr class='bg-even'>
		<td>Birthdate</td>
		<td>$birthdate_description</td>

		<td>Other E-mail</td>
		<td>$email_other</td>
	</tr>
	<tr class='bg-odd'>
		<td>Account Name</td>
		<td>$accountname ($account_type)</td>

		<td>Assistant</td>
		<td>$assistant</td>
	</tr>
	<tr class='bg-even'>
		<td>&nbsp;</td>
		<td>&nbsp;</td>

		<td>Assistant Phone</td>
		<td>$assistant_phone</td>
	</tr>

	<tr><td>&nbsp;</td></tr>
	
	<tr>
		<th colspan=2>Physical Address</th>
		<th colspan=2>Postal Address</th>
	</tr>
	<tr class='bg-even'>
		<td colspan=2 align=left valign=top><xmp>$hadd</xmp></td>
		
		<td colspan=2 align=left><xmp>$padd</xmp></td>
	</tr>
	<tr class='bg-odd'>
		<td>City</td>
		<td>$padd_city</td>
		<td>City</td>
		<td>$hadd_city</td>
	</tr>
	<tr class='bg-even'>
		<td>State/Province</td>
		<td>$padd_state</td>
		<td>State/Province</td>
		<td>$hadd_state</td>
	</tr>
	<tr class='bg-odd'>
		<td>Postal Code</td>
		<td>$padd_code</td>
		<td>Postal Code</td>
		<td>$hadd_code</td>
	</tr>
	<tr class='bg-even'>
		<td>Country</td>
		<td>$padd_country</td>
		<td>Country</td>
		<td>$hadd_country</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<th colspan=2>Description</th>
	</tr>
	<tr class='bg-odd'>
		<td colspan=2 align=left><xmp>$description</xmp></td>
	</tr>
	
	<tr><td>&nbsp;</td></tr>";

// check if own entry own entry, and if it is, create the delete field, so the delete field doesn't display
// when it is not your contact
if ( $Data["by"] == USER_NAME || $user_admin) {
	$DeleteField = "<a class=nav href=\"view_con.php?key=confirm_delete&id=$Data[id]\">
				Delete Contact</a>";
} else {
	$DeleteField = "";
}

// only add the following when not deleting
if ( ! ($busy_deleting) ) {
	$viewContact .= "
	<tr>
		<td align=center colspan=4><font size=2><b>
			<a class=nav target=mainframe href=\"mod_con.php?id=$Data[id]\" onClick='setTimeout(window.close,50);' >Edit Contact</a> &nbsp;
			$DeleteField
		</b></font></td>
	</tr>
	<tr>
		<td align=center colspan=4><font size=2><b>
			<a class=nav target=mainframe href=\"conper-add.php?type=conn&id=$Data[id]\" onClick='setTimeout(window.close,50);' >Add Contact Person</a> &nbsp;
		</b></font></td>
	</tr>";
}

$viewContact .= "
</table>
$conpers
<p></center>";

return $viewContact;
}


// function that deletes a contact
function deleteContact() {
	global $_GET, $_SESSION;
	global $user_admin;

	$OUTPUT = "";

	if ( isset($_GET["key"]) && isset($_GET["id"]) ) {
		$id=$_GET["id"];
		$key=$_GET["key"];

		// first make sure it is this person's contact, or that the user is root
		if ( ! $user_admin ) {
			$rslt = db_exec("SELECT * FROM cons WHERE id='$id' AND
				( by='$_SESSION[USER_NAME]' )");
			if ( pg_num_rows($rslt) <= 0 ) {
				return "You are not allowed to delete this entry!";
			}
		}

		// check if a confirmation or deletion should occur (confirm_delete let's the cofirmation display)
		if ( $key == "confirm_delete" ) {
			$Sl="SELECT * FROM cons WHERE id='$id'";
			$Rl=db_exec($Sl) or errDie("Unable to get contact details.");
			$cdata=pg_fetch_array($Rl);

			$Sl="SELECT * FROM customers WHERE cusnum='$cdata[cust_id]'";
			$Ry=db_exec($Sl) or errDie("Unable to get customer from system.");

			if(pg_num_rows($Ry)>0) {
				return "The contact you are trying to delete still has a customer connected to it.\nRemove the customer first.";
			}


			$Sl="SELECT * FROM suppliers WHERE supid='$cdata[supp_id]'";
			$Ry=db_exec($Sl) or errDie("Unable to get supplier from system.");

			if(pg_num_rows($Ry)>0) {
				return "The contact you are trying to delete still has a supplier connected to it.\nRemove the supplier first.";
			}
			$OUTPUT .= "<font size=2>Are you sure you want to delete this entry:</font><br>";
			$OUTPUT .= viewContact();
			$OUTPUT .= "
				<table><tr><td align=center>
					<form method=post action='".SELF."'>
						<input type=hidden name=key value='delete'>
						<input type=hidden name=id value='$id'>
						<input type=submit value=yes>
						<input type=button value=no onClick='window.close();'>
					</form>
				</td></tr></table>";
		} else if ( $key == "delete" ) {
			// delete it !!!!!!!
			$rslt = db_exec("DELETE FROM cons WHERE id='$id' ");
			if ( pg_cmdtuples($rslt) <= 0 ) {
				$OUTPUT .= "Error Deleting Entry<br> Please check that it exists, else contact Cubit<br>";
			} else {
				$OUTPUT .= "<script> window.opener.parent.mainframe.location.reload(); window.close(); </script>";
			}
		}
	} else {
			$OUTPUT .= "<script> window.opener.parent.mainframe.location.reload(); window.close(); </script>";
	}

	return $OUTPUT;
}

?>
