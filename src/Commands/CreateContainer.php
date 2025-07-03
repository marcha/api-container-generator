<?php

namespace Marcha\Acg\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Marcha\Acg\Commands\Traits\ContainerMakeTrait;

class CreateContainer extends Command
{
  use ContainerMakeTrait;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'marcha:create-container
                            {name}
                            {--namespace=api : Container\'s namespace. Must be defined in composer.json}
                            {--singular_form= : Singular string variant}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create container for model (table) with controllers, models, service, repository, events and routes';

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
    $this->table = $this->argument('name');
    $this->singularForm = $this->option('singular_form');

    $this->setNamespace($this->option('namespace'));
    $this->info('API path: ' . $this->namespacePath);
    $this->containerPath = $this->getContainerPath($this->table);
    $this->info('Container path: ' . $this->containerPath);

    $this->makeModel();
    $this->makeMigration();
    $this->makeEvents();
    $this->makeRepository();
    $this->makeService();
    $this->makeController();
    //$this->makeTransformer();
    $this->makeResource();
    $this->makeRequest('Store');
    $this->makeRequest('Update');
    $this->makeRoutes();

    $this->composer->dumpAutoloads();
  }
}
