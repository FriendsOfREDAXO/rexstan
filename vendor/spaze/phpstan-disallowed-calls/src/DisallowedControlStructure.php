<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed;

use Spaze\PHPStan\Rules\Disallowed\Exceptions\NotImplementedYetException;

class DisallowedControlStructure implements Disallowed
{

	private string $controlStructure;

	private ?string $message;

	/** @var list<string> */
	private array $allowIn;

	/** @var list<string> */
	private array $allowExceptIn;

	private ?string $errorIdentifier;

	private ?string $errorTip;


	/**
	 * @param string $controlStructure
	 * @param string|null $message
	 * @param list<string> $allowIn
	 * @param list<string> $allowExceptIn
	 * @param string|null $errorIdentifier
	 * @param string|null $errorTip
	 */
	public function __construct(
		string $controlStructure,
		?string $message,
		array $allowIn,
		array $allowExceptIn,
		?string $errorIdentifier,
		?string $errorTip
	) {
		$this->controlStructure = $controlStructure;
		$this->message = $message;
		$this->allowIn = $allowIn;
		$this->allowExceptIn = $allowExceptIn;
		$this->errorIdentifier = $errorIdentifier;
		$this->errorTip = $errorTip;
	}


	public function getControlStructure(): string
	{
		return $this->controlStructure;
	}


	public function getMessage(): ?string
	{
		return $this->message;
	}


	/** @inheritDoc */
	public function getAllowIn(): array
	{
		return $this->allowIn;
	}


	/** @inheritDoc */
	public function getAllowExceptIn(): array
	{
		return $this->allowExceptIn;
	}


	public function getAllowInCalls(): array
	{
		throw new NotImplementedYetException();
	}


	public function getAllowExceptInCalls(): array
	{
		throw new NotImplementedYetException();
	}


	public function getErrorIdentifier(): ?string
	{
		return $this->errorIdentifier;
	}


	public function getErrorTip(): ?string
	{
		return $this->errorTip;
	}

}