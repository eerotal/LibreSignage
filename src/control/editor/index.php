<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/css.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	web_auth(NULL, array('editor'), TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			css_include(['font-awesome', 'bootstrap']);
		?>
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/nav.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/common/css/multiselect.css">
		<link rel="stylesheet" href="/control/editor/css/editor.css">
		<link rel="stylesheet" href="/control/editor/css/timeline.css">
		<title>LibreSignage Editor</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="container-main container-fluid w-100 h-100">
				<div class="container-fluid row mx-0 my-1">
					<div class="col-12">
						<div id="queue-select-cont">
							<label for="queue-select">Queue:</label>
							<select class="d-inline-block custom-select small-select mx-2"
								id="queue-select">
							</select>
							<button class="btn btn-primary small-btn mx-1"
								type="button"
								id="queue-create"
								data-toggle="tooltip"
								title="Create queue.">
								<i class="fas fa-plus-circle"></i>
							</button>
							<button class="btn btn-primary small-btn mx-1"
								type="button"
								id="queue-view"
								data-toggle="tooltip"
								title="View queue.">
								<i class="fas fa-eye"></i>
							</button>
							<button class="btn btn-danger small-btn mx-1"
								type="button"
								id="queue-remove"
								data-toggle="tooltip"
								title="Remove queue.">
								<i class="fas fa-trash-alt"></i>
							</button>
						</div>
					</div>
				</div>
				<div class="container-fluid row mx-0 my-1">
					<div class="col-12">
						<div id="timeline" class="d-flex flex-row flex-nowrap">
						</div>
					</div>
				</div>
				<div class="container-fluid row mx-0 my-1">
					<div class="col-md-3 container-fluid pt-2" id="editor-col-l">
						<div id="slide-cant-edit">
							You can't edit this slide.
						</div>
						<div id="slide-edit-as-collab">
							You can edit this slide as a collaborator.
						</div>
						<!-- Slide name input -->
						<div class="form-group" id="slide-name-group">
							<label for="slide-name">Name</label>
							<input type="text"
								class="form-control w-100"
								id="slide-name"
								data-toggle="tooltip"
								title="The name of the slide. This is only visible in the editor.">
							<div class="invalid-feedback"></div>
						</div>

						<!-- Slide owner label -->
						<div class="form-group" id="slide-owner-group">
							<label for="slide-owner">Owner</label>
							<input type="text"
								class="form-control w-100"
								id="slide-owner"
								data-toggle="tooltip"
								title="The owner of the slide."
								disabled>
						</div>

						<!-- Slide collaborators multiselect -->
						<div class="form-group" id="slide-collab-group">
							<label for="slide-collab">
								Collaborators
							</label>
						</div>

						<!-- Slide time selector -->
						<div class="form-group" id="slide-time-group">
							<label for="slide-time">Time (seconds)</label>
							<select class="custom-select w-100"
								id="slide-time"
								data-toggle="tooltip"
								title="The time the slide is shown in seconds.">

								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
							</select>
						</div>

						<!-- Slide index input -->
						<div class="form-group" id="slide-index-group">
							<label for="slide-index">Index</label>
							<input type="number"
								min="0"
								class="form-control w-100"
								id="slide-index"
								data-toggle="tooltip"
								title="The ordinal number of the slide. 0 is the first slide.">
							<div class="invalid-feedback"></div>
						</div>

						<!-- Slide animation selector -->
						<div class="form-group" id="slide-animation-group">
							<label for="slide-animation">Animation</label>
							<select class="custom-select w-100"
								id="slide-animation"
								data-toggle="tooltip"
								title="Slide transition animation.">
								<option value="0">No animation</option>
								<option value="1">Swipe left</option>
								<option value="2">Swipe right</option>
								<option value="3">Swipe up</option>
								<option value="4">Swipe down</option>
							</select>
						</div>

						<!-- Schedule enable -->
						<div class="form-group">
							<a class="link-nostyle"
								data-toggle="collapse"
								href="#slide-sched-group"
								aria-expanded="false"
								aria-controls="slide-sched-group">
								<i class="fas fa-angle-right"></i> Slide scheduling
							</a>
						</div>

						<!-- Schedule date/time selector -->
						<div class="row form-group pl-4 collapse" id="slide-sched-group">
							<div class="col-12 py-1">
								<input type="checkbox"
									id="slide-sched"
									data-toggle="tooltip"
									title="Select whether the slide is scheduled.">
								<label class="form-check-label" for="slide-sched">
									Scheduling enabled
								</label>
							</div>
							<div class="col-12 py-1">
								<label for="slide-sched-date-s">
									Start date
								</label>
								<input type="date"
									id="slide-sched-date-s"
									class="form-control d-inline"
									data-toggle="tooltip"
									title="The slide schedule start date.">
							</div>
							<div class="col-12 py-1">
								<input type="time"
									id="slide-sched-time-s"
									class="form-control d-inline"
									data-toggle="tooltip"
									title="The slide schedule start time."
									step="1">
							</div>
							<div class="col-12 py-1">
								<label for="slide-sched-date-e">
									End date
								</label>
								<input type="date"
									id="slide-sched-date-e"
									class="form-control d-inline"
									data-toggle="tooltip"
									title="The slide schedule end date.">
							</div>
							<div class="col-12 py-1">
								<input type="time"
									id="slide-sched-time-e"
									class="form-control d-inline"
									data-toggle="tooltip"
									title="The slide schedule end time."
									step="1">
							</div>
						</div>

						<!-- Slide enabled checkbox -->
						<div class="form-group" id="slide-enabled-group">
							<input type="checkbox"
								id="slide-enabled"
								data-toggle="tooltip"
								title="Select whether the slide is enabled or not.">
							<label class="form-check-label"
								for="slide-enabled">
								Slide enabled
							</label>
						</div>

						<!-- Control buttons -->
						<div class="row form-group container-fluid d-flex justify-content-center mx-0 px-0">
							<button id="btn-slide-new"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								data-toggle="tooltip"
								title="Create slide.">
								<i class="fas fa-plus-circle"></i>
							</button>
							<button id="btn-slide-save"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								data-toggle="tooltip"
								title="Save slide.">
								<i class="fas fa-save"></i>
							</button>
							<button id="btn-slide-dup"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								data-toggle="tooltip"
								title="Duplicate slide.">
								<i class="fas fa-copy"></i>
							</button>
							<button id="btn-slide-preview"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								data-toggle="tooltip"
								title="Preview slide.">
								<i class="fas fa-eye"></i>
							</button>
							<button id="btn-slide-ch-queue"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								data-toggle="tooltip"
								title="Change queue.">
								<i class="fas fa-arrow-circle-right"></i>
							</button>
							<button id="btn-slide-remove"
								type="button"
								class="btn btn-danger btn-slide-ctrl"
								data-toggle="tooltip"
								title="Remove slide.">
								<i class="fas fa-trash-alt"></i>
							</button>
						</div>
					</div>
					<div class="col-md container-fluid pt-2" id="editor-col-r">
						<label for="slide-input">Markup</label>
						<div id="slide-input" class="no-font rounded"></div>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>
		<script src="/libs/ace-builds/src/ace.js"></script>
		<script src="/control/editor/js/main.js"></script>
	</body>
</html>
