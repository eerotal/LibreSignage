<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');
	use libresignage\common\php\Config;
?>

<!-- Quick help popup for the LibreSignage editor. -->
<div id="quick-help" class="popup">
	<?php require_once(
		Config::config('LIBRESIGNAGE_ROOT').
		Config::config('DOC_HTML_DIR').
		'/keyboard_shortcut_cheatsheet.html'
	); ?>
	<?php require_once(
		Config::config('LIBRESIGNAGE_ROOT').
		Config::config('DOC_HTML_DIR').
		'/markup.html');
	?>
</div>
