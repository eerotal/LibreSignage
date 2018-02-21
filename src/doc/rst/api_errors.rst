API error codes
###############

The LibreSignage API may return one of the following error codes if an
error occurs. The API response is *guaranteed* to contain at least the
``error`` key containing the error code or ``API_E_OK`` if no error
occured.

API_E_OK
  No error

API_E_INTERNAL
  Internal server error

API_E_INVALID_REQUEST
  The request was invalid or some of the arguments passed to the endpoint
  were invalid.

API_E_NOT_AUTHORIZED
  The API call was not authorized, meaning either the user is not logged
  in or they are not in a group that's allowed to use a certain API call.

API_E_QUOTA_EXCEEDED
  One of the user quotas was exceeded and the server ignored the request.
  See `Limits </doc?doc=limits>`_ for more information about limits.

API_E_LIMITED
  A hard server limit was exceeded and the server ignored the request.
  See `Limits </doc?doc=limits>`_ for more information about limits.

API_E_CLIENT
  Reserved for client side use. This error code is **never** used on
  the server.

API_E_RATE
  The API ignored the request because the rate limit was exceeded.
