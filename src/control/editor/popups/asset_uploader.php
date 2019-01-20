<!-- Asset uploader popup for the LibreSignage editor. -->
<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/include_guard.php');
?>
<div id="asset-uploader" class="popup">
	<div class="row header-row">
		<div class="col">
			<h1>Add media</h1>
		</div>
	</div>
	<div class="row filesel-row">
		<div class="col-sm filesel-cont">
			<input type="file"
					class="custom-file-input filesel"
					id="asset-uploader-filesel"
					multiple>
			<label class="custom-file-label filesel-label"
					for="asset-uploader-filesel">
				Choose a file
			</label>
			<div class="invalid-feedback"></div>
		</div>
		<div class="col-sm-auto">
			<button type="button" class="btn btn-primary upload-btn">
				<span class="on-active">Upload</span>
				<i class="fas fa-upload on-active"></i>

				<span class="on-upload">Uploading </span>
				<i class="fas fa-spinner fa-spin on-upload"></i>
			</button>
		</div>
	</div>
	<div class="row file-limit-label-row">
		<div class="col file-limit-label-col">
			The maximum number of files for this slide has been uploaded.
			You can't upload more files.
		</div>
	</div>
	<div class="row filelist-row">
		<div class="col on-error">
			<h2>There was a problem updating this list :(</h2>
		</div>
		<div class="col on-success filelist">
		</div>
	</div>
	<div class="row file-link-row">
		<div class="col">
			<label for="asset-uploader-file-link-input">Link</label>
			<input type="text"
					class="form-control file-link-input"
					id="asset-uploader-file-link-input">
		</div>
	</div>
</div>
