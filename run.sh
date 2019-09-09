#!/usr/bin/env bash
# Script with various utilities related to JarisCMS development.

cd "$(dirname "$0")" || exit

initialize() 
{
    PHAN_PATH=vendor/phan/phan/phan
    PHPLOC_PATH=vendor/phploc/phploc/phploc
    APIGEN_PATH=vendor/apigen/apigen/bin/apigen

    PHP_FOUND=$(command -v php)

    if [ "$PHP_FOUND" = "" ]; then
        echo "Please install php (http://php.net/)"
        exit
    fi

    COMPOSER_FOUND=$(command -v composer)

    if [ "$COMPOSER_FOUND" = "" ]; then
        echo "Please install composer (http://getcomposer.org/)"
        exit
    fi

    if [ ! -d "vendor" ]; then
        composer install
    fi
}

showhelp()
{
    local heading="JarisCMS launcher, test tool and utilities.\n"
    heading="${heading}Copyright (C) 2016, Jefferson González <jgonzalez@jegoyalu.com>\n"

    local usage='Usage: run.sh [COMMAND] [OPTION]\n'

    local help_cmd="  \e[32mhelp\e[0m        Show this help screen. Run './run.sh help help' for details."

    local server_cmd="  \e[32mserver\e[0m      Launches the php builtin server on port 8080 for development."

    local server_opt="  -host    The address to bind to. (default: localhost)\n"
    server_opt=$server_opt"  -port    The port to run the php server. (default: 8080)"

    local ui_cmd="  \e[32mui\e[0m          Same as 'server' but runs an instance of chromium in app mode.\n"
    ui_cmd="$ui_cmd              The php server instance is shutdown automatically when chromium is closed."

    local ui_opt="  -host    The address to bind to. (default: localhost)\n"
    ui_opt=$ui_opt"  -port    The port to run the php server. (default: 8080)"

    local profile_cmd="  \e[32mprofile\e[0m     Same as 'server' but also enables xdebug to generate profiling\n"
    profile_cmd="$profile_cmd              data to cachegrind.out. (requires: xdebug)"

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

    local loc_cmd="  \e[32mloc\e[0m         Count the amount of lines of code (requires: phploc)."

    local loc_opt='  -core    Core sources.\n'
    loc_opt=$loc_opt'  -pages   Core pages sources.\n'
    loc_opt=$loc_opt'  -mods    Module sources.\n'
    loc_opt=$loc_opt'  -all     All sources. (default)'

    local sqlitetobdb_cmd="  \e[32msqlite2bdb\e[0m  Converts all sqlite 3 databases to sql berkeley db."

    local bdbtosqlite_cmd="  \e[32mbdb2sqlite\e[0m  Converts all sql berkeley db to sqlite 3."

    echo -e "$heading"

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
            echo -e "$server_opt"
            ;;
        'ui' )
            echo "Description:"
            echo -e "  Initializes a JarisCMS php server and chromium app instance\n" \
                " for instant development. The php server process is automatically\n" \
                " closed when the chromium app window is closed.\n"
            echo 'Usage: run.sh ui [OPTION]'
            echo -e "\nOptions:\n"
            echo -e "$ui_opt"
            ;;
        'profile' )
            echo "Description:"
            echo -e "  Initializes a JarisCMS php server for development purpose\n" \
                " with xdebug profiling enabled.\n"
            echo 'Usage: run.sh profile [OPTION]'
            echo -e "\nOptions:\n"
            echo -e "$server_opt"
            ;;
        'sqlite2bdb' )
            echo "Description:"
            echo -e "  Converts all sqlite3 databases to berkeley databases using\n" \
                " newest Berkeley DB sqlite mode for better concurrency.\n"
            echo 'Usage: run.sh sqlite2bdb [SITE]'
            echo "Default value for SITE: default"
            ;;
        'bdb2sqlite' )
            echo "Description:"
            echo -e "  Converts all sql berkeley databases to sqlite3 database."
            echo 'Usage: run.sh bdb2sqlite [SITE]'
            echo "Default value for SITE: default"
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
            echo -e "$ui_cmd"
            echo -e "$profile_cmd"
            echo -e "$test_cmd"
            echo -e "$phan_cmd"
            echo -e "$geanytags_cmd"
            echo -e "$tags_cmd"
            echo -e "$docs_cmd"
            echo -e "$loc_cmd"
            echo -e "$sanityze_cmd"
            echo -e "$sqlitetobdb_cmd"
            echo -e "$bdbtosqlite_cmd"
            ;;
    esac
}

