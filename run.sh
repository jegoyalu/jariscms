#!/usr/bin/env bash
# Helper interface to launch jariscms and test it in various ways.

cd "$(dirname $0)"

showhelp()
{
    echo "JarisCMS Launcher and Test tool"
    echo "Copyright (C) 2016, Jefferson González <jgonzalez@jegoyalu.com>"
    echo
    echo 'Usage: run.sh [OPTION]'
    echo
    echo 'OPTIONS:'
    echo
    echo '    help        Show this help screen'
    echo '    server      Launches the php builtin server on port 8080 for development.'
    echo '    profile     Same as "server" but also enables xdebug to generate profiling '
    echo '                data to cachegrind.out. (requires: xdebug)'
    echo '    hhvm        Launches hhvm builtin server. (requires: hhvm)'
    echo '    test        Runs the testing scripts.'
    echo '    phancore    Analyse using phan for core. (requires: phan)'
    echo '    phanpages   Analyse using phan for pages. (requires: phan)'
    echo '    phanmodules Analyse using phan for modules. (requires: phan)'
    echo '    geanytags   Generates a geany editor tags file.'
    echo '    ctags       Generates a tags file compatible with vim. (requires: ctags)'
    echo '    docs        Generates api documentation. (requires: apigen)'
    echo '    sanityze    Helps you keep the sources trimmed and properly formatted to'
    echo '                4 spaces instead of tabs and unix line endings instead of windows.'

}

runserver()
{
    php -S 0.0.0.0:8080 router.php
}

runprofiler()
{
    php -d zend_extension=xdebug.so \
        -d xdebug.profiler_enable=1 \
        -d xdebug.profiler_append=1 \
        -d xdebug.profiler_output_dir=`pwd` \
        -d xdebug.profiler_output_name=cachegrind.out \
        -S 0.0.0.0:8080 router.php
}

runhhvm()
{
    hhvm --config hhvm/server.hdf --mode server
}

runtest()
{
    php tests/run.php
}

runphan()
{
    echo "Checking core functions:"
    echo "========================================================================="
    phan --minimum-severity=0 --backward-compatibility-checks \
        `find src`
}

runphanpages()
{
    echo "Checking system pages and skeleton:"
    echo "========================================================================="
    phan --minimum-severity=0 --backward-compatibility-checks \
        `find src` \
        `find ./system -name "*.php"`
}

runphanmodules()
{
    echo "Checking modules:"
    echo "========================================================================="
    phan --minimum-severity=0 --backward-compatibility-checks \
        `find src` \
        `find ./modules -name "*.php"`
}

rungeanytags()
{
    geany -g jariscms.php.tags \
        `find . -type f -iname "*.php"`

    mkdir -p ~/.config/geany/tags/

    cp -f jariscms.php.tags ~/.config/geany/tags/

    echo
    echo "======================================="
    echo "Geany tags stored on: jariscms.php.tags"
    echo "and Installed to: ~/.config/geany/tags/"
    echo "======================================="
}

runsanityzer()
{
find_command=$(cat <<'END_HEREDOC'
find .
    -name "*.php"
    -o -name "*.css"
    -o -name "*.js"
    -o -name "*.txt"
    -o -name "*.md"
    -o -name "*.ini"
    -o -name "*.config"
    -o -name "*.html"
    -o -name "*.htm"
    -o -name "*.sh"
    -o -name ".htaccess"
    -o -name ".hiawatha"
END_HEREDOC
)

    total=0
    total_updated=0
    total_unchanged=0

    for file in `eval $find_command`; do
        echo -n "Formatting ${file}... "

        original_md5sum=`md5sum $file`

        # replace windows new lines with unix ones
        perl -i -pe 's|\r\n$|\n|' $file

        # trim lines
        perl -i -pe 's|\s+\n$|\n|' $file

        # replace tabs with 4 spaces
        expand -t 4 $file > expand.temp
        cat expand.temp > $file
        rm expand.temp
        #perl -i -pe 's|\t|    |g' $file

        new_md5sum=`md5sum $file`

        total=$(($total + 1))

        if [ "$original_md5sum" = "$new_md5sum" ]; then
            total_unchanged=$(($total_unchanged + 1))
            echo "(unchanged)"
        else
            total_updated=$(($total_updated + 1))
            echo "(updated)"
        fi
    done

    echo "======================================="
    echo "Total updated: ${total_updated}"
    echo "Total unchanged: ${total_unchanged}"
    echo "Total: ${total}"
    echo "======================================="
}

runctags()
{
    ctags -f tags --languages=PHP -R src modules

    echo
    echo "======================================="
    echo "Tags stored on: tags"
    echo "======================================="
}

rundocs()
{
    apigen generate -s src -d doc/api --title=JarisCMS --groups=packages

    echo
    echo "======================================="
    echo "Documentation stored on: docs/api"
    echo "======================================="
}

while [ $1 ]; do
    case $1 in
        'server' )
            runserver
            exit
            ;;
        'profile' )
            runprofiler
            exit
            ;;
        'hhvm' )
            runhhvm
            exit
            ;;
        'test' )
            runtest
            exit
            ;;
        'phancore' )
            runphan
            exit
            ;;
        'phanpages' )
            runphanpages
            exit
            ;;
        'phamodules' )
            runphanmodules
            exit
            ;;
        'geanytags' )
            rungeanytags
            exit
            ;;
        'ctags' )
            runctags
            exit
            ;;
        'docs' )
            rundocs
            exit
            ;;
        'sanityze' )
            runsanityzer
            exit
            ;;
        * )
            showhelp
            exit
            ;;
    esac

    shift
done

showhelp
