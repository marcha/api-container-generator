<?php

namespace Marcha\Acg\Tools;

use Illuminate\Support\Str;

trait StubParser
{

  /**
   * Replace the table name in the stub.
   *
   * @param  string $stub
   * @return $this
   */
  protected function replaceTableName(&$stub)
  {
    $stub = str_replace('{{table}}', $this->table, $stub);
    $stub = str_replace('{{table_name}}', $this->table, $stub);
    return $this;
  }

  /**
   * Replace the table name in the stub.
   *
   * @param string $event
   * @param string $stub
   * @return $this
   */
  protected function replaceEvent(&$stub, $event)
  {
    $stub = str_replace('{{event}}', $event, $stub);
    return $this;
  }

  /**
   * Replace the class name in the stub.
   *
   * @param string $stub
   * @return $this
   */
  protected function replaceClassName(&$stub)
  {
    $className = ucwords(Str::camel($this->table));
    $stub = str_replace('{{class}}', $className, $stub);
    return $this;
  }
  /**
   * Replace the Action name in the stub.
   *
   * @param string $action
   * @param string $stub
   * @return $this
   */
  protected function replaceActionName(&$stub, $action)
  {
    $stub = str_replace('{{action}}', $action, $stub);
    return $this;
  }
  /**
   * Replace the Model name in the stub.
   *
   * @param string $model
   * @param string $stub
   * @return $this
   */
  protected function replaceModelName(&$stub, $model)
  {
    $stub = str_replace('{{model_name}}', $model, $stub);
    return $this;
  }

  /**
   * Replace the serviceVar name in the stub.
   *
   * @param  string $stub
   * @return $this
   */
  protected function replaceServiceVar(&$stub)
  {
    $serviceVar = lcfirst(Str::camel(Str::singular($this->table)));
    $stub = str_replace('{{service_var}}', $serviceVar, $stub);
    return $this;
  }

  /**
   * Replace the serviceVar name in the stub.
   *
   * @param  string $stub
   * @return $this
   */

  protected function replaceResource(&$stub)
  {
    $resource = Str::lower(Str::camel($this->table));
    $stub = str_replace('{{resource}}', $resource, $stub);
    return $this;
  }

  /**
   * Replace the Migration columns in the stub.
   *
   * @param string $migration_columns
   * @param string $stub
   * @return $this
   */
  protected function replaceMigrationColumns(&$stub, $migration_data)
  {
    $stub = str_replace('{{migration_data}}', $migration_data, $stub);
    return $this;
  }

  /**
   * Replace the mass assignment section in Model
   *
   * @param string $mass_assign
   * @param string $stub
   * @return $this
   */
  protected function replaceModelMassAssign(&$stub, $mass_assign)
  {
    $stub = str_replace('{{mass_assign}}', $mass_assign, $stub);
    return $this;
  }

  /**
   * Replace the relations section in Model
   *
   * @param string $relations
   * @param string $stub
   * @return $this
   */
  protected function replaceModelRelations(&$stub, $relations)
  {
    $stub = str_replace('{{relations}}', $relations, $stub);
    return $this;
  }

  /**
   * Replace the uses section in Model
   *
   * @param string $uses
   * @param string $stub
   * @return $this
   */
  protected function replaceModelUses(&$stub, $uses)
  {
    $stub = str_replace('{{uses}}', $uses, $stub);
    return $this;
  }

  /**
   * Replace the namespace prefix in the stub.
   *
   * @param string $stub
   * @return $this
   */
  protected function replaceNamespacePrefix(&$stub, $prefix)
  {
    $stub = str_replace('{{namespace_prefix}}', $prefix, $stub);
    return $this;
  }
}
