<?php

use HTMLPurifier;
use HTMLPurifier_Config;
use DOMDocument;

/**
 * i-doit
 *
 * Smarty plugin for WYSIWYG input fields.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_wysiwyg extends isys_smarty_plugin_f_textarea implements isys_smarty_plugin
{
    /**
     * Array which contains configuration items which will be removed/replaced from the toolbar configuration
     *
     * @var array
     */
    protected static $m_replace_toolbar_configuration_items = [
        'links'       => null,
        'basicstyles' => [
            'Bold',
            'Italic',
            'Underline',
            'Subscript',
            'Superscript',
            '-',
            'RemoveFormat'
        ],
        'styles'      => null,
        'colors'      => null,
        'paragraph'   => [
            'NumberedList',
            'BulletedList'
        ]
    ];

    protected static $m_toolbar_configuration = [
        'full'  => [
            [
                'name'  => 'clipboard',
                'items' => [
                    'Cut',
                    'Copy',
                    'Paste',
                    'PasteText',
                    'PasteFromWord',
                    '-',
                    'Undo',
                    'Redo'
                ]
            ],
            [
                'name'  => 'editing',
                'items' => [
                    'Find',
                    'Replace',
                    '-',
                    'SelectAll'
                ]
            ],
            [
                'name'  => 'links',
                'items' => [
                    'Link',
                    'Unlink',
                    'Anchor'
                ]
            ],
            [
                'name'  => 'insert',
                'items' => [
                    'Image',
                    'Table',
                    'HorizontalRule'
                ]
            ],
            [
                'name'  => 'tools',
                'items' => [
                    'Maximize',
                    'ShowBlocks'
                ]
            ],
            [
                'name'  => 'document',
                'items' => [
                    'Source',
                    '-',
                    'Print'
                ]
            ],
            '/',
            [
                'name'  => 'basicstyles',
                'items' => [
                    'Bold',
                    'Italic',
                    'Underline',
                    'Strike',
                    'Subscript',
                    'Superscript',
                    '-',
                    'RemoveFormat'
                ]
            ],
            [
                'name'  => 'paragraph',
                'items' => [
                    'NumberedList',
                    'BulletedList',
                    '-',
                    'Outdent',
                    'Indent',
                    '-',
                    'Blockquote',
                    '-',
                    'JustifyLeft',
                    'JustifyCenter',
                    'JustifyRight',
                    'JustifyBlock'
                ]
            ],
            [
                'name'  => 'styles',
                'items' => [
                    'Styles',
                    'Format',
                    'Font',
                    'FontSize'
                ]
            ],
            [
                'name'  => 'colors',
                'items' => [
                    'TextColor',
                    'BGColor'
                ]
            ]
        ],
        'basic' => [
            [
                'name'  => 'basicstyles',
                'items' => [
                    'Bold',
                    'Italic',
                    'Underline',
                    'Strike',
                    '-',
                    'RemoveFormat'
                ]
            ],
            [
                'name'  => 'script',
                'items' => [
                    'Subscript',
                    'Superscript'
                ]
            ],
            [
                'name'  => 'paragraph',
                'items' => [
                    'NumberedList',
                    'BulletedList'
                ]
            ],
            [
                'name'  => 'UndoRedo',
                'items' => [
                    'Undo',
                    'Redo'
                ]
            ],
            [
                'name'  => 'tools',
                'items' => ['Maximize']
            ],
        ]
    ];

    /**
     * Whitelist of all allowed tags. "P", "BR" and "DIV" are allowed, because not all browsers use the same line delimiter.
     * Also IE Browsers work with "STRONG", "EM" and a mix of "P" + "DIV" for new lines... Weird.
     *
     * @var  array
     */
    protected static $m_whitelist_tags = [
        // Text formatting.
        'b',
        'i',
        'u',
        'strike',
        'sub',
        'sup',
        'strong',
        'em',
        // Lists.
        'ol',
        'ul',
        'li',
        // Special formatting.
        'blockquote',
        // Breaks and lines.
        'hr',
        'br',
        // Container.
        'div',
        'p',
        'span',
        // Tables.
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
        // Links
        'a'
    ];

    /**
     * @var HTMLPurifier
     */
    protected $htmlPurifier;

    /**
     * Array of plugins to explicitly remove/deactivate.
     *
     * @var array
     */
    protected $m_remove_plugins = ['div', 'flash', 'smiley', 'specialchar', 'forms', 'pagebreak', 'iframe', 'about'];

    /**
     * isys_smarty_plugin_f_wysiwyg constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->htmlPurifier = isys_application::instance()->container->get('htmlpurifier');
    }

    /**
     * This method will return the toolbar configurations.
     *
     * @param   string $p_config
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_toolbar_configuration($p_config = null)
    {
        if ($p_config !== null && isset(self::$m_toolbar_configuration[$p_config])) {
            return self::$m_toolbar_configuration[$p_config];
        }

        return self::$m_toolbar_configuration;
    }

    /**
     * This method returns the (default) whitelisted tags.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tags_whitelist()
    {
        return self::$m_whitelist_tags;
    }

    /**
     * This method adds additional tags to the whitelist
     *
     * @param $p_array
     */
    public static function add_tags_to_whitelist($p_array)
    {
        self::$m_whitelist_tags = array_merge(self::$m_whitelist_tags, $p_array);
    }

    /**
     * Edit mode - Parameters are given in an array $p_params:
     *     Basic parameters
     *         id                        -> ID
     *         name                      -> Name
     *         type                      -> Smarty plug in type
     *         p_strValue                -> Value
     *
     *     Style parameters
     *         p_strStyle                -> Style
     *         p_bEditMode               -> If set to 1 the plug in is always shown in edit style
     *         p_bDisabled               -> Disabled
     *         p_bReadonly               -> Readonly
     *
     *     Special parameters
     *         p_nRows                   -> Textarea rows
     *         p_nCols                   -> Textarea cols
     *         p_extraplugins            -> Extra-Plugins as comma-seperated list
     *         p_toolbarconfig           -> This parameter must be a valid array key of $m_toolbar_configuration. Otherwise all configured toolbars from ckeditor/config.js will be loaded.
     *         p_overwrite_toolbarconfig -> This parameter can be used to overwrite the default toolbar config (via "p_toolbarconfig").
     *         p_onblur                  -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_onready                 -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_onchange                -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_bClickDelegator         -> This parameter activates onClick attributes.
     *         p_bStrip                  -> This parameter indicates, whether p_value should be stripped or not.
     *
     * @global  array                   $g_dirs
     * @global  array                   $g_config
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_config;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if (isset($p_params['p_bEditMode']) && !$p_params['p_bEditMode']) {
            return $this->navigation_view($p_tplclass, $p_params);
        }

        if (!$p_params['forceUsage']) {
            if (isset($g_config['wysiwyg']) && $g_config['wysiwyg'] == false) {
                return parent::navigation_edit($p_tplclass, $p_params);
            }
        }

        $this->m_strPluginClass = 'f_text';
        $this->m_strPluginName = $p_params['name'];

        if ($p_params['p_bDisabled'] || $p_params['p_bReadonly']) {
            $this->navigation_view($p_tplclass, $p_params);
        }

        // Enable entities by default (&quot; instead of ").
        if (!isset($p_params['entities'])) {
            $p_params['entities'] = true;
        }

        // Strip all not-allowed tags (via strip_tags) and then remove all their attributes.
        if ((!isset($p_params['p_bStrip']) && isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) || $p_params['p_bStrip'] == true) {
            if (!is_scalar($p_params['p_strValue'])) {
                // Force "to string" logic.
                $p_params['p_strValue'] .= '';
            }

            // clean-up html code from description
            $p_params['p_strValue'] = $this->htmlPurifier->purify($p_params['p_strValue']);
        }

        if (isset($p_params['id']) && !empty($p_params['id'])) {
            $l_id = $p_params['id'];
        } else {
            $l_id = $p_params['name'];
        }

        if (isset($p_params['p_nRows']) && $p_params['p_nRows'] > 0) {
            $l_rows = $p_params['p_nRows'];
        } else {
            $l_rows = 5;
        }

        if (isset($p_params['p_nCols']) && $p_params['p_nCols'] > 0) {
            $l_cols = $p_params['p_nCols'];
        } else {
            $l_cols = 70;
        }

        // Define the 'basic' toolbar as standard.
        $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration['basic']);

        if (isset($p_params['p_toolbarconfig']) && !empty($p_params['p_toolbarconfig'])) {
            // If a 'toolbar parameter' has been set, we'll try to process it here.
            if (is_string($p_params['p_toolbarconfig']) && isset(self::$m_toolbar_configuration[$p_params['p_toolbarconfig']])) {
                // If the parameter contains a toolbar name and it exists, we apply it.
                $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration[$p_params['p_toolbarconfig']]);
            } elseif (is_array($p_params['p_toolbarconfig'])) {
                // If the parameter is an array, we assume it's a valid configuration - so we convert it to JSON.
                $l_toolbarconfiguration = isys_format_json::encode($p_params['p_toolbarconfig']);
            } elseif (isys_format_json::is_json_array($p_params['p_toolbarconfig'])) {
                // If the parameter is a JSON array, we assume it's a valid configuration.
                $l_toolbarconfiguration = $p_params['p_toolbarconfig'];
            }
        } elseif (isys_tenantsettings::get('gui.wysiwyg-all-controls', false)) {
            // If no 'toolbar parameter' has been set and the setting forces us to use the 'full' toolbar.
            $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration['full']);
        }

        $l_image_upload = $l_image_browser = '';

        if (isset($p_params['p_image_upload_handler']) && !empty($p_params['p_image_upload_handler'])) {
            $l_image_upload = 'filebrowserUploadUrl:"' . isys_helper_link::create_url([
                    'call'           => 'file',
                    'func'           => 'upload_by_ckeditor',
                    'ajax'           => 1,
                    'upload_handler' => $p_params['p_image_upload_handler']
                ]) . '",';
        }

        if (isset($p_params['p_image_browser_handler']) && !empty($p_params['p_image_browser_handler'])) {
            $l_image_browser = 'filebrowserBrowseUrl:"' . isys_helper_link::create_url([
                    'call'           => 'file',
                    'func'           => 'browse_by_ckeditor',
                    'ajax'           => 1,
                    'upload_handler' => $p_params['p_image_browser_handler']
                ]) . '",' . 'filebrowserWindowWidth: "730",' . 'filebrowserWindowHeight: "480",';
        }

        if (!isset($p_params['p_strWidth'])) {
            $l_width = 'width:552px;';
        } else {
            $l_width = 'width:' . $p_params['p_strWidth'] . ';';
        }

        $l_style = $l_width . $p_params['p_strStyle'];
        $p_params['p_strInfoIconClass'] = 'fl';

        if (!isset($p_params['p_strHeight'])) {
            $l_height = '200px';
        } else {
            $l_height = $p_params['p_strHeight'];
        }

        // Add the given CSS classes behind our base "commentary" class.
        $p_params['p_strClass'] = 'commentary ' . $p_params['p_strClass'];

        /**
         * Handling mandatory and validation rules
         * for description fields
         *
         * @see ID-6046
         */
        $extraAttributes = '';

        if (isset($p_params['p_validation_mandatory']) && $p_params['p_validation_mandatory']) {
            $extraAttributes .= 'data-mandatory-rule="1" ';
        }

        if (isset($p_params['p_validation_rule']) && $p_params['p_validation_rule']) {
            $extraAttributes .= 'data-validation-rule="1" ';
        }

        return $this->getInfoIcon($p_params) . '<div class="' . $p_params['p_strClass'] . '">
			<textarea class="input" style="' . $l_style . '" ' . 'data-identifier="' . $p_params['p_dataIdentifier'] . '" id="' . $l_id . '" name="' . $p_params['name'] .
            '" rows="' . $l_rows . '" cols="' . $l_cols . '" ' . $extraAttributes . '>' . // This is necessary to prevent strings like "<mytag>" to be turned into HTML.
            str_replace('&', '&amp;', $p_params['p_strValue']) . '</textarea></div>
			<script type="text/javascript">
                \'use strict\';

                window.renderCKEditor = function(options) {
                    if (window.hasOwnProperty("CKEDITOR")) {
                        window.CKEDITOR.replace( "' . $l_id . '", {
                                extraPlugins: options.plugins,
                                language: options.language,
                                allowedContent: true,
                                toolbar: options.toolbar,
                                removeButtons: "",
                                height: options.height,
                                removePlugins: options.removePlugins,
                                font_names: "' . isys_tenantsettings::get('ckeditor.font_names', 'Arial;Courier New;Times New Roman;Helvetica') . '",
                                readOnly: ' . (isset($p_params['p_bReadonly']) && $p_params['p_bReadonly'] ? 'true' : 'false') . ',
                                entities: ' . ($p_params['entities'] ? 'true' : 'false') . ',
                                stylesSet: "i-doit:' . isys_application::instance()->www_path . 'src/tools/js/ckeditor/i-doit_style.js",
                                format_tags: "p;h1;h2;h3;h4;h5;h6;pre",
                                ' . $l_image_upload . '
                                ' . $l_image_browser . '
                                on: {
                                    instanceReady: function (evt) {
                                        /* Custom callback */
                                        ' . $p_params['p_onready'] . '
                                    },
                                    ' . ($p_params['p_bClickDelegator'] ? 'contentDom: function(evt) {
                            var editable = this.editable();

                            editable.attachListener(editable, "click", function(evt) {
                                if (evt.data.$.hasOwnProperty("srcElement") && evt.data.$.srcElement.getAttribute("data-cke-pa-onclick")) {
                                    evt.data.$.srcElement.setAttribute("onclick", evt.data.$.srcElement.getAttribute("data-cke-pa-onclick"));
                                    evt.data.$.srcElement.onclick.apply(evt.data.$.srcElement);
                                }
                            });

                            editable.attachListener(editable, "dblclick", function(evt) {
                                if (evt.data.$.hasOwnProperty("srcElement") && evt.data.$.srcElement.getAttribute("data-cke-pa-ondblclick")) {
                                    evt.data.$.srcElement.setAttribute("ondblclick", evt.data.$.srcElement.getAttribute("data-cke-pa-ondblclick"));
                                    evt.data.$.srcElement.ondblclick.apply(evt.data.$.srcElement);
                                }
                            });
                        },' : '') . '
                                    blur: function(evt) {
                                        // Blur action.
                                        ' . $p_params['p_onblur'] . '

                                        // Trigger the textarea\'s "onChange" event.' . ($l_id ? 'if ($("' . $l_id . '")) $("' . $l_id . '").simulate("change");' : '') . '
                                    },
                                    change: function(evt) {
                                        // @todo Unlike "onChange" this event gets called by every new character input. Check, if this is necessary

                                        /* Sync content with textarea */
                                        this.updateElement();

                                        // Change action.
                                        ' . $p_params['p_onchange'] . '
                                    }
                                }
                        });
                    }
                };

                try {
                    // ID-3042 Adding "dropoff" plugin to prohibit the user of adding images via drag and drop.
                    var options = {
                        height: "' . $l_height . '",
                        plugins: "' . $p_params['p_extraplugins'] . ',widget,dropoff,tableresize",
                        language: "' . isys_application::instance()->language . '",
                        toolbar: ' . $l_toolbarconfiguration . ',
                        removePlugins: "' . implode(',', $this->m_remove_plugins) . '"
                    };

                    if (window.hasOwnProperty("CKEDITOR")) {
                        window.renderCKEditor(options);
                    }
                    else {
                        document.observe(\'dom:loaded\', function() {
                            window.renderCKEditor(options);
                        });
                    }

                } catch (e) {
                    if (console) console.error(e);
                }
			</script>';
    }

    /**
     * View mode.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_config;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if (isset($g_config['wysiwyg']) && $g_config['wysiwyg'] == false) {
            return parent::navigation_view($p_tplclass, $p_params);
        }

        if (isset($p_params['p_bEditMode']) && $p_params['p_bEditMode']) {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
            if (!is_scalar($p_params['p_strValue'])) {
                // Force "to string" logic.
                $p_params['p_strValue'] .= '';
            }

            // clean-up html code from description
            $p_params['p_strValue'] = $this->htmlPurifier->purify($p_params['p_strValue']);

            // After stripping all "evil" we can replace the links and email addresses.
            $p_params['p_strValue'] = isys_helper_textformat::generateHyperlinksAndMailtosInString($p_params['p_strValue']);
        }

        // ID-3376: adding highlighting to commentaries
        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]) && (bool)isys_tenantsettings::get('search.highlight-search-string', 1)) {
            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $p_params['p_strValue']);
            foreach ($doc->childNodes as $key => $item) {
                $this->domTextReplace($_GET[C__SEARCH__GET__HIGHLIGHT], $item);
            }
            $p_params['p_strValue'] = $doc->saveHTML();
        }

        return '<div class="commentary wysiwyg">' . $p_params['p_strValue'] . '</div>';
    }

    /**
     * method for replacing in all textNodes of a document only nodeValues, inserting <span class=highlight> into them surrounding search string
     * @param         $search
     * @param DOMNode $domNode
     */
    private function domTextReplace($search, DOMNode &$domNode)
    {
        if ($domNode->hasChildNodes()) {
            $children = [];
            // since looping through a DOM being modified is a bad idea we prepare an array:
            foreach ($domNode->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $oldText = $child->wholeText;
                    $newText = isys_string::highlight($search, $oldText);
                    if ($oldText !== $newText) {
                        $fragment = $domNode->ownerDocument->createDocumentFragment();
                        $fragment->appendXML($newText);
                        $domNode->replaceChild($fragment, $child);
                    }
                } else {
                    $this->domTextReplace($search, $child);
                }
            }
        }
    }

    /**
     * Replaces toolbar configuration items which are not working
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_replaced_toolbar_configuration($p_config = null)
    {
        $l_toolbar_config = self::get_toolbar_configuration($p_config);
        $l_return = [];
        foreach ($l_toolbar_config as $l_key => $l_value) {
            if (!is_array($l_value)) {
                continue;
            }

            if (array_key_exists($l_value['name'], self::$m_replace_toolbar_configuration_items)) {
                if (self::$m_replace_toolbar_configuration_items[$l_value['name']] === null) {
                    continue;
                } else {
                    $l_value['items'] = self::$m_replace_toolbar_configuration_items[$l_value['name']];
                }
            }
            $l_return[] = $l_value;
        }

        return $l_return;
    }
}
