# L1 Challenge Devbox

## Summary

- Dockerfile & Docker-compose setup with PHP8.1 and MySQL
- Symfony 5.4 installation with a /healthz endpoint and a test for it
- After the image is started the app will run on port 9002 on localhost. You can try the existing
  endpoint: http://localhost:9002/healthz
- The default database is called `database` and the username and password are `root` and `root`
  respectively
- Makefile with some basic commands

## Installation

```
  make run && make install
```
Enter into the container with:
```
  make enter
```
Run the database migration:
```
  php bin/console doctrine:database:create
  php bin/console doctrine:migrations:migrate
```
and to setup test database:
```
  php bin/console --env=test doctrine:database:create
  php bin/console --env=test doctrine:schema:create
```
## Run parse service log command

Inside the container run the console command:
```
  php bin/console legalone:parse-service-log tests/Command/data/logs.txt
```
## Log analytics API
To access the log analytics API:
```
  http://localhost:9002/count
```
filter by service names
```
  http://localhost:9002/count?serviceNames=USER-SERVICE,INVOICE-SERVICE
```
filter by start or end date
```
  http://localhost:9002/count?endDate=2022-08-18T09:12:28Z&startDate=2022-08-18T09:12:28Z
```
filter by status code
```
  http://localhost:9002/count?statusCode=400
```
## Open API specifications
You can also see an Open API specification here:
```
  http://localhost:9002/specification
```

## To Run tests

```
  make test
```

Note: due to time constraint not all test cases are covered.
