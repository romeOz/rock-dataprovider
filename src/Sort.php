<?php
namespace rock\data;

use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\helpers\Inflector;
use rock\helpers\Instance;
use rock\request\Request;
use rock\template\Html;
use rock\url\Url;


/**
 * Sort represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several attributes,
 * we can use Sort to represent the sorting information and generate
 * appropriate hyperlinks that can lead to sort actions.
 *
 * A typical usage example is as follows,
 *
 * ```php
 * function actionIndex()
 * {
 *     $sort = new Sort([
 *         'attributes' => [
 *             'age',
 *             'name' => [
 *                 'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
 *                 'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
 *                 'default' => SORT_DESC,
 *                 'label' => 'Name',
 *             ],
 *         ],
 *     ]);
 *
 *     $models = Article::find()
 *         ->where(['status' => 1])
 *         ->orderBy($sort->orders)
 *         ->all();
 *
 *     return $this->render('index', [
 *          'models' => $models,
 *          'sort' => $sort,
 *     ]);
 * }
 * ```
 *
 * View:
 *
 * ```php
 * // display links leading to sort actions
 * echo $sort->link('name') . ' | ' . $sort->link('age');
 *
 * foreach ($models as $model) {
 *     // display $model here
 * }
 * ```
 *
 * In the above, we declare two {@see \rock\data\Sort::$attributes} that support sorting: name and age.
 * We pass the sort information to the Article query so that the query results are
 * sorted by the orders specified by the Sort object. In the view, we show two hyperlinks
 * that can lead to pages with the data sorted by the corresponding attributes.
 *
 * @property array $attributeOrders Sort directions indexed by attribute names. Sort direction can be either
 * `SORT_ASC` for ascending order or `SORT_DESC` for descending order. This property is read-only.
 * @property array $orders The columns (keys) and their corresponding sort directions (values). This can be
 * passed to {@see \rock\db\Query::orderBy()} to construct a DB query. This property is read-only.
 *
 */
class Sort implements ObjectInterface
{
    use ObjectTrait;

    /**
     * @var boolean whether the sorting can be applied to multiple attributes simultaneously.
     * Defaults to false, which means each time the data can only be sorted by one attribute.
     */
    public $enableMultiSort = false;
    /**
     * @var array list of attributes that are allowed to be sorted. Its syntax can be
     * described using the following example:
     *
     * ```php
     * [
     *     'age',
     *     'name' => [
     *         'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *         'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     *         'default' => SORT_DESC,
     *         'label' => 'Name',
     *     ],
     * ]
     * ```
     *
     * In the above, two attributes are declared: "age" and "name". The "age" attribute is
     * a simple attribute which is equivalent to the following:
     *
     * ```php
     * 'age' => [
     *     'asc' => ['age' => SORT_ASC],
     *     'desc' => ['age' => SORT_DESC],
     *     'default' => SORT_ASC,
     *     'label' => Inflector::camel2words('age'),
     * ]
     * ```
     *
     * The "name" attribute is a composite attribute:
     *
     * - The "name" key represents the attribute name which will appear in the URLs leading
     *   to sort actions.
     * - The "asc" and "desc" elements specify how to sort by the attribute in ascending
     *   and descending orders, respectively. Their values represent the actual columns and
     *   the directions by which the data should be sorted by.
     * - The "default" element specifies by which direction the attribute should be sorted
     *   if it is not currently sorted (the default value is ascending order).
     * - The "label" element specifies what label should be used when calling {@see \rock\data\Sort::link()} to create
     *   a sort link. If not set, {@see \rock\helpers\Inflector::camel2words()} will be called to get a label.
     *   Note that it will not be HTML-encoded.
     *
     * Note that if the Sort object is already created, you can only use the full format
     * to configure every attribute. Each attribute must include these elements: `asc` and `desc`.
     */
    public $attributes = [];
    /**
     * @var string the name of the parameter that specifies which attributes to be sorted
     * in which direction. Defaults to 'sort'.
     * @see params
     */
    public $sortParam = 'sort';
    /**
     * @var array the order that should be used when the current request does not specify any order.
     * The array keys are attribute names and the array values are the corresponding sort directions. For example,
     *
     * ```php
     * [
     *     'name' => SORT_ASC,
     *     'created_at' => SORT_DESC,
     * ]
     * ```
     *
     * @see attributeOrders
     */
    public $defaultOrder;
    /**
     * @var string the route of the controller action for displaying the sorted contents.
     * If not set, it means using the currently requested route.
     */
    public $route;
    /**
     * @var string the character used to separate different attributes that need to be sorted by.
     */
    public $separator = ',';
    /**
     * @var array parameters (name => value) that should be used to obtain the current sort directions
     * and to create new sort URLs. If not set, $_GET will be used instead.
     *
     * In order to add hash to all links use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by {@see \rock\data\Sort::$sortParam} is considered to be the current sort directions.
     * If the element does not exist, the {@see \rock\data\Sort::$defaultOrder} default order will be used.
     *
     * @see sortParam
     * @see defaultOrder
     */
    public $params = [];
    /**
     * @var Request
     */
    public $request = 'request';
    /**
     * @var Url the URL manager used for creating sort URLs. If not set,
     * the "urlManager" application component will be used.
     */
    public $url;


