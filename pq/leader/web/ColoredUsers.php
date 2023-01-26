<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$query = JoomlaSupport::db()->prepare("SELECT * FROM bv2xj_users WHERE hasColor = 1 ORDER BY username ASC");
$query->execute();

?>
<table>
	<tr>
		<th>Username</th>
		<th>Display Name</th>
		<th>Color</th>
	</tr>
<?php

while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	?>
	<tr>
		<td><?= $row["username"] ?></td>
		<td><?= $row["name"] ?></td>
		<td><pre><?= $row["colorValue"] ?></pre></td>
	</tr>
	<?php
}
?>
</table>
