###################
LibreSignage Markup
###################

LibreSignage uses it's own simple markup syntax for formatting slide
content. The markup is based on classes that contain the actual content.
Different classes affect the way the content is rendered and can change
things like alignment, color and size etc.

Markup classes
--------------

Heading
  | Syntax: ``[h size][/h]``
  | Type:   ``inline``

Display a heading. ``size`` is the size of the heading in percents
relative to the height of the viewport.


Lead
  | Syntax: ``[lead][/lead]``
  | Type:   ``inline``
  
Display a lead paragraph. The default font size for lead paragraphs
is 4% of the viewport height. The font size can be changed using the
``[size][/size]`` class.


Bold text
  | Syntax: ``[b][/b]``
  | Type:   ``inline``

Display **bold** text.


Italics text
  | Syntax: ``[i][/i]``
  | Type:   ``inline``

Display *italic* text.


Image
  | Syntax: ``[img address width height]``
  | Type:   ``inline``

The image class can be used to embed an image. ``address`` is the URL
address of the image, ``width`` is the width of the image and
``height`` is the height of the image. The dimensions are measured
in percents of the horizontal and vertical viewport dimensions
respectively.

*Example: If the width is set to 50, the width of the image is 50% of
the **width** of the viewport. If the height is also set to 50, the
height of the image is 50% of the **height** of the viewport.*


Paragraph
  | Syntax: ``[p][/p]``
  | Type:   ``inline``

Display a paragraph. The default font size for paragraphs is 3% of
the viewport height. The font size can be changed using the
``[size][/size]`` class.


Color
  | Syntax: ``[color col][/color]``
  | Type:   ``inline``

Set the color of text. All text inside this class will have the color
set by this class if no nested classes change the color. ``col`` is
the name of the color or a hexadecimal color code.


Container
  | Syntax: ``[container top right bottom left][/container]``
  | Type:   ``block``

Create a container with specific paddings on each side. The paddings
are defined in percents of the viewport dimensions. The left and right
paddings use the width of the viewport as the reference and the top
and bottom paddings use the height of the viewport as the reference.


Horizontal centering container
  | Syntax: ``[xcenter][/xcenter]``
  | Type:   ``block``

Create a container that horizontally centers all content within it.


Column layout container
  | Syntax: ``[columns][/columns]``
  | Type:   ``block``

Create a container with a column layout. Each ``[container]`` class
inside a ``[columns]`` container creates a new column. All columns
within one ``[columns]`` container have equal width.


Font size
  | Syntax: ``[size s][/size]``
  | Type:   ``block``

Set the font size. All text inside this class will have the specified
font size if not nested classes change the size. ``s`` is the size
of the in percents relative to the height of the viewport.


Align
  | Syntax: ``[align-<right|center|left|justify>][/align-<...>]``
  | Type:   ``block``

Align text either left, center or right or justify it.


Examples
--------

Basic classes
+++++++++++++

::

  [container 10 10 10 10]
      [h 15]This is a heading[/h]
      [lead]This is a short lead paragraph.[/lead]
      [p]This is a normal paragraph that contains the
      main content of the slide[/p]
      [color red]
          [p]This is a paragraph with red text where
          part of the text is [b]bold[/b] and part
          of it is [i]italic[/i].[/p]
      [/color]
  [/container]

Columns
+++++++++

::

  [container 10 10 10 10]
      [xcenter]
          [h 12]Multi-column example[/h]
      [/xcenter]
      [columns]
          [container 2 2 2 2]
              [h 5]First column[/h]
              [p]This is the first column in this slide. Columns are created
              using the [i]columns[/i] class. Each [i]container[/i] inside a
              [i]columns[/i] class creates a new column. The maximum number of
              columns is not limited in any way.[/p]
          [/container]
          [container 2 2 2 2]
              [h 5]Second column[/h]  
              [p]This is the second column in this slide. Columns within one
              [i]columns[/i] class all have equal width and height.[/p]
          [/container]
      [/columns]
      [container 2 2 2 2]
              [p]Containers [b]outside[/b] a [i]columns[/i] class are normal full-width
          containers like this one.[/p]
      [/container]
  [/container]
