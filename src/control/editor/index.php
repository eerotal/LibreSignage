<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/js_include.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	web_auth(NULL, array('editor'), TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/nav.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/control/editor/css/editor.css">
		<title>LibreSignage Editor</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="container-main container-fluid w-100 h-100">
				<div class="container-fluid row m-0">
					<span class="col">Slides</span>
				</div>
				<div class="container-fluid row m-0">
					<div class="col-12">
						<div id="slidelist" class="d-flex flex-row flex-nowrap">
						</div>
					</div>
				</div>
				<div class="container-fluid row m-0">
					<div class="col-md-auto pt-2">
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

						<!-- Automatic expiration enable -->
						<div class="form-group form-check" id="slide-expire-enabled-group">
							<input type="checkbox"
								class="form-check-input"
								id="slide-expire-enabled"
								data-toggle="tooltip"
								title="Select whether the slide should expire automatically.">
							<label class="form-check-label"
								for="slide-expire-enabled">
								Automatic expiration
							</label>
						</div>

						<!-- Expiration date selector -->
						<div class="form-group pl-4" id="slide-expire-group">
							<input type="datetime-local"
								id="slide-expire"
								class="form-control w-100"
								data-toggle="tooltip"
								title="The slide expiration date.">
						</div>

						<!-- Slide enabled checkbox -->
						<div class="form-group form-check" id="slide-enabled-group">
							<input type="checkbox"
								class="form-check-input"
								id="slide-enabled"
								data-toggle="tooltip"
								title="Select whether the slide is enabled or not.">
							<label class="form-check-label"
								for="slide-enabled">
								Enabled
							</label>
						</div>

						<!-- Control buttons -->
						<div class="form-group container-fluid d-flex justify-content-center">
							<button id="btn-slide-save"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								onclick="slide_save()"
								data-toggle="tooltip"
								title="Save the selected slide.">Save</button>
							<button id="btn-slide-new"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								onclick="slide_new()"
								data-toggle="tooltip"
								title="Create a new slide.">New</button>
							<button id="btn-slide-remove"
								type="button"
								class="btn btn-danger btn-slide-ctrl"
								onclick="slide_rm()"
								data-toggle="tooltip"
								title="Remove the selected slide.">Remove</button>
						</div>
						<div class="container-fluid d-flex justify-content-center">
							<button id="btn-slide-preview"
								type="button"
								class="btn btn-success btn-slide-ctrl"
								onclick="slide_preview()"
								data-toggle="tooltip"
								title="Preview the selected slide in a new window.">Preview Slide</button>
						</div>
						<p id="editor-status"></p>
					</div>
					<div class="col-md pt-2">
						<label for="slide-input">Markup</label>
						<div id="slide-input" class="no-font rounded"></div>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);

			js_include_jquery();
			js_include_popper();
			js_include_bootstrap();
			js_include_ace();
		?>
		<script src="/common/js/slide.js"></script>
		<script src="/common/js/util.js"></script>
		<script src="/common/js/validator.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/cookie.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/control/editor/js/slidelist.js"></script>
		<script src="/control/editor/js/editor.js"></script>
	</body>
</html>
