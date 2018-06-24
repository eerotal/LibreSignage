#!/usr/bin/env python3

#
#  Unit test exception definition.
#

import json

class UnitError:
	def __str__(self):
		return type(self).__name__ + ": " + self.message;

	def printerr(self):
		print(str(self));

class UnitStatusError(UnitError):
	def __init__(self, got, expected):
		self.message = ("Expected HTTP status " + str(expected) +
				". Got " + str(got) + " instead.");

class UnitHeaderError(UnitError):
	def __init__(self, name, got, expected):
		self.message = ("Expected '" + name + ": " +
				repr(expected) + "'. Got '" +
				name + ": " + type(got).__name__ +
				"(" + got + ")'.");

class UnitHeaderKeyError(UnitError):
	def __init__(self, got, expected, strict):
		got_str = ', '.join(list(got));
		expected_str = ', '.join(list(expected));
		if strict:
			self.message = ("Expected exactly keys: '" +
					expected_str + "'. Got: '" +
					got_str + "'.");
		else:
			self.message = ("Expected at least keys: '" +
					expected_str + "'. Got: '" +
					got_str + "'.");

class UnitJsonDataError(UnitError):
	def __init__(self, name, got, expected):
		self.message = ("Expected '" + name + ": " +
				repr(expected) + "'. Got '" +
				name + ": " + type(got).__name__ +
				"(" + str(got) + ")'.");

class UnitJsonDataKeyError(UnitError):
	def __init__(self, got, expected, strict):
		got_str = ', '.join(list(got));
		expected_str = ', '.join(list(expected));
		if strict:
			self.message = ("Expected exactly keys: '" +
					expected_str + "'. Got: '" +
					got_str + "'.");
		else:
			self.message = ("Expected at least keys: '" +
					expected_str + "'. Got: '" +
					got_str + "'.");

class UnitTextDataError(UnitError):
	def __init__(self, got, expected):
		self.message = ("Expected " + repr(expected) +
				". Got " + type(got).__name__ +
				"(" + str(got)) + ").";

class UnitDataTypeError(UnitError):
	def __init__(self, expected):
		self.message = ("Invalid response data type. Expected " +
				"a " + expected + " response.");
