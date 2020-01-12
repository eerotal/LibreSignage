<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="/logout/css/logout.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage Logout</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="container-fluid logout-container">
				<h4 class="display-4">Logged out!</h4>
			</div>
		</main>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH')); ?>
		<script src="/logout/js/main.js"></script>
	</body>
</html>
