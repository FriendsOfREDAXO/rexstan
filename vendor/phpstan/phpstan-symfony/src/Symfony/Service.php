<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class Service implements ServiceDefinition
{

	private string $id;

	private ?string $class = null;

	private bool $public;

	private bool $synthetic;

	private ?string $alias = null;

	/** @var ServiceTag[] */
	private array $tags;

	/** @param ServiceTag[] $tags */
	public function __construct(
		string $id,
		?string $class,
		bool $public,
		bool $synthetic,
		?string $alias,
		array $tags = []
	)
	{
		$this->id = $id;
		$this->class = $class;
		$this->public = $public;
		$this->synthetic = $synthetic;
		$this->alias = $alias;
		$this->tags = $tags;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getClass(): ?string
	{
		return $this->class;
	}

	public function isPublic(): bool
	{
		return $this->public;
	}

	public function isSynthetic(): bool
	{
		return $this->synthetic;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function getTags(): array
	{
		return $this->tags;
	}

}
