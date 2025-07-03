<?php

namespace Marcha\Acg\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Marcha\Acg\Commands\Traits\ContainerMakeTrait;


class CreateResource extends Command
{
  use ContainerMakeTrait;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'marcha:create-resource
                            {container_name}
                            {--namespace=api : Container\'s namespace. Must be defined in composer.json}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create resource in container.';


  /**
   * Create a new command instance.
   *
   * @param Filesystem $files
   */
  public function __construct(Filesystem $files)
  {
    parent::__construct();
    $this->files = $files;
    $this->composer = app()['composer'];
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $containerName = $this->argument('container_name');

    $this->setNamespace($this->option('namespace'));
    $this->info('API path: ' . $this->namespacePath);
    $this->containerPath = $this->getContainerPath($containerName);
    $this->info('Container path: ' . $this->containerPath);

    $this->table = $containerName;
    $this->singularForm = $this->getTableFromContainer();

    if (!empty($this->singularForm)) {
      $this->makeResource();

      $this->composer->dumpAutoloads();
    } else {
      $this->error('Model name is empty. Transformer not created!');
    }
  }

  private function getTableFromContainer()
  {

    $model = '';

    $path = $this->containerPath . 'Models' . DIRECTORY_SEPARATOR;
    $files = array_diff(scandir($path), ['..', '.']);

    if (count($files) == 1) {
      $fileName = current($files);
      $model = substr($fileName, 0, strrpos($fileName, '.'));
    } else {
      $model = $this->ask('Specify model/table name');
    }

    return $model;
  }
}
