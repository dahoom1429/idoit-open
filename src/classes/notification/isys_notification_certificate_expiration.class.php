<?php

/**
 * i-doit
 *
 * Notification: License has expired.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_certificate_expiration extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $notificationData Information about notification
     */
    protected function handle_notification($notificationData)
    {
        // Check threshold and its unit:
        $daoCertificate = new isys_cmdb_dao_category_g_certificate($this->m_db);
        if (!isset($notificationData['threshold'])) {
            $this->m_log->warning('Threshold is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($notificationData);
        }

        if (!isset($notificationData['threshold_unit'])) {
            $this->m_log->warning('Threshold unit is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($notificationData);
        }

        // Fetch objects selected by notification:
        $notificationObjects = $this->m_dao->get_objects($notificationData['id']);

        // Objects with certificates
        $objectsWithCertificates = [];

        foreach ($notificationObjects as $object) {
            $result = $daoCertificate->get_all_catg_by_obj_type_id($object['isys_obj_type__id'], defined_or_default('C__CATG__CERTIFICATE'));
            if ($result->num_rows()) {
                $objectsWithCertificates[] = $object;
            }
        }

        unset($notificationObjects);

        $objectsWithCertificatesCounter = count($objectsWithCertificates);

        if ($objectsWithCertificatesCounter == 0) {
            $this->m_log->warning('No Certificates have been set to report! Skip notification.');

            return $this->mark_notification_as_incomplete($notificationData);
        } else {
            $this->m_log->info(sprintf('Amount of Certificates: %s', $objectsWithCertificatesCounter));
        }

        // Certificates which are expired
        $expiredCertificates = [];

        $currentTime = time();
        $threshold = null;

        $unit = $this->m_dao->get_unit($this->m_type['unit']);
        $unitParameters = $this->m_dao->get_unit_parameters($unit['table']);
        $notificationThreshold = $notificationData['threshold'];

        // Calculate the threshold of the specified unit from the notification
        foreach ($unitParameters as $parameter) {
            if ($parameter[$unit['table'] . '__id'] == $notificationData['threshold_unit']) {
                $day = (int)date('d', $currentTime);
                $month = (int)date('m', $currentTime);
                $year = (int)date('Y', $currentTime);

                switch ($parameter[$unit['table'] . '__const']) {
                    case 'C__CMDB__UNIT_OF_TIME__MONTH':

                        while ($month < $notificationThreshold) {
                            $month += 12;
                            $year--;
                        }
                        $month -= $notificationThreshold;
                        $daysInMonth = date('t', strtotime($year . '-' . $month . '-01'));
                        if ($day > $daysInMonth) {
                            $day = $daysInMonth;
                        }
                        $threshold = $currentTime - strtotime($year . '-' . $month . '-' . $day);

                        break;
                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        $year = $year - $notificationThreshold;
                        $threshold = $currentTime - strtotime($year . '-' . $month . '-' . $day);
                        break;
                    default:
                        $threshold = $parameter[$unit['table'] . '__factor'] * $notificationThreshold;
                        break;
                }
                break;
            }
        }

        if ($threshold === null) {
            $this->m_log->warning('Threshold unit is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($notificationData);
        }

        // Iterate through each object:
        foreach ($objectsWithCertificates as $object) {
            $this->m_log->info(sprintf('Handling Object "%s"...', $object['isys_obj__title']));

            // Fetch category data from database:
            $certificateResult = $daoCertificate->get_data(null, $object['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

            if (is_countable($certificateResult) && count($certificateResult) > 0) {
                // Retrieve all certificates of the object
                while ($certificateData = $certificateResult->get_row()) {
                    $expireDate = $certificateData['isys_catg_certificate_list__expire'];

                    // Not enough data provided. Skipping certificate.
                    if (empty($expireDate) || strtotime($expireDate) < 0) {
                        $this->m_log->info('Expiration date not set. Skip certificate ' . $certificateData['isys_catg_certificate_list__id'] . '.');
                        continue;
                    }

                    $expireDate = strtotime($expireDate);

                    $destinedDate = $expireDate - $threshold;
                    $destinedDateFormatted = date('Y-m-d H:i:s', $destinedDate);

                    $this->m_log->info(sprintf('Threshold date is: %s for certificate %s.', $destinedDateFormatted, $certificateData['isys_catg_certificate_list__id']));

                    // Skip certificate if expiration date has not been exceeded
                    if ($destinedDate > $currentTime) {
                        $this->m_log->info(sprintf(
                            'Threshold date not exceeded (%s). Skip certificate %s.',
                            $destinedDateFormatted,
                            $certificateData['isys_catg_certificate_list__id']
                        ));
                        continue;
                    }

                    $this->m_log->info('Threshold exceeded! Add certificate to the list.');

                    $expiredCertificates[] = $certificateData;

                    // @see ID-7762
                    $this->m_type_condition[] = 'isys_catg_certificate_list__id = ' . $this->m_dao->convert_sql_id($certificateData['isys_catg_certificate_list__id']);
                }
            }
        }

        unset($daoCertificate);

        $expiredCertificatesCounter = count($expiredCertificates);

        if ($expiredCertificatesCounter == 0) {
            $this->m_log->info('There are no certificates left to report. Skip notification.');

            return $this->reset_counter($notificationData);
        } else {
            $this->m_log->info(sprintf('Amount of certificate which match the criterias: %s', $expiredCertificatesCounter));
        }

        // Write messages:
        if ($this->write_messages($notificationData, $expiredCertificates) > 0) {
            return $this->increase_counter($notificationData);
        }
    }
}
