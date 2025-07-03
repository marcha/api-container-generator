<?php

namespace Marcha\Acg\Tools;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ERModelParser {

    private $tableName;
    private $primaryKeys;
    private $options;
    private $api_path;

    public function __construct($options, $api_path) {

        $this->options = $options;
        $this->api_path = $api_path;
    }

    public function ParseTableData($tableName, $data) {
    
        $this->tableName = $tableName;        

        $result = [];

        $this->primaryKeys = Arr::pluck($data['primaryKey'], 'referencedColumn');

        $columns = $this->ProcessColumns($data['columns']);
        $indexes = $this->ProcessIndexes($data['indices']);
        if ($this->options['addFK']) {
            $FKconstraints = $this->ProcessFKConstraints($data['foreignKeys']);
        } else {
            $FKconstraints = "";
        }

        $relations = $this->ProcessRelations($data['relations']);

        $result['migration_data'] = $columns['columns']. "\r\n". $indexes. "\r\n". $FKconstraints;
        $result['model_data']['mass_assign'] = $columns['mass_assign'];
        $result['model_data']['relations'] = $relations;

        return $result;
    }

    private function ProcessColumns($data) {

        $result = [];
        $columns = "";
        $fillable = [];
        $guarded = [];

        foreach ($data as $col) {
      
            $column_name = key($col);           
            $colData = $col[$column_name];
            
            $line = "";           

            $length = $colData['length'] == -1 ? 0 : $colData['length'];
            $precision = $colData['precision'] == -1 ? 0 : $colData['precision'];
            $scale = $colData['scale'] == -1 ? 0 : $colData['scale'];
            $flags = explode(',', str_replace(['[', ']'], '', $colData['flags']));

            switch ($colData['simpleType']) {
                case "BIGINT":
                    $line = "big#Integer#('$column_name')";
                    break;
                case "BINARY":
                    $line = "binary('$column_name')";
                    break;
                case "CHAR":
                    $line = "char('$column_name', $length)";
                    break;
                case "DATE":
                    $line = "date('$column_name')";
                    break;
                case "DATETIME":
                    $line = "dateTime('$column_name', $length)";
                    break;
                case "DECIMAL":
                    $line = "decimal('$column_name', $precision, $scale)";
                    break;
                case "DOUBLE":
                    $line = "double('$column_name', $precision, $scale)";
                    break;
                case "ENUM":
                    $datatypeExplicitParams = str_replace(['(', ')'], ['[', ']'], $colData['datatypeExplicitParams']);
                    $line = "enum('$column_name', $datatypeExplicitParams)";
                    break;
                case "FLOAT":
                    $line = "float('$column_name', $precision, $scale)";
                    break;
                case "INT":
                    $line = "#integer#('$column_name')";
                    break;
                case "LONGTEXT":
                    $line = "longText('$column_name')";
                    break;               
                case "MEDIUMINT":
                    $line = "medium#Integer#('$column_name')";
                    break;
                case "MEDIUMTEXT":
                    $line = "mediumText('$column_name')";
                    break;
                case "REAL":
                    $line = "double('$column_name', $precision, $scale)";
                    break;
                case "SET":
                    $datatypeExplicitParams = str_replace(['(', ')'], ['[', ']'], $colData['datatypeExplicitParams']);
                    $line = "set('$column_name', $datatypeExplicitParams)";
                    break;
                case "SMALLINT":
                    $line = "small#Integer#('$column_name')";
                    break;
                case "VARCHAR":
                    $line = "string('$column_name', $length)";
                    break;
                case "TEXT":
                    $line = "text('$column_name')";
                    break;
                case "TIME":
                    $line = "time('$column_name', $length)";
                    break;
                case "TIMESTAMP":
                    $line = "timestamp('$column_name', $length)";
                    break;
                case "TINYINT":
                    $line = "tiny#Integer#('$column_name')";
                    break;
                case "YEAR":
                    $line = "year('$column_name')";
                    break;
                default:
                    throw new \Exception("$this->tableName - Unknown column ($column_name) type: [{$colData['simpleType']}]");                
            }

            if ($colData['autoIncrement'] == 1) {
                $line = str_ireplace('#integer#', 'Increments', $line);

                $guarded[] = $column_name;
            } else {
                if ($colData['datatypeGroup'] == 'Numeric Types') {
                    $line = str_ireplace('#integer#', 'Integer', $line);

                    if (in_array('UNSIGNED', $flags)) {
                        $line = "unsigned". $line;
                    }
                }
                
                $fillable[] = $column_name;
            }

            if (($colData['isNotNull'] == 0) && (!in_array($column_name, $this->primaryKeys))) {
                $line .= "->nullable()";
            }

            if ($colData['defaultValue'] != "") {
                if (($colData['simpleType'] == "TIMESTAMP") && ($colData['defaultValue'] == "CURRENT_TIMESTAMP")) {
                    $line .= "->useCurrent()";
                } else {
                    $line .= "->default({$colData['defaultValue']})";
                }
            } 
            
            if (trim($line) != "") {
                $line = "\t\t\t\t\t\$table->$line;\r\n";

                $columns .= $line;
            }            
        }
      
        if ($this->options['mma'] == 'fillable') {
            $mass_assign = "\$fillable = [{$this->ArrToQuotedItems($fillable)}];";
        } else {
            $mass_assign = "\$guarded = [{$this->ArrToQuotedItems($guarded)}];";
        }

        return compact('columns', 'mass_assign' );
    }

