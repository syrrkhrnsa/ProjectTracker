# ProjectTracker

A small **REST API for tracking projects and weighted tasks**. A project's `status` (`Draft`, `In Progress`, `Done`) and `completion_progress` are computed automatically from its tasks.

Frontend: [ProjectTrackerFrontend](https://github.com/syrrkhrnsa/ProjectTrackerFrontend)

## Features

- CRUD for projects and for tasks nested under a project
- Each task has a `weight`; project progress is the weighted share of completed tasks
- Project status updates automatically when tasks change
- Request validation, cascade delete of tasks with their project

## Tech stack

PHP 8.2 · Laravel 12 · PostgreSQL · PHPUnit

## Endpoints

| Method | Path | Description |
|---|---|---|
| GET / POST | `/api/projects` | List / create projects |
| GET / PUT / DELETE | `/api/projects/{id}` | Show / update / delete a project |
| GET / POST | `/api/projects/{id}/tasks` | List / create tasks of a project |
| GET / PUT / DELETE | `/api/tasks/{id}` | Show / update / delete a task |

## Run locally

```bash
git clone https://github.com/syrrkhrnsa/ProjectTracker.git
cd ProjectTracker
composer install
cp .env.example .env
php artisan key:generate
# edit .env: DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate
php artisan serve        # http://localhost:8000
```

## Author

Syira Khoerunisa
