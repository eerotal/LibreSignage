<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
?>

<footer class="container-fluid footer d-flex align-items-center">
	<div class="container-fluid">
		<span>
			LibreSignage <?php echo LS_VER; ?> &bull;
			Copyright Eero Talus 2018
			<?php if (date('Y') != '2018') { echo '-'.date('Y'); } ?>
			</br>
			<a href="<?php echo ABOUT_PAGE; ?>">LibreSignage</a>
			is free and open source software.
		</span>
	</div>
</footer>
