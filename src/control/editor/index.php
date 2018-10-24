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
		<?php require_css(['font-awesome']); ?>
		<link rel="stylesheet" href="/control/editor/css/editor.css">
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
						<!-- Queue selector -->
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
					<!-- Timeline -->
					<div class="col-12">
						<div id="timeline" class="d-flex flex-row flex-nowrap">
						</div>
					</div>
				</div>
				<div class="container-fluid row mx-0 my-1">
					<div class="col-md-3 container-fluid pt-2" id="editor-col-l">
						<!-- Slide editable status labels -->
						<div id="slide-cant-edit">
							You can't edit this slide.
						</div>
						<div id="slide-readonly">
							You can't edit this slide because someone
							else is already editing it.
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
						<div class="form-group mb-0">
							<a class="link-nostyle"
								data-toggle="collapse"
								href="#slide-sched-group"
								aria-expanded="false"
								aria-controls="slide-sched-group">
								<i class="fas fa-angle-right"></i> Slide scheduling
							</a>
						</div>

						<!-- Schedule date/time selector -->
						<div class="row form-group collapse" id="slide-sched-group">
							<div class="col-12 py-1">
								<input type="checkbox"
									id="slide-sched"
									data-toggle="tooltip"
									title="Select whether the slide is scheduled.">
								<label class="form-check-label" for="slide-sched">
									Enable
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
						<div class="form-group mt-3" id="slide-enabled-group">
							<input type="checkbox"
								id="slide-enabled"
								data-toggle="tooltip"
								title="Select whether the slide is enabled or not.">
							<label class="form-check-label"
								for="slide-enabled">
								Enable slide
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
						<div class="row">
							<!-- Live preview -->
							<div class="col-12 container-fluid">
								<a class="link-nostyle"
									data-toggle="collapse"
									href="#slide-live-preview-collapse"
									aria-expanded="false"
									aria-controls="slide-live-preview-collapse">
									<i class="fas fa-angle-right"></i> Live preview
								</a>
								<div id="slide-live-preview-collapse" class="row collapse">
									<div class="col-12 text-center">
										<button id="btn-preview-ratio-16x9"
												class="btn btn-light btn-border small-btn"
												type="button">
												16:9
										</button>
										<button id="btn-preview-ratio-4x3"
												class="btn btn-light btn-border small-btn"
												type="button">
												4:3
										</button>
									</div>
									<div class="col-12">
										<div id="slide-live-preview"
											class="preview-cont preview-limit">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md container-fluid">
								<div class="row py-2">
									<div class="col-2 m-auto text-left">
										<label for="slide-input" class="m-0">Markup</label>
									</div>

									<!-- Editor toolbar -->
									<div class="col-10 text-right">
										<div class="dropdown">
											<button id="editor-dropdown-menu-btn"
													class="btn btn-info small-btn dropdown-toggle"
													type="button"
													data-toggle="dropdown"
													aria-haspopup="true"
													aria-expanded="false">
												Menu
											</button>
											<div class="dropdown-menu" aria-labelledby="editor-dropdown-menu-btn">
												<a class="dropdown-item" href="#" id="link-add-media">Add media</a>
												<a class="dropdown-item" href="#" id="link-quick-help">Quick help</a>
											</div>
										</div>
									</div>
								</div>
								<div id="slide-input" class="rounded">
								</div>
								<div class="container-fluid">
									<p id="markup-err-display"></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php require_once($_SERVER['DOCUMENT_ROOT'].'/control/editor/popups/quick_help.php'); ?>
				<?php require_once($_SERVER['DOCUMENT_ROOT'].'/control/editor/popups/asset_uploader.php'); ?>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>
		<script src="/libs/ace-builds/src/ace.js"></script>
		<script src="/control/editor/js/main.js"></script>
	</body>
</html>
