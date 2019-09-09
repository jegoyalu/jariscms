# Caddy

Caddy is an open source **Go** webserver that offers ssl encryption by default
using the letsencrypt platform. Supports HTTP/2 and is really easy to use
and configure.

In order to properly run jariscms with Caddy you will need to write a
Caddyfile like the one we are sharing below. Also you should use setcap
to give the caddy binary permission to bind on ports 80/443 for non root
users.

    sudo setcap CAP_NET_BIND_SERVICE=+eip /usr/bin/caddy

## Caddyfile

    # Security options
    (security) {
        timeouts {
            read   10m
            header 5s
            write  10m
            idle   1m
        }

        limits {
            header 256kb
            body   150mb
        }

        # needs the ratelimit plugin
        ratelimit * / 25 50 second
    }

    # Base options for a JarisCMS install
    (jaris) {
        # Comment to disable the default security options when benchmarking
        import security

        # PHP fastcgi setup
        fastcgi / 127.0.0.1:9000 php {
            connect_timeout 1m
            read_timeout 1m
            send_timeout 1m
        }

        # Protect directories from public access
        rewrite {
            if {path} starts_with /sites/
            if {path} has /data/
            to /index.php?p={path}
        }

        rewrite {
            if_op or
            if {path} is /src
            if {path} is /themes
            if {path} is /modules
            if {path} is /changes.txt
            if {path} starts_with /src/
            if {path} starts_with /system/pages/
            if {path} starts_with /system/language/
            if {path} starts_with /system/skeleton/
            if {path} starts_with /system/install/distros/
            to /index.php?p={path}
        }

        # Send non existent content to the index file
        rewrite {
            if {query} not ""
            to {path} {path}/ /index.php?p={path}&{query}
        }

        rewrite {
            to {path} {path}/ /index.php?p={path}
        }
    }

    # HTTP/1 definition
    *:80 {
        # Start the php fastcgi process
        on startup php-fpm -y php-fpm.conf
        on shutdown pkill -9 php-fpm

        # Import base jaris options
        import jaris

        # Compress all output content
        gzip
    }

    # HTTP/2 definition
    *:443 {
        # Generates a self signed certificate that works for local development
        # and enables HTTP/2 support.
        tls self_signed

        # Import base jaris options
        import jaris

        # Compress all output content
        gzip
    }

For more information about configuring Caddy please visit:
[https://caddyserver.com/](https://caddyserver.com/)
