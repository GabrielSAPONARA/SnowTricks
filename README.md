# SnowTricks

## 	Prerequisites

Install Git and clone the project with :
`git clone https://github.com/GabrielSAPONARA/SnowTricks.git` in HTTPS 
`git clone git@github.com:GabrielSAPONARA/SnowTricks.git` in SSH

## Install project

Build PHP container : `docker-compose build php`
Launch docker-compose : `docker-compose up -d`

Stop container with : `docker-compose down`

##  Use Symfony commands

 Run `docker exec -it symfony_php bash` to launch bash and Symfony commands.
 
## Improvements

1. Replace Types::DATE_MUTABLE by Types::DATETIME_MUTABLE if it's necessary.