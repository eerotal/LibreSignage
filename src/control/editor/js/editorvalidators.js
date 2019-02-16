/*
*  LibreSignage editor input validator definitions.
*/

var $ = require('jquery');
var ValidatorSelector = require('ls-validator').ValidatorSelector;
var ValidatorTrigger = require('ls-validator').ValidatorTrigger;
var StrValidator = require('ls-validator').StrValidator;
var NumValidator = require('ls-validator').NumValidator;

class EditorValidators {
	constructor(api) {
		this.triggers = [];
		this.validators = {
			name: new ValidatorSelector(
				$('#slide-name')[0],
				$('#slide-name-group')[0],
				[
					new StrValidator(
						{
							min: 1,
							max: null,
							regex: null
						},
						'The name is too short.'
					),
					new StrValidator(
						{
							min: null,
							max: api.limits.SLIDE_NAME_MAX_LEN,
							regex: null
						},
						'The name is too long.'
					),
					new StrValidator(
						{
							min: null,
							max: null,
							regex: /^[A-Za-z0-9_-]*$/
						},
						'The name contains invalid characters.'
					),
				]	
			),
			duration: new ValidatorSelector(
				$('#slide-duration')[0],
				$('#slide-duration-group')[0],
				[
					new NumValidator(
						{
							min: api.limits.SLIDE_MIN_DURATION/1000,
							max: null,
							nan: true,
							float: true
						},
						`The duration must be at least
						${api.limits.SLIDE_MIN_DURATION/1000}s.`
					),
					new NumValidator(
						{
							min: null,
							max: api.limits.SLIDE_MAX_DURATION/1000,
							nan: true,
							float: true
						},
						`The duration must be
						${api.limits.SLIDE_MAX_DURATION/1000}s
						at the most.`
					),
					new NumValidator(
						{
							min: null,
							max: null,
							nan: false,
							float: true
						},
						'You must specify a duration in seconds.'
					)
				]
			),
			index: new ValidatorSelector(
				$('#slide-index')[0],
				$('#slide-index-group')[0],
				[
					new NumValidator(
						{
							min: 0,
							max: null,
							nan: true,
							float: true
						},
						'The index must be positive or zero.'
					),
					new NumValidator(
						{
							min: null,
							max: api.limits.SLIDE_MAX_INDEX,
							nan: true,
							float: true
						},
						`The index must be ${api.limits.SLIDE_MAX_INDEX}
						at the most.`
					),
					new NumValidator(
						{
							min: null,
							max: null,
							nan: false,
							float: true
						},
						'The index must be an integer number.'
					),
					new NumValidator(
						{
							min: null,
							max: null,
							nan: false,
							float: false
						},
						'You must specify and index.'
					)
				]
			)
		}
	}

	add_trigger_hook(hook) {
		/*
		*  Create a new ValidatorTrigger for all of the
		*  ValidatorSelectors in this.validators.
		*/
		this.triggers.push(
			new ValidatorTrigger(
				Object.values(this.validators),
				hook
			)
		);
	}

	validate_all() {
		/*
		*  Validate all ValidatorSelectors.
		*/
		for (let v of Object.values(this.validators)) {
			v.validate();
		}
	}

	enable(state) {
		/*
		*  Enable/disable all ValidatorSelectors.
		*/
		for (let v of Object.values(this.validators)) {
			v.enable(state);
		}
	}

	get_state() {
		/*
		*  Get the combined state of the ValidatorSelectors, ie.
		*  true if all of them are valid and false otherwise.
		*/
		for (let v of Object.values(this.validators)) {
			if (!v.get_state()) { return false; }
		}
		return true;
	}
}
exports.EditorValidators = EditorValidators;
