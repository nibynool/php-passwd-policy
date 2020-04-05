<?php

namespace NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Interfaces\PolicyInterface;

/**
 * Dictionary Policy
 *
 * Ensures that a password is not in the dictionary
 */
class DictionaryPolicy implements PolicyInterface
{
    /** @var string $errorMessage Error message to be passed to `sprintf` for formatting when password fails validation */
    protected $errorMessage = 'A dictionary based password was entered.';

    /** @var string $descriptionMessage Description message to be passed to `sprintf` */
    protected $descriptionMessage = 'Must not be based on a dictionary word.';

    /** @var int $dictionary The dictionary to use for spell checking */
    protected $dictionary;

    /**
     * {@inheritDoc}
     */
    public function __construct($config = 'EN')
    {
        if (!is_string($config)) {
            throw new PolicyConfigurationException('Invalid dictionary provided');
        }

        $this->dictionary = pspell_new($config);

        if (!$this->dictionary) {
            throw new PolicyConfigurationException('Invalid dictionary provided');
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
        if (!pspell_check($this->dictionary, $password)) {
            throw new PasswordValidationException($this->errorMessage);
        }

        return true;
    }
}
