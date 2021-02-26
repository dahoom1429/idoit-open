<?php
/**
 * @author     Dennis Stuecken
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use idoit\Module\License\Event\License\LegacyLicenseRemovedEvent;
use idoit\Module\License\Exception\LicenseExistsException;
use idoit\Module\License\Exception\LicenseInvalidException;
use idoit\Module\License\Exception\LicenseParseException;
use idoit\Module\License\Exception\LicenseServerAuthenticationException;
use idoit\Module\License\Exception\LicenseServerConnectionException;
use idoit\Module\License\Exception\LicenseServerNoLicensesException;
use idoit\Module\License\LicenseService;

global $g_comp_database, $g_config, $g_absdir, $g_license_token;

$l_template = isys_component_template::instance();

if (!C__ENABLE__LICENCE) {
    throw new Exception("Licence pages are not available in this i-doit version! " . "You need to subscribe at <a href=\"http://www.i-doit.com\">http://www.i-doit.com</a>.");
}

/* Load statistics module */
include_once($g_absdir . '/src/classes/modules/statistics/init.php');

$app = isys_application::instance();

$l_licences = new isys_module_licence();
$l_dao_mandator = new isys_component_dao_mandator($app->database_system);

$licenses = [];
$l_licences_single = [];
$l_licences_hosting = [];

global $licenseService;

/* Request processing */
switch ($_POST["action"]) {
    case "delete":

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $l_licence_data) {
                list($l_tenant_id, $l_licence_id, $licenceType) = explode(",", $l_licence_data);

                if ($l_licence_id > 0 && $l_tenant_id >= 0) {
                    if ($licenceType === 'hosting' && (int)$_POST['multiLicenceAction']) {
                        // Delete all installed child licences referenced by the parent licence
                        $l_licences->deleteLicenceByParentLicence($app->database_system, $l_licence_id);
                    }

                    //connect_mandator($l_tenant_id);
                    $l_licences->delete_licence($app->database_system, $l_licence_id);

                    if ($l_tenant_id === 0 && $l_licence_id > 0) {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__type = " . C__LICENCE_TYPE__HOSTING_SINGLE);
                    } else {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__id = " . (int)$l_licence_id . ";");
                    }

                    $licenseService->getEventDispatcher()->dispatch(
                        new LegacyLicenseRemovedEvent(),
                        LegacyLicenseRemovedEvent::NAME
                    );
                }
            }
        }

        break;
    case "web_license_set_token": {
        if (empty($_POST['license_token'])) {
            $l_template->assign('error', 'License Token cannot be empty');
            break;
        }

        saveLicenseToken($_POST['license_token']);

        break;
    }
    case "web_license_save_token":
        $licenseService->setEncryptionToken($_POST['license_token']);

        try {
            $webLicensesString = $licenseService->getLicensesFromServer();
        } catch (GuzzleException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerAuthenticationException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerConnectionException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerNoLicensesException $exception) {
            $l_template->assign('note', $exception->getMessage());
        }

        if (empty($l_template->get_template_vars('error'))) {
            saveLicenseToken($_POST['license_token']);
        }

        try {
            foreach ($licenseService->parseEncryptedLicenses($webLicensesString) as $license) {
                $licenseService->installLicense($license);
            }
        } catch (LicenseExistsException $exception) {
            $l_template->assign('note', 'Some licenses were already existing');
        } catch (LicenseInvalidException $exception) {
            $l_template->assign('note', 'Some licenses were skipped because they were invalid');
        } catch (LicenseParseException $exception) {
            $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
        } catch (\Exception $exception) {
            $l_template->assign('error', 'An error occured');
        }
        break;
    case "web_license_check_licenses":
        try {
            $webLicensesString = $licenseService->getLicensesFromServer();
        } catch (GuzzleException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerAuthenticationException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerConnectionException $exception) {
            $l_template->assign('error', $exception->getMessage());
        } catch (LicenseServerNoLicensesException $exception) {
            $l_template->assign('note', $exception->getMessage());
        }

        try {
            foreach ($licenseService->parseEncryptedLicenses($webLicensesString) as $license) {
                try {
                    $licenseService->installLicense($license);
                } catch (LicenseExistsException $exception) {
                    $l_template->assign('note', 'Some licenses were already existing');
                } catch (LicenseInvalidException $exception) {
                    $l_template->assign('note', 'Some licenses were skipped because they were expired');
                } catch (\Exception $exception) {
                    $l_template->assign('error', 'An error occured');
                }
            }
        } catch (LicenseParseException $exception) {
            $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
        }
        break;
    case "web_license_remove_token":
        saveLicenseToken('');
        $licenseService->deleteLicenses([LicenseService::C__LICENCE_TYPE__NEW__IDOIT, LicenseService::C__LICENCE_TYPE__NEW__ADDON]);

        break;
    case "add":
        $mandatorDatabase = null;

        if (!empty($_POST['license_file_raw'])) {
            // Handle new licenses
            try {
                foreach ($licenseService->parseEncryptedLicenses($_POST['license_file_raw']) as $license) {
                    $licenseService->installLicense($license);
                }
            } catch (LicenseExistsException $exception) {
                // do nothing
            } catch (LicenseInvalidException $exception) {
                // do nothing
            } catch (LicenseParseException $exception) {
                $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
            } catch (\Exception $exception) {
                $l_template->assign('error', 'An error occured');
            }
        } else {
            isys_module_system::handle_licence_installation($l_tenant, $mandatorDatabase);
        }

        $l_frontend_error = $l_template->get_template_vars('error');

        // Only redirect, if there is no error message!
        if (empty($l_frontend_error)) {
            header('Location: ?req=licences');
        }

        break;
}

