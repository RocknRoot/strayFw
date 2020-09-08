<?php

namespace RocknRoot\StrayFw\Database\Provider;

/**
 * Model representation parent class for all providers.
 * A model represents a table in SQL, a collection in MongoDB, ...
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Model
{
    /**
     * False if instance has been created from existing data.
     */
    protected bool $new;

    /**
     * Flag for deletion. If true, model will be deleted on save.
     */
    protected bool $deletionFlag;

    /**
     * Construct a new model.
     */
    public function __construct()
    {
        $this->new = true;
        $this->deletionFlag = false;
    }

    /**
     * Save the model. Delete if deletionFlag is true.
     *
     * @return bool true if successfully saved
     */
    abstract public function save() : bool;

    /**
     * If not new, delete the model.
     *
     * @return bool true if successfully deleted
     */
    abstract public function delete() : bool;

    /**
     * Flag for deletion.
     *
     * @return bool true if model will be deleted on save
     */
    public function getDeletionFlag() : bool
    {
        return $this->deletionFlag;
    }

    /**
     * Set flag for deletion. If true, model will be deleted on save.
     *
     * @param bool $value new flag value
     */
    public function setDeletionFlag(bool $value) : void
    {
        $this->deletionFlag = $value;
    }
}
