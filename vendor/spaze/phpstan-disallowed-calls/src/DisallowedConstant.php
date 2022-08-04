<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed;

class DisallowedConstant
{

	/** @var string */
	private $constant;

	/** @var string|null */
	private $message;

	/** @var string[] */
	private $allowIn;

	/** @var string */
	private $errorIdentifier;


	/**
	 * DisallowedCall constructor.
	 *
	 * @param string $constant
	 * @param string|null $message
	 * @param string[] $allowIn
	 * @param string $errorIdentifier
	 */
	public function __construct(string $constant, ?string $message, array $allowIn, string $errorIdentifier)
	{
		$this->constant = ltrim($constant, '\\');
		$this->message = $message;
		$this->allowIn = $allowIn;
		$this->errorIdentifier = $errorIdentifier;
	}


	public function getConstant(): string
	{
		return $this->constant;
	}


	public function getMessage(): string
	{
		return $this->message ?? 'because reasons';
	}


	/**
	 * @return string[]
	 */
	public function getAllowIn(): array
	{
		return $this->allowIn;
	}


	public function getErrorIdentifier(): string
	{
		return $this->errorIdentifier;
	}

}
