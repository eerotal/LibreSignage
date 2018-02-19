###################
LibreSignage limits
###################

LibreSignage uses two kinds of action limiting for users

  1. Hard server limits.
  2. User quotas

Hard server limits are server-wide limits for all users including admins.
These limits are mainly used to limit things like slide name lengths etc.
and can only be changed by the server admin by editing the instance config
in *common/php/config.php*. The hard limits are defined in the *LS_LIM*
array.

User quotas are used to limit how many times a user can do an action.
User quotas are used for limiting things like how many slides a user can
create. User quotas differ from hard limits in that the current used quotas
for different users are stored on the server and whether the server allows
an action or not is based on the amount of used quota. If the quota is
completely used, the server won't allow the specific action. User quotas
are defined in the LibreSignage instance config in *common/php/config.php*
in the *DEFAULT_QUOTA* array and can only be changed by the server admin.
