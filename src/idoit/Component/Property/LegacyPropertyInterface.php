<?php

namespace idoit\Component\Property;

interface LegacyPropertyInterface
{
    const C__PROPERTY_TYPE__DYNAMIC = 2;
    const C__PROPERTY_TYPE__STATIC = 1;

    const C__PROPERTY__INFO = 'info';
    const C__PROPERTY__INFO__TITLE = 'title';
    const C__PROPERTY__INFO__DESCRIPTION = 'description';
    const C__PROPERTY__INFO__PRIMARY = 'primary_field';
    const C__PROPERTY__INFO__TYPE = 'type';
    const C__PROPERTY__INFO__BACKWARD = 'backward';

    const C__PROPERTY__INFO__TYPE__TEXT = 'text';
    const C__PROPERTY__INFO__TYPE__TIME = 'time';
    const C__PROPERTY__INFO__TYPE__TEXTAREA = 'textarea';
    const C__PROPERTY__INFO__TYPE__DOUBLE = 'double';
    const C__PROPERTY__INFO__TYPE__FLOAT = 'float';
    const C__PROPERTY__INFO__TYPE__INT = 'int';
    const C__PROPERTY__INFO__TYPE__N2M = 'n2m';
    const C__PROPERTY__INFO__TYPE__DIALOG = 'dialog';
    const C__PROPERTY__INFO__TYPE__DIALOG_PLUS = 'dialog_plus';
    const C__PROPERTY__INFO__TYPE__DIALOG_LIST = 'dialog_list';
    const C__PROPERTY__INFO__TYPE__DATE = 'date';
    const C__PROPERTY__INFO__TYPE__DATETIME = 'datetime';
    const C__PROPERTY__INFO__TYPE__OBJECT_BROWSER = 'object_browser';
    const C__PROPERTY__INFO__TYPE__MULTISELECT = 'multiselect';
    const C__PROPERTY__INFO__TYPE__MONEY = 'money';
    const C__PROPERTY__INFO__TYPE__AUTOTEXT = 'autotext';
    const C__PROPERTY__INFO__TYPE__UPLOAD = 'upload';
    const C__PROPERTY__INFO__TYPE__COMMENTARY = 'commentary';
    const C__PROPERTY__INFO__TYPE__PASSWORD = 'password';
    const C__PROPERTY__INFO__TYPE__TIMEPERIOD = 'timeperiod';

    const C__PROPERTY__DATA = 'data';
    const C__PROPERTY__DATA__TYPE = 'type';
    const C__PROPERTY__DATA__FIELD = 'field';
    const C__PROPERTY__DATA__RELATION_TYPE = 'relation_type';
    const C__PROPERTY__DATA__RELATION_HANDLER = 'relation_handler';
    const C__PROPERTY__DATA__FIELD_ALIAS = 'field_alias';
    const C__PROPERTY__DATA__TABLE_ALIAS = 'table_alias';
    const C__PROPERTY__DATA__SOURCE_TABLE = 'source_table';
    const C__PROPERTY__DATA__REFERENCES = 'references';
    const C__PROPERTY__DATA__READONLY = 'readonly';
    const C__PROPERTY__DATA__JOIN = 'join';
    const C__PROPERTY__DATA__JOIN_LIST = 'join_list';
    const C__PROPERTY__DATA__INDEX = 'index';
    const C__PROPERTY__DATA__SELECT = 'select';
    const C__PROPERTY__DATA__SORT = 'sort';
    const C__PROPERTY__DATA__SORT_ALIAS = 'sort_alias';
    const C__PROPERTY__DATA__ENCRYPT = 'encrypt';

    const C__PROPERTY__UI = 'ui';
    const C__PROPERTY__UI__ID = 'id';
    const C__PROPERTY__UI__TYPE = 'type';
    const C__PROPERTY__UI__PARAMS = 'params';
    const C__PROPERTY__UI__DEFAULT = 'default';
    const C__PROPERTY__UI__PLACEHOLDER = 'placeholder';
    const C__PROPERTY__UI__EMPTYMESSAGE = 'emptyMessage';

    const C__PROPERTY__UI__TYPE__POPUP = 'popup';
    const C__PROPERTY__UI__TYPE__MULTISELECT = 'multiselect';
    const C__PROPERTY__UI__TYPE__TEXT = 'text';
    const C__PROPERTY__UI__TYPE__TIME = 'time';
    const C__PROPERTY__UI__TYPE__LINK = 'link';
    const C__PROPERTY__UI__TYPE__TEXTAREA = 'textarea';
    const C__PROPERTY__UI__TYPE__DIALOG = 'dialog';
    const C__PROPERTY__UI__TYPE__DIALOG_LIST = 'f_dialog_list';
    const C__PROPERTY__UI__TYPE__DATE = 'date';
    const C__PROPERTY__UI__TYPE__DATETIME = 'datetime';
    const C__PROPERTY__UI__TYPE__CHECKBOX = 'checkbox';
    const C__PROPERTY__UI__TYPE__PROPERTY_SELECTOR = 'f_property_selector';
    const C__PROPERTY__UI__TYPE__AUTOTEXT = 'autotext';
    const C__PROPERTY__UI__TYPE__UPLOAD = 'upload';
    const C__PROPERTY__UI__TYPE__WYSIWYG = 'wysiwyg';

    const C__PROPERTY__CHECK = 'check';
    const C__PROPERTY__CHECK__MANDATORY = 'mandatory';
    const C__PROPERTY__CHECK__VALIDATION = 'validation';
    const C__PROPERTY__CHECK__SANITIZATION = 'sanitization';
    const C__PROPERTY__CHECK__UNIQUE_OBJ = 'unique_obj';
    const C__PROPERTY__CHECK__UNIQUE_OBJTYPE = 'unique_objtype';
    const C__PROPERTY__CHECK__UNIQUE_GLOBAL = 'unique_global';

    const C__PROPERTY__PROVIDES = 'provides';
    const C__PROPERTY__PROVIDES__SEARCH = 1;
    const C__PROPERTY__PROVIDES__IMPORT = 2;
    const C__PROPERTY__PROVIDES__EXPORT = 4;
    const C__PROPERTY__PROVIDES__REPORT = 8;
    const C__PROPERTY__PROVIDES__LIST = 16;
    const C__PROPERTY__PROVIDES__MULTIEDIT = 32;
    const C__PROPERTY__PROVIDES__VALIDATION = 64;
    const C__PROPERTY__PROVIDES__VIRTUAL = 128;
    const C__PROPERTY__PROVIDES__SEARCH_INDEX = 256;
    const C__PROPERTY__PROVIDES__FILTERABLE = 512;

    const C__PROPERTY__FORMAT = 'format';
    const C__PROPERTY__FORMAT__CALLBACK = 'callback';
    const C__PROPERTY__FORMAT__REQUIRES = 'requires';
    const C__PROPERTY__FORMAT__UNIT = 'unit';

    const C__PROPERTY__DEPENDENCY = 'dependency';
    const C__PROPERTY__DEPENDENCY__PROPKEY = 'propkey';
    const C__PROPERTY__DEPENDENCY__SMARTYPARAMS = 'smartyParams';
    const C__PROPERTY__DEPENDENCY__CONDITION = 'condition';
    const C__PROPERTY__DEPENDENCY__CONDITION_VALUE = 'conditionValue';
    const C__PROPERTY__DEPENDENCY__SELECT = 'select';
}
