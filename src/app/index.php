<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	auth_is_authorized(array("display"), NULL, TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/app/css/renderer.css">
		<link rel="stylesheet" href="/app/css/display.css">
		<link rel="stylesheet" href="/common/css/footer_minimal.css">
		<link rel="stylesheet" href="/common/css/markup.css">
		<title>LibreSignage Display</title>
	</head>
	<body>
		<main role="main" class="container-fluid">
			<div id="display"></div>
		</main>
		<?php
			js_include_jquery();
			js_include_popper();
			js_include_bootstrap();
		?>

		<script src="/common/js/util.js"></script>
		<script src="/common/js/slide.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/common/js/markup.js"></script>
		<script src="/app/js/loader.js"></script>
		<script src="/app/js/renderer.js"></script>
	</body>
</html>
