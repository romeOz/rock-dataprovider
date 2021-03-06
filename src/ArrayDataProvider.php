<?php

namespace rock\data;

use rock\helpers\ArrayHelper;


/**
 * ArrayDataProvider implements a data provider based on a data array.
 *
 * The {@see \rock\data\ArrayDataProvider::$allModels} property contains all data models that may be sorted and/or paginated.
 * ArrayDataProvider will provide the data after sorting and/or pagination.
 * You may configure the {@see \rock\data\BaseDataProvider::$pagination} properties to
 * customize the sorting and pagination behaviors.
 *
 * Elements in the {@see \rock\data\ArrayDataProvider::$allModels} array may be either objects (e.g. model objects)
 * or associative arrays (e.g. query results of DAO).
 * Make sure to set the {@see \rock\data\ArrayDataProvider::$key} property to the name of the field that uniquely
 * identifies a data record or false if you do not have such a field.
 *
 * Compared to {@see \rock\db\ActiveDataProvider}, ArrayDataProvider could be less efficient
 * because it needs to have {@see \rock\data\ArrayDataProvider::$allModels} ready.
 *
 * ArrayDataProvider may be used in the following way:
 *
 * ```php
 * $query = new Query;
 * $provider = new ArrayDataProvider([
 *     'allModels' => $query->from('post')->all(),
 *     'pagination' => [
 *         'limit' => 20,
 *         'sort' => SORT_DESC,
 *         'pageLimit' => 5,
 *         'page' => (int)$_GET['page'],
 *     ],
 * ]);
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ```
 */
class ArrayDataProvider extends BaseDataProvider
{
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     * If this is not set, the index of the {@see \rock\data\BaseDataProvider::$models} array will be used.
     * @see getKeys()
     */
    public $key;
    /**
     * @var array the data that is not paginated or sorted. When pagination is enabled,
     * this property usually contains more elements than {@see \rock\data\BaseDataProvider::$models}.
     * The array elements must use zero-based integer keys.
     */
    public $allModels;


    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (($models = $this->allModels) === null) {
            return [];
        }

        if (($sort = $this->getSort()) !== false) {
            $models = $this->sortModels($models, $sort);
        }

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();

            if ($pagination->pageLimit > 0) {
                $models = array_slice($models, $pagination->getOffset(), $pagination->getLimit(), true);
            }
        }

        return $models;
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        return count($this->allModels);
    }

    /**
     * Sorts the data models according to the given sort definition
     * @param array $models the models to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted data models
     */
    protected function sortModels($models, $sort)
    {
        $orders = $sort->getOrders();
        if (!empty($orders)) {
            ArrayHelper::multisort($models, array_keys($orders), array_values($orders));
        }

        return $models;
    }
}