<?php
	require("./header.php");
	require("./misc.php");
	require("../Database.php");
?>

<script src="header.js" type="text/javascript"></script>
<script type="text/javascript">
	markActive("marbles");
</script>

<h1>PlatinumQuest Demo</h1>
<h2>Current Marbles in Use</h2>

<table class="table table-striped">
	<tr>
		<th>Marble</th>
		<th>Count</th>
	</tr>
	<?php
		/// Find out what Marbles are being used by what players, and report the numbers.

		$query = $db->prepare("SELECT COUNT(*) AS `count`,`shape_file` FROM `@_user_marble_selection` JOIN `@_marble_selections` ON `@_marble_selections`.id=`@_user_marble_selection`.marble_selection_id 
								LEFT JOIN `@_users` ON `@_users`.id=`@_marble_selections`.userid GROUP BY `@_marble_selections`.shape_file ORDER BY `count` ASC");
		$query->execute();

		while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
			$count = $row["count"];
			$marble = pathinfo($row["shape_file"], PATHINFO_FILENAME);

			// split into multiple words based on camel case.
			$marble = splitCamelCaseStringToWordString($marble);

			echo("<tr><td>" . $marble . "</td><td> " . $count . "</td></tr>\n");
		}
	?>
</table>
<br>

<?php
	/// Find out which marble has been used the most often by grabbing all of the use_count
	/// for each marble and adding them up.
	/// The one with the most gets reported.

	$query = $db->prepare("SELECT `shape_file`,`use_count` FROM `lw3qp_marble_selections`");
	$query->execute();

	$dictionary = [];
	while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
		$dictionary[$row["shape_file"]] += $row["use_count"];
	}
	$max = max($dictionary);
	$marble = array_search($max, $dictionary);
	$marble = pathinfo($marble, PATHINFO_FILENAME);
	$marble = splitCamelCaseStringToWordString($marble);

	echo("<p>The most used marble is the <b>{$marble}</b> marble with <b>{$max}</b> total uses.</p>\n");
?>

<?php
	require("./footer.php");
?>