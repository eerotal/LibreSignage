<div id="dialog-alert-overlay" class="dialog-overlay" style="display: none;">
	<div class="container dialog alert alert-info">
		<h3 id="dialog-alert-header" class="display-5 dialog-header">Dialog Header</h3>
		<p id ="dialog-alert-text" class="dialog-text">Dialog text</p>
		<div class="d-flex flex-row-reverse w-100">
			<button type="button" class="btn btn-primary btn-dialog" onclick="dialog_alert_ok()">Ok</button>
		</div>
	</div>
</div>
<div id="dialog-confirm-overlay" class="dialog-overlay" style="display: none;">
	<div class="container dialog alert alert-info">
		<h3 id="dialog-confirm-header" class="display-5 dialog-header">Dialog Header</h3>
		<p id ="dialog-confirm-text" class="dialog-text">Dialog text</p>
		<div class="d-flex flex-row-reverse w-100">
			<button type="button" class="btn btn-danger btn-dialog" onclick="dialog_confirm_cancel()">Cancel</button>
			<button type="button" class="btn btn-success btn-dialog" onclick="dialog_confirm_ok()">Ok</button>
		</div>
	</div>
</div>
