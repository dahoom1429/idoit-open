<?php

/**
 * i-doit
 *
 * Notification
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Abstract notification class
 */
abstract class isys_notification
{

    /**
     * Send emails.
     */
    const C__CHANNEL__EMAIL = 1;

    /**
     * Use all channels.
     */
    const C__CHANNEL__ALL = 1;

    /**
     * Sends messages through these channels.
     *
     * @var int Bitwise combination
     */
    protected $m_channels;

    /**
     * DAO
     *
     * @var isys_notifications_dao
     */
    protected $m_dao;

    /**
     * Database
     *
     * @var isys_component_database
     */
    protected $m_db;

    /**
     * Logger
     *
     * @var isys_log
     */
    protected $m_log;

    /**
     * Message queue
     *
     * @var array Contains 'subject' (string), 'body' (string), 'locale', and
     * 'contacts' ('name', 'email').
     */
    protected $m_messages = [];

    /**
     * Current notification
     *
     * @var array Associative array
     */
    protected $m_notification;

    /**
     * List of notifications
     *
     * @var array Array of associative arrays
     */
    protected $m_notifications;

    /**
     * Notification templates
     *
     * @var array Associative array
     */
    protected $m_templates;

    /**
     * Information about this notification type
     *
     * @var array
     */
    protected $m_type;

    /**
     * Conditions for this notification type
     *
     * @var array
     */
    protected $m_type_condition = [];

    /**
     * If the messages for the notification should be send
     *
     * @var bool
     */
    protected $skipSending = false;

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    abstract protected function handle_notification($p_notification);

    /**
     * Entity-Decoder
     *
     * @param string $p_lang
     * @param bool   $p_entity_decode
     *
     * @return string
     */
    private static function _l($p_lang, $p_entity_decode = true)
    {
        if ($p_entity_decode) {
            return html_entity_decode(isys_application::instance()->container->get('language')
                ->get($p_lang), ENT_COMPAT, "ISO8859-15");
        } else {
            return isys_application::instance()->container->get('language')
                ->get($p_lang);
        }
    }

    /**
     * Initiates notification.
     *
     * @param array $p_type Information about this notification type
     */
    public function init($p_type)
    {
        assert(is_array($p_type));
        $this->m_type = $p_type;
    }

    /**
     * Notifies all contacts if necessary.
     *
     * A positive threshold means that a notification will be produced if
     * greater equal X objects are counted.
     * A negative threshold means that a notification will be produced if less
     * equal X objects are counted.
     */
    public function notify()
    {
        // Fetch all relevant and activated notifications:

        $this->m_log->debug('Fetching all activated notifications for this type...');

        $l_conditions = [
            'type' => $this->m_type['id']
        ];

        $this->m_notifications = $this->m_dao->get_notifications(null, $l_conditions);

        if (!is_countable($this->m_notifications) || count($this->m_notifications) === 0) {
            $this->m_log->debug('There are no notifications for this type.');

            return;
        }

        // Fetch templates:

        $this->m_log->debug('Fetching notification templates...');

        $l_conditions = [
            'notification_type' => $this->m_type['id']
        ];

        $this->m_templates = $this->m_dao->get_templates(null, $l_conditions);

        if (!is_countable($this->m_templates) || count($this->m_templates) === 0) {
            $this->m_log->warning('There are no notification templates');

            return;
        }

        // Handle notifications:

        $this->m_log->info('Handling notifications...');

        foreach ($this->m_notifications as $l_notification) {
            if ($l_notification['status'] == isys_notifications_dao::C__STATUS__DEACTIVATED) {
                continue;
            }

            $this->skipSending = false;

            if ($l_notification['limit'] >= 1 && $l_notification['count'] >= $l_notification['limit']) {
                $this->skipSending = true;
                $this->m_log->info('Notification limit of "' . $l_notification['limit'] . '" reached. Skipping notification "' . self::_l($l_notification['title']) . '".');
            }

            $this->m_log->info(sprintf('Handling notification "%s" [%s]...', self::_l($l_notification['title']), $l_notification['id']));

            $this->m_notification = $l_notification;

            /**
             * Replace it by execute() without parameters.
             */
            $this->handle_notification($l_notification);
        }

        // Send messages:

        $this->m_log->debug('Delivering messages...');

        if (!is_countable($this->m_messages) || count($this->m_messages) === 0) {
            $this->m_log->debug('There are no messages to deliver.');
        } else {
            foreach ($this->m_messages as $l_message) {
                // Check for existing contacts before trying to send e-mails
                if (!empty($l_message['contacts'])) {
                    $this->send_message($l_message['contacts'], $l_message['subject'], $l_message['body']);
                }
            }
        }

        $this->m_log->debug('Finished notifying for this type.');
    }

