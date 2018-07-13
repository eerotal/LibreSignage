/*
*  A class providing a layer of abstraction between HTML DOM
*  elements and UI code.
*/

class UIControl {
	constructor(elem, perm, enabler, mod, getter, setter) {
		this.elem = elem;
		this.perm = perm;
		this.enabler = enabler;
		this.mod = mod;
		this.getter = getter;
		this.setter = setter;
	}

	get_elem() {
		if (typeof this.elem == 'function') {
			return this.elem();
		} else {
			return this.elem;
		}
	}

	enable(state) {
		if (this.enabler) {
			this.enabler(elem, state);
		}
	}

	get() {
		if (this.getter) {
			return this.getter(elem);
		} else {
			return null;
		}
	}

	set(value) {
		if (this.setter) {
			this.setter(elem, value);
		}
	}

	set_state(state) {
		if (this.enabler) {
			this.enabler(this.get_elem(), state);
		}
	}

	update_state(perm_data) {
		if (this.enabler && this.perm) {
			this.enabler(this.get_elem(), this.perm(perm_data));
		}
	}

	check_mod(data) {
		return this.mod(elem, data);
	}
}
