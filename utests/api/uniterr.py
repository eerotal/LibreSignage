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

class UnitHeaderError(UnitError):
	def __init__(self, name, got, expected):
		self.message = ("Expected '" + name + ": " + expected +
				"'. Got '" + name + ": " + got + "'.");

class UnitHeaderKeyError(UnitError):
	def __init__(self, got, expected):
		got_str = ', '.join(list(got));
		expected_str = ', '.join(list(expected));
		self.message = ("Expected keys: '" + expected_str +
				"'. Got: '" + got_str + "'.");

class UnitDataError(UnitError):
	def __init__(self, name, got, expected, resp):
		self.message = ("Expected '" + name + ": " +
				repr(expected) + "'. Got '" +
				name + ": " + type(got).__name__ +
				"(" + str(got) + ")'.");

class UnitDataKeyError(UnitError):
	def __init__(self, got, expected, resp):
		got_str = ', '.join(list(got));
		expected_str = ', '.join(list(expected));
		self.message = ("Expected keys: '" + expected_str +
				"'. Got: '" + got_str + "'.");

class UnitDataTypeError(UnitError):
	def __init__(self, expected):
		self.message = ("Invalid response data type. Expected " +
				"a " + expected + " response.");
