#Theming System

* Introduction
* How it Works?
    * Mixing it all together
* Granularity
    * Pages
    * Content
    * Blocks
    * Content Blocks
    * User Profiles
* Variables
    * page.php
    * content.php
    * block.php
    * block-content.php
    * user-profile.php
* Theme info file

##Introduction

Jaris works with a PHP based theming system that brings all the powerful features of the language to theme integrators.

Learning another theme system can be tedious, so if you are already doing PHP coding you should feel right at home. While there are many people that prefer a third party theme engine I opted for a PHP solution to keep requirements to a minimum.

##How it Works?

Each theme consist of a basic set of base files that constitute the
skeleton needed for a working theme. These files are:

 * **block.php** - A block is a special way of adding snippets of content to the page. You can place them on the header, center, left, right and footer of the page. This template file is used for global blocks and is displayed within page.php.

 * **block-content.php** - Content blocks share the same use of global blocks but at the content level. They can only be assigned to a single page and are displayed within the content.php template.

 * **content.php** - Html for the part of the site that displays the main content, this template also makes use of content blocks and is displayed within page.php.

 * **info.php** - Store the information of the theme like main author, contact details as theme name and version.

 * **page.php** - The main skeleton of each displayed page. This is the final theme file used to combine all other theme files.

 * **preview.png** - A preview image of the theme with a size of 200px x 150px

 * **search-header.php** - Heading of search results (optional)

 * **search-result.php** - Results row (optional)

 * **search-footer.php** - Footer of search results (optional)

 * **style.css** - The main style of the theme automatically appended to the head section of the page.

 * **user-profile.php** - If user profiles are enabled on the cms settings then this template is used for that purpose.

* **functions.php** - A file where you can write custom functions that can be called from page.php. (optional)

###Mixing it all together

The following ascii graphic shows how template files are used and where
on the page they are displayed. It starts from page.php been the main
container that includes every other theme file.



    +--------------------------------------------------------------------------------------------------+
    |  page.php                                                                                        |
    |                                                                                                  |
    |  +--------------------------------------------------------------------------------------------+  |
    |  |                                    block.php (header)                                      |  |
    |  +--------------------------------------------------------------------------------------------+  |
    |                                                                                                  |
    |  +-----------+--------------------------------------------------------------------+-----------+  |
    |  |           |  content.php                                                       |           |  |
    |  |           | +-----------------+----------------------------+-----------------+ |           |  |
    |  |           | |                 | block-content.php (header) |                 | |           |  |
    |  |           | |                 |                            |                 | |           |  |
    |  |           | |                 | +------------------------+ |                 | |           |  |
    |  |           | |                 | |                        | |                 | |           |  |
    |  |           | |                 | |                        | |                 | |           |  |
    |  | block.php | |                 | |                        | |                 | | block.php |  |
    |  |           | |block-content.php| |        content         | |block-content.php| |           |  |
    |  |  (left)   | |                 | |                        | |                 | |  (right)  |  |
    |  |           | |      (left)     | |                        | |     (right)     | |           |  |
    |  |           | |                 | |                        | |                 | |           |  |
    |  |           | |                 | |                        | |                 | |           |  |
    |  |           | |                 | +------------------------+ |                 | |           |  |
    |  |           | |                 |                            |                 | |           |  |
    |  |           | |                 | block-content.php (footer) |                 | |           |  |
    |  |           | +-----------------+----------------------------+-----------------+ |           |  |
    |  |           |                         block.php (center)                         |           |  |
    |  +-----------+--------------------------------------------------------------------+-----------+  |
    |                                                                                                  |
    |  +--------------------------------------------------------------------------------------------+  |
    |  |                                     block.php (footer)                                     |  |
    |  +--------------------------------------------------------------------------------------------+  |
    |                                                                                                  |
    +--------------------------------------------------------------------------------------------------+

The above graphic is just a general concept so that you can quickly
understand how the theme files are used, but it doesn't means you
would need to always use this kind of format. From page.php you can
control where the content and global blocks are displayed, while from
content.php file you can control where the content blocks are displayed.

##Granularity

As you can notice the page.php file serves as the main skeleton for all
pages. What happens if you want to add a custom skeleton for a specific
section of your site? Lets say you have a section named 'About' and the
uri to access this section is 'about' (mydomain.com/about), you would
need to create another page.php file renamed to page-about.php to give
a custom look to this page.

You can add more template files to theme specific parts of the site.
To do this you copy one of the base ones and rename it to match one
individual element of your choice. Obviously there are certain rules
and conventions for the extra template files name used.

###Pages

For pages you have 2 ways of theming specific sections. The first one
is specifying the uri after the word 'page' followed by a dash ( - ) as
mentioned before. For example:

    page-uri.php

The other method is to give the same look to all the pages that belong
to a top level section by appending a dash at the end, that functions
as a '*' wildcard. For example, you have the section "docs", and you
want to style each sub-section of it like "docs/introduction" the same,
you would create a template file named as follows:

    page-docs-.php

###Content

For theming the region of the site that displays the main text/content
you have also two options. As pages you can name your templates as follows:

    content-uri.php

The other way of theming your content is by indicating the machine
name of the content type, first lets discuss some details. Jaris
supports the addition of different types of content type. By default
jaris ships with the 'page' content type. As an example lets assume
you created a new content type named 'Products' with the machine name
'product'. This new content type has custom fields that you want to
display to the public, also you may want to display images on a
carrousel with some nice effects. To accomplish the mentioned actions
you will need to create a custom content template for the content type
'product' as follows:

    content-product.php

