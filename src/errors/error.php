<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="/errors/css/error.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title><?php echo $ERROR_PAGE_HEADING; ?></title>
	</head>
	<body>
		<main role="main" class="container-fluid">
			<div id="container-error" class="container">
				<h1 class="display-3 text-center"><?php echo $ERROR_PAGE_HEADING; ?></h1>
				<p class="lead text-center"><?php echo $ERROR_PAGE_TEXT; ?></p>
			</div>
		</main>
		<?php
			require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH'));
		?>
	</body>
</html>
