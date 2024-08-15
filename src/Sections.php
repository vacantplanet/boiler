<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use VacantPlanet\Boiler\Exception\LogicException;

class Sections
{
	/** @var array<string, Section> */
	protected array $sections = [];

	protected array $capture = [];
	protected SectionMode $sectionMode = SectionMode::Closed;

	public function begin(string $name): void
	{
		$this->open($name, SectionMode::Assign);
	}

	public function append(string $name): void
	{
		$this->open($name, SectionMode::Append);
	}

	public function prepend(string $name): void
	{
		$this->open($name, SectionMode::Prepend);
	}

	public function end(): void
	{
		$content = ob_get_clean();
		$name = (string) array_pop($this->capture);

		$this->sections[$name] = match ($this->sectionMode) {
			SectionMode::Assign => new Section($content),
			SectionMode::Append => ($this->sections[$name] ?? new Section(''))->append($content),
			SectionMode::Prepend => ($this->sections[$name] ?? new Section(''))->prepend($content),
			SectionMode::Closed => throw new LogicException('No section started'),
		};

		$this->sectionMode = SectionMode::Closed;
	}

	public function get(string $name): string
	{
		return $this->sections[$name]->get();
	}

	public function getOr(string $name, string $default): string
	{
		$section = $this->sections[$name] ?? null;

		if (is_null($section)) {
			return $default;
		}

		if ($section->empty()) {
			$section->setValue($default);
		}

		return $section->get();
	}

	public function has(string $name): bool
	{
		return isset($this->sections[$name]);
	}

	protected function open(string $name, SectionMode $mode): void
	{
		if ($this->sectionMode !== SectionMode::Closed) {
			throw new LogicException('Nested sections are not allowed');
		}

		$this->sectionMode = $mode;
		$this->capture[] = $name;
		ob_start();
	}
}
