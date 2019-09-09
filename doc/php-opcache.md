# PHP Opcache extension

The opcache extension offers nice performance increasing features that will
accelerate Jaris. Depending on your php installation you can create a
opcache.ini file and store it on /etc/php/conf.d

## opcache.ini

Example of a working configuration file.

    zend_extension=opcache.so

    opcache.enable=1
    opcache.memory_consumption=128
    opcache.interned_strings_buffer=8
    opcache.max_accelerated_files=10000
    opcache.revalidate_freq=1
    opcache.fast_shutdown=1
    opcache.enable_cli=1

    opcache.save_comments=1
    opcache.enable_file_override=1

    ;optimizations
    opcache.file_update_protection=0
    opcache.enable_file_override=1
