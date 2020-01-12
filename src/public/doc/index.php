<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
	use libresignage\common\php\auth\Auth;
	use libresignage\common\php\ErrorHandler;

	Auth::web_auth(NULL, NULL, TRUE);

	// Load the documentation list.
	$docs = scandir(
		Config::config('LIBRESIGNAGE_ROOT').
		Config::config('DOC_HTML_DIR')
	);
	if ($docs === FALSE) { throw new \Exception('Failed to scan docs dir.'); }
	$docs = array_diff($docs, ['.', '..']);

	if (!empty($_GET['doc'])) {
		$found = FALSE;
		$dot_pos = FALSE;
		foreach ($docs as $d) {
			$pos = strrpos($d, '.html');
			if ($pos == strlen($_GET['doc'])) {
				// Remove file extension and compare.
				if (substr($d, 0, $pos) == $_GET['doc']) {
					$found = TRUE;
					break;
				}
			} else if ($d == $_GET['doc']) {
				// No file extension, compare original strings.
				$found = TRUE;
				break;
			}
		}
		if (!$found) {
			ErrorHandler::handle(
				ErrorHandler::NOT_FOUND,
				new \Exception('No such documentation file.')
			);
		}
		$include_file = $_GET['doc'].'.html';
	} else {
		$include_file = 'index.html';
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="/doc/css/doc.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage Documentation</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="row doc-row">
				<div class="col"><?php
					include(
						Config::config('LIBRESIGNAGE_ROOT').
						Config::config('DOC_HTML_DIR').
						'/'.$include_file
					);
				?></div>
			</div>
			<div class="row doc-row">
				<div class="col"><?php
					if ($include_file != 'index.html') {
						echo '<a href="/doc">';
						echo '	&lt;&lt; Documentation Index';
						echo '</a>';
					}
				?></div>
			</div>
		</main>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH')); ?>
		<script src="/doc/js/main.js"></script>
	</body>
</html>
