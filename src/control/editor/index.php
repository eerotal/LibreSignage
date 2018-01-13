<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/login/auth_check.php');
	session_start();
	check_authorized(true);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/global_css/footer.css">
		<link rel="stylesheet" href="/control/css/editor.css">
		<title>LibreSignage Editor</title>
	</head>
	<body class="bg-dark">
		<main role="main" class="container-fluid h-100">
			<div class="container-main container-fluid w-100 h-100 text-muted">
				<?php
					require_once($_SERVER['DOCUMENT_ROOT'].
						'/control/editor/slidelist.php');
				?>
				<div class="editor row container-fluid">
					<div class="col-4">
						<label for="slide-name">Name</label>
						<input class="form-control" id="slide-name">
						<label for="slide-name">Time</label>
						<input class="form-control" id="slide-time">
					</div>
					<div class="col-8">
						<label for="slide-input">Markup</label>
						<textarea class="form-control" id="slide-input">
						</textarea>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
		<script src="/global_js/util.js"></script>
		<script src="/global_js/api_interface/api.js"></script>
		<script src="/control/editor/js/editor.js"></script>
	</body>
</html>
