<?php

use idoit\Module\Cmdb\Model\Matcher\Ci\CiMatcher;
use idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname;
use idoit\Module\Cmdb\Model\Matcher\Identifier\IpAddress;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Mac;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;

include_once '../../../../../../../classmap.inc.php';

$array = [
    'hostname' => 'dev.synetics.int',
    'ip'       => '10.10.10.15',
    'serial'   => '1234567890',
    'mac'      => 'ff:ff:ff:ff:ff'
];

$matcher = new CiMatcher(MatchConfig::factory(1, isys_application::instance()->container));

$match = $matcher->match([
    new MatchKeyword(Hostname::KEY, $array['hostname']),
    new MatchKeyword(ModelSerial::KEY, $array['serial']),
    new MatchKeyword(IpAddress::KEY, $array['ip']),
    new MatchKeyword(Mac::KEY, $array['mac']),
]);

print_r($match->getId());

// assert $match->getId() === 1


