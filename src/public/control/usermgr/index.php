<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
	use libresignage\common\php\CSS;
	use libresignage\common\php\auth\Auth;

	Auth::web_auth(NULL, ['admin'], TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<?php CSS::req(['font-awesome']); ?>
		<link rel="stylesheet" href="/control/usermgr/css/usermgr.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage User Manager</title>
	</head>
	<body>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('NAV_PATH')); ?>

		<div class="container">
			<div id="users-table"></div>
			<div id="user-controls">
				<button
					type="button"
					id="btn-create-user"
					class="btn btn-primary">
					<i class="fas fa-plus-circle"></i> New user
				</button>
				<button
					type="button"
					id="btn-create-user-passwordless"
					class="btn btn-primary">
					<i class="fas fa-plus-circle"></i> New passwordless user
				</button>
			</div>
		</div>

		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH')); ?>
		<script src="/control/usermgr/js/main.js"></script>
	</body>
</html>
