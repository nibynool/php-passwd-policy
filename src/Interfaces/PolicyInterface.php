<?php

namespace NibyNool\PasswordPolicy\Interfaces;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;

/**
 * Policy Interface
 *
 * The basic requirements for creating a new policy that can be enforced on both frontend (JS) and backend (PHP)
 */
interface PolicyInterface
{
    /**
     * Class constructor that accepts a configuration
     *
     * @param mixed $config
     *
     * @throws PolicyConfigurationException
     */
    public function __construct($config = null);

    /**
     * Get a string describing (in human readable terms) the password policy.
     *
     * @return string
     */
    public function getPolicyDescription();

    /**
     * Validate the supplied password against the policy.
     *
     * If the password is valid return true, otherwise throw a PasswordValidationExceptions
     *
     * @param string $password The password to be validated
     *
     * @return bool
     * @throws PasswordValidationException
     */
    public function validatePassword($password);

    /**
     * Merge two configurations and return a configuration set based on the mode
     *
     * @param mixed $policyA A configuration
     * @param mixed $policyB A configuration
     * @param string $mode The merge mode
     *
     * @return mixed
     */
    public static function merge($policyA, $policyB, $mode);
}
