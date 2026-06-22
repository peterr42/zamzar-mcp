<?php

use Symfony\Component\Filesystem\Path;

function toAbsolute(string $path): string
{
	if (!Path::isAbsolute($path)) {
		# convert relative path into absolute path
    		$path = dirname(__DIR__) . "/" . $path;
	}
	
	return $path;
}