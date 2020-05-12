<?php

namespace NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\InvalidDictionaryLanguageException;
use NibyNool\PasswordPolicy\Exceptions\InvalidMergeModeException;
use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Interfaces\PolicyInterface;
use NibyNool\PasswordPolicy\PasswdPolicy;

/**
 * Dictionary Policy
 *
 * Ensures that a password is not in the dictionary
 */
class DictionaryPolicy implements PolicyInterface
{
    /** @var string DEFAULT_DICTIONARY The default dictionary to use */
    const DEFAULT_DICTIONARY = 'EN';

    /** @var string $errorMessage Error message to be passed to `sprintf` when password fails validation */
    protected $errorMessage = 'A dictionary based password was entered.';

    /** @var string $descriptionMessage Description message to be passed to `sprintf` */
    protected $descriptionMessage = 'Must not be based on a dictionary word.';

    /** @var int[] $dictionaries The dictionaries to use for spell checking */
    protected $dictionaries = [];

    /**
     * {@inheritDoc}
     */
    public function __construct($config = self::DEFAULT_DICTIONARY)
    {
        if (!extension_loaded('pspell')) {
            trigger_error('Dictionary Policy aborted as Pspell is not available', E_USER_WARNING);
            return;
        }
        if ($config === null) {
            $config = self::DEFAULT_DICTIONARY;
        }
        if (is_string($config)) {
            $config = [$config];
        }
        if (!is_array($config)) {
            throw new PolicyConfigurationException('Invalid dictionary provided');
        }

        foreach ($config as $language) {
            $this->dictionaries[$language] = \pspell_new($language);
            if (!$this->dictionaries[$language]) {
                throw new InvalidDictionaryLanguageException('Could not load a dictionary for ' . $language);
            }
        }

        if (!count($this->dictionaries)) {
            throw new PolicyConfigurationException('No dictionaries provided');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPolicyDescription()
    {
        return sprintf($this->descriptionMessage);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($password)
    {
        if (!extension_loaded('pspell')) {
            trigger_error('Dictionary Policy not enforced as Pspell is not available', E_USER_WARNING);
            return true;
        }

        foreach (array_keys($this->dictionaries) as $language) {
            $this->checkDictionary($language, $password);
        }

        return true;
    }

    /**
     * Look for the password in a dictionary and error if it is found
     *
     * @param string $language The dictionary language
     * @param string $password The password to check
     *
     * @return bool
     */
    protected function checkDictionary($language, $password)
    {
        if (!array_key_exists($language, $this->dictionaries) || $this->dictionaries[$language] === false) {
            throw new InvalidDictionaryLanguageException('A dictionary for ' . $language . ' has not been loaded');
        }
        if (pspell_check($this->dictionaries[$language], $password)) {
            throw new PasswordValidationException($this->errorMessage);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function merge($policyA, $policyB, $mode = PasswdPolicy::MODE_COMBINE)
    {
        if (is_string($policyA)) {
            $policyA = [$policyA];
        }
        if (is_string($policyB)) {
            $policyB = [$policyB];
        }

        $configuration = null;
        switch ($mode) {
            case PasswdPolicy::MODE_COMBINE:
                $configuration = array_unique(array_merge($policyA, $policyB), SORT_REGULAR);
                break;
            case PasswdPolicy::MODE_MAXIMUM:
                $configuration = count($policyA) >= count($policyB) ? $policyA : $policyB;
                break;
            case PasswdPolicy::MODE_MINIMIM:
                $configuration = count($policyA) < count($policyB) ? $policyA : $policyB;
                break;
            default:
                throw new InvalidMergeModeException($mode . ' is not a valid merge mode');
        }

        return $configuration;
    }
}
