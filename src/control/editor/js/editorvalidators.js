/*
*  LibreSignage editor input validator definitions.
*/

var $ = require('jquery');
var ValidatorController = require('ls-validator').ValidatorController;
var ValidatorSelector = require('ls-validator').ValidatorSelector;
var ValidatorTrigger = require('ls-validator').ValidatorTrigger;
var StrValidator = require('ls-validator').StrValidator;
var NumValidator = require('ls-validator').NumValidator;

class EditorValidators extends ValidatorController {
	constructor(api) {
		super({
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
		});
	}
}
exports.EditorValidators = EditorValidators;
