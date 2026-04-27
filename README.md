# SnowTricks

## Prerequisites

Install Docker :
    - Linux :
        - [Documentation to install Docker on Linux](https://docs.docker.com/engine/install)
    - Windows :
        - [Documentation to install Docker on Windows](https://docs.docker.com/desktop/setup/install/windows-install)
    - Mac :
        - [Documentation to install Docker on MacOS](https://docs.docker.com/desktop/setup/install/mac-install)

Put one link to install Docker.

Install Git and clone the project with :
`git clone https://github.com/GabrielSAPONARA/SnowTricks.git` in HTTPS
`git clone git@github.com:GabrielSAPONARA/SnowTricks.git` in SSH

## Install project

Build PHP container : `docker-compose build php`
Rebuild PHP container without cache : `docker-compose build --no-cache php`
Launch docker-compose : `docker-compose up -d`

Stop container with : `docker-compose down`

## Use Symfony commands

Run : `docker exec -it symfony_php bash` to launch bash and use Symfony's 
commands, composer's commands, PHP's commands...

## PHPStan

Launch phpStan : `php -d memory_limit=512M vendor/bin/phpstan analyse src`
Launch phpStan and redirect the output to PHPSTAN.md which doesn't track :
`php -d 
memory_limit=512M vendor/bin/phpstan analyse src > 
PHPSTAN.md`

## Encountered Errors

See container which are running with : `docker ps`
If you have a line with MySQL which is running like this :
```bash
4fb426ce3140   mysql:8.0   "docker-entrypoint.s…"   8 months ago   Up 15 minutes   33060/tcp, 0.0.0.0:3307->3306/tcp, [::]:3307->3306/tcp   mysql-db
```
Kill MySQL with this command : `docker kill mySqlContainerId`
For example : `docker kill 4fb426ce3140`
