<div class="screenlist">
	<div class="container-fluid row btn-row">
		<div class="col-12 d-flex flex-wrap">
			<?php
				require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');

				$content = array_diff(scandir(LIBRESIGNAGE_ROOT.CONTENT_DIR),
							array('.', '..'));
				$content = array_values($content);

				$i = 0;
				while ($i < count($content)) {
					if (substr($content[$i], 0, 1) == '.') {
						continue;
					}

					echo '<button type="button" class="btn btn-primary btn-screen" data-toggle="button"';

					echo 'id="screen-btn-'.$content[$i].'"';
					echo 'onclick="screen_show(\''.$content[$i].'\')">';

					echo 'Screen '.$i.'</button>';
					$i++;
				}
			?>
		</div>
	</div>
	<div class="container-fluid row btn-row">
		<div class="col-12">
			<button type="button" class="btn btn-success btn-screen-ctrl" onclick="screen_mk()">New</button>
			<button type="button" class="btn btn-danger btn-screen-ctrl" onclick="screen_rm()">Remove</button>
		</div>
	</div>
</div>
