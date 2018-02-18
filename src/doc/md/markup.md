# LibreSignage Markup

LibreSignage uses it's own simple markup syntax for formatting slide
content. The markup is based on classes that contain the actual content.
Different classes affect the way the content is rendered and can change
things like alignment, color and size etc.

### Markup classes

**Heading**

Syntax: `[h weight][/h]`  
Display a heading. `weight` is an integer number in the range 1-6 where
1 is the largest heading and 6 is the smallest one.

**Lead**

Syntax: `[lead][/lead]`  
Display a lead paragraph.

**Bold text**

Syntax: `[b][/b]`  
Display **bold** text.

**Italics text**

Syntax: `[i][/i]`  
Display *italic* text.

**Image**

Syntax: `[img address width height]`  
The image class can be used to embed an image. `address` is the URL
address of the image, `width` is the width of the image and `height`
is the height of the image. The dimensions are measured in percents of
the horizontal and vertical viewport dimensions respectively.

*Example: If the width is set to 50, the width of the image is 50% of
the __width__ of the viewport. If the height is also set to 50, the
height of the image is 50% of the __height__ of the viewport.*

**Paragraph**

Syntax: `[p][/p]`  
Display a paragraph.

**Color**

Syntax: `[color col][/color]`  
Set the color of text. All text inside this class will have the color
set by this class if no nested classes change the color. `col` is the
name of the color or a hexadecimal color code.

**Container**

Syntax: `[container pad-top pad-right pad-bottom pad-left][/container]`
Create a container with specific paddings on each side. The paddings
are defined in percents of the viewport dimensions. The left and right
paddings use the width of the viewport as the reference and the top and
bottom paddings use the height of the viewport as the reference.

**Font size**

Syntax: `[size s][/size]`  
Set the font size. All text inside this class will have the specified
font size if not nested classes change the size. `s` is the size
of the text in points.

### Examples

```
[container 10 10 10 10]
        [h 1]This is a heading[/h]
        [lead]This is a short lead paragraph.[/lead]
        [p]
               This is a normal paragraph that contains the
               main content of the slide
        [/p]
        [color red]
                [p]
                        This is a paragraph with red text where
                        part of the text is [b]bold[/b] and part
                        of it is [i]italic[/i].
                [/p]
        [/color]
[/container]
```
