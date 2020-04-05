<?php

namespace spec\NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Policies\CommonPolicy;
use PhpSpec\ObjectBehavior;

class CommonPolicySpec extends ObjectBehavior
{
    /** @var null CONFIG_NONE Configuration that doesn't update the policy at all (default set 10000) */
    const CONFIG_NONE = null;

    /** var array CONFIG_ONE_HUNDRED Configuration to set the common password set to 100 */
    const CONFIG_ONE_HUNDRED = 100;

    /** var array CONFIG_FIVE_HUNDRED Configuration to set the common password set to 500 */
    const CONFIG_FIVE_HUNDRED = 500;

    /** var array CONFIG_ONE_THOUSAND Configuration to set the common password set to 1000 */
    const CONFIG_ONE_THOUSAND = 1000;

    /** var array CONFIG_TEN_THOUSAND Configuration to set the common password set to 10000 */
    const CONFIG_TEN_THOUSAND = 10000;

    /** var array CONFIG_HUNDRED_THOUSAND Configuration to set the common password set to 100000 */
    const CONFIG_HUNDRED_THOUSAND = 100000;

    /** var array CONFIG_ONE_MILLION Configuration to set the common password set to 1000000 */
    const CONFIG_ONE_MILLION = 1000000;

    /** @var bool CONFIG_INVALID_POLICY Completely invalid policy */
    const CONFIG_INVALID_POLICY = 10;

    /** @var string[] $lastPassword The last password in each password file  */
    private $lastPassword = [
        '100' => 'matrix',
        '500' => 'redwings',
        '1000' => 'freepass',
        '10000' => 'brady',
        '100000' => '070162',
        '1000000' => 'vjht008',
        'random' => '2iEBsfrBAd7HlC32Y@u5#&xmAHcZpZPT',
    ];

    /** @var PolicyConfigurationException $invalidPolicy Exception to be thrown on invalid policy */
    private $invalidPolicy;

    /**
     * Configure this set of tests
     */
    public function let()
    {
        $this->invalidPolicy = new PolicyConfigurationException('Invalid common password set provided');
    }

    /**
     * Verify that the class exists
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType(CommonPolicy::class);
    }

    /**
     * Verify that the class can be constructed with a variety of configurations
     */
    public function it_can_be_configured()
    {
        $this->beConstructedWith(self::CONFIG_ONE_HUNDRED);
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

        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['100000'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['10000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 100 password set
     */
    public function it_works_with_set_100()
    {
        $this->beConstructedWith(self::CONFIG_ONE_HUNDRED);
        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['100000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['10000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['1000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['500'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 500 password set
     */
    public function it_works_with_set_500()
    {
        $this->beConstructedWith(self::CONFIG_FIVE_HUNDRED);
        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['100000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['10000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['1000'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 1000 password set
     */
    public function it_works_with_set_1000()
    {
        $this->beConstructedWith(self::CONFIG_ONE_THOUSAND);
        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['100000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['10000'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 10000 password set
     */
    public function it_works_with_set_10000()
    {
        $this->beConstructedWith(self::CONFIG_TEN_THOUSAND);
        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->validatePassword($this->lastPassword['100000'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['10000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 100000 password set
     */
    public function it_works_with_set_100000()
    {
        $this->beConstructedWith(self::CONFIG_HUNDRED_THOUSAND);
        $this->validatePassword($this->lastPassword['1000000'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['10000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }

    /**
     * Run the test suite for 1000000 password set
     */
    public function it_works_with_set_1000000()
    {
        $this->beConstructedWith(self::CONFIG_ONE_MILLION);
        $this->validatePassword($this->lastPassword['random'])->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['10000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['1000']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['500']]);
        $this->shouldThrow(new PasswordValidationException('A common password was entered.'))->during('validatePassword', [$this->lastPassword['100']]);
    }
}
