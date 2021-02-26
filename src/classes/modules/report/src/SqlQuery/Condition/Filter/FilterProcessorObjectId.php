<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

use idoit\Component\Property\Property;

class FilterProcessorObjectId extends AbstractFilterProcessor implements FilterProcessorInterface
{
    public function process()
    {
        $dao = $this->getDao();
        $table = ($dao->get_source_table() ?: $dao->get_table());

        $method = $this->getMethod();
        $result = $dao->get_data(null, $this->getId(), '', null, C__RECORD_STATUS__NORMAL);
        $return = [];

        /**
         * @var AbstractFilterProcessorValue $processorConditionValue
         */
        $processorConditionValue = $this->getProcessorConditionValue();

        while ($row = $result->get_row()) {
            $callBackValue = $dao->$method($row);

            $processorConditionValue->setCheckValue($callBackValue);

            $processedId = $dao->convert_sql_id($row[$table . '__id']);

            if ($processorConditionValue->checkValue()) {
                $this->addProcessedValueIdsPositive($processedId);
            } else {
                $this->addProcessedValueIdsNegative($processedId);
            }
        }
    }
}
