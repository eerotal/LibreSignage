###################
LibreSignage Markup
###################

LibreSignage uses it's own simple markup syntax for formatting slide
content. The markup is based on classes that contain the actual content.
Different classes affect the way the content is rendered and can change
things like alignment, color and size etc.

Markup class arguments
----------------------

Some markup classes like ``color`` or ``container`` also require
arguments describing how the class should affect the rendered output.
These arguments are set by adding ``<name>=<value>`` to the opening tag
of the class, where ``<name>`` is the name of the argument and ``<value>``
is the argument value. The order of the arguments doesn't matter as long
as all required arguments are set.

Markup class arguments always have a specific type. You can find the
argument types for a markup class in it's respective documentation
section. The following argument types are defined.

Percent *(percent)*
  A value of the form ``<number>%``, eg. 10%. The exact interpretation
  of a percentage value depends on the context it's used in.

Integer *(int)*
  A bare number, eg 6.

String *(str)*
  A value of the form ``'<string>'`` or ``"<string>"`` where ``<string>``
  is a string of letters, numbers and other characters.

Raw string *(rstr)*
  The same thing as a normal string but raw strings don't have quotes
  around them.

Markup classes
--------------

Heading
  | Syntax: ``[h size=<size>][/h]``
  | ``size: percent``

Display a heading. ``<size>`` is the size of the heading in percents
relative to the height of the viewport.


Lead
  | Syntax: ``[lead][/lead]``

Display a lead paragraph. The default font size for lead paragraphs
is 4% of the viewport height. The font size can be changed using the
*size* class.


Bold text
  | Syntax: ``[b][/b]``

Display **bold** text.


Italics text
  | Syntax: ``[i][/i]``

Display *italic* text.


Image
  | Syntax: ``[img url=<URL> width=<width> height=<height>][/img]``
  | ``url: str``
  | ``width: percent``
  | ``height: percent``

The image class can be used to embed an image. ``<URL>`` is the URL
address of the image, ``<width>`` is the width of the image and
``<height>`` is the height of the image. The width and height are
defined as percentages of the viewport width or height respectively.
Eg. ``width=50%`` would set the image width to 50% of the viewport
**width** and ``height=50%`` would set the image height to 50% of
the viewport **height**.


Video
  | Syntax: ``[video url=<URL> width=<width> height=<height> muted=<muted>][/video]``
  | ``url: str``
  | ``width: percent``
  | ``height: percent``
  | ``muted: int``

The video class can be used to embed video. ``<URL>`` is the URL
address of the video file, ``<width>`` is the width of the video
element and ``<height>`` is the height of the video element. The
width and height are defined as percentages of the viewport width
and height respectively. Eg. ``width=50%`` would set the video
width to 50% of the viewport **width** and ``height=50%`` would set
the image height to 50% of the viewport **height**. Note that these
sizes are only the size of the video element and the actual video
scales so that the aspect ratio is always kept correct, ie. the actual
video may not always fill the whole space that's allocated for it.
``<muted>`` can be used to control whether the video plays muted or
unmuted. A value of *1* makes the video muted and *0* makes it unmuted.

Paragraph
  | Syntax: ``[p][/p]``

Display a paragraph. The default font size for paragraphs is 3% of
the viewport height. The font size can be changed using the
*size* class.


Color
  | Syntax: ``[color c=<color>][/color]``
  | ``c: rstr``

Set the color of text. All text inside this class will have the color
set by this class if no nested classes change the color. ``<color>`` must
be a valid `CSS color`_.

Font
  | Syntax: ``[font f=<font>][/font]``
  | ``f: str``

Set the font of text. All text inside this class will have the font set
by this class if no nested classes change the font. ``<font>`` must be
a valid font name that exists on the target system.

Container
  | Syntax: ``[container top=<t> right=<r> bottom=<b> left=<l>][/container]``
  | ``top: percent``
  | ``right: percent``
  | ``bottom: percent``
  | ``left: percent``

Create a container with specific paddings on each side. The paddings
are defined in percents of the viewport dimensions. The left and right
paddings use the width of the viewport as the reference and the top
and bottom paddings use the height of the viewport as the reference.
``<t>``, ``<r>``, ``<b>`` and ``<l>`` are the top, right, bottom and
left paddings respectively.


Horizontal centering container
  | Syntax: ``[xcenter][/xcenter]``

Create a container that horizontally centers all content within it.


Column layout container
  | Syntax: ``[columns][/columns]``

Create a container with a column layout. Each ``[container]`` class
inside a ``[columns]`` container creates a new column. All columns
within one ``[columns]`` container have equal width.


Font size
  | Syntax: ``[size size=<s>][/size]``

Set the font size. All text inside this class will have the specified
font size if not nested classes change the size. ``<s>`` is the size
of the font in percents relative to the height of the viewport.


Align
  | Syntax: ``[align type=<type>][/align]``
  | ``type: rstr``

Align text. ``<type>`` can be ``left``, ``right``, ``center``
or ``justify``


Background color
  | Syntax: ``[bgcolor c=<color>][/bgcolor]``
  | ``type: rstr``

Change the background color. ``<color>`` must be a valid `CSS color`_.


Background image
  | Syntax: ``[bgimg url=<URL>][/bgimg]``
  | ``type: str``

Set a background image. ``<URL>`` must be a valid URL pointing to
the image file.

Iframe
  | Syntax: ``[iframe url=<URL>][/iframe]``
  | ``type: str``

Embed a url using an iframe. ``<URL>`` must be a valid URL pointing to
the web page.

Examples
--------

Basic classes
+++++++++++++

::

  [container top=10% right=10% bottom=10% left=10%]
      [h size=15%]This is a heading[/h]
      [lead]This is a short lead paragraph.[/lead]
      [p]This is a normal paragraph that contains the
      main content of the slide[/p]
      [color c=red]
          [p]This is a paragraph with red text where
          part of the text is [b]bold[/b] and part
          of it is [i]italic[/i].[/p]
      [/color]
  [/container]

Columns
+++++++++

::

  [container top=10% right=10% bottom=10% left=10%]
      [xcenter]
          [h size=12%]Multi-column example[/h]
      [/xcenter]
      [columns]
          [container top=2% right=2% bottom=2% left=2%]
              [h size=5%]First column[/h]
              [p]This is the first column in this slide. Columns are created
              using the [i]columns[/i] class. Each [i]container[/i] inside a
              [i]columns[/i] class creates a new column. The maximum number of
              columns is not limited in any way.[/p]
          [/container]
          [container top=2% right=2% bottom=2% left=2%]
              [h size=5%]Second column[/h]
              [p]This is the second column in this slide. Columns within one
              [i]columns[/i] class all have equal width and height.[/p]
          [/container]
      [/columns]
      [container top=2% right=2% bottom=2% left=2%]
              [p]Containers [b]outside[/b] a [i]columns[/i] class are normal full-width
          containers like this one.[/p]
      [/container]
  [/container]


.. _`CSS color`: https://developer.mozilla.org/en-US/docs/Web/CSS/color
