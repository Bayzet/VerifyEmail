<?php

use lib\Verify;

require_once __DIR__."/bootsatrap.php";

$verify = new Verify();

$verify->verifyEmail($argv[1]);