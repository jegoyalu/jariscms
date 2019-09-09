# IIS Webserver

The IIS webserver hasn't been tested much, but there is at least one
success story on running Jaris on it. This document doesn't has all
the details of installing PHP with IIS, etc... but below is a rewrite
rule that used to work properly on some specific version of IIS which
I can't recall at the time of writing this.

## Rewrite Rule

Create a web.config file on the root directory of jariscms and put
something like the following:

```
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <defaultDocument>
            <files>
                <add value="index.php" />
            </files>
        </defaultDocument>
        <rewrite>
            <rules>
                <rule name="JarisCMS Clean URLs" stopProcessing="true">
                    <match url="^(.*)$" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php?p={R:1}" appendQueryString="true" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```
