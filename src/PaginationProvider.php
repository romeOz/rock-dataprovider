<?php

namespace rock\data;


use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\components\Linkable;
use rock\helpers\Instance;
use rock\helpers\Link;
use rock\helpers\Pagination;
use rock\request\Request;
use rock\sanitize\Sanitize;
use rock\url\Url;

/**
 * @property-read int $pageCount total count pages
 * @property-read int pageCurrent current page
 * @property-read int $pageStart page start
 * @property-read int $pageEnd page end
 * @property-read int $pagePrev prev page
 * @property-read int $pageNext next page
 * @property-read int $pageFirst first page
 * @property-read int $pageLast last page
 * @property-read array $pageDisplay list display pages
 * @property-read int $countMore count more items
 * @property-read int $offset integer the offset of the data. This may be used to set the
 * OFFSET value for a SQL statement for fetching the current page of data.
 *
 * @package rock\data
 */
class PaginationProvider implements ObjectInterface, \ArrayAccess, Linkable
{
    use ObjectTrait;

    const LINK_NEXT = 'next';
    const LINK_PREV = 'prev';
    const LINK_FIRST = 'first';
    const LINK_LAST = 'last';

    /**
     * @var string name of the argument storing the current page index.
     */
    public $pageParam = 'page';
    /**
     * @var string name of the parameter storing the limit items.
     * @see params
     */
    public $limitParam = 'limit';
    /**
     * Current page.
     * @var int
     */
    public $page;
    /**
     * @var integer total number of items.
     */
    public $totalCount = 0;
    /**
     * Items limit.
     * @var int
     */
    public $limit;
    /**
     * Max items.
     * @var int
     */
    public $maxLimit = 30;
    /**
     * Default items limit.
     * @var int
     */
    public $defaultLimit = Pagination::LIMIT;
    /**
     * Sorting pages.
     * @var int
     */
    public $sort = Pagination::SORT;
    /**
     * Page limits.
     * @var int
     */
    public $pageLimit = Pagination::PAGE_LIMIT;
    /**
     * @var Request
     */
    public $request = 'request';
    /**
     * @var Url
     */
    public $url;

    protected $data = [];

    public function init()
    {
        $this->request = Instance::ensure($this->request, '\rock\request\Request', [], false);
    }

    /**
     * Sets the current page number.
     * @param integer $value the zero-based index of the current page.
     */
    public function setPage($value)
    {
        $this->page = $value;
    }

    /**
     * Returns the zero-based current page number.
     * @param boolean $recalculate whether to recalculate the current page based on the page size and item count.
     * @return integer the zero-based current page number.
     */
    public function getPage($recalculate = false)
    {
        if ($this->page === null || $recalculate) {
            if ($this->request instanceof Request) {
                $page = $this->request->get($this->pageParam, 0, Sanitize::positive()->int());
            } else {
                $page = isset($_GET[$this->pageParam]) ? (int)$_GET[$this->pageParam] : 0;
                if ($page < 0) {
                    $page = 0;
                }
            }
            $this->page = $page;
        }

        return $this->page;
    }

    /**
     * Returns items limit.
     * @param bool $recalculate
     * @return int
     */
    public function getLimit($recalculate = false)
    {
        if ($this->limit === null || $recalculate) {
            if ($this->request instanceof Request) {
                $this->limit = $this->request->get($this->limitParam, $this->defaultLimit, Sanitize::positive()->int());
            } else {
                $this->limit = isset($_GET[$this->limitParam]) ? (int)$_GET[$this->limitParam] : $this->defaultLimit;
                if ($this->limit < 0) {
                    $this->limit = $this->defaultLimit;
                }
            }
            if ($this->limit > $this->maxLimit) {
                $this->limit = $this->maxLimit;
            }
        }

        return $this->limit;
    }

