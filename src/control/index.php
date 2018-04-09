<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	auth_is_authorized(NULL, NULL, TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/nav.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/control/css/control.css">
		<title>LibreSignage Control Panel</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="row container-fluid mx-auto">
				<div class="col-md-12 header-col">
					<h1>Welcome to LibreSignage!</h1>
				</div>
			</div>
			<div class="row ctrl-panel-row container-fluid">
				<div class="col-md-6 ctrl-panel-col">
					<h4>Your quota</h4>
					<div id="user-quota-cont">
					</div>
				</div>
				<div class="col-md-6 ctrl-panel-col cont-info-primary">
					<h4>Problems using LibreSignage?</h4>
					<p>This LibreSignage instance is
					maintained by <?php echo ADMIN_NAME; ?>.
					If you have any problems using
					LibreSignage, please email the admin at
					<a href="mailto: <?php echo ADMIN_EMAIL; ?>">
					<?php echo ADMIN_EMAIL; ?></a>.</p>
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
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/control/js/control.js"></script>
	</body>
</html>
