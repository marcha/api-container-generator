<?php

namespace Marcha\Acg\Commands\Traits;

use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Marcha\Acg\Tools\StubParser;
use Illuminate\Filesystem\Filesystem;


trait ContainerMakeTrait
{

  use StubParser;

  private static $STUBS_DIR = '/../../stubs/';

  /**
   * Table name
   *
   * @var string
   */
  protected $table;

  /**
   * The filesystem instance.
   *
   * @var Filesystem
   */
  protected $files;

  /**
   * @var Composer
   */
  protected $composer;

  protected $containerPath;
  protected $namespacePath;
  protected $namespacePrefix;
  protected $singularForm;

  /**
   * Generate migration file using command call
   */
  protected function makeMigration()
  {
    $this->call('make:migration', ['name' => 'create_' . $this->namespacePrefix . '_' . $this->table . '_table']);
  }

  /**
   * Generate migration file using stub
   */
  protected function makeStubMigration($migration_data)
  {
    $migrationPath = $this->getMigrationsPath('create_' . $this->namespacePrefix . '_' . $this->table . '_table');
    if (!$this->files->exists($migrationPath)) {
      $this->files->put($migrationPath, $this->compileMigrationStub($migration_data));
      $this->info('Migration: ' . $migrationPath . ' created successfully.');
    }
  }

  /**
   * Generate an Eloquent model, if the user wishes.
   */
  protected function makeModel($model_data = [])
  {
    $model_name = $this->getModelName($this->table);
    $modelPath = $this->containerPath . 'Models' . DIRECTORY_SEPARATOR . $model_name . '.php';
    $this->makeDirectory($modelPath);
    if (!$this->files->exists($modelPath)) {
      $this->files->put($modelPath, $this->compileModelStub($model_data));
      $this->info('Model: ' . $model_name . ' created successfully.');
    }
  }

