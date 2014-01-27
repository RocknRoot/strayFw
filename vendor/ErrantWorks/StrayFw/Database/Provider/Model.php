<?php

namespace ErrantWorks\StrayFw\Database\Provider;

use ErrantWorks\StrayFw\Exception\BadUse;

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
     *
     * @var bool
     */
    protected $new;

    /**
     * Flag for deletion. If true, model will be deleted on save.
     *
     * @var bool
     */
    protected $deletionFlag;

    /**
     * Construct a new model.
     *
     * @throws BadUse if subclass doesnt define NAME class constant
     * @throws BadUse if subclass doesnt define DATABASE class constant
     */
    public function __construct()
    {
        $this->new = true;
        $this->deletionFlag = false;
        if (defined('static::NAME') === false) {
            throw new BadUse('Model subclass doesn\'t define NAME class constant');
        }
        if (defined('static::DATABASE') === false) {
            throw new BadUse('Model subclass doesn\'t define DATABASE class constant');
        }
    }

    /**
     * Save the model. Delete if deletionFlag is true.
     *
     * @return bool true if successfully saved
     */
    abstract public function save();

    /**
     * If not new, delete the model.
     *
     * @return bool true if successfully deleted
     */
    abstract public function delete();

    /**
     * Set flag for deletion. If true, model will be deleted on save.
     *
     * @param bool $value new flag value
     */
    public function setDeletionFlag($value)
    {
        $this->deletionFlag = $value;
    }
}
