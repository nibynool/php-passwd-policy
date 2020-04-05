<?php

namespace spec\NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Policies\LengthPolicy;
use PhpSpec\ObjectBehavior;

class LengthPolicySpec extends ObjectBehavior
{
    /** @var null CONFIG_NONE Configuration that doesn't update the policy at all (default length 8) */
    const CONFIG_NONE = null;

    /** var array CONFIG_SIX Configuration to set the minimum length to 6 */
    const CONFIG_SIX = 6;

    /** var array CONFIG_ZERO Configuration to set the minimum length to 0 */
    const CONFIG_ZERO = 0;

    /** @var bool CONFIG_INVALID_POLICY Completely invalid policy */
    const CONFIG_INVALID_POLICY = true;

    /** @var PolicyConfigurationException $invalidPolicy Exception to be thrown on invalid policy */
    private $invalidPolicy;

    /**
     * Configure this set of tests
     */
    public function let()
    {
        $this->invalidPolicy = new PolicyConfigurationException('Invalid minimum password length provided');
    }

    /**
     * Verify that the class exists
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType(LengthPolicy::class);
    }

    /**
     * Verify that the class can be constructed with a variety of configurations
     */
    public function it_can_be_configured()
    {
        $this->beConstructedWith(self::CONFIG_SIX);
        $this->getWrappedObject();
    }

    /**
     * Verify the correct exception is thrown when the constructor fails due to an invalid configuration
     */
    public function it_rejects_invalid_config()
    {
        $this->beConstructedWith(self::CONFIG_INVALID_POLICY);
        $this->shouldThrow($this->invalidPolicy)->duringInstantiation();
    }

    /**
     * Run the test suite for no configuration
     */
    public function it_works_with_no_configuration()
    {
        $this->beConstructedWith(self::CONFIG_NONE);
        $empty = '';
        $short = '1234567';
        $exact = '12345678';
        $long = '123456789';

        $this->validatePassword($long)->shouldReturn(true);
        $this->validatePassword($exact)->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('Minimum length is set to 8, but 7 characters were entered.'))->during('validatePassword', [$short]);
        $this->shouldThrow(new PasswordValidationException('Minimum length is set to 8, but 0 characters were entered.'))->during('validatePassword', [$empty]);
    }

    /**
     * Run the test suite for basic configuration
     */
    public function it_works_with_requirement_of_six()
    {
        $this->beConstructedWith(self::CONFIG_SIX);
        $empty = '';
        $short = '12345';
        $exact = '123456';
        $long = '1234567';

        $this->validatePassword($long)->shouldReturn(true);
        $this->validatePassword($exact)->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('Minimum length is set to 6, but 5 characters were entered.'))->during('validatePassword', [$short]);
        $this->shouldThrow(new PasswordValidationException('Minimum length is set to 6, but 0 characters were entered.'))->during('validatePassword', [$empty]);
    }

    /**
     * Run the test suite for full configuration
     */
    public function it_works_with_requirement_of_zero()
    {
        $this->beConstructedWith(self::CONFIG_ZERO);
        $empty = '';
        $long = '1';

        $this->validatePassword($long)->shouldReturn(true);
        $this->validatePassword($empty)->shouldReturn(true);
    }
}
