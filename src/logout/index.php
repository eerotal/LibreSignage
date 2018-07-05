<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/css.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			css_include(['bootstrap']);
		?>
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/logout/css/logout.css">
		<title>LibreSignage Logout</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="container-fluid logout-container">
				<h4 class="display-4">Logged out!</h4>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);

			js_include(['jquery', 'popper', 'bootstrap']);
		?>

		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/util.js"></script>
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/logout/js/logout.js"></script>
	</body>
</html>
