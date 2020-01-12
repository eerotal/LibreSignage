<?php

use libresignage\common\php\Config;

?>

<footer class="container-fluid footer d-flex align-items-center">
	<div class="container-fluid">
		<span>
			LibreSignage <?php echo Config::config('LS_VER'); ?> &bull;
			Copyright <?php echo '2018-'.date('Y'); ?> Eero Talus and contributors.
			</br>
			<a href="<?php echo Config::config('README_PAGE'); ?>">LibreSignage</a>
			is free and open source software.
		</span>
	</div>
</footer>
