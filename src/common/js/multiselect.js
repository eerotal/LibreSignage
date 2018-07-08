/*
*  Control functionality for the multiselect element.
*/

class MultiSelect {
	constructor(id, validators) {
		if (!id) {
			throw new Error(
				'Invalid empty ID for multiselector.'
			);
		}

		this.val_valid = true;
		this.selected = [];
		this.root = $(`#${id}`);
		this.input = $(`#${id} > .ms-controls > .ms-input`);
		this.btn_add = $(`#${id} > .ms-controls > .ms-add`);
		this.values = $(`#${id} > .ms-values`);

		// Add listener for Enter keypresses.
		this.input.on('keypress', (event) => {
			if (event.key == 'Enter' && this.val_valid) {
				this.add(this.input.val());
				this.input.val('');
			}
		});

		// Add listener for the (+) button.
		this.btn_add.on('click', () => {
			this.add(this.input.val());
			this.input.val('');
		});

		// Add validators for the input.
		if (validators && validators.length) {
			this.vtrig = new ValidatorTrigger(
				[new ValidatorSelector(
					this.input,
					this.root,
					validators,
					null
				)],
				(valid) => {
					this.val_valid = valid;
					this.btn_add.prop(
						'disabled',
						!valid
					);
				}
			);
		}
	}

	add(option) {
		if (!option) { return; }
		var cont = $('<div>', {
			'id': `ms-opt-${option}`,
			'class': 'ms-val',
			'text': option
		});
		var rm = $('<span>', { 'class': 'ms-rm fas fa-times' });
		cont.append(rm);
		rm.on('click', () => { this.remove(option); });

		this.selected.push(option);
		this.values.append(cont);

	}

	remove(option) {
		if (!this.selected.includes(option)) {
			throw new Error(
				'Option not selected.'
			);
		}
		$(`#ms-opt-${option}`).remove();
		this.selected.splice(this.selected.indexOf(option), 1);
	}
}
