<?php

class isys_global_assigned_sim_cards_export_helper extends isys_export_helper
{
    /**
     * @param $cardId
     *
     * @return isys_export_data|null
     * @throws isys_exception_database
     */
    public function assignedCards($cardId)
    {
        if (isset($cardId) && $cardId > 0) {
            $dao = isys_cmdb_dao_category_g_cards::instance($this->m_database);
            $cardData = $dao->get_data($cardId)
                ->get_row();

            $objectData = $dao->get_object($cardData['isys_catg_cards_list__isys_obj__id'])->get_row();

            $data[] = [
                'id'        => $objectData['isys_obj__id'],
                'sysid'     => $objectData['isys_obj__sysid'],
                'type'      => $objectData['isys_obj_type__const'],
                'title'     => $objectData['isys_obj__title'],
                'ref_id'    => $cardId,
                'ref_type'  => 'C__CATG__CARDS',
                'ref_title' => $cardData['isys_catg_cards_list__title']
            ];

            return new isys_export_data($data);
        }

        return null;
    }

    /**
     * @param $value
     *
     * @return int|mixed|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function assignedCards_import($value)
    {
        $return = null;

        if (isset($value[C__DATA__VALUE])) {
            if (is_array($value[C__DATA__VALUE])) {
                $data = $value[C__DATA__VALUE][0];

                if (isset($this->m_object_ids[$data['id']])) {
                    $daoCards = isys_cmdb_dao_category_g_cards::instance($this->m_database);
                    $result = $daoCards->get_data(
                        null,
                        $this->m_object_ids[$data['id']],
                        'AND isys_catg_cards_list__title = ' . $daoCards->convert_sql_text($data['ref_title'])
                    );

                    if ($result->num_rows() > 0) {
                        return $result->get_row_value('isys_catg_cards_list__id');
                    }

                    $lastId = $daoCards->create_connector('isys_catg_cards_list', $this->m_object_ids[$data['id']]);
                    $daoConnection = isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'));

                    // Category list content.
                    $content = [
                        'title' => $data['ref_title'],
                        'isys_connection__id' => $daoConnection->add_connection(isys_import_handler_cmdb::store__objectID())
                    ];

                    // Builds an insert query.
                    $query = $daoCards->build_query('isys_catg_cards_list', $content, $lastId, C__DB_GENERAL__UPDATE);

                    // Do the update!
                    if ($daoCards->update($query) && $daoCards->apply_update()) {
                        return $lastId;
                    }
                }
            } elseif ($value[C__DATA__VALUE] > 0) {
                $return = $value[C__DATA__VALUE];
            }
        }

        return $return;
    }
}
