# PHP configuration

PHP can be configured from the php.ini file. When using the hiawatha webserver
the follwing values need to be set:

    cgi.fix_pathinfo = 0
    cgi.rfc2616_headers = 1

## Hiawatha php.ini production example

    ; customizations to php engine
    expose_php = Off
    max_execution_time = 120
    max_input_vars = 2500
    error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT
    post_max_size = 151M
    cgi.fix_pathinfo = 0
    cgi.rfc2616_headers = 1
    upload_max_filesize = 150M
    memory_limit = 256M
    session.gc_maxlifetime = 172800
    display_errors = On
    sys_temp_dir = "/path/to/tmp"
    session.save_path = "/path/to/tmp"