    /**
     * Return current page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageCurrent($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageCurrent']) ? $this->data['pageCurrent'] : null;
    }

    /**
     * Returns begin page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageStart($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageStart']) ? $this->data['pageStart'] : null;
    }

    /**
     * Returns end page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageEnd($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageEnd']) ? $this->data['pageEnd'] : null;
    }

    /**
     * Returns first display page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageFirst($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageFirst']) ? $this->data['pageFirst'] : null;
    }

    /**
     * Returns last display page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageLast($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageLast']) ? $this->data['pageLast'] : null;
    }

    /**
     * Returns prev display page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPagePrev($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pagePrev']) ? $this->data['pagePrev'] : null;
    }

    /**
     * Returns next display page.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageNext($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageNext']) ? $this->data['pageNext'] : null;
    }

    /**
     * Returns list display pages.
     * @return array
     */
    public function getPageDisplay()
    {
        return isset($this->data['pageDisplay']) ? $this->data['pageDisplay'] : [];
    }

    /**
     * Returns total count pages.
     * @param bool $recalculate
     * @return int|null
     */
    public function getPageCount($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['pageCount']) ? $this->data['pageCount'] : null;
    }

    /**
     * Returns total count items.
     * @param bool $recalculate
     * @return int|null
     */
    public function getTotalCount($recalculate = false)
    {
        $this->calculate($recalculate);
        return $this->totalCount;
    }

    /**
     * Returns count more items.
     * @param bool $recalculate
     * @return int|null
     */
    public function getCountMore($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['countMore']) ? $this->data['countMore'] : null;
    }

    /**
     * Returns offset.
     * @param bool $recalculate
     * @return int|null
     */
    public function getOffset($recalculate = false)
    {
        $this->calculate($recalculate);
        return isset($this->data['offset']) ? $this->data['offset'] : null;
    }

    /**
     * Returns all list pagination params.
     * @param bool $recalculate
     * @return array
     */
    public function toArray($recalculate = false)
    {
        $this->calculate($recalculate);
        return $this->data;
    }

    /**
     * Creates the URL suitable for pagination with the specified page number.
     * This method is mainly called by pagers when creating URLs used to perform pagination.
     * @param integer $page the zero-based page number that the URL should point to.
     * @param null $limit
     * @param string $scheme
     * @return string the created URL
     * @throws \rock\url\UrlException
     */
    public function createUrl($page, $limit = null, $scheme = Url::REL)
    {
        $params = [$this->pageParam => (int)$page];

        $limit = (int)$limit;
        if ($limit <= 0) {
            $limit = $this->getLimit();
        }
        if ($limit != $this->defaultLimit) {
            $params[$this->limitParam] = $limit;
        } else {
            unset($params[$this->limitParam]);
        }

        if (is_array($this->url)) {
            return Url::modify(array_merge($this->url, $params), $scheme);
        } elseif ($this->url instanceof Url) {
            return $this->url->setArgs($params)->get($scheme);
        }
        return Url::modify($params, $scheme);
    }

    /**
     * Returns a whole set of links for navigating to the first, last, next and previous pages.
     * @param string $scheme
     * @return array the links for navigational purpose.
     * The array keys specify the purpose of the links (e.g. {@see \rock\data\PaginationProvider::LINK_FIRST}),
     * and the array values are the corresponding URLs.
     */
    public function getLinks($scheme = Url::REL)
    {
        $this->calculate();
        return [
            Link::REL_SELF => $this->createUrl($this->getPage(), null, $scheme),
            self::LINK_FIRST => $this->createUrl($this->getPageFirst(), null, $scheme),
            self::LINK_PREV => $this->createUrl($this->getPagePrev(), null, $scheme),
            self::LINK_NEXT => $this->createUrl($this->getPageNext(), null, $scheme),
            self::LINK_LAST => $this->createUrl($this->getPageLast(), null, $scheme),
        ];
    }

    public function offsetSet($name, $value)
    {
        $this->$name = $value;
    }

    public function offsetGet($name)
    {
        $this->calculate();
        return $this->$name;
    }

    public function offsetExists($name)
    {
        $this->calculate();
        return isset($this->data[$name]);
    }

    public function offsetUnset($name)
    {
        throw new DataProviderException(DataProviderException::SETTING_READ_ONLY_PROPERTY, ['class' => __CLASS__, 'property' => $name]);
    }

    /**
     * Calculate params pagination.
     * @param bool $recalculate
     */
    protected function calculate($recalculate = false)
    {
        if (empty($this->data) || $recalculate) {
            $this->data = Pagination::get(
                $this->totalCount,
                $this->getPage(),
                $this->getLimit(),
                $this->sort,
                $this->pageLimit
            );
        }
    }
}