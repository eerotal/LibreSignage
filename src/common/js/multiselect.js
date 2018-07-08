/*
*  Control functionality for the multiselect element.
*/

class MultiSelect {
	constructor(id) {
		this.selected = [];
		this.root = $(`#${id}`);
		this.input = $(`#${id} > .ms-controls > .ms-input`);
		this.btn_add = $(`#${id} > .ms-controls > .ms-add`);
		this.values = $(`#${id} > .ms-values`);

		this.input.on('keypress', (event) => {
			if (event.key == 'Enter') {
				this.add(this.input.val());
				this.input.val('');
			}
		});

		this.btn_add.on('click', () => {
			this.add(this.input.val());
			this.input.val('');
		});
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

	enable() {
		// TODO
	}

	disable() {
		// TODO
	}
}