runserver()
{
    local host="localhost"
    local port=8079
    local port_open=0

    while [ $1 ]; do
        case $1 in
            '-host' )
                shift
                host=$1
                ;;
            '-port' )
                shift
                port=$(($1-1))
                ;;
            * )
                echo "Invalid option given."
                exit
        esac

        shift
    done

    until [ $port_open -eq 1 ]; do
        port=$((port+1))

        port_open=$(netstat -an | grep " $port " | grep LISTEN)

        if [ "$port_open" = "" ]; then
            port_open=1
        else
            port_open=0
        fi
    done

    local started=1

    while [ $started -eq 1 ]; do
        php -S $host:$port router.php 2> /dev/null
        
        started=$?

        port=$((port+1))
    done
}

runui()
{
    local port=8080

    if [ "$1" != "" ]; then
        if [ "$1" != "ui" ]; then
            port=$1
        fi
    fi

    until php -S localhost:$port router.php & 2> /dev/null; do
        port=$((port+1))
    done

    local server=$!
    local browser=""

    local chromium_installed=$(command -v chromium)
    local firefox_installed=$(command -v firefox)

    if [ -n "$chromium_installed" ]; then
        chromium --app="http://localhost:$port" &
        browser="chromium"
    elif [ -n "$firefox_installed" ]; then
        firefox "http://localhost:$port" &
        browser="firefox"
    fi

    sleep 7 # wait to properly get browser process id

    local id=$(pgrep -n -f $browser)

    while [ "$(pgrep -n -f $browser)" = "$id" ]; do
        sleep 1
    done

    echo -n "Shutting webserver down... "
    kill $server
    echo "(Done!)"
}

runprofiler()
{
    local port=8080

    if [ "$1" != "" ]; then
        if [ "$1" != "profile" ]; then
            port=$1
        fi
    fi

    until php -d zend_extension=xdebug.so \
        -d xdebug.profiler_enable=1 \
        -d xdebug.profiler_append=1 \
        -d xdebug.profiler_output_dir="$(pwd)" \
        -d xdebug.profiler_output_name=cachegrind.out \
        -S localhost:$port router.php 2> /dev/null; do

        port=$((port+1))
    done
}

runtest()
{
    php tests/run.php
}

runphan()
{
    local jobs=$(echo "scale=0; $(nproc)/1.2" | bc)

    case $1 in
        '-core' )
            shift
            echo "Checking core functions:"
            echo "========================================================================="
            $PHAN_PATH -j $jobs \
                --directory src \
                --progress-bar \
                $@ \
                index.php upload.php uris.php cron.php
            exit
            ;;
        '-pages' )
            shift
            echo "Checking system pages and skeleton:"
            echo "========================================================================="
            $PHAN_PATH -j $jobs \
                --exclude-directory-list src,vendor \
                --directory src --directory system \
                --progress-bar \
                $@ \
                $(find src -name "*.php")
            exit
            ;;
        '-mods' )
            shift

            local exclude="src,vendor,modules/dompdf,modules/invoice,"
            exclude="${exclude}modules/ads,"
            exclude="${exclude}modules/spreadsheet_reader,"
            exclude="${exclude}modules/engine_parts/tools,"
            exclude="${exclude}modules/hybridauth/Hybrid,"
            exclude="${exclude}modules/hybridauth/docs,"
            exclude="${exclude}modules/minify/min,modules/ophir/src,"
            exclude="${exclude}modules/revision/htmldiff,"
            exclude="${exclude}modules/facebook/php-graph-sdk,"
            exclude="${exclude}modules/markdown/phpmarkdown,"
            exclude="${exclude}modules/reservations"

            echo "Checking modules:"
            echo "========================================================================="
            $PHAN_PATH -j $jobs \
                --minimum-severity 5 \
                --exclude-directory-list $exclude \
                --directory src --directory modules \
                --progress-bar \
                $@ \
                $(find src -name "*.php")
            exit
            ;;
        '--help' | '-h' )
            $PHAN_PATH --help
            exit
            ;;
        * )
            echo "Please specify a valid command option: [-core,-pages, -modules]."
            echo "You can also pass the --help flag to view phan/phan specific help options"
            echo "than can be passed after one of the valid command options."
            exit
            ;;
    esac
}

