# LibreSignage Slides

LibreSignage is based on the concept of using slides for displaying
information. Most people are familiar with slideshows where slides
are also used for various things. In LibreSignage slides work in a
similar way. The only real difference is that LibreSignage slides are
much simpler, meaning they don't support such a wide variety of different
graphical elements etc. This is because these types of elements aren't
really needed in a digital signage system. Images can, however, be used
to replace elements like graphs etc.

## Editing and creating slides

Slides can be edited in the _Editor_ page of LibreSignage. LibreSignage
users can create and edit slides if they are in the group _editor_.
Users of the group _admin_ can change the groups of other users using
the [LibreSignage User Manager](/doc?doc=user_manager).

If the slide data fields like _Name_ and _Index_ are invalid, the _Save_
button is disabled. This means the slide can't be saved until the fields
have valid values.

### Creating a slide

Slides are created with the _New_ button. This button creates a new slide
but doesn't save it yet. The slide must be saved manually by pressing the
_Save_ button.

### Editing an existing slide

Existing slides can be edited by selecting them in the _Slides_ list,
editing the values and saving them using the _Save_ button.

### Data fields

**Name** - The _Name_ field is used for a display slide name in the
LibreSignage editor. This name is only visible in the editor. The _Name_
field only accepts alphanumeric characters (A-Z, a-z, 0-9) and the dash
(-) and underscore (_) characters. The maximum length of the name is set
in the LibreSignage instance config and by default it's set to 32
characters. This length limit can be changed by editing the
*SLIDE_NAME_MAX_LEN* limit value.

**Time** - The _Time_ selector controls how long the slide is shown in
the slideshow. The values in the selector are in seconds.

**Index** - The index value controls the order of the slides in the
slideshow. The slide with index 0 is the first slide, index 1 is the
second slide etc. The _Index_ field only accepts numbers in the range
0-65536 by default. This range can be changed in the LibreSignage
instance config, however it shouldn't be changed without investigating
the consequences (ie. don't change it if you don't know what you are
doing). The *SLIDE_MAX_INDEX* limit controls the maximum index.

#### Markup

The markup field contains the actual slide content. The markup field
accepts a special markup sytax described in
[LibreSignage Markup](/doc?doc=markup). The maximum length of the markup
is set in the LibreSignage instance config and by default it's set to
2048 characters. The amount of space the markup takes should be taken
into account when fiddling with the markup length limit. The
*SLIDE_MARKUP_MAX_LEN* limit controls the length limit.

### Buttons

**Save** - Save the current slide.

**New** - Create a new slide. Note that this doesn't save the slide.

**Remove** - Remove the selected slide.

**Preview Slide** - Preview the current slide in a separate tab. The
slide must be saved before it can be previewed.

## Slide quotas

The amount of slides one user can create is limited by
[User Quotas](/doc?doc=quotas) that are set in the LibreSignage instance
config in _common/php/config.php_. A slide quota of 10 would mean a
user can create a total of 10 slides. If the user attempts to create
more slides than they are allowed to, the user is notified that their
quota is exceeded. The current personal quota limits and the used quotas
are visible in the main _Control Panel_ page for all users.
