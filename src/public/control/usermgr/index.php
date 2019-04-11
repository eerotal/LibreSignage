<?php
	require_once(LIBRESIGNAGE_ROOT.'/common/php/config.php');
	require_once(LIBRESIGNAGE_ROOT.'/common/php/css.php');
	require_once(LIBRESIGNAGE_ROOT.'/common/php/auth/auth.php');
	web_auth(NULL, array('admin'), TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<?php require_css(['font-awesome']); ?>
		<link rel="stylesheet" href="/control/usermgr/css/usermgr.css">
		<?php require_once(LIBRESIGNAGE_ROOT.'/common/php/favicon.php'); ?>
		<title>LibreSignage User Manager</title>
	</head>
	<body>
		<?php require_once(LIBRESIGNAGE_ROOT.NAV_PATH); ?>

		<div class="container">
			<div id="users-table"></div>
			<div id="user-controls">
				<button
					type="button"
					id="btn-create-user"
					class="btn btn-primary">
					<i class="fas fa-plus-circle"></i> Create user
				</button>
			</div>
		</div>

		<?php require_once(LIBRESIGNAGE_ROOT.FOOTER_PATH); ?>
		<script src="/control/usermgr/js/main.js"></script>
	</body>
</html>
