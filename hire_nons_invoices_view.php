<?php

require ("../settings.php");

$OUTPUT = display();

require ("../template.php");

function display()
{
	extract ($_REQUEST);

	$fields = array();
	$fields["search"] = "";

	extract ($fields, EXTR_SKIP);

	if (is_numeric($search)) {
		$invnum_w = "OR invnum='$search'";
	} else {
		$invnum_w = "";
	}

	$sql = "SELECT invid, invnum, cusname, total, hire_invid, accepted, hire_invnum
			FROM cubit.nons_invoices
			WHERE done='y' AND hire_invid>0 AND cusname ILIKE '%$search%' $invnum_w";
	$inv_rslt = db_exec($sql) or errDie("Unable to retrieve hire invoices.");

	$inv_out = "";
	while ($inv_data = pg_fetch_array($inv_rslt)) {
		if ($inv_data["accepted"] != "note") {
			$cnote = "
			<td>
				<a href='hire-invoice-note.php?invid=$inv_data[invid]'>
					Credit Note
				</a>
			</td>";
		} else {
			$cnote = "<td>&nbsp;</td>";
		}

		$inv_out .= "<tr class='".bg_class()."'>
			<td>
				<a href='javascript:printer(\"hire/hire_note_reprint.php?invid=$inv_data[hire_invid]\")'>
					H$inv_data[hire_invnum]
				</a>
			</td>
			<td>$inv_data[invnum]</td>
			<td>$inv_data[cusname]</td>
			<td>".CUR.sprint($inv_data["total"])."</td>
			<td><a href='javascript:popupOpen(\"nons-invoice-reprint.php?invid=$inv_data[invid]\")'>Reprint</a></td>
			$cnote
		</tr>";
	}

	if (empty($inv_out)) {
		$inv_out = "<tr class='".bg_class()."'>
			<td colspan='5'><li>Please enter customer name or hire no.</li></td>
		</tr>";
	}

	$OUTPUT = "<h3>View Hire Invoices</h3>
	<form method='post' action='".SELF."'>
	<table ".TMPL_tblDflts.">
		<tr>
			<th colspan='2'>Enter Hire No or Customer Name</th>
		</tr>
		<tr class='".bg_class()."'>
			<td><input type='text' name='search' value='$search' /></td>
			<td><input type='submit' value='Search' style='width: 100%' /></td>
		</tr>
	</table>
	</form>
	<p></p>
	<table ".TMPL_tblDflts.">
		<tr>
			<th>Hire No</th>
			<th>Invoice No</th>
			<th>Customer</th>
			<th>Total</th>
			<th colspan='2'>Options</th>
		</tr>
		$inv_out
	</table>";

	return $OUTPUT;
}