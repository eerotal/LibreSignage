<!-- Asset uploader popup for the LibreSignage editor. -->
<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/include_guard.php');
?>
<div id="asset-uploader" class="popup">
	<div class="row" id="asset-uploader-header-row">
		<h1>Add media</h1>
	</div>
	<div class="row" id="asset-uploader-filesel-row">
		<div class="col-sm">
			<input type="file"
					class="custom-file-input"
					id="asset-uploader-filesel">
			<label class="custom-file-label"
					for="asset-uploader-filesel">
				Choose a file
			</label>
		</div>
		<div class="col-sm-auto">
			<button type="button" class="btn btn-primary">
				Upload <i class="fas fa-upload"></i>
			</button>
		</div>
	</div>
	<div class="row" id="asset-uploader-filelist-row">
		<div class="col">

		</div>
	</div>
</div>
