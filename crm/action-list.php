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

require("settings.php");
require("libs/crm.lib.php");

	$OUTPUT=list_cats();

	$OUTPUT.="<p>
	<table border=0 cellpadding='2' cellspacing='1'>
	<tr><th>Quick Links</th></tr>
	<tr bgcolor='".TMPL_tblDataColor1."'><td><a href='action-add.php'>Add Action</a></td></tr>
	<script>document.write(getQuicklinkSpecial());</script>
	<tr bgcolor='".TMPL_tblDataColor1."'><td><a href='index.php'>My Business</a></td></tr>
	</table>";

require("template.php");

function list_cats() {

	db_conn('crm');
	$Sl="SELECT * FROM actions ORDER BY action";
	$Ry=db_exec($Sl) or errDie("Unable to list actions.");

	$out="<h3>Actions</h3>
	<table cellpadding='".TMPL_tblCellPadding."' cellspacing='".TMPL_tblCellSpacing."'>
	<tr><th>Actions</th><th colspan=2>Options</th></tr>";

	$i=0;

	while($tcatdata=pg_fetch_array($Ry)) {
		$i++;

		$bgcolor=($i%2) ? TMPL_tblDataColor1 : TMPL_tblDataColor2;

		$out.="<tr bgcolor='$bgcolor'><td>$tcatdata[action]</td><td><a href='action-rem.php?id=$tcatdata[id]'>Remove</a></td></tr>";

	}

	$out.="</table>";

	return $out;
}

?>