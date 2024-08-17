<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use VacantPlanet\Boiler\Exception\LookupException;

class TemplatePath
{
	protected bool $isValid = false;
	protected string $path = '';
	protected string $error = '';

	/**
	 * @param non-empty-string $dir
	 * @param non-empty-string $file
	 */
	public function __construct(protected string $dir, string $file)
	{
		if (strlen(trim($dir)) === 0) {
			$this->error = 'Template directory must not be an empty string';

			return;
		}

		$dir = realpath($this->dir);

		if ($dir === false) {
			$this->error = "Template directory not found: {$dir}";

			return;
		}

		assert(!empty($dir));

		$this->dir = $dir;
		$this->validateFile($dir, $file);
	}

	/** @return non-empty-string */
	public function path(): string
	{
		if (!$this->isValid || $this->path === '') {
			throw new LookupException("Trying to access path of invalid template: `{$this->path}`");
		}

		return $this->path;
	}

	public function error(): string
	{
		return $this->error;
	}

	public function isValid(): bool
	{
		return $this->isValid;
	}

	protected function validateFile(string $dir, string $file): void
	{
		$fullPath = $dir . DIRECTORY_SEPARATOR . $file;

		if (str_ends_with($fullPath, '.php')) {
			$this->validatePath($fullPath);

			return;
		}

		$this->validatePath("{$fullPath}.php");

		if (!$this->isValid) {
			$this->validatePath($fullPath);
		}

		if ($this->isValid) {
			if (!str_starts_with($this->path, $this->dir)) {
				$this->error = "Template resides outside of root directory ({$this->dir}): {$this->path}";
				$this->isValid = false;
			}
		}
	}

	/** @param non-empty-string $path */
	protected function validatePath(string $path): void
	{
		$realpath = realpath($path);

		if ($realpath === false || strlen($realpath) === 0) {
			$this->error = "Template not found: {$path}";

			return;
		}

		assert(!empty($realpath));

		$this->isValid = true;
		$this->path = $realpath;
	}
}
