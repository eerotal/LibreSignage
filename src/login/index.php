<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');

	if (web_auth()) {
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
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/login/css/login.css">
		<title>LibreSignage Login</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="form-login-container">
				<h4 class="display-4 form-login-header">LibreSignage</br>Login</h4>
				<div class="alert alert-warning" <?php
					if (empty($_GET['failed'])) {
						echo 'style="display: none"';
					}?>>
					<span>Incorrect username or password!</span>
				</div>
				<div class="container form-login">
					<div class="form-group form-row">
						<label for="input-user"
							class="col-3 col-form-label">
							Username
						</label>
						<div class="col">
							<input class="form-control"
								id="input-user"
								type="text"
								name="user"
								placeholder="Username">
						</div>
					</div>
					<div class="form-group form-row">
						<label for="input-pass"
							class="col-3 col-form-label">
							Password
						</label>
						<div class="col">
							<input class="form-control"
								id="input-pass"
								type="password"
								name="pass"
								placeholder="Password">
						</div>
					</div>
					<div class="form-group form-row">
						<div class="col">
							<input class="btn btn-primary w-100"
								id="btn-login"
								type="submit"
								value="Login">
						</div>
					</div>
					<div class="form-group form-row">
						<div class="col">
							<input class="form-check-input"
								type="checkbox"
								id="checkbox-perm-session">
							<label class="form-check-label"
								for="checkbox-perm-session">
								Start a display session.
							</label>
						</div>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);

			js_include_jquery();
			js_include_popper();
			js_include_bootstrap();
		?>
		<script src="/common/js/util.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/login/js/login.js"></script>
	</body>
</html>
