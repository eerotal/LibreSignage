<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
?>

<footer class="container-fluid footer d-flex align-items-center">
	<div class="container-fluid">
		<span class="text-muted">
			LibreSignage &bull; Copyright Eero Talus 2018
			<?php
				if (date('Y') != '2018') {
					echo '-'.date('Y');
				}
			?>
			</br>
			LibreSignage is free and open source software
			licensed under the <a href="<?php echo ABOUT_PAGE ?>">
			BSD 3-clause license</a>.
		</span>
	</div>
</footer>
