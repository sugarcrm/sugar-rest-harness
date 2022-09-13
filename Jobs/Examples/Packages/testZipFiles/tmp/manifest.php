<?php
$manifest = array(
    'key' => '4151599949',
    'name' => 'Test Package 1',
    'description' => 'Logs "Test Package 1"',
    'author' => 'Mike Andersen',
    'version' => '1.00',
    'is_uninstallable' => true,
    'published_date' => '02/22/2022 14:15:12',
    'type' => 'module',
    'acceptable_sugar_versions' =>
        array(
            //or
            'regex_matches' => array(
                '9.*', //any 9.0 release
                '10.*', //any 10.0 release
                '11.*', //any 11.0 release
                '12.*',
            ),
        ),
    'acceptable_sugar_flavors' =>
        array(
            'PRO',
            'ENT',
            'ULT'
        ),
    'readme' => '',
    'icon' => '',
    'remove_tables' => '',
    'uninstall_before_upgrade' => false,
);

$installdefs = array (
    'id' =>  'Test Package 1',
    'post_execute' =>
        array (
            0 => '<basepath>/logInstall_1.php',
        ),
    'post_uninstall' =>
        array (
            0 => '<basepath>/logUninstall_1.php',
        ),
    'copy' =>
        array (
            0 =>     array (
                'from' =>  '<basepath>/logInstall_1.php',
                'to' =>  'logInstall_1.php'
            ),
        ),
    array (
        1 =>     array (
            'from' =>  '<basepath>/logUninstall_1.php',
            'to' =>  'logUninstall_1.php'
        ),
    ),
);
