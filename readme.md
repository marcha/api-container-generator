# Laravel 5 Api container generator

Container generator create folder with Controller, Model, Service, Repository, Events and routes files and folders.
Execute DumpAutoload at the end.

## Install

`php composer require marcha/api-container-generator`

## Use

For generator use plural name of entity in snake_case

### Single word table name `flights`:

Run command: `php artisan marcha:create-container flights` and next files and folders will be created:

Container: `app\api\Flights`

Model: `app\api\Flights\Models\Flight.php`

Events: `app\api\Flights\Events\FlightWas{Created|Deleted|Updated}.php`

Repository: `app\api\Flights\Repositories\FlightRepository.php`

Service: `app\api\Flights\Services\FlightServce.php`

Controller: `app\api\Flights\Controllers\FlightController.php`

Resource: `app\api\Flights\Resource\FlightsResource.php`

Routes: `app\api\Flights\routes.php`

### Two words table name: `flight_operators`:

Run command: `php artisan marcha:create-container flight_operators` and next files and folders will be created:

Container: `app\api\FlightOperators`

Model: `app\api\FlightOperators\Models\FlightOperator.php`

Events: `app\api\Flights\Events\FlightOperatorWas{Created|Deleted|Updated}.php`

Repository: `app\api\FlightOperators\Repositories\FlightOperatorRepository.php`

Service: `app\api\FlightOperators\Services\FlightOperatorServce.php`

Controller: `app\api\FlightOperators\Controllers\FlightOperatorController.php`

Resource: `app\api\FlightOperators\Resource\FlightOperatorResource.php`

Routes: `app\api\FlightOperators\routes.php`
