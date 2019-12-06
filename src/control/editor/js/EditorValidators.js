var ValidatorController = require('libresignage/ui/validator/ValidatorController');
var ValidatorSelector = require('libresignage/ui/validator/ValidatorSelector');
var ValidatorTrigger = require('libresignage/ui/validator/ValidatorTrigger');
var StrValidator = require('libresignage/ui/validator/StrValidator');
var NumValidator = require('libresignage/ui/validator/NumValidator');

class EditorValidators extends ValidatorController {
	constructor(api) {
		super({
			name: new ValidatorSelector(
				document.querySelector('#slide-name'),
				document.querySelector('#slide-name-group'),
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
				document.querySelector('#slide-duration'),
				document.querySelector('#slide-duration-group'),
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
				document.querySelector('#slide-index'),
				document.querySelector('#slide-index-group'),
				[
					new StrValidator(
						{
							min: 1,
							max: null,
							regex: null
						},
						'You must specify an index.'
					),
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
							float: false
						},
						'The index must be an integer number.'
					)
				]
			)
		});
	}
}
module.exports = EditorValidators;
