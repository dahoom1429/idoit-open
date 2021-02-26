<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Oscar Pohl <opohl@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_stor_export_helper extends isys_export_helper
{
    /**
     * dialog_plus wrapper.
     *
     * @param   integer $id
     * @param   boolean $tableName
     *
     * @return  array
     * @author Oscar Pohl <opohl@synetics.com>
     */
    public function storageModel($id, $tableName = false)
    {
        return $this->dialog_plus($id, $tableName);
    }

    /**
     * Model manufacturer-title relation handler.
     *
     * @param   mixed $titleLang
     *
     * @return  integer
     * @author Oscar Pohl <opohl@synetics.com>
     */
    public function storageModelImport($titleLang)
    {
        if (is_array($titleLang)) {
            if (isset($titleLang[C__DATA__VALUE]) && is_array($titleLang[C__DATA__VALUE])) {
                $titleLang = $titleLang[C__DATA__VALUE];
            }
            if (!empty($titleLang["title_lang"]) || is_numeric($titleLang["title_lang"])) {
                $titleLang = $titleLang["title_lang"];
            } elseif (!empty($titleLang[C__DATA__VALUE]) || is_numeric($titleLang[C__DATA__VALUE])) {
                $titleLang = $titleLang[C__DATA__VALUE];
            } else {
                return null;
            }
        }

        if (isset($this->m_property_data['manufacturer'])) {
            return isys_import::check_dialog(
                'isys_stor_model',
                $titleLang,
                null,
                (is_numeric($this->m_property_data['manufacturer'][C__DATA__VALUE]) ? $this->m_property_data['manufacturer'][C__DATA__VALUE] : $this->m_property_data['manufacturer']['id'])
            );
        } else {
            return isys_import::check_dialog('isys_stor_model', $titleLang);
        }
    }
}