    /**
     * Gets channels.
     *
     * @return int Bitwise combination
     */
    public function get_channels()
    {
        return $this->m_channels;
    }

    /**
     * Sets channels.
     *
     * @param int $p_channels Bitwise combination
     *
     * @throws isys_exception_general
     */
    public function set_channels($p_channels)
    {
        assert(is_int($p_channels));
        if ($p_channels & self::C__CHANNEL__ALL) {
            $this->m_channels = $p_channels;
        } else {
            throw new isys_exception_general('Unkown channel.');
        }
    }

    /**
     * Writes messages and adds them to the message stack.
     *
     * @param array $p_notification Information about notification
     * @param array $p_objects      (optional) Affected objects. Defaults to null
     *
     * @return int Amount of written messages
     */
    protected function write_messages($p_notification, $p_objects = null)
    {
        global $g_idoit_language_short;
        $languageManager = isys_application::instance()->container->get('language');

        assert(is_array($p_notification));

        if ($this->skipSending) {
            $this->m_log->debug('Skipping messages');

            return 0;
        }

        $this->m_log->debug('Writing messages...');

        // Fetch receivers:

        $l_receivers = $this->m_dao->get_receivers($p_notification['id']);

        if ((!is_countable($l_receivers['contacts']) || count($l_receivers['contacts']) === 0) &&
            (!is_countable($l_receivers['contacts_by_roles']) || count($l_receivers['contacts_by_roles']) === 0)) {
            $this->m_log->debug('There are no receivers. Skipping.');

            $this->reset_counter($p_notification);

            return 0;
        }

        // Needed locales:

        $l_locales = [];

        foreach ($l_receivers as $l_receiver_type => $l_receivers_arr) {
            if (is_countable($l_receivers_arr) && count($l_receivers_arr) > 0) {
                foreach ($l_receivers_arr as $l_receiver) {
                    $l_locales[] = $l_receiver['locale'];
                }
            }
        }

        $l_locales = $l_template_locales = array_unique($l_locales);
        $l_templates = [];

        // Fetch needed templates:
        // Try to retrieve explicitly selected templates
        $tmpLocales = $l_template_locales;

        foreach ($tmpLocales as $languageAbbreviation) {
            // Check for selected template for language
            if (!empty($p_notification['template_' . $languageAbbreviation])) {
                // Get template data
                $languageTemplate = reset($this->m_dao->get_templates(null, ['id' => $p_notification['template_' . $languageAbbreviation]]));

                // Validate template data
                if (is_array($languageTemplate)) {
                    // Add it to templates
                    $l_templates[] = $languageTemplate;

                    // Unset locale to prevent default handling which will send all available templates for given language
                    unset($l_template_locales[array_search($languageAbbreviation, $l_template_locales)]);
                }
            }
        }

        // Check whether notification has not specified a template for a given language
        if (is_countable($l_template_locales) && count($l_template_locales)) {
            // Get all templates by type
            $l_templates_by_type = $this->m_dao->get_templates(null, ['notification_type' => $p_notification['type']]);

            // Iterate and add them in case language has not a specified template
            foreach ($l_templates_by_type as $l_template) {
                if (in_array($l_template['locale'], $l_template_locales)) {
                    $l_templates[] = $l_template;
                }
            }
        }

        if (count($l_templates) === 0 || count($l_locales) > count($l_templates)) {
            $this->m_log->notice(sprintf('There are templates missing. For these locales templates are needed: %s.', implode(', ', $l_locales)));

            $this->reset_counter($p_notification);

            return 0;
        }

        // Prepare messages:

        // Handle locales:
        $l_original_locale = $g_idoit_language_short;
        $l_current_locale = $l_original_locale;

        $l_all_placeholders = $this->m_dao->get_placeholders();

        $l_none = '---';

        foreach ($l_templates as $l_template) {
            // Set locale:
            $l_new_locale = substr($l_template['locale'], 0, 2);

            if ($l_current_locale !== $l_new_locale) {
                $languageManager->load($l_new_locale);
                $l_current_locale = $l_new_locale;
            }

            if ((is_countable($l_receivers['contacts']) && count($l_receivers['contacts']) > 0) ||
                (is_countable($l_receivers['contacts_by_roles']) && count($l_receivers['contacts_by_roles']) > 0)) {
                foreach ($l_receivers as $l_type => $l_receiver_arr) {
                    if (!is_countable($l_receiver_arr) || count($l_receiver_arr) === 0) {
                        continue;
                    }

                    foreach ($l_receiver_arr as $l_receiver) {
                        $l_assigned_objects = null;

                        if (isset($l_receiver['assigned_objects'])) {
                            $l_assigned_objects = $l_receiver['assigned_objects'];
                        }

                        // Add locale:
                        $l_message = [
                            'locale' => $l_template['locale']
                        ];
                        // Add receivers:
                        if ($l_receiver['locale'] === $l_message['locale']) {
                            $l_message['contacts'][] = [
                                'name'  => $l_receiver['title'],
                                'email' => $l_receiver['email']
                            ];
                        }
                        // Replace placeholders:
                        $l_placeholders = [];
                        $l_replacements = [];
                        foreach ($l_all_placeholders as $l_placeholder) {
                            // Skip unnecessary placeholders:
                            if (strpos($l_template['text'], $l_placeholder['value']) === false && strpos($l_template['subject'], $l_placeholder['value']) === false) {
                                continue;
                            }
                            $l_property_type = null;
                            $l_property = null;
                            [$l_property_type, $l_property] = explode('__', substr($l_placeholder['value'], 1, -1));
                            $l_replacement = null;
                            switch ($l_property_type) {
                                case 'notifications':
                                    switch ($l_property) {
                                        case 'id':
                                        case 'threshold':
                                        case 'limit':
                                        case 'count':
                                        case 'last_run':
                                            if (isset($p_notification[$l_property])) {
                                                $l_replacement = $p_notification[$l_property];
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'title':
                                        case 'description':
                                            $l_replacement = self::_l($p_notification[$l_property]);
                                            break;
                                        case 'status':
                                            $l_status = $this->m_dao->get_status();
                                            $l_replacement = $l_status[$p_notification[$l_property]];
                                            break;
                                        case 'threshold_unit':
                                            $l_unit = $this->m_dao->get_unit($this->m_type['unit']);
                                            $l_replacement = $l_none;

                                            if (!is_array($l_unit) || !isset($p_notification[$l_property])) {
                                                break;
                                            }

                                            if (isset($l_unit['table'])) {
                                                $unitDataAsArray = $this->m_dao->get_unit_parameters($l_unit['table']);
                                                foreach ($unitDataAsArray as $unitData) {
                                                    if ($unitData[$l_unit['table'] . '__id'] == $p_notification[$l_property]) {
                                                        $l_replacement = self::_l($unitData[$l_unit['table'] . '__title']);
                                                        break 2;
                                                    }
                                                }
                                            } else {
                                                foreach ($l_unit as $l_subunit) {
                                                    if (!isset($l_subunit['table'])) {
                                                        continue;
                                                    }

                                                    $l_unit_parameters = $this->m_dao->get_unit_parameters($l_subunit['table']);
                                                    foreach ($l_unit_parameters as $l_parameter) {
                                                        if ($l_parameter[$l_subunit['table'] . '__id'] == $p_notification[$l_property]) {
                                                            $l_replacement = self::_l($l_parameter[$l_subunit['table'] . '__title']);
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                            break;
                                        case 'type':
                                            $l_replacement = self::_l($this->m_type['title']);
                                            break;
                                        case 'contacts':
                                            $l_contacts = [];
                                            if (isset($p_notification[$l_property])) {
                                                $l_contact_data = $this->m_dao->get_contacts($p_notification[$l_property]);
                                                foreach ($l_contact_data as $l_contact) {
                                                    $l_contacts[] = $l_contact['isys_obj__title'];
                                                }
                                            }
                                            $l_replacement = $this->build_plain_unsorted_list($l_contacts);
                                            break;
                                    }
                                    break;
                                case 'notification_types':
                                    switch ($l_property) {
                                        case 'id':
                                        case 'callback':
                                            $l_replacement = $this->m_type[$l_property];
                                            break;
                                        case 'title':
                                        case 'description':
                                            $l_replacement = self::_l($this->m_type[$l_property]);
                                            break;
                                        case 'unit':
                                            if (isset($this->m_type[$l_property])) {
                                                $l_unit = current($this->m_dao->get_unit($this->m_type[$l_property]));
                                                if (!is_array($l_unit)) {
                                                    $l_replacement = $l_none;
                                                    break;
                                                }
                                                $l_replacement = self::_l($l_unit['title']);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'default_unit':
                                            if (isset($this->m_type[$l_property])) {
                                                $l_unit = current($this->m_dao->get_unit($this->m_type[$l_property]));
                                                if (!is_array($l_unit)) {
                                                    $l_replacement = $l_none;
                                                    break;
                                                }
                                                $l_unit_parameters = $this->m_dao->get_unit_parameters($l_unit['table']);
                                                foreach ($l_unit_parameters as $l_parameter) {
                                                    if ($l_parameter[$l_unit['table'] . '__id'] == $l_unit['default_unit']) {
                                                        $l_replacement = self::_l($l_parameter[$l_unit['table'] . '__title']);
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'domains':
                                            $l_domain_types = $this->m_dao->get_domain_types();
                                            $l_list = [];
                                            foreach ($l_domain_types as $l_domain_type => $l_domain_info) {
                                                $l_domain_id = null;
                                                switch ($l_domain_type) {
                                                    case 'objects':
                                                        $l_domain_id = isys_notifications_dao::C__DOMAIN__OBJECTS;
                                                        break;
                                                    case 'object_types':
                                                        $l_domain_id = isys_notifications_dao::C__DOMAIN__OBJECT_TYPES;
                                                        break;
                                                    case 'reports':
                                                        $l_domain_id = isys_notifications_dao::C__DOMAIN__REPORTS;
                                                        break;
                                                }
                                                if ($l_domain_id & $this->m_type[$l_property]) {
                                                    $l_list[] = $l_domain_info['title'];
                                                }
                                            }
                                            if (count($l_list) > 0) {
                                                $l_replacement = $this->build_plain_unsorted_list($l_list);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'status':
                                            $l_status = $this->m_dao->get_status();
                                            $l_replacement = $l_status[$this->m_type[$l_property]];
                                            break;
                                    }
                                    break;
                                case 'notification_domains':
                                    $l_domains = $this->m_dao->get_domains($p_notification['id']);
                                    if (!is_countable($l_domains) || count($l_domains) === 0) {
                                        $l_replacement = $l_none;
                                        break;
                                    }
                                    switch ($l_property) {
                                        case 'id':
                                            $l_replacement = $l_none;
                                            break;
                                        case 'notification':
                                            $l_replacement = $p_notification['id'];
                                            break;
                                        case 'objects':
                                            if (isset($l_domains[$l_property])) {
                                                $l_cmdb_dao = new isys_cmdb_dao($this->m_db);
                                                $l_objects = $l_cmdb_dao->get_objects(['ids' => $l_domains[$l_property]])
                                                    ->__as_array();
                                                $l_list = [];
                                                foreach ($l_objects as $l_object) {
                                                    $l_list[] = $l_object['isys_obj__title'];
                                                }
                                                $l_replacement = $this->build_plain_unsorted_list($l_list);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'object_types':
                                            if (isset($l_domains[$l_property])) {
                                                $l_list = [];
                                                $l_cmdb_dao = new isys_cmdb_dao($this->m_db);
                                                $l_result_set = $l_cmdb_dao->get_object_types();
                                                while ($l_row = $l_result_set->get_row()) {
                                                    if (in_array(intval($l_row['isys_obj_type__id']), $l_domains[$l_property])) {
                                                        $l_list[] = self::_l($l_row['isys_obj_type__title']);
                                                    }
                                                }
                                                $l_replacement = $this->build_plain_unsorted_list($l_list);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'reports':
                                            if (isset($l_domains[$l_property])) {
                                                $l_list = [];
                                                $l_all_reports = $this->m_dao->get_all_reports();
                                                foreach ($l_all_reports as $l_report) {
                                                    if (in_array(intval($l_report['isys_report__id']), $l_domains[$l_property])) {
                                                        $l_list[] = self::_l($l_report['isys_report__title']);
                                                    }
                                                }
                                                $l_replacement = $this->build_plain_unsorted_list($l_list);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                    }
                                    break;
                                case 'notification_roles':
                                    switch ($l_property) {
                                        case 'id':
                                            $l_replacement = $l_none;
                                            break;
                                        case 'notification':
                                            $l_replacement = $p_notification['id'];
                                            break;
                                        case 'roles':
                                            $l_roles_data = $this->m_dao->get_roles($p_notification['id']);
                                            $l_roles = [];
                                            foreach ($l_roles_data as $l_role) {
                                                $l_roles[] = intval($l_role['isys_contact_tag__id']);
                                            }
                                            $l_roles = array_unique($l_roles);
                                            if (isset($l_domains[$l_property])) {
                                                $l_list = [];
                                                $l_all_roles = $this->m_dao->get_all_roles();
                                                foreach ($l_all_roles as $l_role) {
                                                    if (is_array($l_roles) && in_array(intval($l_role['isys_contact_tag__id']), $l_roles)) {
                                                        $l_list[] = self::_l($l_role['isys_contact_tag__title']);
                                                    }
                                                }
                                                $l_replacement = $this->build_plain_unsorted_list($l_list);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                    }
                                    break;
                                case 'notification_templates':
                                    switch ($l_property) {
                                        case 'id':
                                        case 'locale':
                                        case 'subject':
                                        case 'text':
                                            if (isset($l_template[$l_property])) {
                                                $l_replacement = $l_template[$l_property];
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'notification_type':
                                            $l_replacement = self::_l($this->m_type['title']);
                                            break;
                                        case 'report':
                                            if (isset($p_objects) && isset($l_template['report'])) {
                                                $l_objects = $p_objects;
                                                if (is_array($l_assigned_objects) && count($l_assigned_objects) > 0) {
                                                    foreach ($l_objects as $l_key => $l_object) {
                                                        if (!in_array($l_object['isys_obj__id'], $l_assigned_objects)) {
                                                            unset($l_objects[$l_key]);
                                                        }
                                                    }
                                                } // // if

                                                if (!is_countable($l_objects) || count($l_objects) === 0) {
                                                    unset($l_message);
                                                    continue 2; // phpcs:ignore
                                                }

                                                assert(is_array($l_objects));
                                                $l_entities = $this->fetch_report($l_objects, $l_template['report']);
                                                $l_parsed_entities = [];
                                                foreach ($l_entities as $l_entity) {
                                                    $l_parsed_entity = [];
                                                    foreach ($l_entity as $l_key => $l_value) {
                                                        $l_new_key = null;
                                                        if ($l_key === '__id__') {
                                                            $l_new_key = 'ID';
                                                        } else {
                                                            $l_new_key = self::_l(current(explode('###', $l_key)));
                                                        }

                                                        if ($l_key !== 'Link') {
                                                            // Strip script and normal tags
                                                            $l_new_value = strip_tags(preg_replace("(<script[^>]*>([\\S\\s]*?)<\/script>)", '', self::_l($l_value)));
                                                        } else {
                                                            $l_new_value = self::_l($l_value);
                                                        }
                                                        $l_parsed_entity[$l_new_key] = $l_new_value;
                                                    }
                                                    $l_parsed_entities[] = $l_parsed_entity;
                                                }
                                                $l_replacement = $this->build_plain_table($l_parsed_entities);
                                            }
                                            break;
                                    }
                                    break;
                                case 'units':
                                    if (!isset($this->m_type['unit'])) {
                                        $l_replacement = $l_none;
                                        break;
                                    }
                                    $l_unit = $this->m_dao->get_unit($this->m_type['unit']);
                                    if (!is_array($l_unit) || count($l_unit) === 0) {
                                        $l_replacement = $l_none;
                                        break;
                                    }
                                    switch ($l_property) {
                                        case 'id':
                                            $l_replacement = $l_unit[$l_property];
                                            break;
                                        case 'title':
                                        case 'description':
                                            if (isset($l_unit[$l_property])) {
                                                $l_replacement = self::_l($l_unit[$l_property]);
                                            } else {
                                                $l_replacement = $l_none;
                                            }
                                            break;
                                        case 'table':
                                            $l_replacement = $l_none;
                                            break;
                                        case 'default':
                                            $l_unit_parameters = $this->m_dao->get_unit_parameters($l_unit['table']);
                                            if (!isset($l_unit['default_unit'])) {
                                                $l_replacement = $l_none;
                                                break;
                                            }
                                            foreach ($l_unit_parameters as $l_parameter) {
                                                if ($l_parameter[$l_unit['table'] . '__id'] == $l_unit['default_unit']) {
                                                    $l_replacement = self::_l($l_parameter[$l_unit['table'] . '__title']);
                                                    break;
                                                }
                                            }
                                            break;
                                    }
                                    break;
                                case 'receivers':
                                    switch ($l_property) {
                                        case 'title':
                                            $l_replacement = $l_receiver['title'];
                                            break;
                                        case 'email':
                                            $l_replacement = $l_receiver['email'];
                                            break;
                                    }
                                    break;
                            }
                            if (isset($l_replacement)) {
                                $l_replacements[$l_placeholder['value']] = $l_replacement;
                                $l_placeholders[] = $l_placeholder['value'];
                            } else {
                                $this->m_log->warning(sprintf('Placeholder "%s" is currently not supported. It will be ignored.', $l_placeholder['value']));
                            }
                        }
                        if (is_array($l_message)) {
                            $l_message['subject'] = str_replace($l_placeholders, $l_replacements, $l_template['subject']);
                            $l_message['body'] = str_replace($l_placeholders, $l_replacements, $l_template['text']);
                            $this->m_messages[] = $l_message;
                        }
                    }
                } //foreach receivers
            }
        } //foreach templates

        // Restore locale:
        if ($l_current_locale !== $l_original_locale) {
            $languageManager->load($l_original_locale);
        }

        $l_amount = is_countable($this->m_messages) ? count($this->m_messages) : 0;

        $this->m_log->debug(sprintf('Amount of messages to send: %s', $l_amount));

        return $l_amount;
    }

    /**
     * Updates notification: increases counter and set current date.
     *
     * @param array $p_notification Information about notification
     */
    protected function increase_counter($p_notification)
    {
        assert(is_array($p_notification));

        $this->m_log->debug('Increasing counter...');

        $l_property_type = 'notifications';

        $l_data = $p_notification;
        $l_data['last_run'] = date('Y-m-d H:i:s');
        $l_data['count'] = $p_notification['count'] + 1;

        if ($p_notification['limit'] > 0 && $l_data['count'] >= $p_notification['limit']) {
            // Limit is reached:
            $l_data['status'] = isys_notifications_dao::C__STATUS__LIMIT_REACHED;
        }

        return $this->m_dao->save($l_property_type, $l_data);
    }

    /**
     * Updates notification: resets counter.
     *
     * @param array $p_notification Information about notification
     */
    protected function reset_counter($p_notification)
    {
        assert(is_array($p_notification));

        $this->m_log->debug('Resetting counter...');

        $l_property_type = 'notifications';

        $l_data = $p_notification;
        $l_data['last_run'] = date('Y-m-d H:i:s');
        $l_data['count'] = 0;
        $l_data['status'] = isys_notifications_dao::C__STATUS__ACTIVATED;

        return $this->m_dao->save($l_property_type, $l_data);
    }

    /**
     * Marks notification as incomplete.
     *
     * @param array $p_notification Information about notification
     */
    protected function mark_notification_as_incomplete($p_notification)
    {
        assert(is_array($p_notification));

        $this->m_log->debug('Marking notification as incomplete...');

        $l_property_type = 'notifications';

        $l_data = $p_notification;
        $l_data['status'] = isys_notifications_dao::C__STATUS__INCOMPLETE;
        $l_data['last_run'] = date('Y-m-d H:i:s');

        return $this->m_dao->save($l_property_type, $l_data);
    }

    /**
     * Sends a message through all given channels to all given contacts.
     *
     * @param array  $p_contacts List of contacts. Associative array with object
     *                           identifiers as keys and child arrays with contact names ('name') and
     *                           their addresses ('email')
     * @param string $p_subject  Message subject
     * @param string $p_body     Message body
     */
    protected function send_message($p_contacts, $p_subject, $p_body)
    {
        $this->m_log->debug('Sending message...');

        if (self::C__CHANNEL__EMAIL & $this->m_channels) {
            $this->send_email($p_contacts, $p_subject, $p_body);
        }
    }

    /**
     * Sends an email.
     *
     * @param array  $p_contacts List of contacts. Associative array with object
     *                           identifiers as keys and child arrays with contact names ('name') and
     *                           their addresses ('email')
     * @param string $p_subject  Message subject
     * @param string $p_body     Message body
     */
    protected function send_email($p_contacts, $p_subject, $p_body)
    {
        $this->m_log->info('Sending message via email...');

        try {
            $l_mailer = new isys_library_mail();
            $l_mailer->set_charset('UTF-8');
            $l_mailer->set_content_type(isys_library_mail::C__CONTENT_TYPE__PLAIN);
            $l_mailer->set_backend(isys_library_mail::C__BACKEND__SMTP);

            foreach ($p_contacts as $l_contact) {
                $l_mailer->AddAddress($l_contact['email'], $l_contact['name']);
            } //foreach contact

            // Max. line length:
            $l_lengths = array_map('strlen', explode(PHP_EOL, $p_body));
            sort($l_lengths, SORT_NUMERIC);
            $l_max_length = end($l_lengths);
            $l_mailer->WordWrap = $l_max_length;

            // Fetch subject prefix that's already set in mailer and append our
            // subject:
            $l_subject = $l_mailer->get_subject() . $p_subject;
            $l_mailer->set_subject($l_subject);

            $l_mailer->set_body($p_body);
            $l_mailer->add_default_signature();

            $l_mailer->send();
        } catch (Exception $e) {
            $this->m_log->error('Mailer threw exception.');
            throw new Exception($e->getMessage());
        } //try/catch
    }

    /**
     * Fetches a report and adds a link to each object within i-doit.
     *
     * @param array  $p_objects    List these objects in report
     * @param string $p_properties JSON array of selected properties
     *
     * @return array
     */
    protected function fetch_report($p_objects, $p_properties)
    {
        $l_category_property_dao = isys_cmdb_dao_category_property::instance(isys_application::instance()->database)
            ->reset()
            ->setMultivalueStatusFilter([C__RECORD_STATUS__NORMAL])
            ->set_query_as_report(true);
        $l_report_dao = isys_report_dao::instance(isys_application::instance()->database);

        $l_condition = ' AND (';
        if (!empty($this->m_type_condition)) {
            $l_condition .= implode(' OR ', $this->m_type_condition);
        } else {
            foreach ($p_objects as $l_object_data) {
                $l_condition .= ' obj_main.isys_obj__id = ' . $l_report_dao->convert_sql_id($l_object_data['isys_obj__id']) . ' OR';
            }
        }

        $l_condition = rtrim($l_condition, 'OR') . ')';

        $l_properties = isys_format_json::decode($p_properties);

        foreach ($l_properties as $l_index => $l_property) {
            foreach ($l_property as $l_index1 => $l_prop_data) {
                if (!isset(current($l_prop_data)[1])) {
                    $l_properties[$l_index][$l_index1][key($l_prop_data)][] = current($l_prop_data)[0];
                }
            }
        }
        $l_query = $l_category_property_dao->create_property_query_for_report(null, ['root' => $l_properties], null, $l_condition);

        $l_fetched_report = $l_report_dao->query($l_query, null);
        $l_report = [];
        $l_key = self::_l('LC__UNIVERSAL__LINK');

        if (is_countable($l_fetched_report['content']) && count($l_fetched_report['content'])) {
            foreach ($l_fetched_report['content'] as $l_row) {
                $l_row = array_map('stripslashes', $l_row);

                /**
                 * Switched to isys_helper_link to prevent wrong link generation
                 * caused by wrongly setted url related constants in constant cache.
                 *
                 * @see ID-5313
                 */
                $l_row[$l_key] = '<' . isys_helper_link::create_url(['objID' => $l_row['__id__']], true) . '>';

                $l_report[] = $l_row;
            }
        }

        return $l_report;
    }

    /**
     * Builds a simple plaintext table with header. Columns are left-aligned.
     *
     * @param array $p_entities Entities
     *
     * @return string
     *
     * @todo This is not the right place. Move it to MVC view or something.
     */
    protected function build_plain_table($p_entities)
    {
        assert(is_array($p_entities));

        $l_table = '';
        $l_headers = [];
        $l_layouted_headers = [];

        if (count($p_entities) === 0) {
            return $l_table;
        }

        // Layout:

        $l_horizontal_line = '-';
        $l_horizontal_line_header = '=';
        $l_vertical_line = '|';
        $l_edge = '+';

        // Analyze entities:
        $l_column_widths = [];
        $l_count = 0;

        // Iterate through each entity:
        foreach ($p_entities as $l_entity) {
            // Also analyze header:
            if ($l_count === 0) {
                $l_count++;

                $l_headers = array_keys($l_entity);

                foreach ($l_headers as $l_header) {
                    $l_layouted_headers[$l_header] = $this->emphasize(self::_l($l_header));
                }

                foreach ($l_headers as $l_header) {
                    $l_column_widths[$l_header] = strlen($l_layouted_headers[$l_header]);
                }
            }

            // Analyse values:
            foreach ($l_entity as $l_key => $l_value) {
                $l_value_length = strlen($l_value);
                if ($l_value_length > $l_column_widths[$l_key]) {
                    $l_column_widths[$l_key] = $l_value_length;
                }
            }
        }

        $l_horizonatal_header_line_parts = [];
        $l_horizonatal_line_parts = [];
        foreach ($l_column_widths as $l_column_width) {
            $l_horizonatal_header_line_parts[] = $l_horizontal_line_header . str_pad('', $l_column_width, $l_horizontal_line_header) . $l_horizontal_line_header;
            $l_horizonatal_line_parts[] = $l_horizontal_line . str_pad('', $l_column_width, $l_horizontal_line) . $l_horizontal_line;
        }
        $l_complete_horizontal_header_line = $l_edge . implode($l_edge, $l_horizonatal_header_line_parts) . $l_edge;
        $l_complete_horizontal_line = $l_edge . implode($l_edge, $l_horizonatal_line_parts) . $l_edge;

        // Prepend header:

        $l_padded_headers = array_map([
            $this,
            'table_header'
        ], $l_layouted_headers, $l_column_widths);
        $l_table .= $l_complete_horizontal_header_line . "\n" . $l_vertical_line . ' ' . implode(' ' . $l_vertical_line . ' ', $l_padded_headers) . ' ' . $l_vertical_line .
            "\n" . $l_complete_horizontal_header_line . "\n";

        // Iterate through each entity:
        foreach ($p_entities as $l_entity) {
            $l_table .= $l_vertical_line;
            foreach ($l_entity as $l_key => $l_value) {
                $l_table .= ' ' . str_pad($l_value, $l_column_widths[$l_key]) . ' ' . $l_vertical_line;
            }

            $l_table .= "\n" . $l_complete_horizontal_line . "\n";
        }

        return $l_table;
    }

    /**
     * Helps to create a table header.
     *
     * @param string $p_header
     * @param int    $p_width
     *
     * @return string
     *
     * @todo This is not the right place. Move it to MVC view or something.
     */
    private function table_header($p_header, $p_width)
    {
        return str_pad($p_header, $p_width);
    }

    /**
     * Emphazises plain text.
     *
     * @param string $p_string
     *
     * @return string
     *
     * @todo This is not the right place. Move it to MVC view or something.
     */
    private function emphasize($p_string)
    {
        return '*' . $p_string . '*';
    }

    /**
     * Creates an unsorted list in plain text.
     *
     * @param array $p_list
     * @param int   $p_hierarchy
     *
     * @return string
     *
     * @todo This is not the right place. Move it to MVC view or something.
     */
    private function build_plain_unsorted_list($p_list, $p_hierarchy = 0)
    {
        assert(is_array($p_list));
        assert(is_int($p_hierarchy));

        $l_result = '';
        $l_indent = 2 * $p_hierarchy;
        $l_item = '* ';

        foreach ($p_list as $l_entity) {
            if (is_array($l_entity)) {
                $l_result .= $this->build_plain_unsorted_list($l_entity, $p_hierarchy + 1);
            } else {
                $l_result .= "\n" . str_pad(null, $l_indent, ' ', STR_PAD_RIGHT) . $l_item . $l_entity;
            }
        }

        return $l_result;
    }

    /**
     * Constructor
     *
     * @param isys_component_database $p_db
     * @param isys_log                $p_log
     */
    public function __construct(isys_notifications_dao $p_dao, isys_component_database $p_db, isys_log $p_log)
    {
        $this->m_dao = $p_dao;
        $this->m_db = $p_db;
        $this->m_log = $p_log;
    }
}
