<?php
namespace rockunit;

use rock\data\Sort;
use rock\url\Url;

/**
 * @group data
 */
class SortTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
        ]);

        $orders = $sort->getOrders();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(SORT_ASC, $orders['age']);
        $this->assertEquals(SORT_DESC, $orders['first_name']);
        $this->assertEquals(SORT_DESC, $orders['last_name']);

        $sort->enableMultiSort = false;
        $orders = $sort->getOrders(true);
        $this->assertEquals(1, count($orders));
        $this->assertEquals(SORT_ASC, $orders['age']);
    }

    public function testGetAttributeOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
        ]);

        $orders = $sort->getAttributeOrders();
        $this->assertEquals(2, count($orders));
        $this->assertEquals(SORT_ASC, $orders['age']);
        $this->assertEquals(SORT_DESC, $orders['name']);

        $sort->enableMultiSort = false;
        $orders = $sort->getAttributeOrders(true);
        $this->assertEquals(1, count($orders));
        $this->assertEquals(SORT_ASC, $orders['age']);
    }

    public function testGetAttributeOrder()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
        ]);

        $this->assertEquals(SORT_ASC, $sort->getAttributeOrder('age'));
        $this->assertEquals(SORT_DESC, $sort->getAttributeOrder('name'));
        $this->assertNull($sort->getAttributeOrder('xyz'));
    }

    public function testCreateSortParam()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
            'route' => 'site/index',
        ]);

        $this->assertEquals('-age,-name', $sort->createSortParam('age'));
        $this->assertEquals('name,age', $sort->createSortParam('name'));
    }

    public function testCreateUrl()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
            'url' => ['/index.php'],
        ]);

        $this->assertEquals('/index.php?sort=-age%2C-name', $sort->createUrl('age'));
        $this->assertEquals('/index.php?sort=name%2Cage', $sort->createUrl('name'));
    }

    public function testLink()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name'
            ],
            'enableMultiSort' => true,
            'url' => Url::set('/index.php'),
        ]);

        $this->assertEquals('<a class="asc" href="/index.php?sort=-age%2C-name" data-sort="-age,-name">Age</a>', $sort->link('age'));
    }
}
