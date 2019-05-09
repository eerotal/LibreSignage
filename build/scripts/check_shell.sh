#!/bin/sh

if [ -n "$BASH" ]; then
	SHELL_NAME='bash'
elif [ -n "$ZSH_NAME" ]; then
	SHELL_NAME='zsh'
fi

if [ -n "$SHELL_NAME" ]; then
	printf '%80s\n' | tr ' ' '='
	echo "[Warning] You appear to be using the $SHELL_NAME shell."\
		"This shouldn't normally happen unless '/bin/sh' is actually"\
		"not the 'sh' shell on your system. The LibreSignage build"\
		"system is designed to be used with 'sh', so it can't be"\
		"guaranteed to work with $SHELL_NAME. It probably will, but"\
		"you may face problems." | fold --spaces --width=80
	printf '%80s\n' | tr ' ' '='
fi
