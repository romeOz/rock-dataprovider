<?php

namespace rockunit;


use rock\data\PaginationProvider;

class PaginationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array test data
     */
    public function dataProviderCreateUrl()
    {
        return [
            [
                2,
                null,
                '/index.php?page=2',
            ],
            [
                2,
                5,
                '/index.php?page=2&limit=5',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCreateUrl
     *
     * @param integer $page
     * @param integer $limit
     * @param string $expectedUrl
     */
    public function testCreateUrl($page, $limit, $expectedUrl)
    {
        $pagination = new PaginationProvider();
        $pagination->url = ['/index.php'];
        $this->assertEquals($expectedUrl, $pagination->createUrl($page, $limit));
    }

    public function testGetLinks()
    {
        $pagination = new PaginationProvider();
        $pagination->limit = 10;
        $pagination->totalCount = 100;
        $expected = [
            'self' => '/',
            'first' => '/',
            'prev' => '/',
            'next' => '/?page=2',
            'last' => '/?page=10',
        ];
        $this->assertEquals($expected, $pagination->getLinks());

        $pagination = new PaginationProvider();
        $pagination->limit = 10;
        $pagination->totalCount = 100;
        $pagination->url = ['/index.php'];
        $expected = [
            'self' => '/index.php',
            'first' => '/index.php',
            'prev' => '/index.php',
            'next' => '/index.php?page=2',
            'last' => '/index.php?page=10',
        ];
        $this->assertEquals($expected, $pagination->getLinks());

    }
}
