<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use idoit\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DynamicGroupsSyncCommand
 *
 * @package idoit\Console\Command\Idoit
 * @author Selcuk Kekec <skekec@i-doit.com>
 */
class DynamicGroupsSyncCommand extends AbstractCommand
{
    /**
     * Command name
     */
    const NAME = 'sync-dynamic-groups';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Syncronize dynamic group members';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('groups', 'g', InputOption::VALUE_REQUIRED, 'Comma seperated Ids of dynamic group CIs', 'all'));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->output = $output;

            // Get target groups
            $targetGroupsOption = $input->getOption('groups');
            $targetGroups = null;

            $this->output->writeln('Calculate target dynamic groups...');
            // Check whether all dynamic groups are targetd
            if ($targetGroupsOption !== 'all' && !empty($targetGroupsOption)) {
                // Check whether provided list is valid
                if (!preg_match('/^\d*(,\d+?)*$/', $targetGroupsOption)) {
                    throw new \Exception('Please provide a valid comma separated list of object ids.');
                }

                // Explode list into pieces
                $targetGroups = explode(',', $targetGroupsOption);

                $this->output->writeln('Using provided list of object ids.');
            } else {
                $this->output->writeln('No objects are specified - update all available dynamic groups.');
            }

            // Collect dynamic groups
            $dynamicGroups = $this->collectDynamicGroups($targetGroups);

            // Check whether there are any groups to proceed
            if (!is_countable($dynamicGroups) || count($dynamicGroups) === 0) {
                $this->output->writeln('There are no dynamic groups with specified reports.');

                return Command::SUCCESS;
            }

            // Create group dao
            $dao = new \isys_cmdb_dao_category_s_group(\isys_application::instance()->container->get('database'));

            // Iterate over dynamic groups and update them
            foreach ($dynamicGroups as $group) {
                $this->output->writeln('Handling dynamic group #' . $group['groupId'] . ' \'' . $group['groupTitle'] . '\':');

                // Get members of group
                $memberIds = $this->getObjectIdsByReport($group['reportId']);
                if (!is_array($memberIds)) {
                    $memberIds = [$memberIds];
                }

                $this->output->writeln('Report has ' . count($memberIds) . ' results. Attach them.');

                // Refresh group assignments
                $dao->attachObjects($group['groupId'], $memberIds);
            }
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
        return Command::SUCCESS;
    }

    /**
     * Get objects of report
     *
     * @param int $reportId
     *
     * @return array
     * @throws \isys_exception_general
     * @throws \Exception
     */
    private function getObjectIdsByReport($reportId)
    {
        // Create report dao
        $reportDao = new \isys_report_dao(\isys_application::instance()->container->get('database_system'));

        // Get report
        $report = $reportDao->get_report($reportId);

        // Check for placeholder
        if (!$reportDao->hasQueryOnlyInternalPlaceholder($report['isys_report__query'])) {
            throw new \Exception(sprintf('Report with id %s contains non internal placeholders, therefore cannot be executed by the api', $reportId));
        }

        // Replace placeholders in query
        $query = $reportDao->replacePlaceHolders($report['isys_report__query']);

        // Execute report.
        $reportResult = $reportDao->query($query);

        $objectIds = [];

        if (is_array($reportResult['content'])) {
            $objectIds = array_map(function ($result) {
                return $result['__id__'];
            }, $reportResult['content']);
        }

        return $objectIds;
    }

    /**
     * Collect dynamic groups with reports
     *
     * @param array $ids
     *
     * @return array
     * @throws \Exception
     */
    private function collectDynamicGroups($ids)
    {
        // Create group type dao
        $groupTypeDao = new \isys_cmdb_dao_category_s_group_type(\isys_application::instance()->container->get('database'));
        ;

        // Prepare base condition
        $condition = ' AND isys_cats_group_type_list__type = 1 AND isys_cats_group_type_list__isys_report__id IS NOT NULL ';

        // Check whether ids are provided
        if (is_array($ids) && count($ids)) {
            $condition .= ' AND isys_obj__id IN(' . implode(',', $ids) . ') ';
        }

        // Get dynamic groups with configured report
        $resource = $groupTypeDao->get_data(null, null, $condition);

        $collectedGroups = [];

        // Check wheter there are any to proceed
        if ($resource->count()) {
            // Collect target groups
            while ($groupRow = $resource->get_row()) {
                $collectedGroups[] = [
                    'groupTitle' => $groupRow['isys_obj__title'],
                    'groupId'    => $groupRow['isys_obj__id'],
                    'reportId'   => $groupRow['isys_cats_group_type_list__isys_report__id']
                ];
            }
        }

        return $collectedGroups;
    }
}
