#########################
LibreSignage User Manager
#########################

The LibreSignage User Manager is available for users of the group *admin*.
Other users can't access the page. This page is used for managing the users
and groups of the LibreSignage instance.

Editing users
-------------

Users can be edited by clicking the *Edit* button on a row in the users table.
This opens a dropdown editor for editing user settings. Admins can currently
only edit the groups of a user. Usernames are immutable and can't be changed
after creation. If a username needs to be changed, the user must be deleted and
recreated with a different name afterwards.

User groups can be changed by editing the Groups list, where the groups are
defined by a comma separated list. This input only accepts alphanumeric characters
(A-Z, a-z, 0-9) and the dash (-) and underscore (_) chacraters. Spaces are also
accepted but they are removed when the changes are saved.

Users can be removed by clicking the *Remove* button.

Creating new users
++++++++++++++++++

New user can be created by clicking the *Create User* button and specifying a
name for the new user in the popup dialog. The maximum length of the username is
set in the LibreSignage instance config and can be changed only by server admins.
The maximum length is 64 by default.

When the user is created, it is given an automatically generated password. The
password is visible to the admin user on the *Information* column of the users table.
Note that the password is only visible until the page is reloaded. After reloading
the page the admin user can't see any user passwords.
