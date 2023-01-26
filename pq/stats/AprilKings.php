<?php
define("PQ_RUN", true);
require_once("../leader/Framework.php");

$query = $db->prepare("
SELECT * FROM ex82r_april20_kings
JOIN prod_joomla.bv2xj_users ON ex82r_april20_kings.user_id = bv2xj_users.id
ORDER BY total_scores DESC, last_update DESC
");
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<html>
<head>
	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<style>
        html, body {
            min-height: 100%;
        }
        html {
            border: 1em solid rgb(0, 92, 230);
        }
        body {
            border: 0.5em solid rgb(123, 183, 242);
        }
        tr.current {
            background-color: rgb(123, 183, 242) !important;
        }
	</style>
    <title>April Fools King of the Hill Leaderboard</title>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="col-12 text-center">
			<h1>April Fools King of the Hill Leaderboard</h1>
			<h2>Event is over, thanks for playing!</h2>
		</div>
	</div>
    <br>
    <br>
	<div class="row">
        <div class="col-md-3 col-lg-3 col-xl-3">
            <h2>How to Enter</h2>
            <ul>
                <li>Get a score on any LB level online to have a chance of becoming king</li>
                <li>Chance of winning all WRs goes down the more times you have played that level</li>
                <li>Score increases for every score someone submits while you are king</li>
            </ul>
        </div>
		<div class="col-md-9 col-lg-9 col-xl-9">
			<table class="table table-bordered table-striped">
				<tr>
					<th>#</th>
					<th>User</th>
					<th>Score</th>
					<th>Last Updated</th>
				</tr>
				<?php
					$sum = 0;
                    $i = 1;
                    $currentKing = (int)$db->getSetting("april20_uid");
					foreach ($result as $row) {
						?>
				<tr<?= ($row["user_id"] === $currentKing ? " class='current'" : "") ?>>
					<td><?= $i ?></td>
					<td><?= $row["name"] ?></td>
					<td><?= $row["total_scores"] ?></td>
					<td><?= $row["last_update"] ?></td>
				</tr>
						<?php
                        $i ++;
                        $sum += $row["total_scores"];
					}
				?>
                <tr>
	                <td></td>
	                <td>Total</td>
	                <td><?= $sum ?></td>
	                <td></td>
                </tr>
			</table>
		</div>
    </div>
</div>
</body>
</html>

