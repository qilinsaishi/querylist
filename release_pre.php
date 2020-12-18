<?php

    $command = "git checkout pre && git pull";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "cp .env.pre .env";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "php artisan config:cache";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
