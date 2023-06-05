<?php
declare(strict_types = 1);

namespace Spaze\PHPStan\Rules\Disallowed;

use Spaze\PHPStan\Rules\Disallowed\Allowed\AllowedConfig;

class DisallowedAttribute implements DisallowedWithParams
{

	/** @var string */
	private $attribute;

	/** @var list<string> */
	private $excludes;

	/** @var string|null */
	private $message;

	/** @var AllowedConfig */
	private $allowedConfig;

	/** @var string|null */
	private $errorIdentifier;

	/** @var string|null */
	private $errorTip;


	/**
	 * @param string $attribute
	 * @param list<string> $excludes
	 * @param string|null $message
	 * @param AllowedConfig $allowedConfig
	 * @param string|null $errorIdentifier
	 * @param string|null $errorTip
	 */
	public function __construct(
		string $attribute,
		array $excludes,
		?string $message,
		AllowedConfig $allowedConfig,
		?string $errorIdentifier,
		?string $errorTip
	) {
		$this->attribute = $attribute;
		$this->excludes = $excludes;
		$this->message = $message;
		$this->allowedConfig = $allowedConfig;
		$this->errorIdentifier = $errorIdentifier;
		$this->errorTip = $errorTip;
	}


	public function getAttribute(): string
	{
		return $this->attribute;
	}


	/**
	 * @return list<string>
	 */
	public function getExcludes(): array
	{
		return $this->excludes;
	}


	public function getMessage(): string
	{
		return $this->message ?? 'because reasons';
	}


	/** @inheritDoc */
	public function getAllowIn(): array
	{
		return $this->allowedConfig->getAllowIn();
	}


	/** @inheritDoc */
	public function getAllowExceptIn(): array
	{
		return $this->allowedConfig->getAllowExceptIn();
	}


	public function getAllowInCalls(): array
	{
		return $this->allowedConfig->getAllowInCalls();
	}


	public function getAllowExceptInCalls(): array
	{
		return $this->allowedConfig->getAllowExceptInCalls();
	}


	public function getAllowParamsInAllowed(): array
	{
		return $this->allowedConfig->getAllowParamsInAllowed();
	}


	public function getAllowParamsAnywhere(): array
	{
		return $this->allowedConfig->getAllowParamsAnywhere();
	}


	public function getAllowExceptParamsInAllowed(): array
	{
		return $this->allowedConfig->getAllowExceptParamsInAllowed();
	}


	public function getAllowExceptParams(): array
	{
		return $this->allowedConfig->getAllowExceptParams();
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
