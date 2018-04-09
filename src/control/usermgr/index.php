<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	auth_is_authorized(array('admin'), NULL, TRUE);
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
		<link rel="stylesheet" href="/control/usermgr/css/usermgr.css">
		<title>LibreSignage User Manager</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="container text-center">
				<div class="container-fluid">
					<div class="row usr-table-row">
						<div class="usr-table-col col-1">
							#
						</div>
						<div class="usr-table-col col-2">
							User
						</div>
						<div class="usr-table-col col-3">
							Groups
						</div>
						<div class="usr-table-col col-3">
							Information
						</div>
						<div class="usr-table-col col-3">
						</div>
					</div>
				</div>
				<div id="users-table" class="container-fluid mt-3">
				</div>
				<div class="container-fluid mt-3 md-5">
					<div class="usr-table-row">
						<div class="usr-table-col">
							<input type="button"
								class="btn btn-primary"
								value="Create user"
								onclick="usermgr_create()">
							</input>
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
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/user.js"></script>
		<script src="/control/usermgr/js/usermgr_ui.js"></script>
	</body>
</html>
