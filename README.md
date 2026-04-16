# SnowTricks

## 	Prerequisites

Install Git and clone the project with :
`git clone https://github.com/GabrielSAPONARA/SnowTricks.git` in HTTPS 
`git clone git@github.com:GabrielSAPONARA/SnowTricks.git` in SSH

## Install project

Build PHP container : `docker-compose build php`
Rebuild PHP container without cache : `docker-compose build --no-cache php`
Launch docker-compose : `docker-compose up -d`

Stop container with : `docker-compose down`

##  Use Symfony commands

 Run : `docker exec -it symfony_php bash` to launch bash and Symfony commands.
 
## phpStan

Launch phpStan : `php -d memory_limit=512M vendor/bin/phpstan analyse src`
Launch phpStan and redirect the output to PHPSTAN.md which doesn't track : 
`php -d 
memory_limit=512M vendor/bin/phpstan analyse src > 
PHPSTAN.md`

## Improvements

1. Replace Types::DATE_MUTABLE by Types::DATETIME_MUTABLE if it's necessary.