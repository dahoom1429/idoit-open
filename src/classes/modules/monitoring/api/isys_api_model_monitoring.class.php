<?php

/**
 * i-doit
 *
 * API model for monitoring
 *
 * @package    i-doit
 * @subpackage API
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @since      1.10.2
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_api_model_monitoring extends isys_api_model implements isys_api_model_interface
{
    /**
     * Router for the API monitoring models.
     *
     * @param   string $modelName
     * @param   array  $parameters
     *
     * @return  $this
     * @throws  isys_exception_api
     */
    public function route($modelName, $parameters)
    {
        $modelClassName = 'isys_api_model_monitoring_' . $modelName;

        if (class_exists($modelClassName)) {
            if (!is_object($this->m_db)) {
                throw new isys_exception_api('Database not loaded. Your login may did not work!');
            }

            /** @var  isys_api_model_monitoring $model */
            $model = new $modelClassName($this->m_db);

            if (isset($parameters['option']) && in_array($parameters['option'], ['read', 'create', 'update', 'delete'])) {
                $modelMethod = $parameters['option'];
            } else {
                $modelMethod = 'read';
            }

            // Check for mandatory parameters.
            $validation = $model->get_validation();

            if (isset($validation[$modelMethod]) && is_array($validation[$modelMethod])) {
                foreach ($validation[$modelMethod] as $validate) {
                    if ($validate && !isset($parameters[$validate])) {
                        throw new isys_exception_api('Mandatory parameter "' . $validate . '" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
                    }
                }
            }

            if (method_exists($model, $modelMethod)) {
                $this->m_log->info('Retrieving data from: ' . $modelName);
                $this->format($model->$modelMethod($parameters));
            }
        } else {
            $this->m_log->error('Method "' . $modelName . '" does not exit.');
            throw new isys_exception_api('API Method "' . $modelName . '" (' . $modelClassName . ') does not exist.', isys_api_controller_jsonrpc::ERR_Method);
        }

        return $this;
    }

    /**
     * Format method.
     *
     * @param  array $mapping
     * @param  array $data
     *
     * @return array
     */
    protected function format_by_mapping(array $mapping, array $data)
    {
        $return = [];

        $data['isys_monitoring_hosts__id'] = (int)$data['isys_monitoring_hosts__id'];
        $data['isys_monitoring_hosts__active'] = (bool)$data['isys_monitoring_hosts__active'];
        $data['isys_monitoring_hosts__port'] = (int)$data['isys_monitoring_hosts__port'];

        foreach ($mapping as $key => $map) {
            if (isset($data[$key])) {
                $return[$map] = $data[$key];
            }
        }

        return $return;
    }

    /**
     * Empty "read" method.
     *
     * @param   array $parameters
     *
     * @return  null
     */
    public function read($parameters)
    {
        return null;
    }
}