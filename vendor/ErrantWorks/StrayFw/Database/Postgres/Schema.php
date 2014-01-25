<?php

namespace ErrantWorks\StrayFw\Database\Postgres;

use ErrantWorks\StrayFw\Exception\FileNotWritable;
use ErrantWorks\StrayFw\Exception\InvalidSchemaDefinition;
use ErrantWorks\StrayFw\Database\Helper;
use ErrantWorks\StrayFw\Database\Mapping;
use ErrantWorks\StrayFw\Database\Provider\Schema as ProviderSchema;

/**
 * Schema representation class for PostgreSQL ones.
 * User code shouldn't use this class directly, nor the entire Postgres namespace.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Schema extends ProviderSchema
{
    /**
     * Generate base models.
     *
     * @throws InvalidSchemaDefinition if a model has no field
     * @throws InvalidSchemaDefinition if an enum-typed field has no values defined
     * @throws FileNotWritable         if base model file can't be opened with write permission
     * @throws FileNotWritable         if model file can't be opened with write permission
     */
    public function generateModels()
    {
        $definition = $this->getDefinition();
        foreach ($definition as $modelName => $modelDefinition) {
            $constructor = '    public function __construct(array $fetch = null)' . "\n    {\n";
            $constructor .= "        parent::__construct();\n";
            $constructor .= '        if ($fetch == null) {' . PHP_EOL . '            $this->new = false;' . "\n        } else {\n" . '            $fetch = array();' . "\n        }\n";
            $properties = null;
            $accessors = null;
            $allFieldsRealNames = "    public static function getAllFieldsRealNames()\n    {\n        return array(";
            $allFieldsAliases = "    public static function getAllFieldsAliases()\n    {\n        return array(";

            $modelRealName = null;
            if (isset($modelDefinition['name']) === true) {
                $modelRealName = $modelDefinition['name'];
            } else {
                $modelRealName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
            }

            if (isset($modelDefinition['fields']) === false) {
                throw new InvalidSchemaDefinition('model "' . $modelName . '" has no field');
            }
            foreach ($modelDefinition['fields'] as $fieldName => $fieldDefinition) {
                $fieldRealName = null;
                if (isset($fieldDefinition['name']) === true) {
                    $fieldRealName = $fieldDefinition['name'];
                } else {
                    $fieldRealName = Helper::codifyName($modelName) . '_' . Helper::codifyName($fieldName);
                }

                $properties .= '    protected $field' .  ucfirst($fieldName) . ";\n";
                $properties .= '    const FIELD_' . strtoupper(Helper::codifyName($fieldName)) . ' = \'' . $modelRealName . '.' . $fieldRealName . "';\n";
                if ($fieldDefinition['type'] == 'enum') {
                    if (isset($fieldDefinition['values']) === false) {
                        throw new InvalidSchemaDefinition('enum-typed field "' . $fieldName . '" of model "' . $modelName . '" has no values defined');
                    }
                    foreach ($fieldDefinition['values'] as $value) {
                        $properties .= '    const ' . strtoupper(Helper::codifyName($fieldName)) . '_' . strtoupper(Helper::codifyName($value)) . ' = \'' . $value . "';\n";
                    }
                }
                $properties .= PHP_EOL;

                $constructor .= '        $this->field' .  ucfirst($fieldName) . ' = [ \'name\' => \'' . $fieldRealName . '\', \'alias\' => \'' . $fieldName . '\', \'value\' => @$fetch[\'' . $fieldRealName . "'] ];\n";
                if (isset($fieldDefinition['primary']) === true && $fieldDefinition['primary'] === true) {
                    $constructor .= '        $this->primary[] = \'' . $fieldName . "';\n";
                }

                $accessors .= '    public function get' . ucfirst($fieldName) . "()\n    {\n        ";
                switch ($fieldDefinition['type']) {
                    case 'string':
                        $accessors .= 'return stripslashes($this->field' . ucfirst($fieldName) . '[\'value\']);';
                        break;
                    case 'char':
                        $accessors .= 'return stripslashes($this->field' . ucfirst($fieldName) . '[\'value\']);';
                        break;
                    case 'bool':
                        $accessors .= 'return filter_var($this->field' . ucfirst($fieldName) . '[\'value\'], FILTER_VALIDATE_BOOLEAN);';
                        break;
                    case 'json':
                        $accessors .= 'return json_decode($this->field' . ucfirst($fieldName) . '[\'value\'], true);';
                        break;
                    default:
                        $accessors .= 'return $this->field' . ucfirst($fieldName) . '[\'value\'];';
                        break;
                }
                $accessors .= "\n    }\n\n";

                $accessors .= '    public function set' . ucfirst($fieldName) . '($value)' . "\n    {\n        ";
                switch ($fieldDefinition['type']) {
                case 'enum':
                    $accessors .= 'if (in_array($value, array(\'' . implode('\', \'', $fieldDefinition['values']) . '\')) === false) {' . "\n            return false;\n        }";
                    $accessors .= '        $this->field' . ucfirst($fieldName) . '[\'value\'] = $value;';
                    break;
                case 'bool':
                    $accessors .= '$this->field' . ucfirst($fieldName) . '[\'value\'] = (bool) $value;';
                    break;
                case 'json':
                    $accessors .= '$this->field' . ucfirst($fieldName) . '[\'value\'] = json_encode($value);';
                    break;
                default:
                    $accessors .= '$this->field' . ucfirst($fieldName) . '[\'value\'] = $value;';
                    break;
                }
                $accessors .= PHP_EOL . '        $this->modified[\'' . $fieldName . '\'] = true;';
                $accessors .= "\n        return true;\n    }\n\n";

                $allFieldsRealNames .= '\'' . $modelRealName . '.' . $fieldRealName . '\', ';
                $allFieldsAliases .= '\'' . $fieldName . '\', ';
            }

            $allFieldsRealNames = substr($allFieldsRealNames, 0, -2) . ");\n    }\n\n";
            $allFieldsAliases = substr($allFieldsAliases, 0, -2) . ");\n    }\n";
            $constructor .= "    }\n\n";

            $mapping = Mapping::get($this->mapping);

            $path = null;
            if ($mapping['config']['models']['path'][0] == DIRECTORY_SEPARATOR) {
                $path = ltrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            } else {
                $path = rtrim($mapping['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
            $path .= 'Base' . DIRECTORY_SEPARATOR . ucfirst($modelName) . '.php';
            $file = fopen($path, 'w+');
            if ($file === false) {
                throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
            }
            $content = "<?php\n\nnamespace " . rtrim($mapping['config']['models']['namespace'], '\\') . "\\Base;\n\nuse ErrantWorks\StrayFw\Database\Postgres\Model;\n\nclass " . ucfirst($modelName) . " extends Model\n{\n";
            $content .= '    const NAME = \'' . $modelRealName . "';\n    const DATABASE = '" . $mapping['config']['database'] . "';\n\n";
            $content .= $properties . $constructor . $accessors . $allFieldsRealNames . $allFieldsAliases;
            $content .= "}";
            if (fwrite($file, $content) === false) {
                throw new FileNotWritable('can\'t write in "' . $path . '"');
            }
            fclose($file);

            if ($mapping['config']['models']['path'][0] == DIRECTORY_SEPARATOR) {
                $path = ltrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            } else {
                $path = rtrim($mapping['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
            $path .= ucfirst($modelName) . '.php';
            if (file_exists($path) === false) {
                $file = fopen($path, 'w+');
                if ($file === false) {
                    throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
                }
                $content = "<?php\n\nnamespace " . rtrim($mapping['config']['models']['namespace'], '\\') . ";\n\nuse " . rtrim($mapping['config']['models']['namespace'], '\\') . "\\Base\\" . ucfirst($modelName) . " as BaseModel;\n\nclass " . ucfirst($modelName) . " extends BaseModel\n{\n}";
                if (fwrite($file, $content) === false) {
                    throw new FileNotWritable('can\'t write in "' . $path . '"');
                }
            }

            echo $modelName . ' - Done' . PHP_EOL;
        }
    }
}
