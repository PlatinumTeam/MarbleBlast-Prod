<?php
	require("./header.php");
	require("../Database.php");
?>

<script src="header.js" type="text/javascript"></script>
<script src="levels.js" type="text/javascript"></script>
<script type="text/javascript">
	markActive("levels");
</script>

<h1>PlatinumQuest Demo</h1>
<h2>Levels</h2>

	<label for="uniqueCheck"><input type="checkbox" id="uniqueCheck"> Unique Tops</label>
<select class="selectMenu">
	<option value="-1">Select Level Here</option>
	<?php
		$query = $db->prepare("SELECT `id`,`basename`,`name` FROM `@_missions_official` ORDER BY `name` ASC");
		$query->execute();

		while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
			 // Create options for each level
			$val = $row["id"];
			$text = $row["name"];
			echo("   <option value=\"{$val}\">{$text}</option>\n");
		}
	?>
</select>

<br>
<br>
<div class="levelDataMenu">
</div>

<?php
	require("./footer.php");
?>