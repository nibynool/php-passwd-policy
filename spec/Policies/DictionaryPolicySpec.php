<?php

namespace spec\NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\PasswdPolicy;
use NibyNool\PasswordPolicy\Policies\DictionaryPolicy;
use PhpSpec\ObjectBehavior;

class DictionaryPolicySpec extends ObjectBehavior
{
    /** @var null CONFIG_NONE Configuration that doesn't update the policy at all (default EN) */
    const CONFIG_NONE = null;

    /** @var string CONFIG_ENGLISH Configuration to set the dictionary to English */
    const CONFIG_ENGLISH = 'EN';

    /** @var string CONFIG_FRENCH Configuration to set the dictionary to French */
    const CONFIG_FRENCH = 'FR';

    /** @var string[] CONFIG_GERMAN Configuration to set the dictionary to German in an array */
    const CONFIG_GERMAN = [
        'DE',
    ];

    /** @var string[] CONFIG_ARRAY Configuration to set the dictionary to English and French */
    const CONFIG_ARRAY = [
        'EN',
        'FR',
    ];

    /** @var string[] CONFIG_MERGED Configuration to set the dictionary to English and German */
    const CONFIG_MERGED = [
        'EN',
        'DE',
    ];

    /** @var bool CONFIG_INVALID_POLICY Completely invalid policy */
    const CONFIG_INVALID_POLICY = false;

    /** @var PolicyConfigurationException $invalidPolicy Exception to be thrown on invalid policy */
    private $invalidPolicy;

    /**
     * Configure this set of tests
     */
    public function let()
    {
        $this->invalidPolicy = new PolicyConfigurationException('Invalid dictionary provided');
    }

    /**
     * Verify that the class exists
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType(DictionaryPolicy::class);
    }

    /**
     * Verify that the class can be constructed with a variety of configurations
     */
    public function it_can_be_configured()
    {
        $this->beConstructedWith(self::CONFIG_ENGLISH);
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

        $this->validatePassword('3#ee1CN7Xhd%*pwGZgzNQneTPynAyduf')->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A dictionary based password was entered.'))->during('validatePassword', ['hello']);
    }

    /**
     * Run the test suite for no configuration
     */
    public function it_works_in_english()
    {
        $this->beConstructedWith(self::CONFIG_ENGLISH);

        $this->validatePassword('3#ee1CN7Xhd%*pwGZgzNQneTPynAyduf')->shouldReturn(true);
        $this->shouldThrow(new PasswordValidationException('A dictionary based password was entered.'))->during('validatePassword', ['hello']);
    }

    /**
     * Test merging two configurations in combine mode
     */
    public function it_merges_in_combine_mode() {
        self::merge(self::CONFIG_ENGLISH, self::CONFIG_FRENCH, PasswdPolicy::MODE_COMBINE)->shouldReturn(self::CONFIG_ARRAY);
        self::merge(self::CONFIG_ENGLISH, self::CONFIG_GERMAN, PasswdPolicy::MODE_COMBINE)->shouldReturn(self::CONFIG_MERGED);
    }

    /**
     * Test merging two configurations in maximum mode
     */
    public function it_merges_in_maximum_mode() {
        self::merge(self::CONFIG_ARRAY, self::CONFIG_GERMAN, PasswdPolicy::MODE_MAXIMUM)->shouldReturn(self::CONFIG_ARRAY);
    }

    /**
     * Test merging two configurations in minimum mode
     */
    public function it_merges_in_minimum_mode() {
        self::merge(self::CONFIG_ARRAY, self::CONFIG_GERMAN, PasswdPolicy::MODE_MINIMIM)->shouldReturn(self::CONFIG_GERMAN);
    }
}
