<?php declare(strict_types = 1);

namespace PHPStan\Rules\PHPUnit;

use PHPStan\Parser\Parser;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\FileTypeMapper;
use PHPUnit\Framework\TestCase;
use function dirname;
use function explode;
use function file_get_contents;
use function is_file;
use function json_decode;

class DataProviderHelperFactory
{

	private ReflectionProvider $reflectionProvider;

	private FileTypeMapper $fileTypeMapper;

	private Parser $parser;

	public function __construct(
		ReflectionProvider $reflectionProvider,
		FileTypeMapper $fileTypeMapper,
		Parser $parser
	)
	{
		$this->reflectionProvider = $reflectionProvider;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->parser = $parser;
	}

	public function create(): DataProviderHelper
	{
		$phpUnit10OrNewer = false;
		if ($this->reflectionProvider->hasClass(TestCase::class)) {
			$testCase = $this->reflectionProvider->getClass(TestCase::class);
			$file = $testCase->getFileName();
			if ($file !== null) {
				$phpUnitRoot = dirname($file, 3);
				$phpUnitComposer = $phpUnitRoot . '/composer.json';
				if (is_file($phpUnitComposer)) {
					$composerJson = @file_get_contents($phpUnitComposer);
					if ($composerJson !== false) {
						$json = json_decode($composerJson, true);
						$version = $json['extra']['branch-alias']['dev-main'] ?? null;
						if ($version !== null) {
							$majorVersion = (int) explode('.', $version)[0];
							if ($majorVersion >= 10) {
								$phpUnit10OrNewer = true;
							}
						}
					}
				}
			}
		}

		return new DataProviderHelper($this->reflectionProvider, $this->fileTypeMapper, $this->parser, $phpUnit10OrNewer);
	}

}
