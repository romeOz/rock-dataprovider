<?php

namespace rock\data;

/**
 * DataProviderInterface is the interface that must be implemented by data provider classes.
 */
interface DataProviderInterface
{
    /**
     * Prepares the data models and keys.
     *
     * This method will prepare the data models and keys that can be retrieved via
     * {@see \rock\data\DataProviderInterface::getModels()} and {@see \rock\data\DataProviderInterface::getKeys()}.
     *
     * This method will be implicitly called by {@see \rock\data\DataProviderInterface::getModels()} and {@see \rock\data\DataProviderInterface::getKeys()} if it has not been called before.
     *
     * @param boolean $forcePrepare whether to force data preparation even if it has been done before.
     */
    public function prepare($forcePrepare = false);

    /**
     * Returns the number of data models in the current page.
     * This is equivalent to `count($provider->getModels())`.
     * When {@see \rock\data\DataProviderInterface::getPagination()} is false, this is the same as {@see \rock\data\DataProviderInterface::getTotalCount()}.
     * @return integer the number of data models in the current page.
     */
    public function getCount();

    /**
     * Returns the total number of data models.
     * When {@see \rock\data\DataProviderInterface::getPagination()} is false, this is the same as {@see \rock\data\DataProviderInterface::getCount()}.
     * @return integer total number of possible data models.
     */
    public function getTotalCount();

    /**
     * Returns the data models in the current page.
     * @return array the list of data models in the current page.
     */
    public function getModels();

    /**
     * Returns the key values associated with the data models.
     * @return array the list of key values corresponding to {@see \rock\data\DataProviderInterface::getModels()}. Each data model in {@see \rock\data\DataProviderInterface::getModels()}
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys();

    /**
     * @return PaginationProvider the pagination object. If this is false, it means the pagination is disabled.
     */
    public function getPagination();
}