#!/usr/bin/php
<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
require_once("lib/Harness.php");

$harness = new SugarRestHarness\Harness();
print($harness->exec());
print("\n");
