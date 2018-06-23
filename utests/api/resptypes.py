#!/usr/bin/env python3

#
# Response validation types.
#

class RespVal: # General type, extend this.
	type = type(None);
	realname = "";

	def __init__(self, proto):
		self.proto = proto;

	def validate(self, val):
		return ((self.proto == None or val == self.proto)
			and type(val) == self.type);

	def __repr__(self):
		return self.realname + '(' + str(self.proto) + ')';

class RespDict(RespVal): # Dictionary
	type = type({});
	realname = "dict";

	def validate(self, val):
		if not self.proto:
			return True;

		if (set(self.proto.keys()) == set(val.keys())):
			for k in self.proto.keys():
				if not (self.proto[k].validate(val[k])):
					return False;
		else:
			return False;

		return True;

class RespList(RespVal): # List
	type = type([]);
	realname = "list";

	def validate(self, val):
		if not self.proto:
			return True;

		if not (len(val) == len(self.proto)):
			return False;
		for i in range(len(proto)):
			if not (proto[i] == val[i]):
				return False;
		return True;

class RespInt(RespVal): # Integer
	type = type(0);
	realname = "int";

class RespStr(RespVal): # String
	type = type("");
	realname = "str";

class RespBool(RespVal): # Boolean
	type = type(False);
	realname = "bool";
