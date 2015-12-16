<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
$config = array(
    'user_name' => 'admin',              // sugar user_name
    'password' => 'asdf',             // sugar user pass
    'base_url' => 'http://hoth',      // protocol + domain/i.p. address
    'install_path' => '/sc/ult',      // path to sugar install.
    'rest_dir' => '/rest',            // rest directory (usually /rest)
    'rest_version_dir' => '/v10',     // rest version, currently /v10
    'jobs_dir' => getcwd(),           // path to directory containing Jobs/
    // user_agent_string - modify at your own risk.
    'user_agent_string' => 'Mozilla/5.0 (Linux; U; Android 4.3; en-us; SAMSUNG-SGH-I747 Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 IBM/SalesConnect 1.3.0.61',
    'client_platform' => '',          // 'mobile' for mobile
    'client_name' => '',              // 'nomad' for mobile, empty for web browser
    'client_id' => 'sugar',           // 'sc_web' for salesconnect, 'sugar' for core
    'client_app_version' => '',       // ex: '2.4.2' for nomad version -- may change in the future as mobile version changes
    'mode' => 'dev',                  // dev or test - 'test' means 'testing a sugar install', not testing the harness.
    'devFormatter' => array(
        'single' => 'TwoColumn',      // for a single job, use TwoColumn formatting (shows all data)
        'multiple' => 'Concise'       // for multiple jobs, use Concise formatting (just shows success/failure and errors)
    ),
    'testFormatter' => array(
        'single' => 'Concise',        // for a single job, use Concise formatting (just shows success/failure and errors)
        'multiple' => 'Dots'          // for multiple jobs, use Dots formatting (each job is representing by a '.' or an 'F')
    ),
);
