<?php

namespace RocknRoot\StrayFw\Database;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Exception\DatabaseNotFound;
use RocknRoot\StrayFw\Exception\ExternalLink;

/**
 * Database representation class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Database
{
    /**
     * Registered databases.
     *
     * @static
     * @var array<string, Database>
     */
    protected static array $databases = array();

    /**
     * Database alias.
     */
    protected string $alias;

    /**
     * Provider's classes namespace.
     */
    protected string $providerNamespace;

    /**
     * Provider's instance.
     */
    protected \RocknRoot\StrayFw\Database\Provider\Database $providerDatabase;

    /**
     * Servers info.
     *
     * @var array<mixed, array<string, mixed>>
     */
    protected array $servers;

    /**
     * Transaction level.
     * If 0, there's no happening transaction.
     * If 1, it's a classic transaction.
     * If >= 2, there's at least one save point.
     */
    protected int $transactionLevel;

    /**
     * Construct a new database representation.
     *
     * @param  string           $alias database alias
     * @throws DatabaseNotFound if database parameters in settings can't be found
     * @throws BadUse           if database parameters in settings aren't well formatted
     * @throws BadUse           if database parameters in settings miss provider
     */
    protected function __construct(string $alias)
    {
        $this->alias = $alias;
        $this->transactionLevel = 0;
        $settings = Config::getSettings();
        if (isset($settings['databases']) === false || isset($settings['databases'][$alias]) === false) {
            throw new DatabaseNotFound('database "' . $alias . '" parameters can\'t be found in settings.yml');
        }
        $config = $settings['databases'][$alias];
        if (isset($config['provider']) === false) {
            throw new BadUse('database "' . $alias . '" parameters in settings.yml miss provider');
        }
        $this->providerNamespace = $config['provider'];
        if ($this->providerNamespace[0] != '\\') {
            $this->providerNamespace = __NAMESPACE__ . '\\' . $this->providerNamespace;
        }
        $database = $this->providerNamespace . '\\Database';
        $this->providerDatabase = new $database();
        if (isset($config['name']) === true) {
            $info = array();
            $info['host'] = $config['host'];
            $info['port'] = $config['port'];
            $info['name'] = $config['name'];
            $info['user'] = $config['user'];
            $info['pass'] = $config['pass'];
            $this->servers['all'] = $info;
        } elseif (isset($config['read']) === true && \is_array($config['read']) === true && isset($config['write']) === true && \is_array($config['write']) === true) {
            $read = $config['read'];
            if (\is_array(\current($read)) === true) {
                $read = $read[\mt_rand(0, \count($read) - 1)];
            }
            $info = array();
            $info['host'] = $read['host'];
            $info['port'] = $read['port'];
            $info['name'] = $read['name'];
            $info['user'] = $read['user'];
            $info['pass'] = $read['pass'];
            $this->servers['read'] = $info;
            $write = $config['write'];
            if (\is_array(\current($write)) === true) {
                $write = $write[\mt_rand(0, \count($write) - 1)];
            }
            $info = array();
            $info['host'] = $write['host'];
            $info['port'] = $write['port'];
            $info['name'] = $write['name'];
            $info['user'] = $write['user'];
            $info['pass'] = $write['pass'];
            $this->servers['write'] = $info;
        } else {
            throw new BadUse('database "' . $alias . '" parameters in settings.yml aren\'t well formatted');
        }
    }

    /**
     * Ensure that link is disconnected at object destruction.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Connect to the database.
     *
     * @throws ExternalLink if database connection can't be established
     */
    public function connect(): void
    {
        if ($this->isConnected() === false) {
            try {
                if (isset($this->servers['all']) === true) {
                    $dsn = $this->providerDatabase->getDsn($this->servers['all']);
                    $this->servers['all']['link'] = new \PDO($dsn, $this->servers['all']['user'], $this->servers['all']['pass']);
                } else {
                    $dsn = $this->providerDatabase->getDsn($this->servers['read']);
                    $this->servers['read']['link'] = new \PDO($dsn, $this->servers['read']['user'], $this->servers['read']['pass']);
                    $dsn = $this->providerDatabase->getDsn($this->servers['write']);
                    $this->servers['write']['link'] = new \PDO($dsn, $this->servers['write']['user'], $this->servers['write']['pass']);
                }
            } catch (\PDOException $e) {
                throw new ExternalLink('can\'t connect to database (' . $e->getMessage() . ')');
            }
        }
    }

    /**
     * Disconnect link to database.
     */
    public function disconnect(): void
    {
        if (isset($this->servers['all']) === true) {
            unset($this->servers['all']['link']);
        } else {
            unset($this->servers['read']['link']);
            unset($this->servers['write']['link']);
        }
    }

    /**
     * Check if connected to database.
     *
     * @return bool true if connected
     */
    public function isConnected(): bool
    {
        if (isset($this->servers['all']) === true) {
            return isset($this->servers['all']['link']);
        }

        return isset($this->servers['read']['link']) && isset($this->servers['write']['link']);
    }

    /**
     * Get link.
     *
     * @return mixed link info
     */
    public function getLink()
    {
        if ($this->isConnected() === false) {
            $this->connect();
        }
        if (isset($this->servers['all']) === true) {
            return $this->servers['all']['link'];
        }
        if ($this->transactionLevel >= 1) {
            return $this->servers['write']['link'];
        }

        return $this->servers['read']['link'];
    }

    /**
     * Get master server link.
     *
     * @return mixed link info
     */
    public function getMasterLink()
    {
        if ($this->isConnected() === false) {
            $this->connect();
        }
        if (isset($this->servers['all']) === true) {
            return $this->servers['all']['link'];
        }

        return $this->servers['write']['link'];
    }

    /**
     * Begin transaction or create a new save point if already transactionning.
     *
     * @return bool true if successful
     */
    public function beginTransaction(): bool
    {
        if ($this->isConnected() === false) {
            $this->connect();
        }
        ++$this->transactionLevel;
        if ($this->transactionLevel == 1) {
            return $this->providerDatabase->beginTransaction($this->GetMasterLink());
        }

        return $this->providerDatabase->savePoint($this->GetMasterLink(), 'LEVEL' . ($this->transactionLevel - 1));
    }

    /**
     * Commit transaction modifications.
     *
     * @return bool true if successful
     */
    public function commit(): bool
    {
        if ($this->isConnected() === false) {
            $this->connect();
        }
        if ($this->transactionLevel > 0) {
            --$this->transactionLevel;
            if ($this->transactionLevel == 0) {
                return $this->providerDatabase->commit($this->GetMasterLink());
            }

            return $this->providerDatabase->releaseSavePoint($this->GetMasterLink(), 'LEVEL' . $this->transactionLevel);
        }

        return false;
    }

    /**
     * Roll back transaction modifications.
     *
     * @return bool true if successful
     */
    public function rollBack(): bool
    {
        if ($this->isConnected() === false) {
            $this->connect();
        }
        if ($this->transactionLevel > 0) {
            --$this->transactionLevel;
            if ($this->transactionLevel == 0) {
                return $this->providerDatabase->rollBack($this->GetMasterLink());
            }

            return $this->providerDatabase->rollBackSavePoint($this->GetMasterLink(), 'LEVEL' . $this->transactionLevel);
        }

        return false;
    }

    /**
     * Get database alias.
     *
     * @return string alias
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Register a new database.
     *
     * @static
     * @param string $alias database alias
     */
    public static function registerDatabase(string $alias): void
    {
        if (isset(self::$databases[$alias]) === false) {
            self::$databases[$alias] = new static($alias); // @phpstan-ignore-line
        }
    }

    /**
     * Get a database instance aliased as requested.
     *
     * @static
     * @param  string           $alias requested database alias
     * @throws DatabaseNotFound if database isn't registered
     * @return Database         instance
     */
    public static function get(string $alias): Database
    {
        if (isset(self::$databases[$alias]) === false) {
            throw new DatabaseNotFound('database "' . $alias . '" doesn\'t seem to be registered');
        }

        return self::$databases[$alias];
    }
}
