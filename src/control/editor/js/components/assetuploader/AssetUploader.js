var AssetUploaderController = require('./AssetUploaderController.js');
var AssetList = require('./AssetList.js');

var ValidatorSelector = require('libresignage/ui/validator/ValidatorSelector');
var ValidatorTrigger = require('libresignage/ui/validator/ValidatorTrigger');
var FileSelectorValidator = require('libresignage/ui/validator/FileSelectorValidator');
var Assert = require('assert');
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');
var UIStatic = require('libresignage/ui/controller/UIStatic');
var Popup = require('libresignage/ui/components/Popup');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

/**
* A view class for the AssetUploader popup.
*/
class AssetUploader {
	/**
	* Construct a new AssetUploader object.
	*
	* @param {HTMLElement} container The container element where the popup
	*                                is created.
	* @param {APIInterface} api      An APIInterface object.
	*/
	constructor(container, api) {
		this.controller = new AssetUploaderController(api);
		this.container = container;
		this.popup = new Popup(container);
		this.assetlist = new AssetList(
			this.container.querySelector('.filelist')
		);

		this.inputs = new UIController({
			files: new UIInput({
				elem: this.container.querySelector('.filesel'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
					&& !d.slide.filelimit
				),
				enabler: null,
				attach: {
					input: () => this.update_file_selector_label()
				},
				defer: () => !this.ready,
				mod: null,
				getter: e => e.files,
				setter: null,
				clearer: e => {
					e.value = '';
					e.dispatchEvent(new Event('input'));
				}
			}),
			filelist: new UIInput({
				elem: this.container.querySelector('.filelist'),
				cond: d => true,
				enabler: null,
				attach: {
					'component.assetlist.select': e => {
						this.select_asset(this.assetlist.get_selection());
					},
					'component.assetlist.remove': async (e, data) => {
						await this.remove_asset(this.assetlist.get_selection());
					}
				},
				defer: () => !this.ready,
				mod: null,
				setter: null,
				getter: null,
				clearer: null
			}),
			filelink: new UIInput({
				elem: this.container.querySelector('.file-link-input'),
				cond: d => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				mod: null,
				setter: (e, val) => e.value = val,
				getter: e => e.value,
				clearer: e => e.value = ''
			})
		});
		this.buttons = new UIController({
			upload: new UIButton({
				elem: this.container.querySelector('.upload-btn'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
					&& !d.slide.uploading
					&& this.inputs.get('files').get().length != 0
				),
				enabler: null,
				attach: {
					click: () => this.upload_assets()
				},
				defer: () => !this.ready
			})
		});
		this.statics = new UIController({
			popup: new UIStatic({
				elem: this.container,
				cond: d => true,
				enabler: null,
				attach: {
					'component.popup.close': () => this.clear()
				},
				defer: () => !this.ready,
				setter: null,
				getter: null,
				clearer: null
			}),
			files_label: new UIStatic({
				elem: this.container.querySelector('.filesel-label'),
				cond: d => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				setter: null,
				getter: null,
				clearer: null
			}),
			files_limit_label: new UIStatic({
				elem: this.container.querySelector('.file-limit-label-row'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
					&& d.slide.filelimit
				),
				enabler: (elem, s) => {
					elem.style.display = s ? 'block' : 'none'
				},
				attach: null,
				defer: () => !this.ready,
				setter: null,
				getter: null,
				clearer: null
			})
		});

		let f_mimes = this.controller.get_valid_file_mime_types();
		let f_regex = this.controller.get_valid_filename_regex();
		let f_max_len = this.controller.get_max_filename_len();

		// Create validators for the file selector.
		this.fileval_sel = new ValidatorSelector(
			this.container.querySelector('.filesel'),
			this.container.querySelector('.filesel-cont'),
			[new FileSelectorValidator(
				{
					mimes: Object.values(f_mimes),
					name_len: null,
					regex: null,
					minfiles: null,
					bl: null
				},
				`Invalid file type. The allowed types are: ` +
				`${Object.keys(f_mimes).join(', ')}.`
			),
			new FileSelectorValidator(
				{
					mimes: null,
					name_len: f_max_len,
					regex: null,
					minfiles: null,
					bl: null
				},
				`Filename too long. The maximum length ` +
				`is ${f_max_len} characters.`
			),
			new FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: f_regex,
					minfiles: null,
					bl: null
				},
				"Invalid characters in filename. " +
				"A-Z, a-z, 0-9, ., _, - and space are allowed."
			),
			new FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: null,
					minfiles: null,
					bl: () => {
						// Create and return the uploaded files blacklist.
						if (this.controller.get_state().slide.loaded) {
							return Object.values(this.controller.get_assets())
								.map(x => x.get_filename());
						}
					}
				}, 'At least one of the selected files already exists.'
			),
			new FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: null,
					minfiles: 1,
					bl: null
				}, '', true
			)]
		);

		// Create a validator trigger.
		this.fileval_trig = new ValidatorTrigger(
			[this.fileval_sel],
			() => this.update()
		);

		this.ready = true;
	}

	/**
	* Open the asset uploader for a Slide.
	*
	* @param {Slide} slide The Slide to open the AssetUploader for.
	*/
	show(slide) {
		Assert.ok(slide != null, "No slide specified.");
		this.controller.open(slide);
		this.assetlist.show(slide);
		this.popup.visible(true);
		this.update();
	}

	/**
	* Clear the asset uploader.
	*/
	clear() {
		this.inputs.all(function() { this.clear(); });
		this.assetlist.hide();
		this.controller.close();
	}

	/**
	* Upload the currently selected files.
	*
	* This function also handles indicating uploads in progress in the UI.
	*/
	async upload_assets() {
		let selector = this.inputs.get('files');
		this.indicate_upload_begin();
		try {
			let tmp = this.controller.upload_assets(selector.get());
			this.update(); // Disable the upload button.
			await tmp;
		} catch (e) {
			this.indicate_upload_end();
			this.update();
			new APIErrorDialog(e);
			return;
		}
		selector.clear();
		this.indicate_upload_end();
		this.update();
	}

	/**
	* Show the upload in progess -indicator.
	*/
	indicate_upload_begin() {
		this.buttons.get('upload')
			.get_elem()
			.classList
			.add('uploading');
	}

	/**
	* Clear the upload in progress -indicator.
	*/
	indicate_upload_end() {
		this.buttons.get('upload')
			.get_elem()
			.classList
			.remove('uploading');
	}

	/**
	* Remove an asset.
	*
	* @param {string} name The name of the asset to remove.
	*/
	async remove_asset(name) {
		try {
			await this.controller.remove_asset(name);
		} catch (e) {
			new APIErrorDialog(e);
		}
		this.deselect_asset();
		this.update();
	}

	/**
	* Select an asset.
	*
	* @param {string} name The name of the asset to select.
	*/
	select_asset(name) {
		this.inputs.get('filelink').set(
			window.location.origin
			+ this.controller.get_assets()[name].get_url()
		);
	}

	/**
	* Deselect an asset.
	*/
	deselect_asset() {
		this.assetlist.deselect();
		this.inputs.get('filelink').clear();
	}

	/**
	* Update the file selector label with the selected
	* filenames.
	*/
	update_file_selector_label() {
		let names = [];
		let elem = this.inputs.get('files').get_elem();
		if (elem.files.length != 0) {
			for (let f of elem.files) { names.push(f.name); }
			this.statics.get('files_label').set(names.join(', '));
		} else {
			this.statics.get('files_label').set('Choose a file');
		}
	}

	/**
	* Update the UI of the asset uploader.
	*/
	update() {
		this.inputs.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.buttons.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.statics.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.assetlist.update();
	}
}
module.exports = AssetUploader;
