# Taskoid: a task management REST API web service

This is a BackEnd Exercise project.

## Requirements

### Create a REST web service

Create a REST web service (API only no web ui needed) of a TO DO list. The actions to perform are:

- [x] Create a new task
- [x] Update a task
- [x] Delete a task.
- [x] Show a task by id.
- [x] List all tasks:
- [x] I want to filter the tasks by due date, completed and uncompleted, date of creation, and date of update.
- [x] The response, must be paginated showing only 5 results per page.

### Technologies to use:

- [x] The exercise must be developed in PHP.
- [x] You CAN use any framework that you want with no restrictions.
- [x] You MUST use a MongoDB Database.
- [x] The list of all result must be cached with Redis or Memcached.
- [x] Take care of the validations of the required fields.

### Task Schema:

| Attribute     | Data Type     | Notes            |
| ------------- | ------------- | ---------------- |
| _id           | [id]          |                  |
| title         | [string]      | (required)       |
| description   | [string]      |                  |
| due_date      | [datetime]    | (required)       |
| completed     | [boolean]     | (default: false) |
| created_at    | [datetime]    |                  |
| updated_at    | [datetime]    |                  |

### Notes:

- [x] Submit the application to a git repository with the necessary installation/execution instructions.
- [x] All documentation and comments in code should be in English

## Solution

The Web Service API was implemented using

1. Laravel 5.4
2. Laravel API Boilerplate (JWT Edition) - [francescomalatesta/laravel-api-boilerplate-jwt](https://github.com/francescomalatesta/laravel-api-boilerplate-jwt) which provides:
  * JWT-Auth - [tymondesigns/jwt-auth](https://github.com/tymondesigns/jwt-auth)
  * Dingo API - [dingo/api](https://github.com/dingo/api)
3. Laravel MongoDB - [jenssegers/laravel-mongodb](https://github.com/jenssegers/laravel-mongodb)
4. MongoDB 3.5
5. Redis 4.0

Tested with

* Postman collections (newman) (provided in testing folder)
* phpunit Laravel tests (included in src folder)

A docker-compose setup is provided for testing with the following containers:

* web: nginx:1.10 exposes port 8081
* app: php:7-fpm
* database: mongo:3.5 exposes port 28017 to allow inspection
* cache: redis:4.0 exposes port 63791 to allow inspection

### Added features

An authentication and authorization scheme was added to the original specs. Authentication was implemented using JSON Web Tokens [JWT-Auth](https://github.com/tymondesigns/jwt-auth). Authorization was implemented in Laravel Policies with 3 user roles:

* User (regular user): can only CRUD on its own Tasks.
* Admin: can CRUD on Tasks owned by regular users, can CRUD regular users.
* Superuser: can CRUD any Task and regular or admin users. There is only 1 superuser: admin@localhost.dev | admin

Some users available after you run the database seeding command are:

* Superuser: admin@localhost.dev | admin
* Admin: john@localhost.dev | john
* User: patric@localhost.dev | patric

### Web Service API docs

Base URL: http://localhost:8081/api/

Required JWT HTTP Accept Header: application/x.taskoid.v1+json

Required JWT HTTP Authorization Header: Bearer {your token}

See API specs in [apidocs.md](https://github.com/lucho2d7/taskoid/blob/master/apidocs.md)

### Installation

Requires docker, docker-compose, localhost port 8081 available for the Web Service, localhost port 28017 for MongoDB inspection and localhost port 63791 for Redis cache inspection.

```
git clone https://github.com/lucho2d7/taskoid.git
cd taskoid/
docker-compose build
docker-compose up -d
```

Open http://localhost:8081 you should see a simple welcome screen that reads "Taskoid, a task manager tool".

### Running tests

The included tests are far from exhaustive, but a basic set.

To run the included Postman Collections with [newman](https://www.npmjs.com/package/newman) you first need to migrate and seed the database

```
# Identify CONTAINER ID corresponding to taskoid_app image with docker ps
docker ps

# Enter taskoid_app console
docker exec -t -i [CONTAINER ID] php artisan migrate:refresh --seed
```

Then you can run the tests with newman

```
cd testing
newman run -n 1 -e TaskoidENV.postman_environment.json Taskoid.postman_collection.json
```

Laravel and phpunit (within app container)

```
# Identify CONTAINER ID corresponding to taskoid_app image with docker ps
docker ps

# Run tests within the container
docker exec -t -i [CONTAINER ID] phpunit
```
