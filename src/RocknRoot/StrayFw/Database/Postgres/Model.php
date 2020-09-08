<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Postgres\Query\Delete;
use RocknRoot\StrayFw\Database\Postgres\Query\Insert;
use RocknRoot\StrayFw\Database\Postgres\Query\Select;
use RocknRoot\StrayFw\Database\Postgres\Query\Update;
use RocknRoot\StrayFw\Database\Provider\Model as ProviderModel;

/**
 * Model representation class for PostgreSQL tables.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Model extends ProviderModel
{
    /**
     * Aliases of modified columns values.
     *
     * @var array<string, mixed>
     */
    protected array $modified = array();

    /**
     * Save the model. Delete if deletionFlag is true.
     *
     * @return bool true if successfully saved
     */
    public function save() : bool
    {
        $status = false;
        if ($this->new === false) {
            if ($this->deletionFlag === true) {
                $status = $this->delete();
            } elseif (\count($this->modified) > 0) {
                $updateQuery = new Update($this->getDatabaseName());
                $updateQuery->update($this->getTableName());

                $where = array();
                foreach ($this->getPrimary() as $primary) {
                    $field = $this->{'field' . \ucfirst($primary)};
                    $realName = (string) \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($primary)));
                    $where[$realName] = ':primary' . \ucfirst($primary);
                    $updateQuery->bind('primary' . \ucfirst($primary), $field['value']);
                }
                $updateQuery->where($where);

                $set = array();
                foreach ($this->modified as $key => $value) {
                    if ($value === true) {
                        $field = $this->{'field' . \ucfirst($key)};
                        $realName = (string) \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                        $set[$realName] = ':field' . \ucfirst($key);
                        $updateQuery->bind(':field' . \ucfirst($key), $field['value']);
                    }
                }
                $updateQuery->set($set);

                $this->modified = array();

                $status = $updateQuery->execute();
            }
        } else {
            if ($this->deletionFlag === false) {
                $insertQuery = new Insert($this->getDatabaseName());
                $insertQuery->into($this->getTableName());

                $returning = array();
                foreach ($this->getPrimary() as $primary) {
                    $field = $this->{'field' . \ucfirst($primary)};
                    $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($primary)));
                    $returning[] = $realName;
                }
                $insertQuery->returning($returning);

                $values = array();
                foreach ($this->getAllFieldsAliases() as $name) {
                    $field = $this->{'field' . \ucfirst($name)};
                    if (isset($field['value']) === true) {
                        $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($name)));
                        $values[$realName] = ':field' . \ucfirst($name);
                        $insertQuery->bind('field' . \ucfirst($name), $field['value']);
                    }
                }
                $insertQuery->values($values);

                $status = $insertQuery->execute();

                if ($status === true) {
                    $this->modified = array();
                    $statement = $insertQuery->getStatement();
                    if (!$statement) {
                        throw new AppException('Database/Postgres/Model.save: insert query statement is null');
                    }
                    $rows = $statement->fetch(\PDO::FETCH_ASSOC);
                    $imax = \count($rows);
                    for ($i = 0; $i < $imax; $i++) {
                        $field = &$this->{'field' . \ucfirst($this->getPrimary()[$i])};
                        $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($this->getPrimary()[$i])));
                        $realName = \substr($realName, \stripos($realName, '.') + 1);
                        $field['value'] = $rows[$realName];
                    }
                    $this->new = false;
                }
            }
        }

        return $status;
    }

    /**
     * If not new, delete the model.
     *
     * @return bool true if successfully deleted
     */
    public function delete() : bool
    {
        $status = false;
        if ($this->new === false) {
            $deleteQuery = new Delete($this->getDatabaseName());
            $deleteQuery->from($this->getTableName());
            $where = array();
            foreach ($this->getPrimary() as $primary) {
                $field = $this->{'field' . \ucfirst($primary)};
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($primary)));
                $where[$realName] = ':primary' . \ucfirst($primary);
                $deleteQuery->bind('primary' . \ucfirst($primary), $field['value']);
            }
            $deleteQuery->where($where);

            $status = $deleteQuery->execute();
        }

        return $status;
    }

    /**
     * Get field values as associative array (alias => value).
     *
     * @return array<string, mixed> values
     */
    public function toArray() : array
    {
        $values = array();
        foreach ($this->getAllFieldsAliases() as $name) {
            $field = $this->{'field' . \ucfirst($name)};
            $values[$name] = $field['value'];
        }

        return $values;
    }

    /**
     * Get field values as associative array (real names => value).
     *
     * @return array<string, mixed> values
     */
    public function toRealNamesArray() : array
    {
        $values = array();
        foreach ($this->getAllFieldsAliases() as $name) {
            $field = $this->{'field' . \ucfirst($name)};
            $realName = (string) \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($name)));
            $values[$realName] = $field['value'];
        }

        return $values;
    }

    /**
     * Fetch one entity satisfying the specified conditions.
     *
     * @param  mixed[]          $conditions where conditions
     * @param  string[]         $orderBy    order clause
     * @param  bool             $critical   if true, will be executed on write server
     * @return null|false|Model model instance, null if nothing found, false on error
     */
    public static function fetchEntity(array $conditions, array $orderBy = [], bool $critical = false)
    {
        $entity = new static(); // @phpstan-ignore-line
        $selectQuery = new Select($entity->getDatabaseName(), $critical);
        $selectQuery->select($entity->getAllFieldsRealNames());
        $selectQuery->from($entity->getTableName());
        if (\count($conditions) > 0) {
            $where = array();
            foreach ($conditions as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $where[$realName] = ':where' . \ucfirst($key);
                $selectQuery->bind('where' . \ucfirst($key), $value);
            }
            $selectQuery->where($where);
        }
        if (\count($orderBy) > 0) {
            $orders = array();
            foreach ($orderBy as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $orders[$realName] = \strtoupper(\ucfirst($value));
            }
            $selectQuery->orderBy($orders);
        }
        $selectQuery->limit(1);
        if ($selectQuery->execute() === false) {
            return false;
        }
        $data = $selectQuery->fetch();
        if ($data === false) {
            return null;
        }

        return new static($data); // @phpstan-ignore-line
    }

    /**
     * Fetch one row satisfying the specified conditions.
     *
     * @param  mixed[]           $conditions where conditions
     * @param  string[]          $orderBy    order clause
     * @param  bool              $critical   if true, will be executed on write server
     * @return null|bool|mixed[] row data, null if nothing found, false on error
     */
    public static function fetchArray(array $conditions, array $orderBy = [], bool $critical = false)
    {
        $entity = new static(); // @phpstan-ignore-line
        $selectQuery = new Select($entity->getDatabaseName(), $critical);
        $selectQuery->select($entity->getAllFieldsRealNames());
        $selectQuery->from($entity->getTableName());
        if (\count($conditions) > 0) {
            $where = array();
            foreach ($conditions as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $where[$realName] = ':where' . \ucfirst($key);
                $selectQuery->bind('where' . \ucfirst($key), $value);
            }
            $selectQuery->where($where);
        }
        if (\count($orderBy) > 0) {
            $orders = array();
            foreach ($orderBy as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $orders[$realName] = \strtoupper(\ucfirst($value));
            }
            $selectQuery->orderBy($orders);
        }
        $selectQuery->limit(1);
        if ($selectQuery->execute() === false) {
            return false;
        }
        $data = $selectQuery->fetch();
        if (\is_array($data) === false) {
            return null;
        }

        return $data;
    }

    /**
     * Fetch all entities satisfying the specified conditions.
     *
     * @param  mixed[]       $conditions where conditions
     * @param  string[]      $orderBy    order clause
     * @param  bool          $critical   if true, will be executed on write server
     * @return false|Model[] rows data, false on error
     */
    public static function fetchEntities(array $conditions, array $orderBy = [], bool $critical = false)
    {
        $entity = new static(); // @phpstan-ignore-line
        $res = static::fetchArrays($conditions, $orderBy, $critical);
        $selectQuery = new Select($entity->getDatabaseName(), $critical);
        $selectQuery->select($entity->getAllFieldsRealNames());
        $selectQuery->from($entity->getTableName());
        if (\count($conditions) > 0) {
            $where = array();
            foreach ($conditions as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $where[$realName] = ':where' . \ucfirst($key);
                $selectQuery->bind('where' . \ucfirst($key), $value);
            }
            $selectQuery->where($where);
        }
        if (\count($orderBy) > 0) {
            $orders = array();
            foreach ($orderBy as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $orders[$realName] = \strtoupper(\ucfirst($value));
            }
            $selectQuery->orderBy($orders);
        }
        if ($selectQuery->execute() === false) {
            return false;
        }
        $res = $selectQuery->fetchAll();
        $data = [];
        foreach ($res as $r) {
            $data[] = new static($r); // @phpstan-ignore-line
        }

        return $data;
    }

    /**
     * Fetch all rows satisfying the specified conditions.
     *
     * @param  mixed[]      $conditions where conditions
     * @param  string[]     $orderBy    order clause
     * @param  bool         $critical   if true, will be executed on write server
     * @return bool|mixed[] rows data, false on error
     */
    public static function fetchArrays(array $conditions, array $orderBy = [], bool $critical = false)
    {
        $entity = new static(); // @phpstan-ignore-line
        $selectQuery = new Select($entity->getDatabaseName(), $critical);
        $selectQuery->select((array) \array_combine($entity->getAllFieldsAliases(), $entity->getAllFieldsRealNames()));
        $selectQuery->from($entity->getTableName());
        if (\count($conditions) > 0) {
            $where = array();
            foreach ($conditions as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $where[$realName] = ':where' . \ucfirst($key);
                $selectQuery->bind('where' . \ucfirst($key), $value);
            }
            $selectQuery->where($where);
        }
        if (\count($orderBy) > 0) {
            $orders = array();
            foreach ($orderBy as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $orders[$realName] = \strtoupper(\ucfirst($value));
            }
            $selectQuery->orderBy($orders);
        }
        if ($selectQuery->execute() === false) {
            return false;
        }
        $data = $selectQuery->fetchAll();

        return $data;
    }

    /**
     * Count rows satisfying the specified conditions.
     *
     * @param  mixed[]    $conditions where conditions
     * @param  bool       $critical   if true, will be executed on write server
     * @return bool|mixed number of rows, false on error
     */
    public static function countRows(array $conditions, bool $critical = false)
    {
        $entity = new static(); // @phpstan-ignore-line
        $selectQuery = new Select($entity->getDatabaseName(), $critical);
        $selectQuery->select([ 'count' => 'COUNT(*)' ]);
        $selectQuery->from($entity->getTableName());
        if (\count($conditions) > 0) {
            $where = array();
            foreach ($conditions as $key => $value) {
                $realName = \constant(static::class . '::FIELD_' . \strtoupper(Helper::codifyName($key)));
                $where[$realName] = ':where' . \ucfirst($key);
                $selectQuery->bind('where' . \ucfirst($key), $value);
            }
            $selectQuery->where($where);
        }
        if ($selectQuery->execute() === false) {
            return false;
        }
        $data = $selectQuery->fetch();
        if ($data === false) {
            return false;
        }

        return $data['count'];
    }

    /**
     * Get database's name.
     *
     * @abstract
     * @return string database's name
     */
    abstract public function getDatabaseName() : string;

    /**
     * Get table's name.
     *
     * @abstract
     * @return string table's name
     */
    abstract public function getTableName() : string;

    /**
     * Get primary fields' names.
     *
     * @abstract
     * @return string[] primary fields' names
     */
    abstract public function getPrimary() : array;

    /**
     * Get all fields' names.
     *
     * @abstract
     * @return string[] all fields' names
     */
    abstract public function getAllFieldsRealNames() : array;

    /**
     * Get all fields' aliases.
     *
     * @abstract
     * @return string[] all fields' aliases
     */
    abstract public function getAllFieldsAliases() : array;
}