    /**
     * Normalizes the {@see \rock\data\Sort::$attributes} property.
     */
    public function init()
    {
        $this->request = Instance::ensure($this->request, '\rock\request\Request', [], false);
        $attributes = [];
        foreach ($this->attributes as $name => $attribute) {
            if (!is_array($attribute)) {
                $attributes[$attribute] = [
                    'asc' => [$attribute => SORT_ASC],
                    'desc' => [$attribute => SORT_DESC],
                ];
            } elseif (!isset($attribute['asc'], $attribute['desc'])) {
                $attributes[$name] = array_merge([
                    'asc' => [$name => SORT_ASC],
                    'desc' => [$name => SORT_DESC],
                ], $attribute);
            } else {
                $attributes[$name] = $attribute;
            }
        }
        $this->attributes = $attributes;
    }

    /**
     * Returns the columns and their corresponding sort directions.
     * @param boolean $recalculate whether to recalculate the sort directions
     * @return array the columns (keys) and their corresponding sort directions (values).
     * This can be passed to {@see \rock\db\Query::orderBy()} to construct a DB query.
     */
    public function getOrders($recalculate = false)
    {
        $attributeOrders = $this->getAttributeOrders($recalculate);
        $orders = [];
        foreach ($attributeOrders as $attribute => $direction) {
            $definition = $this->attributes[$attribute];
            $columns = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            foreach ($columns as $name => $dir) {
                $orders[$name] = $dir;
            }
        }

        return $orders;
    }

    /**
     * @var array the currently requested sort order as computed by {@see \rock\data\Sort::getAttributeOrders()} .
     */
    private $_attributeOrders;

    /**
     * Returns the currently requested sort information.
     * @param boolean $recalculate whether to recalculate the sort directions
     * @return array sort directions indexed by attribute names.
     * Sort direction can be either `SORT_ASC` for ascending order or
     * `SORT_DESC` for descending order.
     */
    public function getAttributeOrders($recalculate = false)
    {
        if ($this->_attributeOrders === null || $recalculate) {
            $this->_attributeOrders = [];
            $param = $this->getParam();
            if (!empty($param) && is_scalar($param)) {
                $attributes = explode($this->separator, $param);
                foreach ($attributes as $attribute) {
                    $descending = false;
                    if (strncmp($attribute, '-', 1) === 0) {
                        $descending = true;
                        $attribute = substr($attribute, 1);
                    }

                    if (isset($this->attributes[$attribute])) {
                        $this->_attributeOrders[$attribute] = $descending ? SORT_DESC : SORT_ASC;
                        if (!$this->enableMultiSort) {
                            return $this->_attributeOrders;
                        }
                    }
                }
            }
            if (empty($this->_attributeOrders) && is_array($this->defaultOrder)) {
                $this->_attributeOrders = $this->defaultOrder;
            }
        }

        return $this->_attributeOrders;
    }

    /**
     * Returns the sort direction of the specified attribute in the current request.
     * @param string $attribute the attribute name
     * @return boolean|null Sort direction of the attribute. Can be either `SORT_ASC`
     * for ascending order or `SORT_DESC` for descending order. Null is returned
     * if the attribute is invalid or does not need to be sorted.
     */
    public function getAttributeOrder($attribute)
    {
        $orders = $this->getAttributeOrders();

        return isset($orders[$attribute]) ? $orders[$attribute] : null;
    }

