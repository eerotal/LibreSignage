/*
*  A class providing a layer of abstraction between HTML DOM
*  elements and UI code.
*/

class UIControl {
	constructor(_elem, _perm, _enabler, _mod, _getter, _setter, _clear) {
		this._elem = _elem;
		this._perm = _perm;
		this._enabler = _enabler;
		this._mod = _mod;
		this._getter = _getter;
		this._setter = _setter;
		this._clear = _clear;
	}

	get_elem() {
		if (typeof this._elem == 'function') {
			return this._elem();
		} else {
			return this._elem;
		}
	}

	enable(state) {
		if (this._enabler) {
			this._enabler(this.get_elem(), state);
		}
	}

	get() {
		if (this._getter) {
			return this._getter(this.get_elem());
		} else {
			return null;
		}
	}

	set(data) {
		if (this._setter) {
			this._setter(this.get_elem(), data);
		}
	}

	set_state(state) {
		if (this._enabler) {
			this._enabler(this.get_elem(), state);
		}
	}

	state(perm_data) {
		if (this._enabler && this._perm) {
			this._enabler(this.get_elem(), this._perm(perm_data));
		}
	}

	is_mod(data) {
		if (this._mod) {
			return this._mod(this.get_elem(), data);
		} else {
			return false;
		}
	}

	clear() {
		if (this._clear) {
			this._clear(this.get_elem());
		}
	}
}
