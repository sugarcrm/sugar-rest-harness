#!/usr/bin/php
<?php
require_once("lib/Harness.php");

$harness = new SugarRestHarness\Harness();
print($harness->exec());
print("\n");
