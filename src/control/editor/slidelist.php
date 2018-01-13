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
						'btn-slide" data-toggle="button"';

					echo 'id="slide-btn-'.$slides[$i].'"';
					echo 'onclick="slide_show(\''.$slides[$i].'\')">';

					echo 'Screen '.$i.'</button>';
					$i++;
				}
			?>
		</div>
	</div>
	<div class="container-fluid row btn-row">
		<div class="col-12">
			<button type="button" class="btn btn-success btn-slide-ctrl"
					onclick="slide_save()">Save</button>
			<button type="button" class="btn btn-success btn-slide-ctrl"
					onclick="slide_mk()">New</button>
			<button type="button" class="btn btn-danger btn-slide-ctrl"
					onclick="slide_rm()">Remove</button>
		</div>
	</div>
</div>
