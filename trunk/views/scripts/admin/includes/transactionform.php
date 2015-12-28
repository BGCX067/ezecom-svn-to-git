<form action="/admin/transactionlist/" method="POST">
<table>	
	<tr>
		<td>Search Tracking Code or Email</td>
		<td><input type="text" name="q" /></td>	
	</tr>
	<tr>
		<td>Or Filter By:</td>
		<td>&nbsp;</td>	
	</tr>

	<tr>
		<td>Payment Method</td>
		<td>
			<select name="pm" />
				<option value=""></option>
				<option value="1">GCASH</option>
				<option value="2">BDO</option>
				<option value="3">BPI</option>
				<option value="4">LBC</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Status</td>
		<td>
	<select name="st" />
		<option value=""></option>
		<option value="1">Pending</option>
		<option value="2">In Process</option>
		<option value="3">Shipped</option>
	</select>
		</td>
	</tr>
</table>	
	<input type="submit" value="Search" />
</form>
