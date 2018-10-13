<!-- Asset uploader popup for the LibreSignage editor. -->
<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/include_guard.php');
?>
<div id="asset-uploader" class="popup">
	<div class="row" id="asset-uploader-header-row">
		<div class="col">
			<h1>Add media</h1>
		</div>
	</div>
	<div class="row" id="asset-uploader-filesel-row">
		<div class="col-sm" id="asset-uploader-filesel-cont">
			<input type="file"
					class="custom-file-input"
					id="asset-uploader-filesel"
					multiple>
			<label class="custom-file-label"
					for="asset-uploader-filesel"
					id="asset-uploader-filesel-label">
				Choose a file
			</label>
			<div class="invalid-feedback"></div>
		</div>
		<div class="col-sm-auto">
			<button id="asset-uploader-upload-btn"
					type="button"
					class="btn btn-primary">
				Upload <i class="fas fa-upload"></i>
			</button>
		</div>
	</div>
	<div class="row" id="asset-uploader-cant-upload-row">
		<div class="col">
			You must save the slide before adding media.
		</div>
	</div>
	<div class="row" id="asset-uploader-filelist-row">
		<div class="col">

		</div>
	</div>
</div>
