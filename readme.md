# About this software
Enkele web applications om regelgeving rond Badminton competities te vereenvoudigen voor de gebruiker
* opstellings app: stel een geldige opstelling samen
* verplaatsings app: structureer de aanvraag om een match te verplaatsen voor thuisploeg / uitploeg & competitie organisator
* competitie agenda: link naar google agenda's van de verschillende ploegen

# Local setup
cp laravel-9.x/.env.example laravel-9.x/.env

docker-compose up

docker run -it --rm -v  $(pwd)/laravel-9.x:/app composer:2.4.2 /bin/sh
> php artisan key:generate

# License
The software is open-sourced software [MIT](https://opensource.org/licenses/MIT)
