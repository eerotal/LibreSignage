Universal input validator
#########################

The LibreSignage web interface uses a universal input validator
system for validating user input. The system consists of the
``ValidatorSelector``, ``Validator`` and ``ValidatorTrigger``
classes defined in *src/common/js/validator.js*. The validator
system is designed to be simple to use and straighforward so that
it doesn't clutter up existing code.

Validator system classes
------------------------

**``ValidatorSelector(query, style, validators, callbacks)``**

The ValidatorSelector class is used to select the input elements
to validate.

* ``query`` is the query string or jQuery object used to select the
  inputs that will be validated.
* ``style`` is the query string or jQuery object used to select the
  elements on which the styling will be applied.
* ``validators`` is an array of the used validators. Validators are
  classes extending the ``Validator`` class.
* ``callbacks`` is an array of callback functions that are called
  when a change in the validation state occurs. The ``ValidatorSelector``
  object is passed to these functions as the first argument.

After the ValidatorSelector object is constructed, it automatically
starts validating the selected input elements every time an ``input``
event is fired on any one of them. If any of the configured validators
validates any of the inputs as invalid, the validation state of the
``ValidatorSelector`` is set to ``false``. Otherwise it is set to
``true``. The HTML DOM element styling is also set accordingly.

The ``ValidatorSelector`` class styles invalid inputs by applying the
``is-invalid`` class to the elements selected by ``style``. It also
adds the message of the failing validator to all elements of the class
``invalid-feedback`` that are children of the elements selected by
``style``. The used CSS classes are defined by the Bootstrap front-end
library.

The ``ValidatorSelector`` class also has the following useful functions.

``add(validator)``

  * Add a ``Validator`` object to the ``ValidatorSelector`` object.

``state()``

  * Get the validation state of the ``ValidatorSelector`` object.

``add_callback(callback)``

  * Add a callback function to the ``ValidatorSelector`` object.

``set_state(valid)``

  * Set the validation state of the ``ValidatorSelector`` object.
    This function also applies the HTML DOM styling and calls the
    callback functions when needed.

``enable()``

  * Enable the ``ValidatorSelector`` object. Enabled
    ``ValidatorSelector`` objects automatically validate the
    selected inputs.

``disable()``

  * Disable the ``ValidatorSelector`` object. This stops the
    ``ValidatorSelector`` object from validating inputs and calling
    callbacks.

``validate()``

  * Validate the selected inputs. Calling this function isn't normally
    necessary, since the ``input`` events fired on the selected inputs
    result in this function being automatically called.

**``Validator(settings, msg)``**

``Validator`` is the parent class of all actual validators. This class
is meant to be extended to provide the actual validation functionality
for different validators.

* ``settings`` is the associative settings array provided by the
  caller. The settings array contains the validator specific flags
  and values for configuring how inputs are validated. The parent
  ``Validator`` class doesn't actually use this array for anything
  other than to validate the settings using the ``chk_settings()``
  function.
* ``msg`` is the message string that's displayed to a user when an input
  is invalid. Messages are specific to validators. This means that
  when a certain validator validates an input as invalid, the message
  of that validator is displayed to the user. The message is displayed
  by adding it to the HTML of all elements of the class
  ``invalid-feedback`` that are children of the elements selected by
  the ``style`` query in a ``ValidatorSelector`` object.

The ``Validator`` class also has the following useful functions.

``get_msg()``

  * Get the message of the validator.

``chk_settings(proto)``

  * Check the settings supplied to the validator against the
    array ``proto``. If any of the settings in ``proto`` don't
    exist in the keys of the validator settings, this function
    throws an error. This function is meant to be called in the
    constructor of a validator class extending the main ``Validator``
    class after calling the original constructor using ``super()``.
    An example constructor of a validator class is below.

    ::

        constructor(...args) {
            super(...args);
            this.chk_settings(['min', 'max', 'regex']);
        }

    A validator class with this constructor function would require
    the settings ``min``, ``max`` and ``regex``. This constructor
    is actually what's used in the predefined ``StrValidator`` class.

**``ValidatorTrigger(selectors, callback)``**

The ``ValidatorTrigger`` class can be used to group together multiple
``ValidatorSelector`` objects to execute actions based on whether all
of the specified selectors have a valid state.

* ``selectors`` is an array of the selectors to use for this trigger.
* ``callback`` is the callback that's called when a change in the
  validation state of the trigger occurs.

The ``ValidatorTrigger`` class automatically calls the callback function
when either all of the selectors become valid or at least one of them
becomes invalid. In the former case the callback function is called with
``true`` as the first argument. In the latter case the first argument
is ``false``.

Predefined validators
---------------------

**``StrValidator(settings, msg)``**

  * Validate string inputs. The accepted settings are

    * ``min`` = The minimum length of the input. (integer/``null``)
    * ``max`` = The maximum length of the input. (integer/``null``)
    * ``regex`` = A whitelist regex for the input string. (regex/``null``)

**``NumValidator(settings, msg)``**

  * Validate numeric inputs. The accepted settings are

    * ``min`` = The minimum value. (number/``null``)
    * ``max`` = The maximum value. (number/``null``)
    * ``nan`` = Allow NaN values. (boolean)
    * ``float`` = Allow float values. (boolean)

**``EqValidator(settings, msg)``**

  * Validate all the selected inputs to have the same value. This
    validator doesn't require any settings.
