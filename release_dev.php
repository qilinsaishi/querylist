<?php

    $command = "git checkout dev && git pull";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "cp .env.dev .env";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "php artisan config:cache";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
