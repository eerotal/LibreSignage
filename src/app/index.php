<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	/*
	*  Try to authenticate using an authentication token
	*  provided via the GET parameter 'tok'. This is only used
	*  in the display page because standalone clients need to
	*  authenticate without user intervention. If the 'tok'
	*  parameter doesn't exist, fall back to the normal auth
	*  system ($wa_tok = NULL).
	*/
	$wa_tok = NULL;
	if (!empty($_GET['tok'])) {
		$wa_tok = $_GET['tok'];
	}
	web_auth(NULL, array("display"), TRUE, $wa_tok);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet"
			href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css"
			integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy"
			crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/app/css/animations.css">
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
		<script src="/common/js/slidelist.js"></script>
		<script src="/common/js/queue.js"></script>
		<script src="/app/js/display.js"></script>
	</body>
</html>
