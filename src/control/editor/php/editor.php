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
					<button type="button"
						class="dropdown-item"
						id="btn-add-media">
						Add media
					</button>
					<button type="button"
						class="dropdown-item"
						id="btn-quick-help">
						Quick help
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="slide-input" class="rounded"></div>

	<div class="container-fluid">
		<p id="slide-label-editor-error"></p>
	</div>
</div>
