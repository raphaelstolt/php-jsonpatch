<?php

require 'vendor/autoload.php';

// Json Patch integration test files revision
// Update this from time to time
$revision = 'c91f7e8';
$repository = 'https://raw.githubusercontent.com/json-patch/json-patch-tests';

$specTests = file_get_contents($repository.'/'.$revision.'/spec_tests.json');
$tests = file_get_contents($repository.'/'.$revision.'/tests.json');

file_put_contents('tests/integration/specs.json', $specTests);
file_put_contents('tests/integration/tests.json', $tests);
