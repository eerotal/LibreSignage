<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
	use libresignage\common\php\CSS;
	use libresignage\common\php\auth\Auth;

	Auth::web_auth(NULL, ["display"], TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="/app/css/display.css">
		<?php CSS::req(['font-awesome']); ?>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage Display</title>
	</head>
	<body>
		<main role="main" class="container-fluid">
			<div id="display"></div>
			<div id="splash">
				<img
					src="/assets/images/logo/libresignage_text.svg"
					alt="LibreSignage logo.">
			</div>
			<div id="controls">
				<div class="left">
					<i class="fas fa-angle-left"></i>
				</div>
				<div class="center"></div>
				<div class="right">
					<i class="fas fa-angle-right"></i>
				</div>
			</div>
		</main>
		<script src="/app/js/main.js"></script>
	</body>
</html>
