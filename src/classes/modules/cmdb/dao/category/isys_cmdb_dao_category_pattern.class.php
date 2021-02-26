<?php

/**
 * i-doit category pattern
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @method static array text()
 * @method static array textarea()
 * @method static array double()
 * @method static array float()
 * @method static array int()
 * @method static array dialog()
 * @method static array dialog_plus()
 * @method static array dialog_list()
 * @method static array date()
 * @method static array datetime()
 * @method static array object_browser()
 * @method static array multiselect()
 * @method static array money()
 * @method static array autotext()
 * @method static array upload()
 * @method static array virtual()
 * @method static array password()
 * @method static array timeperiod()
 * @method static array commentary()
 */
abstract class isys_cmdb_dao_category_pattern extends isys_cmdb_dao_category
{
    /**
     * The whole new patterns for each type (text, textarea, dialog, ...).
     *
     * @var   array
     * @todo  Type "checkbox"
     */
    protected static $m_patterns = [
        'text'           => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__TEXT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__DEFAULT => null,
                C__PROPERTY__UI__PARAMS  => [
                    'p_nMaxLen' => 255
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'sanitize_text'
                        ]
                    ]
                ]
            ]
        ],
        'time'           => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__TIME,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TIME,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TIME,
                C__PROPERTY__UI__DEFAULT => null,
                C__PROPERTY__UI__PARAMS  => [
                    'p_nMaxLen' => 5
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'sanitize_text'
                        ]
                    ]
                ]
            ]
        ],
        'textarea'       => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__TEXTAREA,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT_AREA,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXTAREA,
                C__PROPERTY__UI__DEFAULT => null,
                C__PROPERTY__UI__PARAMS  => [
                    'p_nMaxLen' => 65534
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'sanitize_text'
                        ]
                    ]
                ]
            ]
        ],
        'double'         => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DOUBLE,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__DOUBLE,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPlaceholder' => '0.00',
                    'default'          => '0.00'
                ],
                C__PROPERTY__UI__DEFAULT => null,
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__VALIDATION   => [
                    FILTER_VALIDATE_FLOAT,
                    []
                ],
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_number'
                        ]
                    ]
                ]
            ]
        ],
        'float'          => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__FLOAT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__FLOAT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__DEFAULT => null,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPlaceholder' => '0.00'
                ],
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__VALIDATION   => [
                    FILTER_VALIDATE_FLOAT,
                    []
                ],
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_number'
                        ]
                    ]
                ]
            ]
        ],
        'int'            => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__INT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPlaceholder' => '0',
                    'default'          => '0'
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__VALIDATION   => [
                    FILTER_VALIDATE_INT,
                    []
                ],
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_SANITIZE_NUMBER_INT,
                    []
                ]
            ]
        ],
        'dialog'         => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DIALOG,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__DIALOG,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strTable' => ''
                ],
                C__PROPERTY__UI__DEFAULT => '-1'
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'dialog'
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'dialog_plus'    => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DIALOG_PLUS,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__POPUP,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPopupType' => 'dialog_plus',
                    'p_strTable'     => ''
                ],
                C__PROPERTY__UI__DEFAULT => '-1'
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'dialog_plus'
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'dialog_list'    => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DIALOG_LIST,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                C__PROPERTY__UI__DEFAULT => ''
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'dialog_multiselect'
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'date'           => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DATE,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__DATE,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__DATE,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPopupType' => 'calendar',
                    'p_bTime'        => 0
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'date'
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY  => false,
                C__PROPERTY__CHECK__VALIDATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_date'
                        ]
                    ]
                ]
            ]
        ],
        'datetime'       => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__DATETIME,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__DATE_TIME,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__DATETIME,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPopupType' => 'calendar',
                    'p_bTime'        => 1
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'datetime'
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY  => false,
                C__PROPERTY__CHECK__VALIDATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_date'
                        ]
                    ]
                ]
            ]
        ],
        'object_browser' => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__OBJECT_BROWSER,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__POPUP,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPopupType' => 'browser_object_ng'
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'object'
                ]
            ]
        ],
        'multiselect'    => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__MULTISELECT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__POPUP,
                C__PROPERTY__UI__PARAMS  => [
                    'type'           => 'f_popup',
                    'p_strPopupType' => 'dialog_plus',
                    'multiselect'    => true
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'dialog_multiselect'
                ]
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY  => false,
                C__PROPERTY__CHECK__VALIDATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_list_of_ids'
                        ]
                    ]
                ]
            ]
        ],
        'money'          => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__MONEY,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__DOUBLE,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__PARAMS  => [
                    'p_strPlaceholder' => '0.00',
                    'default'          => '0.00'
                ],
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_export_helper',
                    'money_format'
                ]
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY    => false,
                C__PROPERTY__CHECK__VALIDATION   => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_number'
                        ]
                    ]
                ],
                C__PROPERTY__CHECK__SANITIZATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_number'
                        ]
                    ]
                ]
            ]
        ],
        'autotext'       => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__AUTOTEXT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__INT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__AUTOTEXT,
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'upload'         => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__UPLOAD,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__UPLOAD,
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__REPORT       => false,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'virtual'        => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__TEXT,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__DEFAULT => null,
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => false,
                C__PROPERTY__PROVIDES__EXPORT       => false,
                C__PROPERTY__PROVIDES__REPORT       => false,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => true,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'password'        => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__PASSWORD,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false,
                C__PROPERTY__DATA__ENCRYPT => true
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__DEFAULT => null,
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => false,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__FORMAT   => [
                C__PROPERTY__FORMAT__CALLBACK => [
                    'isys_global_password_export_helper',
                    'password'
                ]
            ]
        ],
        'timeperiod'           => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__TIMEPERIOD,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXT,
                C__PROPERTY__UI__DEFAULT => 'hh:mm'
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__REPORT       => false,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ]
        ],
        'commentary'     => [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__PRIMARY  => false,
                C__PROPERTY__INFO__TYPE     => C__PROPERTY__INFO__TYPE__COMMENTARY,
                C__PROPERTY__INFO__BACKWARD => false
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__TYPE     => C__TYPE__TEXT_AREA,
                C__PROPERTY__DATA__READONLY => false,
                C__PROPERTY__DATA__INDEX    => false
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__TYPE    => C__PROPERTY__UI__TYPE__TEXTAREA,
                C__PROPERTY__UI__DEFAULT => null
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ],
            C__PROPERTY__CHECK    => [
                C__PROPERTY__CHECK__MANDATORY  => false,
                C__PROPERTY__CHECK__VALIDATION => [
                    FILTER_CALLBACK,
                    [
                        'options' => [
                            'isys_helper',
                            'filter_textarea'
                        ]
                    ]
                ]
            ]
        ],
    ];

    /**
     * Magic static call method.
     *
     * @static
     *
     * @param   string $p_method
     * @param   mixed  $p_arguments
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public static function __callStatic($p_method, $p_arguments)
    {
        if (isset(self::$m_patterns[$p_method])) {
            return self::$m_patterns[$p_method];
        }

        return [];
    }

    /**
     * Magic call method.
     *
     * @param   string $p_method
     * @param   mixed  $p_arguments
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function __call($p_method, $p_arguments)
    {
        if (isset(self::$m_patterns[$p_method])) {
            return self::$m_patterns[$p_method];
        }

        return [];
    }
}
