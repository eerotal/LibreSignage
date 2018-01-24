<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	session_start();
	if (auth_is_authorized()) {
		header('Location: '.LOGIN_LANDING);
		exit(0);
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/login/css/login.css">
		<title>LibreSignage Login</title>
	</head>
	<body class="bg-dark">
		<main role="main" class="container-fluid h-100">
			<div class="form-login-container text-muted">
				<h4 class="display-4 form-login-header">LibreSignage Login</h4>
				<div class="alert alert-warning" <?php
					if (empty($_GET['failed'])) {
						echo 'style="display: none"';
					}?>>
					<span>Incorrect username or password!</span>
				</div>
				<form class="container form-login" action="/login/login_form.php" method="post">
					<div class="form-group form-row">
						<label for="input-user" class=" col-3 col-form-label">Username</label>
						<div class="col">
							<input class="form-control" id="input-user" type="text" name="user" placeholder="Username">
						</div>
					</div>
					<div class="form-group form-row">
						<label for="input-pass" class="col-3 col-form-label">Password</label>
						<div class="col">
							<input class="form-control" id="input-pass" type="password" name="pass" placeholder="Password">
						</div>
					</div>
					<div class="form-group form-row">
						<div class="col">
							<input class="btn btn-primary w-100" type="submit" value="Login">
						</div>
					</div>
				</form>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
	</body>
</html>
