Note: Most of this document applies, but parts of it are outdated and incomplete.

# Table of Contents

* [Introduction](#introduction)
* [Features](#features)
* [How It Works?](#how-it-works)
    * [Blocks](#blocks)
    * [Menus](#menus)
    * [Pages](#pages)
    * [Settings](#settings)
    * [Users](#users)
    * [Groups](#groups)
* [Database File Format](#database-file-format)
    * [Database File Example](#database-file-example)
* [Install](#install)
    * [Requirements](#requirements)
    * [Url Rewriting](#url-rewriting)
    * [Running with PHP HipHop HHVM](#running-with-php-hiphop-hhvm)
* [Contact Info](#contact-info)


## Introduction

Jaris CMS is a content management system that doesn't requires a RDBMS like mysql
since it is based on a file structure that stores all the content of the website.
The file structure is based on directories for each page and all it's data, making
a page query really fast. The idea of Jaris was during the development of Jaris
FLV player website on sourceforge.net. Since I could not install a CMS easily I
decided to write my own. Also I had experienced so much slowness with well known
CMS based on mysql in shared web-hosting environments, that I wanted something
lite and fast, but with almost all the features of existing well known CMS. Also
I wanted something were I could launch my favorite text editor and make
modifications without using complicated sql queries.


## Features

These are some of the features and characteristics implemented on Jaris.

    * Menus
    * Themes
    * Users
    * User Picture
    * Groups and Permissions
    * Clean Url's
    * Translations
    * Global Blocks
    * Page Image Uploads
    * Page File Uploads
    * Page blocks
    * Content Search (using sqlite)
    * Modules
    * Multiple sites hosting.


## How It Works?

You will find a directory named main inside the data directory. This directory
gets copied into data/default at install time, and it serves as the site data
for any domain pointing to the directory where you installed Jaris. The data/main
directory serves as a template to create more sites on a multi-site environment.
You can for example have domain1.com and domain2.com, if you want a different site
for each just make a copy of the data/main into data/domain1.com and data/domain2.com.

For now, lets just focus on a single site setup. Inside of data/default you will
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

    data/default/pages/sections/docs/i/in/install/data.php

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

    data/default/pages/singles/c/co/contact-us/data.php

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


## Install

For installing Jaris CMS just copy the source files to a directory on your
public_html directory and visit it on the browser to launch the installer.

### Requirements

* PHP 5 or greater
* PHP GD library to manage images.
* Write permission on the data directory.
* Apache, Lighttpd or Hiawatha

### Url Rewriting

Url rewriting is the method used to convert from a http://domain.com/index.php?p=page
to a more human readable and search engine friendly format http://domain.com/page
In apache if mod_rewrite is enabled there is nothing you have to do to enable this,
but in lighttpd you have to manually edit the config file in order to enable
good looking url's. Here is the lines you need to add on lighttpd:

    url.rewrite-once = (
        "^/([^.?]*)\?(.*)$" => "/index.php?p=$1&$2",
        "^/([^.?]*)$" => "/index.php?p=$1"
    )

### Running with PHP HipHop HHVM

Testing was done on original version of jaris that doesn't uses namespaces and
it works pretty well. Since hiphop hasn't implemented full support for namespaces
yet, this version may not work.

On the current Jaris directory execute the hhvm binary as follows:

    hhvm -m server --config hhvm.hdf


## Contact Info

Website:    http://jariscms.com
Source:     http://github.com/jegoyalu/jariscms