    /**
     * Generates a hyperlink that links to the sort action to sort by the specified attribute.
     * Based on the sort direction, the CSS class of the generated hyperlink will be appended
     * with "asc" or "desc".
     * @param string $attribute the attribute name by which the data should be sorted by.
     * @param array $options additional HTML attributes for the hyperlink tag.
     * There is one special attribute `label` which will be used as the label of the hyperlink.
     * If this is not set, the label defined in {@see \rock\data\Sort::$attributes} will be used.
     * If no label is defined, {@see \rock\helpers\Inflector::camel2words()} will be called to get a label.
     * Note that it will not be HTML-encoded.
     * @return string the generated hyperlink
     * @throws DataProviderException if the attribute is unknown
     */
    public function link($attribute, $options = [])
    {
        if (!class_exists('\rock\template\Html')) {
            throw new DataProviderException(DataProviderException::NOT_INSTALL_TEMPLATE);
        }
        if (($direction = $this->getAttributeOrder($attribute)) !== null) {
            $class = $direction === SORT_DESC ? 'desc' : 'asc';
            if (isset($options['class'])) {
                $options['class'] .= ' ' . $class;
            } else {
                $options['class'] = $class;
            }
        }

        $url = $this->createUrl($attribute);
        $options['data-sort'] = $this->createSortParam($attribute);

        if (isset($options['label'])) {
            $label = $options['label'];
            unset($options['label']);
        } else {
            if (isset($this->attributes[$attribute]['label'])) {
                $label = $this->attributes[$attribute]['label'];
            } else {
                $label = Inflector::camel2words($attribute);
            }
        }

        return Html::a($label, $url, $options);
    }

    /**
     * Creates a URL for sorting the data by the specified attribute.
     * This method will consider the current sorting status given by {@see \rock\data\Sort::$attributeOrders}.
     * For example, if the current page already sorts the data by the specified attribute in ascending order,
     * then the URL created will lead to a page that sorts the data by the specified attribute in descending order.
     * @param string $attribute the attribute name
     * @param string $scheme
     * @return string the URL for sorting. False if the attribute is invalid.
     * @see attributeOrders
     * @see params
     */
    public function createUrl($attribute, $scheme = Url::REL)
    {
        $params = [$this->sortParam => $this->createSortParam($attribute)];

        if (is_array($this->url)) {
            return Url::modify(array_merge($this->url, $params), $scheme);
        } elseif ($this->url instanceof Url) {
            return $this->url->setArgs($params)->get($scheme);
        }
        return Url::modify($params);
    }

    /**
     * Creates the sort variable for the specified attribute.
     * The newly created sort variable can be used to create a URL that will lead to
     * sorting by the specified attribute.
     * @param string $attribute the attribute name
     * @return string the value of the sort variable
     * @throws DataProviderException if the specified attribute is not defined in {@see \rock\data\Sort::$attributes}
     */
    public function createSortParam($attribute)
    {
        if (!isset($this->attributes[$attribute])) {
            throw new DataProviderException("Unknown attribute: {$attribute}");
        }
        $definition = $this->attributes[$attribute];
        $directions = $this->getAttributeOrders();
        if (isset($directions[$attribute])) {
            $direction = $directions[$attribute] === SORT_DESC ? SORT_ASC : SORT_DESC;
            unset($directions[$attribute]);
        } else {
            $direction = isset($definition['default']) ? $definition['default'] : SORT_ASC;
        }

        if ($this->enableMultiSort) {
            $directions = array_merge([$attribute => $direction], $directions);
        } else {
            $directions = [$attribute => $direction];
        }

        $sorts = [];
        foreach ($directions as $attribute => $direction) {
            $sorts[] = $direction === SORT_DESC ? '-' . $attribute : $attribute;
        }

        return implode($this->separator, $sorts);
    }

    /**
     * Returns a value indicating whether the sort definition supports sorting by the named attribute.
     * @param string $name the attribute name
     * @return boolean whether the sort definition supports sorting by the named attribute.
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    protected function getParam()
    {
        if (empty($this->params)) {
            if ($this->request instanceof Request) {
                return $this->request->get($this->sortParam);
            }
            return isset($_GET[$this->sortParam]) ? strip_tags($_GET[$this->sortParam]) : null;
        }

        return isset($this->params[$this->sortParam]) ? strip_tags($this->params[$this->sortParam]) : null;
    }
}
