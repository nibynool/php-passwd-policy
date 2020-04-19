<?php

namespace NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\InvalidMergeModeException;
use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Interfaces\PolicyInterface;
use NibyNool\PasswordPolicy\PasswdPolicy;

/**
 * Common Policy
 *
 * Ensures that a password is not commonly used
 */
class CommonPolicy implements PolicyInterface
{
    /** @var string $errorMessage Error message to be passed to `sprintf` when password fails validation */
    protected $errorMessage = 'A common password was entered.';

    /** @var string $descriptionMessage Description message to be passed to `sprintf` */
    protected $descriptionMessage = 'Must not be in the top %s common passwords.';

    /** @var int $passwordSet The size of the password set (default to 10000) */
    protected $passwordSet = 10000;

    /** @var string $passwordSetLocation Directory containing the password sets */
    protected $passwordSetLocation = __DIR__ . '/../Libraries/danielmiessler/SecLists/Passwords/Common-Credentials/';

    /** @var string $passwordSetFileNames Format of the filename containing the password set */
    protected $passwordSetFileNames = '10-million-password-list-top-%s.txt';

    /** @var string $passwordSetFile Fully qualified filename for the password set being used */
    protected $passwordSetFile;

    /**
     * {@inheritDoc}
     */
    public function __construct($config = null)
    {
        if (!is_int($config) && $config !== null) {
            throw new PolicyConfigurationException('Invalid common password set provided');
        }

        if ($config !== null) {
            $this->passwordSet = $config;
        }

        $this->passwordSetFile = $this->passwordSetLocation . sprintf($this->passwordSetFileNames, $this->passwordSet);
        if (!file_exists($this->passwordSetFile)) {
            throw new PolicyConfigurationException('Invalid common password set provided');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPolicyDescription()
    {
        return sprintf($this->descriptionMessage, $this->passwordSet);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($password)
    {
        $validPassword = true;

        $fileHandle = fopen($this->passwordSetFile, 'rb');
        while (!feof($fileHandle)) {
            if ($password === trim(fgets($fileHandle))) {
                $validPassword = false;
            }
        }
        fclose($fileHandle);

        if (!$validPassword) {
            throw new PasswordValidationException($this->errorMessage);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function merge($policyA, $policyB, $mode = PasswdPolicy::MODE_COMBINE)
    {
        $configuration = null;
        switch ($mode) {
            case PasswdPolicy::MODE_COMBINE:
            case PasswdPolicy::MODE_MAXIMUM:
                $configuration = $policyA >= $policyB ? $policyA : $policyB;
                break;
            case PasswdPolicy::MODE_MINIMIM:
                $configuration = $policyA < $policyB ? $policyA : $policyB;
                break;
            default:
                throw new InvalidMergeModeException($mode . ' is not a valid merge mode');
        }

        return $configuration;
    }
}
