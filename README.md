# Projet SnowTricks

Projet 6 OpenClassrooms-Blog 
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7ac950a4227a466288d0e76a67e02a03)](https://www.codacy.com/gh/AureLey/P6-SnowTricks/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=AureLey/P6-SnowTricks&amp;utm_campaign=Badge_Grade)


Setup

Set PHP in 8.1

Get git repository

```
git clone https://github.com/AureLey/P6-SnowTricks.git
```

Get composer dependencies

```
composer install
```

Check server adress
```
server adress : http://localhost:8000
```
Database creation and Fixtures
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
E-mail command to consume/send all e-mails ( can be catch with Mailhog)
```
php bin/console messenger:consume async
```

Admin login 

```
login:admin
password:admin
```
