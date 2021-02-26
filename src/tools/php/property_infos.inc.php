<?php
/**
 * @param array $p_structure
 */
function structure(array $p_structure)
{
    $languageManager = isys_application::instance()->container->get('language');

    foreach ($p_structure as $l_category => $l_properties) {
        $l_cat = explode(':', $l_category);

        echo '<h3>' . $l_cat[0] . '</h3>
            <h4>' . $languageManager->get('LC__DATABASE_OBJECTS__TABLE') . ': ' . $l_cat[2] . '</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%">' . $languageManager->get('LC__UNIVERSAL__FIELD') . '</th>
                        <th style="width: 5%">' . $languageManager->get('LC__CMDB__CATS__GROUP_TYPE') . '</th>
                        <th style="width: 15%">' . $languageManager->get('LC__DATABASE_OBJECTS__TABLE') . '</th>
                        <th style="width: 30%">' . $languageManager->get('LC__CMDB__TREE__DATABASE') . '-' . $languageManager->get('LC__UNIVERSAL__FIELD') . '</th>
                        <th style="width: 20%">' . $languageManager->get('LC__CMDB__CATG__REFERENCED_VALUE') . '-' . $languageManager->get('LC__UNITS__TABLE') . '</th>
                        <th style="width: 15%">' . $languageManager->get('LC__CMDB__CATG__REFERENCED_VALUE') . '</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($l_properties as $l_prop) {
            echo '<tr>
                    <td>' . $l_prop['title'] . '</td>
                    <td>' . $l_prop['type'] . '</td>
                    <td>' . $l_prop['table'] . '</td>
                    <td>' . $l_prop['field'] . '</td>
                    <td>' . $l_prop['ref_table'] . '</td>
                    <td>' . $l_prop['ref'] . '</td>
                </tr>';
        }

        echo '</tbody></table><br />';
    }
}

$languageManager = isys_application::instance()->container->get('language');
$database = isys_application::instance()->container->get('database');

$l_dao = new isys_cmdb_dao($database);
$l_structure = [];
$i = 0;

foreach (['g', 's'] as $l_cattype) {
    $l_categories = $l_dao->get_isysgui('isysgui_cat' . $l_cattype);

    while ($l_row = $l_categories->get_row()) {
        $l_class = $l_row['isysgui_cat' . $l_cattype . '__class_name'];
        $l_source_table = $l_row['isysgui_cat' . $l_cattype . '__source_table'];
        $l_source_table .= (strpos($l_source_table, '_listener') !== false) ? '_list' : (strpos($l_source_table, '_list') === false ? '_list' : '');

        if (strpos(' ' . $l_class, 'isys_') !== false && class_exists($l_class)) {
            $l_category_dao = new $l_class($database);
            $l_properties = $l_category_dao->get_properties();

            if ($l_properties && is_array($l_properties) && count($l_properties) > 0) {
                foreach ($l_properties as $l_key => $l_prop) {
                    $l_structure[$l_cattype][$languageManager->get($l_row['isysgui_cat' . $l_cattype . '__title']) . ':' . $l_row['isysgui_cat' . $l_cattype . '__const'] . ':' . $l_source_table][$l_key] = [
                        'title'     => $languageManager->get($l_prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                        'key'       => $l_key,
                        'table'     => isset($l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) &&
                        $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] ? $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : $l_source_table,
                        'type'      => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE],
                        'field'     => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                        'ref'       => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1],
                        'ref_table' => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]
                    ];
                }
            }
        }
    }
}

echo '<h1>i-doit Database</h1>';

if (is_array($l_structure['g'])) {
    echo '<h2>' . $languageManager->get('LC__CMDB__GLOBAL_CATEGORIES') . '</h2>';
    structure($l_structure['g']);
}

if (is_array($l_structure['s'])) {
    echo '<h2>' . $languageManager->get('LC__CMDB__SPECIFIC_CATEGORIES') . '</h2>';
    structure($l_structure['s']);
}

echo '<style type="text/css">
    html {
        font-family: Tahoma,Geneva,Helvetica,sans-serif;
        line-height:1.15
    }

    body {
        margin:10px
    }

    table {
        table-layout: fixed;
        border-spacing: 0;
        width: 100%;
        min-width: 1280px
    }

    thead {
        background: #ddd
    }

    thead th {
        text-align: left
    }

    tbody tr {
        font-size: 80%;
        background-color: #fff
    }

    tbody td {
        padding: 2px
    }

    tbody tr:nth-child(2n+1) {
        background-color: #eee
    }

    tbody tr:hover {
        background-color: #d6dde5
    }
    </style>';
