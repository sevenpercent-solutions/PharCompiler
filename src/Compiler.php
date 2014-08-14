<?php

namespace SevenPercent;

use FilesystemIterator;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Compiler {

	public function compile($name, array $directories) {

		$phar = new Phar("$name.phar", 0, "$name.phar");
		$phar->setSignatureAlgorithm(Phar::SHA1);
		$phar->startBuffering();

		foreach ($directories as $directory) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
				if ($file->getExtension() === 'php') {
					$this->_addFile($phar, $file);
				}
			}
		}
		$phar->addFromString("bin/$name", preg_replace('{^#!/usr/bin/env php\s*}', '', file_get_contents(__DIR__ . "/../bin/$name")));

		$phar->setStub("#!/usr/bin/env php\n<?php require'phar://$name.phar/bin/$name';__halt_compiler();");

		$phar->stopBuffering();
		unset($phar);
	}

	private function _addFile(Phar $phar, SplFileInfo $file) {
		$path = strtr(str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');
		$content = file_get_contents($file);
		$phar->addFromString($path, $content);
	}
}
