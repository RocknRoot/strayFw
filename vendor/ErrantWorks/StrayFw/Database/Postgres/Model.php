<?php

namespace ErrantWorks\StrayFw\Database\Postgres;

use ErrantWorks\StrayFw\Database\Provider\Model as ProviderModel;

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
     * Aliases of primary columns.
     *
     * @var string[]
     */
    protected $primary;

    /**
     * Aliases of modified columns values.
     *
     * @var string[]
     */
    protected $modified;

    /**
     * Save the model. Delete if deletionFlag is true.
     *
     * @return bool true if successfully saved
     */
    public function save()
    {
    }

    /**
     * If not new, delete the model.
     *
     * @return bool true if successfully deleted
     */
    public function delete()
    {
    }
}
