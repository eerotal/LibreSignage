###################
LibreSignage limits
###################

LibreSignage uses two kinds of action limiting for users

  1. Hard server limits.
  2. User quotas

Hard server limits are server-wide limits for all users including admins.
These limits are mainly used to limit things like slide name lengths etc.
and can only be changed by the server admin by editing the server config.

User quotas are used to limit how many times a user can do an action.
User quotas are used for limiting things like how many slides a user can
create.

Server limits and quotas are located in ``[INSTALL_DIR]/config/limits/``
and ``[INSTALL_DIR]/config/quota/`` respectively. See `Configuration
</doc?doc=configuration>`_ for more info on how to use LibreSignage
configuration files.
