# sortir
<p align="center">
  <img width="300" height="250" src="https://cdn.pixabay.com/photo/2018/03/21/06/30/people-3245739_960_720.png">
</p>
<p align="center" ><em>image source : Pixabay</em></p>

<p align="center">
  <a href="https://symfony.com/"><img height="30" src="https://img.shields.io/badge/Symfony-lightgrey?style=flat&logo=symfony&logoColor=white&labelColor=black&link=http://left&link=http://right"></a>
  <a href="https://mariadb.org/"><img height="30" src="https://img.shields.io/badge/MySQL-lightgrey?style=flat&logo=MySQL&logoColor=white&labelColor=red"></a>
  <a href="https://www.php.net/"><img height="30" src="https://img.shields.io/badge/Php-lightgrey?style=flat&logo=php&logoColor=white&labelColor=8892BF"></a>
  <a href="https://getcomposer.org/"><img height="30" src="https://img.shields.io/badge/Composer-lightgrey?style=flat&logo=composer&logoColor=44f&labelColor=eee&Color=red"></a>
</p>


## Install

### Clone and install required packages :

    git clone https://github.com/christanvt/sortir.git
    cd sortir
    composer install

### Configure database access (change db_user and db_password to your needs) :

    echo "APP_ENV=dev" > .env.local
    echo "# APP_DEBUG=0" > .env.local
    echo "APP_SECRET=secret" >> .env.local
    echo "DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/Projet_student" >> .env.local

### Create database :

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

### Load required fixtures :

    php bin/console doctrine:fixtures:load required

## Test fixtures

### Load test fixtures :

    php bin/console doctrine:fixtures:load


<p align="center"><em><a href="https://github.com/christanvt"><kbd><img src="https://avatars.githubusercontent.com/u/74545031?v=4" height="30"/></kbd>christanvt</a></em> - <em><a href="https://github.com/sebbod"><kbd><img src="https://avatars.githubusercontent.com/u/4048286?v=4" height="30"/></kbd>sebbod</a></em> - <em><a href="https://github.com/BertrandBurel"><kbd><img src="https://avatars.githubusercontent.com/u/36533855?v=4" height="30"/></kbd>BertrandBurel</a></em></p>

