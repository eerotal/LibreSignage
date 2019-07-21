<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

	use libresignage\common\php\Config;
	use libresignage\common\php\CSS;
	use libresignage\common\php\auth\Auth;

	Auth::web_auth(NULL, ['editor'], TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php CSS::req(['font-awesome']); ?>
		<link rel="stylesheet" href="/control/editor/css/main.css">
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/common/php/favicon.php'); ?>
		<title>LibreSignage Editor</title>
	</head>
	<body>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('NAV_PATH')); ?>
		<main class="container-fluid">
			<div class="container-main container-fluid w-100 h-100">
				<div class="container-fluid row mx-0 my-1">
					<div class="col-12">
						<div id="queueselector">
							<?php
								require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/php/queueselector.php');
							?>
						</div>
					</div>
					<div class="col-12">
						<div id="timeline"></div>
					</div>
				</div>
				<div class="container-fluid row mx-0 my-1">
					<div class="col-md-3 container-fluid pt-2" id="editor-col-l">
						<?php
							require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/php/controls.php');
							require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/php/buttons.php');
						?>
					</div>
					<div class="col-md container-fluid pt-2" id="editor-col-r">
						<div class="row">
							<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/php/preview.php'); ?>
						</div>
						<div class="row">
							<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/php/editor.php'); ?>
						</div>
					</div>
				</div>
				<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/popups/quick_help.php'); ?>
				<?php require_once(Config::config('LIBRESIGNAGE_ROOT').'/control/editor/popups/asset_uploader.php'); ?>
			</div>
		</main>
		<?php require_once(Config::config('LIBRESIGNAGE_ROOT').Config::config('FOOTER_PATH')); ?>
		<script src="/libs/ace-builds/src/ace.js"></script>
		<script src="/control/editor/js/main.js"></script>
	</body>
</html>
