#!/usr/bin/env bash
# Script with various utilities related to JarisCMS development.

cd "$(dirname $0)"

PHAN_PATH=vendor/etsy/phan/phan
PHPLOC_PATH=vendor/phploc/phploc/phploc
APIGEN_PATH=vendor/apigen/apigen/bin/apigen

PHP_FOUND=`command -v php`

if [ "$PHP_FOUND" = "" ]; then
    echo "Please install php (http://php.net/)"
    exit
fi

COMPOSER_FOUND=`command -v composer`

if [ "$COMPOSER_FOUND" = "" ]; then
    echo "Please install composer (http://getcomposer.org/)"
    exit
fi

if [ ! -d "vendor" ]; then
    composer install
fi

showhelp()
{
    local heading="JarisCMS launcher, test tool and utilities.\n"
    heading="${heading}Copyright (C) 2016, Jefferson González <jgonzalez@jegoyalu.com>\n"

    local usage='Usage: run.sh [COMMAND] [OPTION]\n'

    local help_cmd="  \e[32mhelp\e[0m        Show this help screen. Run './run.sh help help' for details."

    local server_cmd="  \e[32mserver\e[0m      Launches the php builtin server on port 8080 for development."

    local server_opt="  -port    The port to run the php server. (default: 8080)"

    local profile_cmd="  \e[32mprofile\e[0m     Same as 'server' but also enables xdebug to generate profiling\n"
    profile_cmd="$profile_cmd              data to cachegrind.out. (requires: xdebug)"

    local hhvm_cmd="  \e[32mhhvm\e[0m        Launches hhvm builtin server. (requires: hhvm)"

    local test_cmd="  \e[32mtest\e[0m        Runs the testing scripts."

    local phan_cmd="  \e[32mphan\e[0m        Statically analyze the sources. (requires: phan)"

    local phan_opt="  -core    Analyse core.\n"
    phan_opt=$phan_opt"  -pages   Analyse core pages.\n"
    phan_opt=$phan_opt"  -mods    Analyse modules."

    local geanytags_cmd="  \e[32mgeanytags\e[0m   Generates a geany editor tags file."

    local tags_cmd="  \e[32mctags\e[0m       Generates a tags file compatible with vim. (requires: ctags)"

    local docs_cmd="  \e[32mdocs\e[0m        Generates api documentation. (requires: apigen)"

    local sanityze_cmd="  \e[32msanityze\e[0m    Helps you keep the sources trimmed and properly formatted to\n"
    sanityze_cmd=$sanityze_cmd"              4 spaces instead of tabs and unix line endings instead of windows.\n"
    sanityze_cmd=$sanityze_cmd"              (requires: perl)"

    local loc_cmd="  \e[32mloc\e[0m         Count the amount of lines of code (requres: phploc)."

    local loc_opt='  -core    Core sources.\n'
    loc_opt=$loc_opt'  -pages   Core pages sources.\n'
    loc_opt=$loc_opt'  -mods    Module sources.\n'
    loc_opt=$loc_opt'  -all     All sources. (default)'

    echo -e $heading

    case $1 in
        'phan' )
            echo "Description:"
            echo -e "  Do static analysis of source code. (requires: phan).\n"
            echo 'Usage: run.sh phan OPTION'
            echo -e "\nOptions:\n"
            echo -e "$phan_opt"
            ;;
        'loc' )
            echo "Description:"
            echo -e "  Count the amount of lines of code. (requires: phploc).\n"
            echo 'Usage: run.sh loc [OPTION]'
            echo -e "\nOptions:\n"
            echo -e "$loc_opt"
            ;;
        'server' )
            echo "Description:"
            echo -e "  Initializes a JarisCMS php server for development purpose.\n"
            echo 'Usage: run.sh server [OPTION]'
            echo -e "\nOptions:\n"
            echo "$server_opt"
            ;;
        'profile' )
            echo "Description:"
            echo -e "  Initializes a JarisCMS php server for development purpose\n" \
                " with xdebug profiling enabled.\n"
            echo 'Usage: run.sh profile [OPTION]'
            echo -e "\nOptions:\n"
            echo "$server_opt"
            ;;
        'help' )
            echo "Description:"
            echo -e "  Displays help for a given command with its options.\n"
            echo 'Usage: run.sh help [COMMAND]'
            echo -e "\nDocumented Commands:\n"
            echo "  server"
            echo "  profile"
            echo "  phan"
            echo "  loc"
            ;;
        * )
            echo -e "$usage"
            echo -e "Available Commands:\n"
            echo -e "$help_cmd"
            echo -e "$server_cmd"
            echo -e "$profile_cmd"
            echo -e "$hhvm_cmd"
            echo -e "$test_cmd"
            echo -e "$phan_cmd"
            echo -e "$geanytags_cmd"
            echo -e "$tags_cmd"
            echo -e "$docs_cmd"
            echo -e "$sanityze_cmd"
            ;;
    esac
}

