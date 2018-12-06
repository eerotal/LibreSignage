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
				<span class="on-active">Upload</span>
				<i class="fas fa-upload on-active"></i>

				<span class="on-upload">Uploading </span>
				<i class="fas fa-spinner fa-spin on-upload"></i>

				<span class="on-error">Upload failed. Retry?</span>
			</button>
		</div>
	</div>
	<div class="row limit-indicator-row"
		id="asset-uploader-cant-upload-row">
		<div class="col">
			You must save the slide before adding media.
		</div>
	</div>
	<div class="row limit-indicator-row"
		id="asset-uploader-no-more-assets-row">
		<div class="col">
			The maximum number of files for this slide has been
			reached. You can't upload more files.
		</div>
	</div>
	<div class="row" id="asset-uploader-filelist-row">
		<div class="col on-error">
			<h2>There was a problem updating this list :(</h2>
		</div>
		<div class="col on-success" id="asset-uploader-filelist">
		</div>
	</div>
	<div class="row" id="asset-uploader-file-link-row">
		<div class="col">
			<label for="asset-uploader-file-link-input">Link</label>
			<input type="text"
					class="form-control"
					id="asset-uploader-file-link-input">
		</div>
	</div>
</div>
