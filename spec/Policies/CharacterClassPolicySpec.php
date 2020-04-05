<?php

namespace spec\NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Policies\CharacterClassPolicy;
use PhpSpec\ObjectBehavior;

class CharacterClassPolicySpec extends ObjectBehavior
{
    /** @var null CONFIG_NONE Configuration that doesn't update the policy at all */
    const CONFIG_NONE = null;

    /** var array CONFIG_BASIC The base configuration used by the policy */
    const CONFIG_BASIC = [
        'classes' => [
            'uppercase' => false,
            'lowercase' => true,
            'number' => false,
            'symbol' => false,
            'accented' => false,
        ],
        'diversity' => 1,
    ];

    /** var array CONFIG_FULL The most stringent configuration used by the policy */
    const CONFIG_FULL = [
        'classes' => [
            'uppercase' => true,
            'lowercase' => true,
            'number' => true,
            'symbol' => true,
            'accented' => true,
        ],
        'diversity' => 5,
    ];

    /** var array CONFIG_PARTIAL Configuration that only updates part of the policy */
    const CONFIG_PARTIAL = [
        'classes' => [
            'symbol' => true,
        ],
    ];

    /** @var string CONFIG_JSON A JSON configuration for testing */
    const CONFIG_JSON = '{"classes":{"symbol":true},"diversity":4}';

    /** @var bool CONFIG_INVALID_POLICY Completely invalid policy */
    const CONFIG_INVALID_POLICY = true;

    /** @var array CONFIG_INVALID_CHAR_CLASS Configuration containing an invalid character class */
    const CONFIG_INVALID_CHAR_CLASS = [
        'classes' => [
            'ascii' => true
        ],
    ];

    /** @var array CONFIG_INVALID_OPTION Configuration containing an invalid config option */
    const CONFIG_INVALID_OPTION = [
        'required' => true
    ];

    /** @var PolicyConfigurationException $invalidPolicy Exception to be thrown on invalid policy */
    private $invalidPolicy;

    /** @var PolicyConfigurationException $invalidConfig Exception to be thrown on invalid configuration option */
    private $invalidConfig;

    /** @var array $configs Array of configuration sets to test*/
    public $configs = [
        'none' => self::CONFIG_NONE,
        'basic' => self::CONFIG_BASIC,
        'full' => self::CONFIG_FULL,
        'partial' => self::CONFIG_PARTIAL,
        'json' => self::CONFIG_JSON
    ];

    /** @var string[] $descriptions Array of expected descriptions */
    public $descriptions = [
        'none' => 'Must contain an uppercase letter, a lowercase letter and a number.',
        'basic' => 'Must contain a lowercase letter.',
        'full' => 'Must contain an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
        'partial' => 'Must contain at least 3 of an uppercase letter, a lowercase letter, a number or a symbol.',
        'json' => 'Must contain an uppercase letter, a lowercase letter, a number and a symbol.',
    ];

    /** @var array $goodPasswords Array of arrays of good passwords */
    public $goodPasswords = [
        'none' => [
            'Aa0',
            'Aa0#',
            'Aa0Ÿ',
            'Aa0#Ÿ',
        ],
        'basic' => [
            'a',
            'Aa',
            'a0',
            'a#',
            'aŸ',
            'Aa0',
            'Aa#',
            'AaŸ',
            'a0#',
            'a0Ÿ',
            'a#Ÿ',
            'Aa0#',
            'Aa0Ÿ',
            'Aa#Ÿ',
            'a0#Ÿ',
            'Aa0#Ÿ',
        ],
        'full' => [
            'Aa0#Ÿ',
        ],
        'partial' => [
            'Aa0',
            'Aa#',
            'A0#',
            'a0#',
            'Aa0#',
            'Aa0Ÿ',
            'Aa#Ÿ',
            'A0#Ÿ',
            'a0#Ÿ',
            'Aa0#Ÿ',
        ],
        'json' => [
            'Aa0#',
            'Aa0#Ÿ',
        ],
    ];

