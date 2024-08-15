<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;

/**
 * @psalm-api
 *
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type Dirs = list<string>|array<non-empty-string, non-empty-string>
 */
class Engine
{
	use RegistersMethod;

	/** @var Dirs */
	protected readonly array $dirs;

	/**
	 * @psalm-param DirsInput $dirs
	 * @psalm-param list<class-string> $whitelist
	 */
	public function __construct(
		array|string $dirs,
		protected readonly array $defaults = [],
		protected readonly array $whitelist = [],
		protected readonly bool $autoescape = true,
	) {
		$this->dirs = $this->prepareDirs($dirs);
		$this->customMethods = new CustomMethods();
	}

	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template
	{
		if (!preg_match('/^[\w\.\/:_-]+$/u', $path)) {
			throw new UnexpectedValueException('The template path is invalid or empty');
		}

		$template = new Template($this->getFile($path), new Sections(), $this);
		$template->setCustomMethods($this->customMethods);

		return $template;
	}

	/** @psalm-param non-empty-string $path */
	public function render(
		string $path,
		array $context = [],
		?bool $autoescape = null,
	): string {
		if (is_null($autoescape)) {
			// Use the engine's default value if nothing is passed
			$autoescape = $this->autoescape;
		}

		$template = $this->template($path);

		return $template->render(array_merge($this->defaults, $context), $this->whitelist, $autoescape);
	}

	/**
	 * @psalm-param non-empty-string $path
	 *
	 * @psalm-return non-empty-string
	 */
	public function getFile(string $path): string
	{
		[$namespace, $file] = $this->getSegments($path);

		if (!is_Null($namespace)) {
			$dir = $this->dirs[$namespace] ?? '';
			$templatePath = $this->validateFile($this->dirs[$namespace], $file);
		} else {
			$templatePath = false;

			foreach ($this->dirs as $dir) {
				$templatePath = $this->validateFile($dir, $file);

				if ($templatePath !== false) {
					break;
				}
			}
		}

		if (isset($dir) && $templatePath !== false && is_file($templatePath)) {
			if (!str_starts_with($templatePath, (string) $dir)) {
				throw new LookupException(
					'Template resides outside of root directory: ' . $templatePath,
				);
			}

			return $templatePath;
		}

		throw new LookupException('Template not found: ' . $path);
	}

	/** @psalm-param non-empty-string $path */
	public function exists(string $path): bool
	{
		try {
			$this->getFile($path);

			return true;
		} catch (LookupException) {
			return false;
		}
	}

	/**
	 * @psalm-param DirsInput $dirs
	 *
	 * @return Dirs
	 */
	protected function prepareDirs(array|string $dirs): array
	{
		if (is_string($dirs)) {
			$realpath = realpath($dirs);

			return [
				$realpath !== false ?
					$realpath :
					throw new LookupException(
						'Template directory does not exist ' . $dirs,
					),
			];
		}

		return array_map(
			function ($dir) {
				$realpath = realpath($dir);

				return $realpath !== false ?
					$realpath :
					throw new LookupException('Template directory does not exist ' . $dir);
			},
			$dirs,
		);
	}

	/** @return false|non-empty-string */
	protected function validateFile(string $dir, string $file): false|string
	{
		/** @var callable(string):(false|non-empty-string) */
		$rigidRealPath = function (string $path): false|string {
			$realpath = realpath($path);

			if ($realpath !== false) {
				if (strlen($realpath) === 0) {
					return false;
				}

				return $realpath;
			}

			return false;
		};

		$realpath = $rigidRealPath($dir . DIRECTORY_SEPARATOR . $file . '.php');

		if ($realpath !== false) {
			return $realpath;
		}

		return $rigidRealPath($dir . DIRECTORY_SEPARATOR . $file);
	}

	/** @return list{null|non-empty-string, non-empty-string} */
	protected function getSegments(string $path): array
	{
		if (strpos($path, ':') === false) {
			$path = trim($path);
			assert(!empty($path));

			return [null, $path];
		}
		$segments = array_map(fn($s) => trim($s), explode(':', $path));

		if (count($segments) == 2) {
			if (($segments[0] ?? '') && ($segments[1] ?? '')) {
				/** @var list{non-empty-string, non-empty-string} */
				return [$segments[0], $segments[1]];
			}

			throw new LookupException(
				"Invalid template format: '{$path}'. " .
					"Use 'namespace:template/path or template/path'.",
			);
		} else {
			throw new LookupException(
				"Invalid template format: '{$path}'. " .
					"Use 'namespace:template/path or template/path'.",
			);
		}
	}
}
