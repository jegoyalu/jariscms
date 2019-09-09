# PHP-FPM

The php-fpm fast cgi server is the standard choice to serve your PHP
web applicaitons and it also offers the best performance in contrast to
plain cgi solutions or alternate generic fast cgi servers. You can launch
a php-fpm process from the cli and give it a custom configuration file as
follows:

    php-fpm -y php-fpm.conf

An example configuration file is below.

## php-fpm.conf

    [global]
    error_log=/dev/null
    daemonize = yes

    [jaris]
    ;Optional user and group for the process.
    ;user = http
    ;group = users
    listen = 127.0.0.1:9000

    pm = dynamic
    pm.max_children = 5
    pm.start_servers = 2
    pm.min_spare_servers = 1
    pm.max_spare_servers = 3

For more information about configuring php-fpm please visit:
[https://www.php.net/manual/en/install.fpm.php](https://www.php.net/manual/en/install.fpm.php)
