<?php

namespace Marcha\Acg\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Marcha\Acg\Commands\Traits\ContainerMakeTrait;
use Marcha\Acg\Tools\ERModelParser;

class CreateContainers extends Command
{
    use ContainerMakeTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marcha:create-containers 
                            {--namespace=api : Container\'s namespace. Must be defined in composer.json}
                            {--addFK : Add FK constraints in migration files.}
                            {--mma=fillable : Use "fillable" or "guarded" attribute in model\'s mass assignment.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create containers from MySQL workbench exported ER model with controllers, models, service, repository, events and routes';

    /**
     * Create a new command instance.
     *
     * @return void
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
        if (!$this->files->exists($mwb_model = database_path('mysqlworkbench/ERModel.json'))) {
            $this->error("Model file '$mwb_model' not found!");
            return;
        }

        $options['addFK'] = $this->option('addFK');
        $options['mma'] = $this->option('mma');

        $this->setNamespace($this->option('namespace'));
        $this->info('API path: ' . $this->namespacePath);

        $model = json_decode($this->files->get($mwb_model), true);   

        foreach ($model['data'] as $val) {        
            $this->table = key($val);
            
            $this->containerPath = $this->getContainerPath($this->table);
            $this->info('Container path: ' . $this->containerPath);

            $ERmodel = new ERModelParser($options, $this->namespacePath);
            $tableData = $ERmodel->ParseTableData($this->table, $val[$this->table]);
     
            $this->makeModel($tableData['model_data']);
            
            sleep(1); 
            //mora pauza zbog timestamp dela naziva migracije, kako bi se zadržao pravilan redosled izvršavanja
            $this->makeStubMigration($tableData['migration_data']);
           
            $this->makeEvents();
            $this->makeRepository();
            $this->makeService();
            $this->makeController();
            $this->makeTransformer();
            $this->makeRoutes();

            $this->info('--------------------------------------------------------');             
        }

        $this->composer->dumpAutoloads();  
    } 
}