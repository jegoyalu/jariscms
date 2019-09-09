# Lighttpd

The lighttpd webserver was tested many years ago and it worked properly
using the fast cgi mechanism. A working rewrite rule used at that time
can be seen below.

## Rewrite Rule

Since Lighttpd has evolved a lot in the years, the syntax for defining
rewrite rules may have changed.

    url.rewrite-once = (
         "^/([^.?]*)\?(.*)$" => "/index.php?p=$1&$2",
         "^/([^.?]*)$" => "/index.php?p=$1"
    )

For more information about configuring lighttpd please visit:
[https://www.lighttpd.net/](https://www.lighttpd.net/)
