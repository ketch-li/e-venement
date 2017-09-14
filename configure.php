<?php

// Copy file.ext.template to file.ext
function copyConfigFiles($dir)
{
	if($dir) {
		foreach (new \DirectoryIterator($dir) as $file)
		{
		    if (!$file->isDot() && !$file->isDir())
		    {
		    	$name = explode('.', $file->getPathName());

		    	if(array_pop($name) == 'template') {
		    		$name = implode('.', $name);

		    		copy($file->getPathName(), $name);
		    		echo 'Created ' . $name . "\n";
				}
		    }
		}
	}
}

// Browse modules to find config directories
function browseModules() {
	foreach (new \DirectoryIterator('./apps') as $dir)
	{
		if(isDir($dir)) {
			copyConfigFiles(getConfigDir($dir->getPathName()));

			foreach(new \DirectoryIterator($dir->getPathName()) as $subDir) 
			{
				if(isDir($subDir) && $subDir->getFileName() == 'modules') {
					copyConfigFiles(getConfigDir($subDir->getPathName()));
				}
			}
		}
	}
}

// Find config directory
function getConfigDir($dir) {
	foreach(new \DirectoryIterator($dir) as $subDir)
	{
		if($subDir->getFileName() == 'config') {
			return $subDir->getPathName();
		}
	}
}

// Check directory
function isDir($dir) {
	return !$dir->isDot() && $dir->isDir();
}

// Main config files
copyConfigFiles(getConfigDir('.'));
//Modules
browseModules();

?>
