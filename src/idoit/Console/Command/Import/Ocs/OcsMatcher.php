<?php

namespace idoit\Console\Command\Import\Ocs;

use idoit\Component\ContainerFacade;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;
use idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword;
use idoit\Module\Cmdb\Model\Matcher\Ci\CiMatcher;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTitle;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Fqdn;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Mac;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTypeConst;
use isys_tenantsettings;
use Symfony\Component\Console\Output\OutputInterface;
use idoit\Console\Command\IsysLogWrapper;

class OcsMatcher
{
    /**
     * @var ContainerFacade
     */
    private $container = null;

    /**
     * @var [] CiMatcher
     */
    private $ciMatcher = [];

    /**
     * @var OutputInterface
     */
    private $output = null;

    /**
     * @var IsysLogWrapper
     */
    private $logger = null;

    /**
     * @param ContainerFacade $container
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @param OutputInterface $output
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param IsysLogWrapper $logger
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retrieve object id by matching Serial, Mac, Object title and optional Object type
     *
     * @param string $p_serial
     * @param array  $p_macaddresses
     * @param string $p_objectTitle
     * @param int    $p_objecttype
     *
     * @return bool|int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_object_id_by_matching($p_serial = null, $p_macaddresses = [], $p_objectTitle = null, $p_objecttype = null, $p_bits = null, $p_minMatch = null)
    {
        $l_match = null;
        $l_matchKeywords = [];

        if ($p_bits !== null && $p_minMatch !== null) {
            $l_ciMatcherKey = $p_bits . '_' . $p_minMatch;
            if (!isset($this->ciMatcher[$l_ciMatcherKey])) {
                $l_virtualConfig = new MatchConfig($this->container);
                $l_virtualConfig->setBits($p_bits)
                    ->setMinMatch($p_minMatch);
                $this->ciMatcher[$l_ciMatcherKey] = new CiMatcher($l_virtualConfig);
                $this->logger->debug('Using virtual object matcher for object matching.');
                $this->output->writeln('Using virtual object matcher for object matching.');
            }
        } else {
            $l_ciMatcherKey = 'main';
            if ($this->ciMatcher[$l_ciMatcherKey] === null) {
                $l_matchConfig = MatchConfig::factory(isys_tenantsettings::get('ocs.object-matching', 1), $this->container);
                $this->ciMatcher[$l_ciMatcherKey] = new CiMatcher($l_matchConfig);
                $this->logger->info('Using object match profile "' . $l_matchConfig->getTitle() . '" for object matching.');
                $this->output->writeln('Using object match profile "' . $l_matchConfig->getTitle() . '" for object matching.');
            }
        }

        /**
         * TypeHinting
         *
         * @var $l_ciMatcher CiMatcher
         */
        $l_ciMatcher = $this->ciMatcher[$l_ciMatcherKey];

        if ($p_objectTitle !== null) {
            // Object title
            $l_matchKeywords[] = new MatchKeyword(ObjectTitle::KEY, $p_objectTitle);

            if (strpos($p_objectTitle, '.') !== false) {
                // Possible FQDN
                $l_matchKeywords[] = new MatchKeyword(Fqdn::KEY, $p_objectTitle);

                // Hostname
                $l_matchKeywords[] = new MatchKeyword(Hostname::KEY, substr($p_objectTitle, 0, strpos($p_objectTitle, '.') + 1));
                $l_matchKeywords[] = new MatchKeyword(Hostname::KEY, $p_objectTitle);
            } else {
                // Hostname
                $l_matchKeywords[] = new MatchKeyword(Hostname::KEY, $p_objectTitle);
            }
        }

        if ($p_serial !== null) {
            $l_matchKeywords[] = new MatchKeyword(ModelSerial::KEY, $p_serial);
        }

        if (count($p_macaddresses)) {
            foreach ($p_macaddresses AS $l_mac) {
                $l_matchKeywords[] = new MatchKeyword(Mac::KEY, $l_mac);
            }
        }

        if ($p_objecttype !== null) {
            $l_matchKeywords[] = new MatchKeyword(ObjectTypeConst::KEY, $p_objecttype);
        }

        if (count($l_matchKeywords)) {
            $l_match = $l_ciMatcher->match($l_matchKeywords);
        }

        if ($l_match !== null) {
            $l_object_id = $l_match->getId();

            if ($l_object_id) {
                $this->logger->info('Found Object "' . $p_objectTitle . '" with ID "' . $l_object_id . '" via object matching.');
                $this->output->writeln('Found Object "' . $p_objectTitle . '" with ID "' . $l_object_id . '".');
                if ($l_match->getMatchCount() > 1) {
                    $l_otherMatches = $l_match->getMatchResult();
                    $this->logger->debug('Other matches found for Object "' . $p_objectTitle . '". Please check the following objects:');
                    foreach ($l_otherMatches AS $l_otherMatch) {
                        $this->logger->debug('- ' . $l_otherMatch->getTitle() . ' (' . $l_otherMatch->getId() . ')');
                    }
                }

                return $l_object_id;
            }
        }

        $this->logger->info('Object "' . $p_objectTitle . '" not found.');
        $this->output->writeln('Object "' . $p_objectTitle . '" not found.');

        return false;
    }
}