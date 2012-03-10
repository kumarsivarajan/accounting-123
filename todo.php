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

foreach ($HTTP_GET_VARS as $key=>$value) {
	$HTTP_POST_VARS[$key] = $value;
}

# decide what to do
if (isset($HTTP_POST_VARS["key"])) {
	switch ($HTTP_POST_VARS["key"]) {
        case "account_info":
			$OUTPUT = account_info($HTTP_POST_VARS);
			break;
		case "archive":
			$OUTPUT = archive();
			break;
        default:
			$OUTPUT = order($HTTP_POST_VARS);
	}
} elseif (isset($HTTP_GET_VARS["id"])) {
        # Display default output
	$HTTP_POST_VARS["id"]=$HTTP_GET_VARS["id"];
	if (isset($HTTP_GET_VARS["tripid"])) {$HTTP_POST_VARS["tripid"]=$HTTP_GET_VARS["tripid"];}
	if (isset($HTTP_GET_VARS["proid"])) {$HTTP_POST_VARS["proid"]=$HTTP_GET_VARS["proid"];}
	if (isset($HTTP_GET_VARS["proid"])) {$HTTP_POST_VARS["busy"]="No";}
	$OUTPUT = order($HTTP_POST_VARS);
	}

else {
        # Display default output

	$OUTPUT = order($HTTP_POST_VARS);

}

# get templete
require("template.php");

function order($HTTP_POST_VARS,$errors="")
{
	$Out="";
        # get vars
	foreach ($HTTP_POST_VARS as $key => $value) {
		$$key = $value;
	}

	db_conn("cubit");
	$date=date("Y-m-d");

	pglib_transaction("begin");

	$cdate=date("D, d M Y");
	$datemade=date("Y-m-d");
	$timemade=date("H:i");
	$op=USER_NAME;

	if(!isset($con)){$con='';}
	if(!isset($name)){$name='';}
	if(!isset($notes)){$notes='';}
	if(!isset($comp)){$comp='';}

	$Pals="";
	$Sl = "SELECT * FROM todos WHERE com='No' and op='$op' ORDER BY datemade DESC,timemade DESC";
	$Rs = db_exec ($Sl) or errDie ("Unable to view clients");
	$numrow=pg_numrows($Rs);
	if (pg_numrows ($Rs) < 1) {$Trips="";}
	else
	{
		$i=0;
		while($Tp = pg_fetch_array($Rs))
		{
			$i++;
			$Tpdes=substr($Tp['timemade'],0,2).":".substr($Tp['timemade'],2,2);
			$bgColor = ($i % 2) ? TMPL_tblDataColor2 : TMPL_tblDataColor1;
			$Pals .= "<tr bgcolor='$bgColor'><td>$Tp[datemade]</td><td>$Tpdes</td><td>$Tp[des]</td><td><input type=checkbox name=done[$Tp[id]] OnClick='javascript:document.form.submit();'></td></tr>";
		}
	}

	pglib_transaction("commit");

	$account_dets =
	"<form action='".SELF."' method=post name=form>
	<input type=hidden name=key value=account_info>
	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr bgcolor='".TMPL_tblDataColor2."'><th colspan=3 align=left><h3>TO DO LIST ($numrow)</h3></th></tr>
	 <tr bgcolor='".TMPL_tblDataColor2."'><td width='20%'>CURRENT DATE</td><td>$cdate</td></tr>
	</table>

	<table border=0 cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	 <tr><th>DATE</th><th>TIME</th><th>DESCRIPTION</th><th>DONE</th></tr>
	 <tr bgcolor='".TMPL_tblDataColor1."'><td><input type=hidden name=datemade value='$datemade'>$datemade</td><td><input type=hidden name=timemade value='$timemade'>$timemade</td><td><input type=text size=20 name=des value=''></td><td> &nbsp; </td></tr>

	 $Pals

	 <tr><td valign=center><input type=submit value='Update >>>'></td></tr>
	</table>
	</form>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."' width=30%>
	 <tr><td><br><br></tr>
	 <tr><th>Quick Links</th></tr>
	 <tr bgcolor='".TMPL_tblDataColor1."'><td><a href='index_die.php'>Diary</td>
	 <tr bgcolor='".TMPL_tblDataColor1."'><td><a href='main.php'>Main Menu</td>
	 </tr>
	</table>

	<script>
		setOnload
	</script>";
	return $account_dets;

}

# Write Account Info
function account_info($HTTP_POST_VARS)
{
	$Out="";
	#get & send vars
	foreach ($HTTP_POST_VARS as $key => $value) {

		$$key = remval($value);
		$Out .="<input type=hidden name=$$key value='$value'>";
	}

	# validate input
	require_lib("validate");
	$v = new  validate ();

        # display errors, if any
	if ($v->isError ()) {
		$errors = "";
		$Errors = $v->getErrors();
		foreach ($Errors as $e) {
			$errors .= "<li class=err>".$e["msg"];
		}
		$errors .= "<input type=hidden name=errors value='$errors'>";
		return order($HTTP_POST_VARS,$errors);
	}

	if (isset($cc)){$com="Yes";} else {$com="No";}
	$op=USER_NAME;
	
	db_conn("cubit");

	if ((strlen($des)>0))
	{
		$Sl = "INSERT INTO todos (datemade,timemade,op,des,com) VALUES ('$datemade','$timemade','$op','$des','$com')";
		$Rs = db_exec ($Sl) or errDie ("Unable to update database.", SELF);
	}

	if(isset($done))
	{

		#get & send vars
		foreach ($done as $key => $value) {
			$Sl = "UPDATE todos SET com='Yes' WHERE id='$key'";
			$Rs = db_exec ($Sl) or errDie ("Unable to update database.", SELF);

		}
	}

	return order($HTTP_POST_VARS);

}

?>