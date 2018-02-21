<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');

	session_start();
	auth_init();
	auth_is_authorized(NULL, NULL, TRUE);

	// Load the documentation list.
	$docs = @scandir(LIBRESIGNAGE_ROOT.DOC_HTML_DIR);
	if ($docs === FALSE) {
		throw new Exception('Failed to scan docs dir.');
	}
	$docs = array_diff($docs, array('.', '..'));

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
			} elseif ($d == $_GET['doc']) {
				/*
				*  No file extension, compare
				*  original strings.
				*/
				$found = TRUE;
				break;
			}
		}
		if (!$found) {
			// Doc not found.
			header('Location: '.ERROR_PAGES.'/404');
			exit(0);
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
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/doc/css/doc.css">
		<title>LibreSignage Documentation</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="row doc-row">
				<div class="col"><?php
					include(LIBRESIGNAGE_ROOT.
						DOC_HTML_DIR.'/'.
						$include_file);
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
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
	</body>
</html>
