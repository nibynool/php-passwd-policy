<?php

namespace NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Interfaces\PolicyInterface;

/**
 * Length Policy
 *
 * Ensures that a password meets minimum length requirements
 */
class LengthPolicy implements PolicyInterface
{
    /** @var string $errorMessage Error message to be passed to `sprintf` for formatting when password fails validation */
    protected $errorMessage = 'Minimum length is set to %s, but %s characters were entered.';

    /** @var string $descriptionMessage Description message to be passed to `sprintf` */
    protected $descriptionMessage = 'Must be at least %s characters long.';

    /** @var int $minimumLength The minimum required password length (defaults to 8) */
    protected $minimumLength = 8;

    /**
     * {@inheritDoc}
     */
    public function __construct($config = null)
    {
        if (!is_int($config) && $config !== null) {
            throw new PolicyConfigurationException('Invalid minimum password length provided');
        }

        if ($config !== null) {
            $this->minimumLength = $config;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPolicyDescription()
    {
        return sprintf($this->descriptionMessage, $this->minimumLength);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($password)
    {
        if (strlen($password) < $this->minimumLength) {
            $errorMessage = sprintf($this->errorMessage, $this->minimumLength, strlen($password));
            throw new PasswordValidationException($errorMessage);
        }

        return true;
    }
}