try {
    if ($app->database_system) {
        $totalObjects = 0;

        // New licenses
        $licenseEntities = $licenseService->getLicenses();

        foreach ($licenseEntities as $id => $licenseEntity) {
            $start = \Carbon\Carbon::createFromTimestamp($licenseEntity->getValidityFrom()->getTimestamp());
            $end = \Carbon\Carbon::createFromTimestamp($licenseEntity->getValidityTo()->getTimestamp());

            $invalid = !(\Carbon\Carbon::now()->between($start, $end));

            $start = $start->format('l, F j, Y H:i');
            $end = $end->format('l, F j, Y H:i');

            $licenses[$licenseEntity->getProductType()][$id] = [
                'label' => $licenseEntity->getProductName() ?: $licenseEntity->getProductIdentifier(),
                'licenseType' => $licenseEntity->getProductType(),
                'start' =>  $start,
                'end' => $end,
                'objects' => $licenseEntity->getObjects(),
                'tenants' => $licenseEntity->getTenants(),
                'environment' => $licenseEntity->getEnvironment()
            ];
        }

        $oldLicenses = $licenseService->getLegacyLicenses();

        foreach ($oldLicenses as $oldLicense) {
            $start = Carbon::createFromTimestamp($oldLicense[LicenseService::C__LICENCE__REG_DATE]);
            $end = Carbon::parse($oldLicense[LicenseService::LEGACY_LICENSE_EXPIRES]);

            $invalid = !(\Carbon\Carbon::now()->between($start, $end));

            $start = $start->format('l, F j, Y H:i');
            $end = $end->format('l, F j, Y H:i');

            $label = 'Subscription (Classic)';
            $tenants = 1;

            if (in_array(
                $oldLicense[LicenseService::LEGACY_LICENSE_TYPE],
                LicenseService::LEGACY_LICENSE_TYPES_HOSTING,
                false
            )) {
                $label = 'Hosting (Classic)';
                $tenants = 50;
            }

            $licenses['idoit'][$oldLicense[LicenseService::LEGACY_LICENSE_ID]] = [
                'label' => $label,
                'start' =>  $start,
                'end' => $end,
                'objects' => $oldLicense[LicenseService::C__LICENCE__OBJECT_COUNT],
                'tenants' => $tenants,
                'environment' => 'production',
                'invalid' => $invalid
            ];
        }
    }
} catch (isys_exception_database $e) {
    $l_template->assign("error", $e->getMessage());
}

$l_template->assign("licences", $licenses);
$l_template->assign("licensedAddOns", $licenseService->getLicensedAddOns());
$l_template->assign("totalLicenseObjects", $licenseService->getTotalObjects());
$l_template->assign("licenseObjectsUsed", $licenseService->getUsedLicenseObjects(true));
$l_template->assign("totalTenants", $licenseService->getTotalTenants());
$l_template->assign("licenseToken", $g_license_token);

$lastCommunicationLog = $licenseService->getLastLicenseServerCommuncation();

$l_template->assign(
    "lastCommunicationLog",
    ($lastCommunicationLog !== null ? sprintf('Last check on %s: %s, %d licenses retrieved', \Carbon\Carbon::parse($lastCommunicationLog['created'])->format('l, F j, Y H:i'), (in_array($lastCommunicationLog['status'], LicenseService::HTTP_STATUS_POSITIVE, false) ? 'OK' : 'ERROR'), $lastCommunicationLog['licenses_count']) : '')
);
