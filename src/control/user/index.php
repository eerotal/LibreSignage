<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	session_start();
	auth_is_authorized(NULL, NULL, TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/nav.css">
		<link rel="stylesheet" href="/common/css/dialog.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/control/user/css/user.css">
		<title>LibreSignage User Settings</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="user-settings-cont container mx-auto">
				<!-- Username -->
				<div class="row form-group">
					<label class="col-sm-4 col-form-label"
						for="user-name">
						Username
					</label>
					<input id="user-name"
						type="text"
						class="col-sm-8 form-control"
						readonly>
					</input>
				</div>

				<!-- User groups -->
				<div class="row form-group">
					<label class="col-sm-4 col-form-label"
						for="user-groups">
							Groups
					</label>
					<input id="user-groups"
						type="text"
						class="col-sm-8 form-control"
						readonly>
					</input>
				</div>

				<!-- Password input -->
				<div id="user-pass-group"
					class="row form-group">
					<label class="col-sm-4 col-form-label"
						for="user-pass">
						Password
					</label>
					<input id="user-pass"
						type="password"
						class="col-sm-8 form-control">
					</input>
				</div>

				<!-- Password confirm input -->
				<div class="row form-group">
					<label class="col-sm-4 col-form-label"
						for="user-pass-confirm">
						Confirm&nbsp;password
					</label>
					<div id="user-pass-confirm-group"
						class="col-sm-8 p-0">
						<input id="user-pass-confirm"
							type="password"
							class="form-control">
						</input>
						<div class="invalid-feedback"></div>
					</div>
				</div>

				<!-- Save button -->
				<div class="row form-group">
					<input id="user-save"
						class="col-sm-12 btn btn-primary"
						type="button"
						value="Save"
						onclick="user_settings_save()">
					</input>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
		<script src="/common/js/api.js"></script>
		<script src="/common/js/dialog.js"></script>
		<script src="/common/js/user.js"></script>
		<script src="/common/js/validator.js"></script>
		<script src="/control/user/js/user.js"></script>
	</body>
</html>
