<?php

namespace idoit\Component\Table;

use idoit\Component\Table\Pagerfanta\View\IdoitView;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;
use isys_application;
use isys_tenantsettings as TenantSettings;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * i-doit Table Component.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Table
{
    /**
     * Parameter for current page.
     */
    const CURRENT_PAGE         = 'page';
    const DEFAULT_FILTER_FIELD = 'isys_cmdb_dao_category_g_global__title';
    const CURRENT_STATUS       = 'status';

    /**
     * @var array
     */
    protected $header;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Config
     */
    protected $tableConfig;

    /**
     * @var Pagerfanta
     */
    protected $pager;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $options = [
        'enableCheckboxes'         => false,
        'rowClick'                 => false,
        'rowClickURL'              => null,
        'keyboardCommands'         => false,
        'tableConfigURL'           => null,
        'dragDrop'                 => false,
        'filter'                   => false,
        'filterColumns'            => [],
        'resizeColumns'            => false,
        'resizeColumnAjaxURL'      => null,
        'resizeTableAjaxURL'       => null,
        'columnSizes'              => [],
        'tableSize'                => 0,
        'pages'                    => 0,
        'rowsPerPage'              => 50,
        'fuzzySuggestion'          => false,
        'fuzzySuggestionThreshold' => 0.2,
        'fuzzySuggestionDistance'  => 50,
        'idField'                  => '__id__',
        'status'                   => C__RECORD_STATUS__NORMAL,
        'ajaxMethod'               => 'get',
        'enableMultiSelection'     => false
    ];

    protected $unique;

    /**
     * Table factory.
     *
     * @param AdapterInterface $adapter
     * @param array            $header
     * @param array            $options
     *
     * @return static
     */
    public static function factory(AdapterInterface $adapter, array $header, array $options = [])
    {
        return new static($adapter, $header, $options);
    }

    /**
     * Table constructor.
     *
     * @param AdapterInterface $adapter
     * @param Config           $tableConfig
     * @param array            $header
     * @param array            $options
     */
    public function __construct(AdapterInterface $adapter, Config $tableConfig, array $header, array $options = [])
    {
        global $g_dirs;

        $this->header = $header;
        $this->adapter = $adapter;
        $this->tableConfig = $tableConfig;
        $this->pager = new Pagerfanta($adapter);
        $this->template = $g_dirs['smarty'] . 'templates/content/bottom/content/component_table.tpl';
        $this->options = array_replace_recursive($this->options, $options);
        $this->unique = uniqid('mainTable-');

        $this->initializePager($tableConfig->getPaging(), $tableConfig->getRowsPerPage() ?: $this->options['rowsPerPage']);
    }

    /**
     * @return Pagerfanta
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * Returns the unique table ID.
     *
     * @return string
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Method for configurating the Pager.
     *
     * @param integer $currentPage
     * @param integer $entriesPerPage
     *
     * @return $this
     */
    public function initializePager($currentPage = null, $entriesPerPage = null)
    {
        if ($currentPage === null) {
            $currentPage = 1;
        }

        if ($entriesPerPage === null) {
            $entriesPerPage = isys_glob_get_pagelimit();
        }

        // Configurating the Pagerfanta instance.
        $this->pager->setMaxPerPage($entriesPerPage)
            ->setCurrentPage($currentPage);

        return $this;
    }

    /**
     * Render method for the table component.
     *
     * @param bool $return
     *
     * @return string
     */
    public function render($return = false)
    {
        global $g_dirs;

        $rowsPerPageOnLastPage = $pagerNextUrl = null;

        $rowPerPage = \isys_usersettings::get('gui.objectlist.rows-per-page', 50);
        $rows = $this->pager->getNbResults();
        $currentStatus = $this->options[self::CURRENT_STATUS];

        $routeGenerator = function ($page) use ($currentStatus) {
            $url = [];
            parse_str($_SERVER['QUERY_STRING'], $url);
            $url[self::CURRENT_PAGE] = $page;
            $url[self::CURRENT_STATUS] = $currentStatus;

            unset($url[C__GET__AJAX], $url[C__GET__AJAX_CALL]);

            return '?' . http_build_query($url);
        };

        $pagerView = (new IdoitView())->render($this->pager, $routeGenerator, [
            'proximity'          => 3,
            'previous_message'   => '<img src="' . $g_dirs['images'] . 'icons/silk/resultset_previous.png" alt="<" />',
            'next_message'       => '<img src="' . $g_dirs['images'] . 'icons/silk/resultset_next.png" alt=">" />',
            'container_template' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__PAGES') . ' %pages%'
        ]);

        $callbacks = [];
        $rawData = $this->pager->getCurrentPageResults();
        $data = [];

        $properties = [];

        foreach ($this->tableConfig->getProperties() as $property) {
            $key = $property->getPropertyKey();
            $properties[$key] = str_replace(' ', '', ucwords(str_replace('_', ' ', $property->getType())));
        }

        if (is_array($rawData) && count($rawData)) {
            // Check which property has callbacks.
            foreach ($properties as $key => $value) {
                $callbackClass = '\\idoit\\Module\\Cmdb\\Model\\Ci\\Type\\' . $value;

                if (class_exists($callbackClass) && is_a($callbackClass, 'idoit\\Module\\Cmdb\\Model\\Ci\\Category\\DynamicCallbackInterface', true)) {
                    $callbacks[$key] = $callbackClass;
                }

                list($class, $property) = explode('__', str_replace('isys_cmdb_dao_category_', '', $key));

                $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $class)));
                $property = str_replace(' ', '', ucwords(str_replace('_', ' ', (($class === 'GCustomFields') ? substr($property, 1, strpos($property, '_c_')) : $property))));

                $callbackClass = '\\idoit\\Module\\Cmdb\\Model\\Ci\\Category\\' . substr($class, 0, 1) . '\\' . substr($class, 1) . '\\' . $property;

                if (!empty($class) && class_exists($callbackClass) && is_a($callbackClass, 'idoit\\Module\\Cmdb\\Model\\Ci\\Category\\DynamicCallbackInterface', true)) {
                    $callbacks[$key] = $callbackClass;
                }
            }

            foreach ($rawData as &$rawRow) {
                $row = [];
                $field = isset($this->options['idField']) ? $this->options['idField'] : 0;
                if ($this->options['enableCheckboxes']) {
                    $row['__id__'] = $rawRow[$field] ?: null;
                }
                foreach (array_keys($properties) as $key) {
                    $value = isset($rawRow[$key]) ? $rawRow[$key] : null;
                    $value = _LL($value);

                    // Check if a callback is set and call it!
                    if (isset($callbacks[$key])) {
                        $value = call_user_func($callbacks[$key] . '::render', $value);
                    }
                    $row[$key] = $value;
                }
                $row['__id__'] = $rawRow[$field] ?: null;
                if ($this->options['rowClickAttribute']) {
                    $row['__link__'] = $rawRow[$this->options['rowClickAttribute']] ?: null;
                }

                $data[] = $row;
            }
        } else {
            $data = [];
        }

        $rowsPerPage = array_unique([
            50               => '50',
            100              => '100',
            150              => '150',
            200              => '200',
            250              => '250',
            500              => '500',
            (int)$rowPerPage => $rowPerPage
        ]);

        if (!$this->pager->hasNextPage()) {
            $rowsPerPageOnLastPage = count($data);
            $rowsPerPage[$rowsPerPageOnLastPage] = $rowsPerPageOnLastPage;

            foreach ($rowsPerPage as $rowCount => $rowName) {
                if ($rowsPerPageOnLastPage < $rowCount) {
                    unset($rowsPerPage[$rowCount]);
                }
            }
        } else {
            $pagerNextUrl = $routeGenerator($this->pager->getNextPage());

            // ID-3533 Always limit the "$rowsPerPage" to the maximum rows (for example: a list with 55 rows should not display any options above 50 and "all").
            foreach ($rowsPerPage as $rowCount => $rowName) {
                if ($rows < $rowCount) {
                    unset($rowsPerPage[$rowCount]);
                }
            }
        }

        natsort($rowsPerPage);

        if ($rows <= TenantSettings::get('cmdb.limits.table-order-threshhold', 20000)) {
            $rowsPerPage[$rows] = isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__ALL');
        }

        $rules = [
            $this->unique . '-filter'        => [
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strValue'        => $this->options['filterDefaultValue']
            ],
            $this->unique . '-filter-field'  => [
                'disableInputGroup' => true,
                'p_bDbFieldNN'      => true,
                'p_bSort'           => false,
                'p_bInfoIconSpacer' => 0,
                'p_arData'          => $this->options['filterColumns'],
                'p_strSelectedID'   => $this->options['filterDefaultColumn']
            ],
            $this->unique . '-rows-per-page' => [
                'disableInputGroup' => true,
                'p_bDbFieldNN'      => true,
                'p_bSort'           => false,
                'p_bInfoIconSpacer' => 0,
                'p_arData'          => $rowsPerPage,
                'p_strSelectedID'   => (int)$rowsPerPageOnLastPage ?: $this->options['rowsPerPage']
            ]
        ];
        $properties = $this->tableConfig->getProperties();

        if ($this->tableConfig->isBroadsearch()) {
            $this->options['filterDefaultColumn'] = [
                'title' => isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__CMDB__BROADSEARCH_TITLE'),
                'field' => '*'
            ];
            $this->options['autocomplete'] = false;
            array_unshift($properties, new Property('*', '', '', 'LC__MODULE__CMDB__BROADSEARCH_TITLE', false, null, 'text'));
        }

        if (!isset($this->options['pages']) || !$this->options['pages']) {
            $this->options['pages'] = $this->pager->getNbPages();
        }

        // Use integer to better check this value in the template.
        $this->options['fuzzySuggestion'] = (int)$this->options['fuzzySuggestion'];
        $this->options['fuzzySuggestionThreshold'] = (float)$this->options['fuzzySuggestionThreshold'];
        $this->options['fuzzySuggestionDistance'] = (int)$this->options['fuzzySuggestionDistance'];

        $this->options['objectPathCutLength'] = TenantSettings::get('maxlength.location.objects', 16);

        $tpl = \isys_application::instance()->template
            ->assign('unique', $this->unique)
            ->assign('header', $this->header)
            ->assign('data', $data)
            ->assign('pager', $pagerView)
            ->assign('pagerUrl', $routeGenerator('%page%'))
            ->assign('pagerCurrentUrl', $routeGenerator($this->pager->getCurrentPage()))
            ->assign('pagerNextUrl', $pagerNextUrl)
            ->assign('rows', $rows)
            ->assign('options', $this->options)
            ->assign('properties', $properties)
            ->smarty_tom_add_rules('tom.content.table', $rules);

        if (!$return) {
            $tpl->display($this->template);
        }

        return $tpl->fetch($this->template);
    }
}