    private function ProcessIndexes($data) {

        $result = "";

        foreach ($data as $ind) {

            $line = "";
            $referencedColumns = explode(",", $ind['referencedColumns']);
            $strReferencedColumns = "[{$this->ArrToQuotedItems($referencedColumns)}]";
            
            switch ($ind['indexType']) {
                case "PRIMARY":
                    if ($ind['hasAutoIncrement'] == 0) {
                        $line = "\$table->primary($strReferencedColumns);";
                    }
                    break;
                case "UNIQUE":
                    $line = "\$table->unique($strReferencedColumns);";
                    break;
                case "INDEX":
                    $line = "\$table->index($strReferencedColumns);";
                    break;
                default:
                    throw new \Exception("$this->tableName - Unknown index ($column_name) type: [{$ind['indexType']}]");
            }

            if (trim($line) != "") {
                $line = "\t\t\t\t\t$line\r\n";

                $result .= $line;
            }            
        }

        return $result;
    }

    private function ProcessFKConstraints($data) {

        $result = "";

        $line = "";

        foreach ($data as $fkc) {
            $line = "\$table->foreign('{$fkc['columns']}')->references('{$fkc['referencedColumns']}')".
                    "->on('{$fkc['referencedTable']}');";
            
            if (trim($line) != "") {
                $line = "\t\t\t\t\t$line\r\n";
                
                $result .= $line;
            }
        }

        return $result;
    }

    private function ProcessRelations($data) {

        $body = "";
        $uses = "";

        foreach ($data as $rel) {

            $relTable = $rel['relationTable'];
            $relCol = $rel['relationColumn'];
            $col = $rel['column'];

            $model = ucwords(Str::singular(Str::camel($relTable)));
            $api_dir = basename($this->api_path);
            $modelNamespace = "use $api_dir\\". ucwords(Str::camel($relTable)). "\\Models\\$model;\r\n";            

            $uses .= $modelNamespace; 

            $template = <<<EOT
    public function $relTable() {

        return \$this->hasMany($model::class, '$relCol', '$col');
    }
\r\n
EOT;
            
            $body .= $template;
        }

        return compact('body', 'uses');
    }

    private function ArrToQuotedItems(Array $arr) {
        
        return implode(",", array_map(
            function($col) {
                return "'$col'";
            }, $arr));
    }
}