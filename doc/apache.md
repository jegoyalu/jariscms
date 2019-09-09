# Apache

Apache is one of the most well known web servers and this could be attributed
to its antiquity.

To run jaris on apache make sure that you properly configure
it with mod-php which is php running embedded as an apache module and the
most easier to configure or with php running in fast cgi mode. In either
of the methods that you choose we recommend you to follow the official
apache documentation to get up and running.

## Rewrite Rule

Besides enabling **mod_rewrite** in the Apache configuration there shouldn't
be a need for any further configuration, since the needed rewrite rule is included
in the .htaccess file which is automatically read on each request that
apache process.

For more information about configuring apache please visit:
[https://httpd.apache.org/](https://httpd.apache.org/)
