<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');
	session_start();
	auth_init();
	auth_is_authorized('editor', true);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/control/css/editor.css">
		<title>LibreSignage Editor</title>
	</head>
	<body class="bg-dark">
		<main role="main" class="container-fluid h-100">
			<div class="container-main container-fluid w-100 h-100 text-muted">
				<div class="container-fluid btn-row m-0">
					<div id="slidelist" class="col-12 d-flex flex-wrap justify-content-center">
					</div>
				</div>
				<div class="editor row container-fluid">
					<div class="col-md-auto">
						<label for="slide-name">Name</label>
						<input type="text"
							class="form-control w-100"
							id="slide-name"
							data-toggle="tooltip"
							title="The name of the slide. This is only visible in the editor.">

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

						<label for="slide-index">Index</label>
						<input type="number"
							min="0"
							class="form-control w-100"
							id="slide-index"
							data-toggle="tooltip"
							title="The ordinal number of the slide. 0 is the first slide.">

						<div class="container-fluid d-flex justify-content-center">
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
					<div class="col-md">
						<label for="slide-input">Markup</label>
						<div id="slide-input" class="rounded"></div>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>
		<script src="/common/js/slide.js"></script>
		<script src="/common/js/util.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/api.js"></script>
		<script src="/control/editor/js/slidelist.js"></script>
		<script src="/control/editor/js/editor.js"></script>
	</body>
</html>
