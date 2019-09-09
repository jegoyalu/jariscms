# Core

## How Jaris Works?

You will find a directory named skeleton inside the system directory.
This directory gets copied into sites/default at install time, and it
serves as the site data for any domain or virtual host pointing to the
directory where you installed Jaris. The system/skeleton directory serves
as a template to create more sites on a multi-site environment. If you
want to have different domains with different site data like for example
domain1.com and domain2.com, just make a copy of the system/skeleton into
sites/domain1.com and another copy into sites/domain2.com. This way
the core will detect that a specific data directory exists for the
domain and use it in palce of sites/default.

For now, lets just focus on a single site setup. Inside of sites/default/data you will
find more directories, we are going to discuss 6 of them: blocks, menus, pages,
users, groups and settings. Each of these directories store sub-directories or database
files (discussed later) with parts of the website. Lets take a look into each.

### Blocks

The blocks folder has 6 files in it. These files are:

 * left.php      (stores the global left blocks)
 * right.php     (stores the global right blocks)
 * header.php    (stores the global header blocks)
 * footer.php    (stores the global footer blocks)
 * center.php    (stores the global center blocks)
 * none.php      (stores the deactivated blocks)

### Menus

The menus folders store a default set of 2 menus and all the menus created
by the user in different files. For example if the user created the menu
music it is stored inside the menus folder in the file music.php. Default
menu files shipped with the CMS are:

 * primary.php     (stores primary links)
 * secondary.php   (stores secondary links)

### Pages

The pages directory has 2 folders, sections and singles. The sections folder
stores all the pages that belong to a section for example:

    http://mywebsite.com/docs/install

In the example 'docs' is the section and 'install' is the page. The singles
directory as it says stores files that does not belong to a sections like for
example:

    http://mywebsite.com/contact-us

Now lets see how every page is stored in the sections folder. Lets take the
first example 'http://mywebsite.com/docs/install' the path will look like this:

    sites/default/data/pages/sections/docs/i/in/install/data.php

The section docs is created and an alphabetical structure of directories
in case there are thousand of pages. The data.php is the database
file that stores the title and content of the page. There are more files
in each page folder like:

 * image.php     (stores the list of images uploaded for the page)
 * files.php     (stores the list of any kind of file uploaded to the page)
 * blocks        (folder that stores individual blocks for the page only)
 * images        (folder that stores image binaries)
 * files         (folder that stores file binaries)

Now the singles folder use the same semantics but without sections lets
take the example 'http://mywebsite.com/contact-us' its path would look like
this:

    sites/default/data/pages/singles/c/co/contact-us/data.php

Like you see is the same thing but without sections. We just want to mark
a difference between both for more easier navigation of content. In this
way pointing your browser to a page created with jaris cms just takes some
milli seconds to query on a system with thousands of pages since it use
a folder structure with database files and not a flat file database to
store all the pages. We only need to translate the uri into a valid data
path and retrieve the data from the data.php database file.

### Settings

The settings folder is used to store configuration files known as tables
using the configuration functions available on Jaris CMS. The configuration
table that stores the website title, base url, default theme, etc... is the
'main' table with the database file name main.php. If writing a module all
your configuration options should go on a file with your module name in the
settings directory.

### Users

In the users directory you will find a group folder that classifies users and
inside, each user that belongs to that group. For example:

    users/administrator/m/my/myusername/data.php

### Groups

On the groups directory is stored a folder with the machine name of each
group and inside a data.php file with the description and Human readable
name of the group. Also a permissions.php file is stored on the groups
folders with a list of all the permissions for that group.


## Database File Format

Now that you have some understanding of the file structure used to store the CMS
data I'm going to explain the database file format implemented to store information.

### Database File Example

    <?php exit; ?>
    row: id
        field: name
            value
        field;
    row;

Lets explain each line:

1. <?php exit; ?> - This line protects the content of the file from prying eyes.
2. row: id - As you see a row of data in the file where the id is a numerical value.
3. field: name - This is a field on the row were the name is a string.
4. value - The actual value of the field
5. field; - The fields ending
6. row; - The rows ending

It is a really simple syntax and easy to parse with php built in functions.
Also the advantage is that you can mix it with html and php and still have good
syntax highlighting.

## Api

To better learn the core you can generate the api documentation. Generating
this documentation requires [apigen](http://www.apigen.org/) (php application to generate
documentation from sources) which should be automatically installed into
the vendor directory on your jaris directory the first time that you
execute **./run.sh**. With that said you can generate the documentation
executing:


    ./run.sh docs


The above command will generate all documentation files on the
doc/api directory