rungeanytags()
{
    GEANY_FOUND=$(command -v geany)

    if [ "$GEANY_FOUND" = "" ]; then
        echo "Please install geany (http://geany.org/)"
        exit
    fi

    geany -g jariscms.php.tags \
        $(find . -type f -iname "*.php")

    mkdir -p "$HOME"/.config/geany/tags/

    cp -f jariscms.php.tags "$HOME"/.config/geany/tags/

    echo
    echo "======================================="
    echo "Geany tags stored on: jariscms.php.tags"
    echo "and Installed to: ~/.config/geany/tags/"
    echo "======================================="
}

runsanityzer()
{
    PERL_FOUND=$(command -v perl)

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

    for file in $(eval $find_command); do
        echo -n "Formatting ${file}... "

        original_md5sum=$(md5sum "$file")

        # replace windows new lines with unix ones
        perl -i -pe 's|\r\n$|\n|' "$file"

        # trim lines
        perl -i -pe 's|\s+\n$|\n|' "$file"

        # replace tabs with 4 spaces
        expand -t 4 "$file" > expand.temp
        cat expand.temp > "$file"
        rm expand.temp
        #perl -i -pe 's|\t|    |g' $file

        new_md5sum=$(md5sum "$file")

        total=$((total + 1))

        if [ "$original_md5sum" = "$new_md5sum" ]; then
            total_unchanged=$((total_unchanged + 1))
            echo "(unchanged)"
        else
            total_updated=$((total_updated + 1))
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
    CTAGS_FOUND=$(command -v ctags)

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
            $PHPLOC_PATH --progress $(find src -name "*.php")
            exit
            ;;
        '-pages' )
            $PHPLOC_PATH --progress $(find system/pages -name "*.php")
            exit
            ;;
        '-mods' )
            $PHPLOC_PATH --progress $(find modules -name "*.php")
            exit
            ;;
        * )
            $PHPLOC_PATH --progress $(find src -name "*.php") \
                $(find system/pages -name "*.php") \
                $(find modules -name "*.php")
            exit
            ;;
    esac
}

runsqlitetobdb()
{   
    SITE="default"
    if [ "$1" != "" ]; then
        SITE="$1"
    fi

    for file in $(find "sites/$SITE" modules -regextype egrep -not -regex '.+\.[a-zA-Z0-9]+$' -not -type d); do
        ftype=$(head -c 16 "$file" | tr '\0' '\n')
        if [ "$ftype" == "SQLite format 3" ]; then
            echo "Converting: $file"
            mv "$file" "$file.sqlite";
            sqlite3 "$file.sqlite" .dump \
                    | db_sqlite3 $file
            rm "$file.sqlite"
        fi
    done
}

runbdbtosqlite()
{   
    SITE="default"
    if [ "$1" != "" ]; then
        SITE="$1"
    fi

    for file in $(find "sites/$SITE" modules -regextype egrep -not -regex '.+\.[a-zA-Z0-9]+$' -not -type d); do
        if [ -d "$file-journal" ]; then
            echo "Converting: $file"
            db_sqlite3 "$file" .dump \
                    | sqlite3 "$file.sqlite"
            rm -rf "$file-journal"
            mv "$file.sqlite" "$file"
            sqlite3 "$file" "pragma journal_mode=wal;" > /dev/null
        fi
    done
}

runsqlitetowal()
{   
    SITE="default"
    if [ "$1" != "" ]; then
        SITE="$1"
    fi

    for file in $(find "sites/$SITE" modules -regextype egrep -not -regex '.+\.[a-zA-Z0-9]+$' -not -type d); do
        ftype=$(head -c 16 "$file" | tr '\0' '\n')
        if [ "$ftype" == "SQLite format 3" ]; then
            echo "Changing to wal: $file"
            sqlite3 "$file" "pragma journal_mode=wal;" > /dev/null
        fi
    done
}

while [ $1 ]; do
    case $1 in
        'server' )
            shift
            runserver $@
            exit
            ;;
        'ui' )
            shift 2
            runui $1
            exit
            ;;
        'profile' )
            shift 2
            runprofiler $1
            exit
            ;;
        'test' )
            runtest
            exit
            ;;
        'phan' )
            initialize
            shift
            runphan $@
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
            initialize
            rundocs
            exit
            ;;
        'sanityze' )
            runsanityzer
            exit
            ;;
        'loc' )
            initialize
            shift
            runloc $1
            exit
            ;;
        'sqlite2bdb' )
            shift
            runsqlitetobdb $1
            exit
            ;;
        'bdb2sqlite' )
            shift
            runbdbtosqlite $1
            exit
            ;;
        'sqlite2wal' )
            shift
            runsqlitetowal $1
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
