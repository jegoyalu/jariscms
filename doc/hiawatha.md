# Hiawatha Webserver

## Rewrite Rule

```
UrlToolkit {
    ToolkitID = jariscms
    RequestURI exists Return
    Match /(.*)\?(.*) Rewrite /index.php?p=$1&$2
    Match /(.*) Rewrite /index.php?p=$1
}
```

## Options for the virtual host

```
UseFastCGI = PHP5
UseToolkit = jariscms

StartFile = index.php
```
