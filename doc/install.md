# Installation

This document will explain how to install jaris for develepment and
lightly touch installation in a production webserver.

## Requirements

 * PHP 5 or greater
 * Write permission on the sites directory.
 * GD extension to manage images.
 * SQLite extension
 * Url Rewriting

## Url Rewriting

Before proceeding we are going to superficially explain what url rewriting is
in case you aren't familiar with the concept. Jaris uses url rewriting
which is the method used to convert from a url like this
http://domain.com/index.php?p=page to a more human readable and search
engine friendly format like http://domain.com/page The way url rewriting
work is dependant of the web server software that you are going to use,
but all of them use regular expressions that evaluate and match a path
of a url and make neccesary substitutions depending on the given rules.

## Installation for local development

Jaris can be fully tested using the PHP built-in web server. We have
included a shell script that facilitates many of the development task
associated to Jaris. To start working with Jaris open a terminal and:

    cd /path/to/jariscms
    ./run.sh server

By default the **./run.sh server** will launch the php built-in server
on port 8080, but you can modify this behaviour by using the **-port**
flag. You can run *./run.sh help server* for more details.

After running **./run.sh server** you can open your web browser and
point it to **http://localhost:8080** where you will be greeted by
the installer. Follow the installation steps and enjoy!

In case that you are running on a non unix OS that doesn't has access
to a decent shell scripting environment (like windows), you can
manually invoke the php built-in server by running:

    cd /path/to/jariscms
    php -S localhost:8080 router.php

## Installation on a web server for production

For installing Jaris CMS just copy your jaris project files to a directory
on your webserver public_html directory and visit it on the browser to
launch the installer. For more partial installation instructions for
specific web servers, visit the following sections:

 * [Apache](apache.md)
 * [Caddy](caddy.md)
 * [Hiawatha](hiawatha.md)
 * [IIS](iis.md)
 * [Lighttpd](lighttpd.md)