runserver()
{
    local port=8080

    if [ "$1" != "" ]; then
        if [ "$1" != "server" ]; then
            port=$1
        fi
    fi

    php -S localhost:$port router.php
}

runprofiler()
{
    local port=8080

    if [ "$1" != "" ]; then
        if [ "$1" != "profile" ]; then
            port=$1
        fi
    fi

    php -d zend_extension=xdebug.so \
        -d xdebug.profiler_enable=1 \
        -d xdebug.profiler_append=1 \
        -d xdebug.profiler_output_dir=`pwd` \
        -d xdebug.profiler_output_name=cachegrind.out \
        -S localhost:$port router.php
}

runhhvm()
{
    HHVM_FOUND=`command -v hhvm`

    if [ "$HHVM_FOUND" = "" ]; then
        echo "Please install hhvm (http://hhvm.com/)"
        exit
    fi

    hhvm --config hhvm/server.hdf --mode server
}

runtest()
{
    php tests/run.php
}

runphan()
{
    case $1 in
        '-core' )
            echo "Checking core functions:"
            echo "========================================================================="
            $PHAN_PATH --minimum-severity=0 --backward-compatibility-checks \
                --progress-bar `find src` index.php upload.php uris.php cron.php
            exit
            ;;
        '-pages' )
            echo "Checking system pages and skeleton:"
            echo "========================================================================="
            $PHAN_PATH --minimum-severity=0 --backward-compatibility-checks \
                --progress-bar \
                `find src` \
                `find ./system -name "*.php"`
            exit
            ;;
        '-mods' )
            echo "Checking modules:"
            echo "========================================================================="
            $PHAN_PATH --minimum-severity=0 --backward-compatibility-checks \
                --progress-bar \
                `find src` \
                `find ./modules -name "*.php"`
            exit
            ;;
        * )
            echo "Please specify a phan command option."
            exit
            ;;
    esac
}

rungeanytags()
{
    GEANY_FOUND=`command -v geany`

    if [ "$GEANY_FOUND" = "" ]; then
        echo "Please install geany (http://geany.org/)"
        exit
    fi

    geany -g jariscms.php.tags \
        `find . -type f -iname "*.php"`

    mkdir -p $HOME/.config/geany/tags/

    cp -f jariscms.php.tags $HOME/.config/geany/tags/

    echo
    echo "======================================="
    echo "Geany tags stored on: jariscms.php.tags"
    echo "and Installed to: ~/.config/geany/tags/"
    echo "======================================="
}

runsanityzer()
{
    PERL_FOUND=`command -v perl`

    if [ "$PERL_FOUND" = "" ]; then
        echo "Please install perl (http://www.perl.org/)"
        exit
    fi

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
    CTAGS_FOUND=`command -v ctags`

    if [ "$CTAGS_FOUND" = "" ]; then
        echo "Please install ctags (http://ctags.sourceforge.net/)"
        exit
    fi

    ctags -f tags --languages=PHP -R src modules

    echo
    echo "======================================="
    echo "Tags stored on: tags"
    echo "======================================="
}

rundocs()
{
    $APIGEN_PATH generate -s src -d doc/api --title=JarisCMS --groups=packages

    echo
    echo "======================================="
    echo "Documentation stored on: doc/api"
    echo "======================================="
}

runloc()
{
    case $1 in
        '-core' )
            $PHPLOC_PATH --progress `find src`
            exit
            ;;
        '-pages' )
            $PHPLOC_PATH --progress `find system/pages`
            exit
            ;;
        '-mods' )
            $PHPLOC_PATH --progress `find modules -name "*.php"`
            exit
            ;;
        * )
            $PHPLOC_PATH --progress `find src` \
                `find system/pages` \
                `find modules -name "*.php"`
            exit
            ;;
    esac
}

while [ $1 ]; do
    case $1 in
        'server' )
            shift 2
            runserver $1
            exit
            ;;
        'profile' )
            shift 2
            runprofiler $1
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
        'phan' )
            shift
            runphan $1
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
        'loc' )
            shift
            runloc $1
            exit
            ;;
        'help' )
            shift
            showhelp $1
            exit
            ;;
        * )
            echo "Invalid command or option given."
            exit
            ;;
    esac

    shift
done

showhelp
