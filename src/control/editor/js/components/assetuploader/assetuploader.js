var $ = require('jquery');
var UIStatic = require('ls-uicontrol').UIStatic
var UIInput = require('ls-uicontrol').UIInput;
var UIButton = require('ls-uicontrol').UIButton;
var UIController = require('ls-uicontrol').UIController;
var Popup = require('ls-popup').Popup;
var assert = require('ls-assert').assert;
var APIUI = require('ls-api-ui');

var AssetUploaderController = require('./assetuploader_controller.js').AssetUploaderController;
var AssetList = require('./assetlist.js').AssetList;

var ValidatorSelector = require('ls-validator').ValidatorSelector;
var ValidatorTrigger = require('ls-validator').ValidatorTrigger;
var FileSelectorValidator = require('ls-validator').FileSelectorValidator;

class AssetUploader {
	constructor(container, api) {
		this.controller = new AssetUploaderController(api);
		this.container = container;

		this.popup = new Popup(container);

		this.assetlist = new AssetList(
			$(this.container).find('.filelist')
		);

		this.inputs = new UIController({
			files: new UIInput({
				elem: $(this.container).find('.filesel'),
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
				getter: e => e.prop('files'),
				setter: null,
				clearer: e => {
					e.val('');
					e.trigger('input');
				}
			}),
			filelist: new UIInput({
				elem: $(this.container).find('.filelist'),
				cond: d => true,
				enabler: null,
				attach: {
					'component.assetlist.select': (e, data) => {
						this.select_asset(data.name);
					},
					'component.assetlist.remove': async (e, data) => {
						await this.remove_asset(data.name);
						this.inputs.get('filelink').clear();
					}
				},
				defer: () => !this.ready,
				mod: null,
				setter: null,
				getter: null,
				clearer: null
			}),
			filelink: new UIInput({
				elem: $(this.container).find('.file-link-input'),
				cond: d => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				mod: null,
				setter: (e, val) => e.val(val),
				getter: e => e.val(),
				clearer: e => e.val('')
			})
		});
		this.buttons = new UIController({
			upload: new UIButton({
				elem: $(this.container).find('.upload-btn'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
					&& !d.slide.uploading
					&& this.inputs.get('files').get().length != 0
				),
				enabler: null,
				attach: {
					click: () => {
						this.upload_assets();
					},
					'local.upload.begin': e => {
						$(e.target).addClass('uploading');
					},
					'local.upload.end': e => {
						$(e.target).removeClass('uploading');
					}
				},
				defer: () => !this.ready
			})
		});
		this.statics = new UIController({
			files_label: new UIStatic({
				elem: $(this.container).find('.filesel-label'),
				cond: d => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				setter: null,
				getter: null,
				clearer: null
			}),
			files_limit_label: new UIStatic({
				elem: $(this.container).find('.file-limit-label-row'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
					&& d.slide.filelimit
				),
				enabler: (elem, s) => {
					elem.css('display', s === true ? 'block' : 'none');
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

		/*
		*  Create validators for the file selector.
		*/
		this.fileval_sel = new ValidatorSelector(
			$(this.container).find('.filesel'),
			$(this.container).find('.filesel-cont'),
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
						let tmp = [];
						let slide = this.controller.get_slide();
						if (
							this.controller.get_state().slide.loaded
							&& slide.has('assets')
						) {
							for (let a of slide.get('assets')) {
								tmp.push(a['filename']);
							}
						}
						return tmp;
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

		/*
		*  Create a validator trigger and manually trigger it once.
		*/
		(this.fileval_trig = new ValidatorTrigger(
			[this.fileval_sel],
			() => this.update()
		)).trigger();

		this.ready = true;
	}

	show(slide) {
		/*
		*  Open the asset uploader for 'slide'.
		*/
		assert(slide != null, "No slide specified.");
		this.controller.open(slide);
		this.assetlist.show(slide);
		this.popup.visible(true);
		this.update();
	}

	hide() {
		/*
		*  Hide the asset uploader.
		*/
		this.popup.visible(false);
		this.assetlist.hide();
		this.controller.close();
	}

	async upload_assets() {
		/*
		*  Upload the currently selected files. This function
		*  also handles indicating uploads in progress by firing
		*  the local.upload.begin and local.upload.end events on
		*  the upload button.
		*/
		let btn = this.buttons.get('upload').get_elem();
		let selector = this.inputs.get('files');

		btn.trigger('local.upload.begin');
		try {
			let tmp = this.controller.upload_assets(selector.get());
			this.update(); // Disable the upload button.
			await tmp;
		} catch (e) {
			btn.trigger('local.upload.end')
			this.update();

			APIUI.handle_error(e);
			return;
		}
		selector.clear();
		btn.trigger('local.upload.end');
		this.update();
	}

	async remove_asset(name) {
		/*
		*  Remove the asset 'name' and update the UI.
		*/
		try {
			await this.controller.remove_asset(name);
		} catch (e) {
			APIUI.handle_error(e);
		}
		this.inputs.get('filelink').clear();
		this.update();
	}

	select_asset(name) {
		/*
		*  Select the asset 'name'.
		*/
		this.inputs.get('filelink').set(
			window.location.origin
			+ this.controller.get_slide().get_asset_uri(name)
		);
	}

	update_file_selector_label() {
		/*
		*  Update the file selector label with the selected
		*  filenames.
		*/
		let names = [];
		let elem = this.inputs.get('files').get_elem()[0];
		if (elem.files.length != 0) {
			for (let f of elem.files) { names.push(f.name); }
			this.statics.get('files_label').set(names.join(', '));	
		} else {
			this.statics.get('files_label').set('Choose a file');
		}
	}

	update() {
		/*
		*  Update the UI of the asset uploader.
		*/
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
exports.AssetUploader = AssetUploader;
