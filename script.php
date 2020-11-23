<?php
	$command = "git checkout main && git status  && git pull";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "cp .env.master .env";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);
	$command = "php artisan config:cache";
	(exec($command,$return));
	echo implode("\n",$return)."\n";
	unset($return);