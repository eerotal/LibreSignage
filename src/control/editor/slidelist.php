<div class="slidelist">
	<div class="container-fluid row btn-row">
		<div class="col-12 d-flex flex-wrap">
			<?php
				require_once($_SERVER['DOCUMENT_ROOT'].'/global_php/config.php');
				require_once($_SERVER['DOCUMENT_ROOT'].'/api/slide.php');

				$slides = get_slides_id_list();

				$i = 0;
				while ($i < count($slides)) {
					if (substr($slides[$i], 0, 1) == '.') {
						continue;
					}

					echo '<button type="button" class="btn btn-primary '.
						'btn-slide"';

					echo 'id="slide-btn-'.$slides[$i].'"';
					echo 'onclick="slide_show(\''.$slides[$i].'\')">';

					echo 'Screen '.$i.'</button>';
					$i++;
				}
			?>
		</div>
	</div>
</div>
