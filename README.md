rexstan
=======

Adds code analysis to REDAXO improving developer productivity and code quality.

![Screenshots](https://github.com/FriendsOfREDAXO/rexstan/blob/assets/stanscreen.png?raw=true)


## IDE Integration

The most effective way to use rexstan is to integrate it with your IDE.
That way you get problems reported while working on your source code.

### PHPStorm

Open `Preferences` and search for `phpstan`.

Navigate to `PHP` -> `Quality Tools` -> `PHPStan` and open the `Local Configuration` by pressing the `...`-Button.

Configure the `PHPStan path` to `/path/to/your/project/redaxo/src/addons/rexstan/vendor/bin/phpstan`.
Click `validate` and verify no error is reported.

Click `PHPStan Inpsection`. Enable the `PHPStan validation` by ticking the checkbox.
Configure the `Configuration file` to `/path/to/your/project/redaxo/src/addons/rexstan/phpstan.neon`.

You might consider raising the `Severity` for the `PHPStan validation` to either `Warning` or `Error`.

Close all Dialogs with `OK`.

## Web UI

When your webserver allows, you can see and run the analysis via the REDAXO backend web interface.
This might not work on any server, because of security settings.

To optimize the developer experience you should enable REDAXO editor integration.

## REDAXO Console

Its possible to run the analysis via the REDAXO console via `php redaxo/bin/console rexstan:analyze` command, which should work in most environments.

This might be usefull, to e.g. create reports, so you can track reported problems and your progress handling them over time.

## REDAXO Docker 

If you use rexstan with [docker-redaxo](https://github.com/FriendsOfREDAXO/docker-redaxo) you might need to set the /tmp folder writable. Open the docker console and run: `chmod 777 -R /tmp && chmod o+t -R /tmp`

## PHP Memory limits 

If you encounter problems with memory consumption, the PHP memory limit should be increased. 

Set the PHP Memory limit in php.ini to: `memory_limit = 1024M` or more

**For REDAXO Docker Image**

Open the Docker console and set the new memory limit with : 
`printf 'memory_limit = 1024M\n' >> /usr/local/etc/php/conf.d/uploads.ini \`

Restart the container

## 💌 Support rexstan

[Consider supporting the project](https://github.com/sponsors/staabm), so we can make this tool even better even faster for everyone.


## Credits

- rexstan by [Markus Staab](https://github.com/staabm)
- rexstan logo by Ralph Zumkeller, yakamara.de
- PHPStan by [Ondřej Mirtes](https://github.com/ondrejmirtes) and [contributors](https://github.com/phpstan/phpstan-src/graphs/contributors) 
