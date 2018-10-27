<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/css.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/auth.php');
	web_auth(NULL, array('admin'), TRUE);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			require_css(['font-awesome']);
		?>
		<link rel="stylesheet" href="/control/usermgr/css/usermgr.css">
		<title>LibreSignage User Manager</title>
	</head>
	<body>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].NAV_PATH);
		?>
		<main class="container-fluid">
			<div class="container text-center">
				<div class="container-fluid">
					<div class="row usr-table-row">
						<div class="usr-table-col col">
							User
						</div>
						<div class="usr-table-col col"
							id="heading-usr-groups">
							Groups
						</div>
						<div id="heading-usr-comment"
							class="usr-table-col col">
							Comment
						</div>

						<!-- Adjust table spacing. -->
						<div class="usr-table-col col-auto invisible">
							<div class="btn btn-primary usr-edit-btn small-btn">
								<i class="fas fa-save"></i>
							</div>
							<div class="btn btn-primary usr-edit-btn small-btn">
								<i class="fas fa-trash-alt"></i>
							</div>
							<div class="btn btn-primary usr-edit-btn small-btn">
								<i class="fas fa-edit"></i>
							</div>
						</div>
					</div>
				</div>
				<div id="users-table" class="container-fluid">
				</div>
				<div class="container-fluid">
					<div class="usr-table-row">
						<div class="usr-table-col">
							<input
								id="btn-create-user"
								type="button"
								class="btn btn-primary"
								value="Create user">
							</input>
						</div>
					</div>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>
		<script src="/control/usermgr/js/main.js"></script>
	</body>
</html>