  /**
   * Generate an Controller
   */
  protected function makeController()
  {
    $controller_name = $this->getControllerName($this->table);
    $path = $this->containerPath . 'Controllers' . DIRECTORY_SEPARATOR . $controller_name . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileControllerStub());
      $this->info('Controller: ' . $controller_name . ' created successfully.');
    }
  }
  /**
   * Generate an Repository
   */
  protected function makeRepository()
  {
    $repositoryName  = $this->getModelName() . 'Repository';
    $path = $this->containerPath . 'Repositories' . DIRECTORY_SEPARATOR . $repositoryName . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileRepositoryStub());
      $this->info('Repository: ' . $repositoryName . ' created successfully.');
    }
  }

  /**
   * Generate an Repository
   */
  protected function makeService()
  {
    $serviceName  = $this->getModelName() . 'Service';
    $path = $this->containerPath . 'Services' . DIRECTORY_SEPARATOR . $serviceName . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileServiceStub());
      $this->info('Service: ' . $serviceName . ' created successfully.');
    }
  }
  /**
   * Generate Events
   */
  protected function makeEvents()
  {
    $this->makeEvent('Created');
    $this->makeEvent('Updated');
    $this->makeEvent('Deleted');
  }

  /**
   * Generate Events
   *
   * @param string $event
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function makeEvent($event)
  {
    $eventName  = $this->getModelName() . 'Was' . $event;
    $path = $this->containerPath . 'Events' . DIRECTORY_SEPARATOR . $eventName . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileEventStub($event));
      $this->info('Event: ' . $eventName . ' created successfully.');
    }
  }

  /**
   * Generate routes
   */
  protected function makeRoutes()
  {
    $path = $this->containerPath . 'routes.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileRoutesStub());
      $this->info('Routes file created successfully.');
    }
  }

  /**
   * Generate an Transformer
   */
  protected function makeTransformer()
  {
    $transformerName = $this->getModelName($this->table) . 'Transformer';
    $path = $this->containerPath . 'Transformers' . DIRECTORY_SEPARATOR . $transformerName . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileTransformerStub());
      $this->info('Transformer: ' . $transformerName . ' created successfully.');
    }
  }

  /**
   * Generate an Resource
   */
  protected function makeResource()
  {
    $resourceName = $this->getModelName($this->table) . 'Resource';
    $path = $this->containerPath . 'Resources' . DIRECTORY_SEPARATOR . $resourceName . '.php';
    $this->makeDirectory($path);
    if (!$this->files->exists($path)) {
      $this->files->put($path, $this->compileResourceStub());
      $this->info('Resource: ' . $resourceName . ' created successfully.');
    }
  }
  /**
   * Build the directory for the class if necessary.
   *
   * @param  string $path
   * @return string
   */
  protected function makeDirectory($path)
  {
    if (!$this->files->isDirectory(dirname($path))) {
      $this->files->makeDirectory(dirname($path), 0777, true, true);
    }
  }

  /**
   * Get the path to where we should store the migration.
   *
   * @param  string $name
   * @return string
   */
  protected function getMigrationsPath($name)
  {
    return database_path('migrations' . DIRECTORY_SEPARATOR .  date('Y_m_d_His') . '_' . $name . '.php');
  }

  protected function setNamespace($namespace)
  {

    list('namespace' => $NSPrefix, 'path' => $NSPath) = $this->getNamespacePath($namespace);

    $this->namespacePrefix = $NSPrefix ? rtrim($NSPrefix, '\\') : '';

    if (DIRECTORY_SEPARATOR === '\\') {
      $NSPath = str_replace('/', DIRECTORY_SEPARATOR, $NSPath);
    }

    $this->namespacePath = base_path($NSPath);
  }

  /**
   * Get the container path.
   *
   * @param  string $name
   * @return string
   */
  protected function getContainerPath($name)
  {
    $name = ucwords(Str::camel($name));
    $name = str_replace($this->getAppNamespace(), '', $name);

    if (DIRECTORY_SEPARATOR === '/') {
      $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
    }

    return $this->namespacePath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
  }

  /**
   * Get the class name for the Eloquent model generator.
   *
   * @return string
   */
  protected function getModelName()
  {
    if (empty($this->singularForm))
      return ucwords(Str::singular(Str::camel($this->table)));
    else
      return ucwords(Str::camel($this->singularForm));
  }

  /**
   * Get the class name for the Controller.
   *
   * @return string
   */
  protected function getControllerName()
  {
    if (empty($this->singularForm))
      return ucwords(Str::singular(Str::camel($this->table))) . 'Controller';
    else
      return ucwords(Str::camel($this->singularForm)) . 'Controller';
  }

  /**
   * Compile the model stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileModelStub($model_data = [])
  {
    $mass_assign = '$fillable = [];';
    $uses = '';
    $relations = '';

    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'model.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceModelName($stub, $this->getModelName());

    if (!empty($model_data)) {
      $mass_assign = $model_data['mass_assign'];
      $uses = $model_data['relations']['uses'];
      $relations = $model_data['relations']['body'];
    }

    $this->replaceModelMassAssign($stub, $mass_assign)
      ->replaceModelUses($stub, $uses)
      ->replaceModelRelations($stub, $relations);

    return $stub;
  }

  /**
   * Compile the migration stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileMigrationStub($migration_data)
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'migration.create.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)->replaceClassName($stub)->replaceTableName($stub)->replaceMigrationColumns($stub, $migration_data);
    return $stub;
  }

  /**
   * Compile the controller stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileControllerStub()
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'controller.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceModelName($stub, $this->getModelName())
      ->replaceServiceVar($stub);
    return $stub;
  }

  /**
   * Compile the repository stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileRepositoryStub()
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'repository.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceModelName($stub, $this->getModelName())
      ->replaceServiceVar($stub);
    return $stub;
  }

  /**
   * Compile the service stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileServiceStub()
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'service.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceModelName($stub, $this->getModelName())
      ->replaceServiceVar($stub);
    return $stub;
  }

  /**
   * Compile the event stub.
   *
   * @param  string $event
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileEventStub($event)
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'event.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceEvent($stub, $event)
      ->replaceModelName($stub, $this->getModelName())
      ->replaceServiceVar($stub);
    return $stub;
  }

  /**
   * Compile the routes stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileRoutesStub()
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'routes.stub');
    $this->replaceClassName($stub)
      ->replaceTableName($stub)
      ->replaceModelName($stub, $this->getModelName())->replaceResource($stub);
    return $stub;
  }

  /**
   * Compile the resource stub.
   *
   * @return string
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function compileResourceStub()
  {
    $stub = $this->files->get(__DIR__ . self::$STUBS_DIR . 'resource.stub');
    $this->replaceNamespacePrefix($stub, $this->namespacePrefix)
      ->replaceClassName($stub)
      ->replaceModelName($stub, $this->getModelName());
    return $stub;
  }

  protected function getAppNamespace()
  {
    return Container::getInstance()->getNamespace();
  }

  protected function getNamespacePath($aNamespace)
  {

    $composer = json_decode(file_get_contents(base_path('composer.json')), true);

    foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
      if (strcasecmp(str_replace('\\', '', $namespace), $aNamespace) == 0) {
        return compact('namespace', 'path');
      }
    }

    throw new \RuntimeException("Unable to detect $aNamespace namespace.");
  }
}
