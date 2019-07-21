<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
	use libresignage\common\php\auth\Auth;
	use libresignage\common\php\CSS;

	if (Auth::web_auth()) {
		header('Location: '.Config::config('LOGIN_LANDING'));
		exit(0);
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php CSS::req(['font-awesome']); ?>
		<link rel="stylesheet" href="/login/css/login.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage Login</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="form-login-container">
				<img class="ls-logo" src="/assets/images/logo/libresignage_text.svg"></img>
				<div class="alert alert-warning" <?php
					if (empty($_GET['failed'])) {
						echo 'style="display: none"';
					}?>>
					<span>Incorrect username or password!</span>
				</div>
				<div class="container form-login">
					<div class="form-group form-row">
						<label for="input-user"
							class="col col-form-label">
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
							class="col col-form-label">
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
					<hr/>
					<div class="form-group form-row m-0">
						<div class="col text-left">
							<a class="link-nostyle"
								data-toggle="collapse"
								href="#collapse-adv"
								aria-expanded="false"
								aria-controls="collapse-adv">
								<i class="fas fa-angle-right"></i> Advanced
							</a>
						</div>
					</div>
					<div class="form-group form-row">
						<div id="collapse-adv" class="col collapse">
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
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH')); ?>
		<script src="/login/js/main.js"></script>
	</body>
</html>
