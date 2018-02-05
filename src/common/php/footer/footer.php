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
			<a href="/about">LibreSignage</a>
			is free and open source software.
		</span>
	</div>
</footer>
