# Simple Symfony Rest Api

## Installation

* Clone or download project
* You can change the variables in the optional .env file(NGINX_PORT, MYSQL_HOST_NAME, MYSQL_DATABASE ...)
* Start containers
* Run migration and fixtures creation commands. In this step, the data will be created in the permanent database. You don't need to run this command again.

```bash
$ git clone https://github.com/epicentre/symfony-simple-rest-api.git
$ cd symfony-simple-rest-api
$ docker-compose up --build -d

$ docker exec -it path_app php bin/console doctrine:database:create --if-not-exists
$ docker exec -it path_app php bin/console doctrine:migrations:migrate -n
$ docker exec -it path_app php bin/console doctrine:fixtures:load -n

```

## Usage
Import Postman Collection file(Postman.postman_collection.json) set "url" environment(http://localhost:3000) and run endpoints.

Default Api Base Url: http://localhost:3000 

Default User-Pass: cwepicentre@gmail.com:123456
