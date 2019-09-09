# Introduction

Jaris CMS is a content management system that doesn't requires a RDBMS like mysql
since it is based on a file structure that stores all the content of the website.
The file structure is based on directories for each page and all it's data, making
a page query really fast. The idea of Jaris was during the development of Jaris
FLV player website on sourceforge.net. Since I could not install a CMS easily I
decided to write my own. Also I had experienced so much slowness with well known
CMS based on mysql in shared web-hosting environments, that I wanted something
lite and fast, but with almost all the features of existing well known CMS. Also
I wanted something were I could launch my favorite text editor and make
modifications.

## Features

These are some of the features and characteristics implemented on Jaris:
menus, themes, users management, groups and permissions system,
built-in support for translating content, global blocks of content, image
and file uploads per page page,  blocks of content for specific pages,
search, extendability with modules and support for multiple sites from a
single installation.

## More Reading

* [Installation](install.md)
* [Core](core.md)
* [Theming](theming.md)
* Web Servers
  * [Apache](apache.md)
  * [Caddy](caddy.md)
  * [Hiawatha](hiawatha.md)
  * [IIS](iis.md)
  * [Lighttpd](lighttpd.md)
* PHP
  * [Building](building-php.md)
  * [Configuration](php-conf.md)
  * [PHP-FPM](php-fpm.md)
  * [Opcache](php-opcache.md)
* [TODO](todo.md)
