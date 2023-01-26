<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Modify Custom Categories");

	//-------------------------------------------------------------------
	// Navbar start

	navbarCreate("MBP Admin");
	navbarAddSet("default");
	if ($access > 1) {
		navbarAddSet("admin");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	}
	navbarAddItem("Access: " . accessTitle($access));
	navbarEnd();

	// Navbar end
	//------------------------------------––––––––––––––––––––––––––––---

if ($access > 1) {
	?>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Modify Custom Categories</h1>
			<br>
			<?php if (isset($_GET["error"])) {?>
			<div class="alert alert-danger">
				There was an error deleting the category. The error was: <b><?php
				switch ($_GET["error"]) {
					case 0: echo("There was an internal server error"); break;
				}
				?></b>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<?php if (isset($_GET["success"])) {?>
			<div class="alert alert-success">
				<?php
				switch ($_GET["success"]) {
					case 1: echo("The category was deleted successfully"); break;
					case 2: echo("The category was created successfully"); break;
				}
				?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<br>
			<a href="categoryadd.php" class="btn btn-success">Create Category</a>
			<table class="table table-striped table-bordered" style="margin-top: 10px">
				<tr>
					<th>Id</th>
					<th>Name</th>
					<th>Delete</th>
				</tr>
			<?php

$query = pdo_prepare("SELECT * FROM `categories` ORDER BY `id` ASC");
$result = $query->execute();
if ($result) {
	$i = 0;
	while (($row = $result->fetch())) {
		$i ++;
		echo("<tr><td>{$i}.</td><td>");
		echo($row["display"]);
		echo("</td><td><a class=\"btn btn-danger\" href=\"docategorydelete.php?category={$row['id']}\">Delete Category</a>");
		echo("</td></tr>");
	}
}

			?>
			</table>
		</div>
	</div>
</div>

	<?php
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
