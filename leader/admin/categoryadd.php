<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Create Category");

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
			<h1 class="text-center">Create Category</h1>
			<br>
			<?php if (isset($_GET["error"])) {?>
			<div class="alert alert-danger">
				There was an error creating the category. The error was: <b><?php
				switch ($_GET["error"]) {
					case 0: echo("There was an internal server error"); break;
					case 1: echo("Please specify a name for the category"); break;
					case 2: echo("The category name cannot be only symbols"); break;
					case 3: echo("A category with that name already exists"); break;
				}
				?></b>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<br>
			<form action="docategoryadd.php" method="POST" class="form" style="margin: 0px auto; width: 270px" autocomplete="off">
				<fieldset>
					<span class="help-block">Category Name:</span>
					<input type="text" class="input-xlarge" name="name" id="name" placeholder="Category" autocomplete="off">
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-primary">Create Category</button>
							<a href="categories.php" class="btn">Cancel</a>
						</div>
					</div>
				</fieldset>
			</form>
			<div class="text-center">
				<i>Note: Categories will not show up in-game until edited in to MBPPrefs.cs</i>
			</div>
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
