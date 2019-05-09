#########################
LibreSignage User Manager
#########################

The LibreSignage User Manager is available for users of the group *admin*.
Other users can't access the page. This page is used for managing the users
and groups of the LibreSignage instance.

Editing users
-------------

Users can be edited by clicking the name of a user in the list of users on the
*User Manager* page. This opens a dropdown editor for editing user settings.
Admins can only edit the groups of a user. Usernames are immutable and can't
be changed after creation. If a username needs to be changed, the user must
be deleted and recreated with a different name afterwards.

New user groups can be added by writing a group name in the *Groups* input
box and clicking the *Plus (+)* button. Group names can only contain the characters
A-Z, a-z, 0-9, - and _. Groups can be removed by clicking the *X* symbol next to
a group name in the list. Note that you must save the user data afterwards by
clicking the *Save (Floppy icon)* button or the changes won't be preserved.

Users can be removed by clicking the *Remove (Trash icon)* button.

Creating new users
------------------

New users can be created by clicking the *Create user* button and specifying a
name for the new user in the popup dialog. The maximum length of a username is
set in the LibreSignage instance config and can be changed only by server admins.
The maximum length is 64 characters by default.

When a new user is created they are given an automatically generated password.
The initial password is visible to the admin user in the *Password* input box
in the user editor dropdown **once** after creating the user. The password is not
visible (or known by the server) on subsequent reloads of the page.