    /** @var array $badPasswords Array of arrays of bad passwords and expected errors */
    public $badPasswords = [
        'none' => [
            'A' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '0' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'Aa' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A0' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'AŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a0' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'aŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '0#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'Aa#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'AaŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A0#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a0#' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            '0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'Aa#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'A0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
            'a0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter and a number.',
        ],
        'basic' => [
            'A' => 'Your password must contain at least a lowercase letter.',
            '0' => 'Your password must contain at least a lowercase letter.',
            '#' => 'Your password must contain at least a lowercase letter.',
            'Ÿ' => 'Your password must contain at least a lowercase letter.',
            'A0' => 'Your password must contain at least a lowercase letter.',
            'A#' => 'Your password must contain at least a lowercase letter.',
            'AŸ' => 'Your password must contain at least a lowercase letter.',
            '0#' => 'Your password must contain at least a lowercase letter.',
            '0Ÿ' => 'Your password must contain at least a lowercase letter.',
            '#Ÿ' => 'Your password must contain at least a lowercase letter.',
            'A0#' => 'Your password must contain at least a lowercase letter.',
            'A0Ÿ' => 'Your password must contain at least a lowercase letter.',
            'A#Ÿ' => 'Your password must contain at least a lowercase letter.',
            '0#Ÿ' => 'Your password must contain at least a lowercase letter.',
            'A0#Ÿ' => 'Your password must contain at least a lowercase letter.',
        ],
        'full' => [
            'A' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'AŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'aŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'AaŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            '0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'Aa#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'A0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
            'a0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number, a symbol and an accented character.',
        ],
        'partial' => [
            'A' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            'a' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            '0' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            '#' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            'Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 0.',
            'Aa' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'A0' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'A#' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'AŸ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            'a0' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'a#' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'aŸ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            '0#' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            '0Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            '#Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 1.',
            'AaŸ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'A0Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'A#Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'a0Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            'a#Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
            '0#Ÿ' => 'Your password must contain 3 character types from an uppercase letter, a lowercase letter, a number or a symbol, you provided 2.',
        ],
        'json' => [
            'A' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Aa' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'AŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'aŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Aa0' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Aa#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'AaŸ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a0#' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            '0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Aa0Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'Aa#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'A0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
            'a0#Ÿ' => 'Your password must contain at least an uppercase letter, a lowercase letter, a number and a symbol.',
        ],
    ];

    /**
     * Configure this set of tests
     */
    public function let()
    {
        $this->invalidPolicy = new PolicyConfigurationException('Invalid character class policy provided');
        $this->invalidConfig = new PolicyConfigurationException('Character class policy contains invalid settings');
    }

    /**
     * Verify that the class exists
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType(CharacterClassPolicy::class);
    }

    /**
     * Verify that the class can be constructed with a variety of configurations
     */
    public function it_can_be_configured()
    {
        $this->beConstructedWith(self::CONFIG_BASIC);
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
     * Verify the correct exception is thrown when the constructor fails due to invalid options in the config
     */
    public function it_rejects_invalid_options()
    {
        $this->beConstructedWith(self::CONFIG_INVALID_OPTION);
        $this->shouldThrow($this->invalidConfig)->duringInstantiation();
    }

    /**
     * Verify the correct exception is thrown when the constructor fails due to invalid character classes
     */
    public function it_rejects_invalid_char_class()
    {
        $this->beConstructedWith(self::CONFIG_INVALID_CHAR_CLASS);
        $this->shouldThrow($this->invalidConfig)->duringInstantiation();
    }

    /**
     * Run the test suite for no configuration
     */
    public function it_works_with_no_configuration()
    {
        $this->test_process('none');
    }

    /**
     * Run the test suite for basic configuration
     */
    public function it_works_with_basic_configuration()
    {
        $this->test_process('basic');
    }

    /**
     * Run the test suite for full configuration
     */
    public function it_works_with_full_configuration()
    {
        $this->test_process('full');
    }

    /**
     * Run the test suite for partial configuration
     */
    public function it_works_with_partial_configuration()
    {
        $this->test_process('partial');
    }

    /**
     * Run the test suite for json configuration
     */
    public function it_works_with_json_configuration()
    {
        $this->test_process('json');
    }

    public function it_detects_missed_character_classes()
    {
        $this->beConstructedWith($this->configs['partial']);
        $this->shouldThrow(new PasswordValidationException($this->badPasswords['partial']['Aa']))->during('validatePassword', ['Aa']);
        $this->getLastMisses()->shouldReturn('You did not enter a number or a symbol.');
    }

    public function it_detects_matched_character_classes()
    {
        $this->beConstructedWith($this->configs['partial']);
        $this->shouldThrow(new PasswordValidationException($this->badPasswords['partial']['Aa']))->during('validatePassword', ['Aa']);
        $this->getLastMatches()->shouldReturn('You entered an uppercase letter and a lowercase letter.');
    }

    /**
     * Runs all the tests for a distinct configuration
     *
     * @param string $type The configuration set to use
     */
    private function test_process($type)
    {
        $this->beConstructedWith($this->configs[$type]);
        $this->getPolicyDescription()->shouldReturn($this->descriptions[$type]);
        $this->test_good_passwords($this->goodPasswords[$type]);
        $this->test_bad_passwords($this->badPasswords[$type]);
    }

    /**
     * Iterate through an array of good passwords
     *
     * @param string[] $passwords Array of good passwords to test
     */
    private function test_good_passwords($passwords)
    {
        foreach ($passwords as $password) {
            $this->validatePassword($password)->shouldReturn(true);
        }
    }

    /**
     * Iterate through an array of bad passwords
     *
     * @param string[] $passwords Array of bad passwords and the associated error message
     */
    private function test_bad_passwords($passwords) {
        foreach ($passwords as $password => $error) {
            $expectedException = new PasswordValidationException($error);
            $this->shouldThrow($expectedException)->during('validatePassword', [$password]);
        }
    }
}
