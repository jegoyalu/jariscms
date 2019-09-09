# Hiawatha Webserver

Hiawatha is an open source webserver that is secure, lightweight and
easy to use. It has a special emphasis on security and has features
to prevent: SQL injections, XSS and CSRF attacks and exploit attempts.

In order to properly run jariscms with hiawatha you will need to add
the following options to the hiawatha configuration file.

## Rewrite Rule

You can place the following rewrite rule on the
/etc/hiawatha/hiawatha.conf file

    UrlToolkit {
        ToolkitID = jariscms
        RequestURI exists Return
        Match /(.*)\?(.*) Rewrite /index.php?p=$1&$2
        Match /(.*) Rewrite /index.php?p=$1
    }

## Options for the virtual host

The following options can be placed directly on the the main
/etc/hiawatha/hiawatha.conf file if you are going to host
a jariscms install as the default hiawatha site.

    UseFastCGI = PHP7
    UseToolkit = jariscms
    StartFile = index.php

If you are planning to host it as an alternative virtual host an example
configuration can be the following:

    VirtualHost {
        Hostname = www.my-domain.com
        WebsiteRoot = /srv/http/my-domain/public
        AccessLogfile = /srv/http/my-domain/log/access.log
        ErrorLogfile = /srv/http/my-domain/log/error.log
        TimeForCGI = 30
        UseFastCGI = PHP7
        UseToolkit = jariscms
        UseLocalConfig = yes
        StartFile = index.php
    }


For more information about configuring hiawatha please visit:
[https://www.hiawatha-webserver.org/](https://www.hiawatha-webserver.org/)