###Blocks

Blocks can be themed by position. The valid positions for a block are:

 * header
 * center
 * left
 * right
 * footer

Blocks can be also themed by the id of a block which is the numerical
value assigned to it by the system, and page or uri where the block is
being displayed. The following is a reference to the name file formats
supported.

 * block-position-id.php
 * block-position.php
 * block-uri.php

###Content Blocks

As mentioned before, content blocks work the same way as blocks due but
belong only to a single page and are displayed inside the content.php
template. They support same name formats supported by blocks plus some
additional ones:

 * block-content-position-id.php
 * block-content-position.php
 * block-content-uri-position.php
 * block-content-uri.php
 * block-content-type.php

Like you can see it supports two new name formats
"block-content-uri-position" and "block-content-type". The first one
should be obvious, and the second one uses the type of content been
displayed.

###User Profiles

With the user profile template you can control how profile pages
look by username or group as follows:

 * user-profile-username-the_username.php
 * user-profile-group.php

##Variables

Each of the template files discussed so far have access to a set of
predefined variables. I will list them here for convenience. Beside the
predefined variables you can call core Jaris functions from theme files
for more control and custom functionality over the templates.

###page.php

Variables used on the head section of the page:

 * **$title** - A combination of $site_title and $content_title.
 * **$header_info** - System generated metatags.
 * **$meta** - Meta description and other meta tags appended by modules.
 * **$styles** - Dynamically generated styles required for the page.
 * **$scripts** - Dynamically generated scripts required for the page.

Other variables used through the page content:

 * **$page** - Uri of the current page.
 * **$base_url** - Main url of the site.
 * **$theme_path** - Path to current theme.
 * **$slogan** - Slogan of the site.
 * **$content** - The main content of the page, generated with content.php
 * **$left** - Global blocks for left, generated with blocks.php
 * **$center** - Global blocks for center, generated with blocks.php
 * **$right** - Global blocks for right, generated with blocks.php
 * **$header** - Global blocks for header, generated with blocks.php
 * **$footer** - Global blocks for footer, generated with blocks.php
 * **$primary_links** - UL HTML code of primary menu.
 * **$secondary_links** - UL HTML code of secondary menu.
 * **$site_title** - Main title of the site.
 * **$tabs** - List of essential buttons to perform many actions.
 * **$messages** - Automatically generated system messages.
 * **$content_title** - Title of the current content.
 * **$footer_message** - Usually the copyright message of the site.
 * **$breadcrumb** - Site navigation menu (currently disabled).

###content.php

 * **$page** - Uri of current page.
 * **$title** - Title of current content.
 * **$content** - The main content.
 * **$views** - The amount of times the content has been seen.
 * **$content_data** - Array with all the fields of the content.
 * **$images** - Array of images uploaded to the content.
 * **$files** - Array of files uploaded for the content.
 * **$header** - Content blocks for header, generated with blocks-content.php
 * **$footer** - Content blocks for footer, generated with blocks-content.php
 * **$left** - Content blocks for left, generated with blocks-content.php
 * **$right** - Content blocks for right, generated with blocks-content.php
 * **$center** - Content blocks for center, generated with blocks-content.php

###block.php

 * **$page** - Uri of current page.
 * **$position** - Current position of block.
 * **$id** - Numerical id of the current block.
 * **$title** - Title of the block.
 * **$content** - Content of the block.
 * **$field** - Array with all the fields of the block.

###block-content.php

 * **$page** - Uri of current page.
 * **$position** - Current position of block.
 * **$id** - Numerical id of the current block.
 * **$title** - Title of the block.
 * **$content** - Content of the block.
 * **$field** - Array with all the fields of the block.
 * **$post** - Boolean that indicate if the block should be displayed as post.
 * **$image** - HTML for image preview in case of $post == true.
 * **$image_path** - Individual path to image in case of $post == true.
 * **$post_title** - HTML for post title in case of $post == true.
 * **$post_title_plain** - Title of post without HTML in case of $post == true.
 * **$view_more** - HTML for post title in case of $post == true.
 * **$view_url** - Plain url of view more in case of $Post == true.

###user-profile.php

 * **$username**
 * **$user_data** - Array with all the individual data of user.
 * **$age** - Age in years of the user.
 * **$gender** - Male or Female.
 * **$personal_text** - Some information entered by the user itself.
 * **$birth_date** - Date of birth in 'Month day' format
 * **$register_date** - Day the user registered on the site as 'd/m/Y' format.
 * **$latest_post** - HTML list with latest posts.

## Theme info file

The theme info.php file purpose is to provide you with a facility to
include the theme author and some other details. It consist of an
associative array named $theme with the following indexes:

 * **name** - Name of the theme.
 * **description** - Description of the theme.
 * **version** - Numerical version of the theme.
 * **author** - Main author name.
 * **email** - Main author e-mail.
 * **website**  - Main author site.

Here is some example of the info.php content:

    <?php
        $theme["name"] = "My Theme";
        $theme["description"] = "A theme created by me.";
        $theme["version"] = "1.0";
        $theme["author"] = "My Name";
        $theme["email"] = "myemail@mypersonalorcorporatesite.com";
        $theme["website"] = "http://mypersonalorcorporatesite.com";
    ?>
