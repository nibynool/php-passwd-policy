<?php

namespace NibyNool\PasswordPolicy\Policies;

use NibyNool\PasswordPolicy\Exceptions\InvalidMergeModeException;
use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;
use NibyNool\PasswordPolicy\Exceptions\PolicyConfigurationException;
use NibyNool\PasswordPolicy\Interfaces\PolicyInterface;
use NibyNool\PasswordPolicy\PasswdPolicy;

/**
 * Character Class Policy
 *
 * Ensures that a password contains enough different character classes
 */
class CharacterClassPolicy implements PolicyInterface
{
    /** @var string $errorLastMatched Error message containing the last matched classes */
    protected $errorLastMatched = 'You entered %s.';

    /** @var string $errorLastMissed Error message containing the last missed classes */
    protected $errorLastMissed = 'You did not enter %s.';

    /** @var string $errorMessageSome Error message password fails validation and some classes are required */
    protected $errorMessageSome = 'Your password must contain %s character types from %s, you provided %s.';

    /** @var string $errorMessageAll Error message password fails validation and all classes are required */
    protected $errorMessageAll = 'Your password must contain at least %s.';

    /** @var string $descMessageSome Description message to be passed to `sprintf` when some classes are required */
    protected $descMessageSome = 'Must contain at least %s of %s.';

    /** @var string $descMessageAll Description message to be passed to `sprintf` when all classes are required */
    protected $descMessageAll = 'Must contain %s.';

    /** @var array $config Configuration for the character class requirements */
    protected $config = [
        'classes' => [
            'uppercase' => true,
            'lowercase' => true,
            'number' => true,
            'symbol' => false,
            'accented' => false
        ],
        'diversity' => 3,
    ];

    /** @var int $requiredClassCount Number of required classes */
    protected $requiredClassCount = 3;

    /** @var string[] $descriptions Description of each character class */
    protected $descriptions = [
        'uppercase' => 'an uppercase letter',
        'lowercase' => 'a lowercase letter',
        'number' => 'a number',
        'symbol' => 'a symbol',
        'accented' => 'an accented character'
    ];

    /** @var string|null $lastMatchedClasses String to be added to show what classes were matched */
    protected $lastMatchedClasses;

    /** @var string|null $lastMissedClasses String to be added to show what classes were missed */
    protected $lastMissedClasses;

    /** @var string[] $classRegEx Regular expressions to match each character class */
    protected $classRegEx = [
        'uppercase' => '/[A-Z]/',
        'lowercase' => '/[a-z]/',
        'number' => '/[0-9]/',
        'symbol' => '/[!"#$%&\'()*+,.:;<=>?@[^_`{|}~‘’“”•–—˜™›œ¡¢£' .
            '¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿Æ×Þßæ÷þ\-\/\] ]/',
        'accented' => '/[šžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜ' .
            'Ýàáâãäåçèéêëìíîïðñòóôõöøùúûüýÿ]/',
    ];

//    /**
//     * @var string[] $classCombinations Array of all possible combinations of character classes
//     *      (this includes emojis for future use)
//     */
//    private $classCombinations = [
//        'A' => '',
//        'a' => '',
//        '0' => '',
//        '#' => '',
//        '😀' => '',
//        'Ÿ' => '',
//        'Aa' => '',
//        'A0' => '',
//        'A#' => '',
//        'A😀' => '',
//        'AŸ' => '',
//        'a0' => '',
//        'a#' => '',
//        'a😀' => '',
//        'aŸ' => '',
//        '0#' => '',
//        '0😀' => '',
//        '0Ÿ' => '',
//        '#😀' => '',
//        '#Ÿ' => '',
//        '😀Ÿ' => '',
//        'Aa0' => '',
//        'Aa#' => '',
//        'Aa😀' => '',
//        'AaŸ' => '',
//        'A0#' => '',
//        'A0😀' => '',
//        'A0Ÿ' => '',
//        'A#😀' => '',
//        'A#Ÿ' => '',
//        'A😀Ÿ' => '',
//        'a0#' => '',
//        'a0😀' => '',
//        'a0Ÿ' => '',
//        'a#😀' => '',
//        'a#Ÿ' => '',
//        'a😀Ÿ' => '',
//        '0#😀' => '',
//        '0#Ÿ' => '',
//        '0😀Ÿ' => '',
//        '#😀Ÿ' => '',
//        'Aa0#' => '',
//        'Aa0😀' => '',
//        'Aa0Ÿ' => '',
//        'Aa#😀' => '',
//        'Aa#Ÿ' => '',
//        'Aa😀Ÿ' => '',
//        'A0#😀' => '',
//        'A0#Ÿ' => '',
//        'A0😀Ÿ' => '',
//        'A#😀Ÿ' => '',
//        'a0#😀' => '',
//        'a0#Ÿ' => '',
//        'a0😀Ÿ' => '',
//        'a#😀Ÿ' => '',
//        '0#😀Ÿ' => '',
//        'Aa0#😀' => '',
//        'Aa0#Ÿ' => '',
//        'Aa0😀Ÿ' => '',
//        'Aa#😀Ÿ' => '',
//        'A0#😀Ÿ' => '',
//        'a0#😀Ÿ' => '',
//        'Aa0#😀Ÿ' => '',
//    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($config = null)
    {
        if ($config === null) {
            return;
        }
        if (!is_string($config) && !is_array($config)) {
            throw new PolicyConfigurationException('Invalid character class policy provided');
        }
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        foreach ($config as $key => $value) {
            if ($key === 'classes') {
                $this->processClassConfig($config[$key]);
            } else {
                if (!array_key_exists($key, $this->config)) {
                    throw new PolicyConfigurationException('Character class policy contains invalid settings');
                }
                $this->config[$key] = $value;
            }
        }
        $this->calculateRequiredClassCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getPolicyDescription()
    {
        if ($this->requiredClassCount === $this->config['diversity']) {
            $descriptionString = $this->generateClassString('and');

            return sprintf($this->descMessageAll, $descriptionString);
        }

        $descriptionString = $this->generateClassString('or');

        return sprintf($this->descMessageSome, $this->config['diversity'], $descriptionString);
    }

    /**
     * Get a string describing the last matches in a failed password validation
     *
     * @return string
     */
    public function getLastMatches()
    {
        return $this->lastMatchedClasses;
    }

    /**
     * Get a string describing the last misses in a failed password validation
     *
     * @return string
     */
    public function getLastMisses()
    {
        return $this->lastMissedClasses;
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($password)
    {
        $requiredClassesFound = 0;
        $foundClasses = [];
        $missingClasses = [];

        foreach ($this->config['classes'] as $class => $required) {
            $match = preg_match($this->classRegEx[$class] . 'u', $password);
            if ($match) {
                if ($required) {
                    $foundClasses[] = $this->descriptions[$class];
                    $requiredClassesFound++;
                }
            } elseif ($required) {
                $missingClasses[] = $this->descriptions[$class];
            }
        }

        $this->lastMatchedClasses = sprintf(
            $this->errorLastMatched,
            $this->joinClassDescriptions($foundClasses, 'and')
        );
        $this->lastMissedClasses = sprintf(
            $this->errorLastMissed,
            $this->joinClassDescriptions($missingClasses, 'or')
        );

        if ($requiredClassesFound < $this->config['diversity']) {
            if ($this->requiredClassCount > $this->config['diversity']) {
                $descriptionString = $this->generateClassString('or');

                throw new PasswordValidationException(
                    sprintf(
                        $this->errorMessageSome,
                        $this->config['diversity'],
                        $descriptionString,
                        $requiredClassesFound
                    )
                );
            }

            $descriptionString = $this->generateClassString('and');

            throw new PasswordValidationException(
                sprintf(
                    $this->errorMessageAll,
                    $descriptionString
                )
            );
        }

        return true;
    }

    /**
     * Recalculate the required class count
     */
    protected function calculateRequiredClassCount()
    {
        $requiredClassCount = 0;

        foreach ($this->config['classes'] as $required) {
            if ($required) {
                $requiredClassCount++;
            }
        }

        $this->requiredClassCount = $requiredClassCount;
    }

    /**
     * Process the character class configuration when instantiating this policy
     *
     * @param bool[] $classConfig
     */
    protected function processClassConfig($classConfig)
    {
        foreach ($classConfig as $class => $required) {
            if (!array_key_exists($class, $this->config['classes']) || !is_bool($required)) {
                throw new PolicyConfigurationException('Character class policy contains invalid settings');
            }
            $this->config['classes'][$class] = $required;
        }
    }

    /**
     * Generate a class description string using the provided final joiner
     *
     * @param string $finalJoiner The string to use to join the last two items
     *
     * @return string
     */
    protected function generateClassString($finalJoiner = 'and')
    {
        $descriptions = [];
        foreach ($this->config['classes'] as $class => $required) {
            if ($required) {
                $descriptions[] = $this->descriptions[$class];
            }
        }

        return $this->joinClassDescriptions($descriptions, $finalJoiner);
    }

    /**
     * Join an array of strings with comma(,) delimiters, using final joiner between the last items
     *
     * @param string[] $strings Array of string to be joined
     * @param string $finalJoiner The joiner for the final element
     *
     * @return string
     */
    protected function joinClassDescriptions($strings, $finalJoiner = 'and')
    {
        $last = array_slice($strings, -1);
        $first = implode(', ', array_slice($strings, 0, -1));
        $both = array_filter(array_merge(array($first), $last), 'strlen');

        return implode(' ' . $finalJoiner . ' ', $both);
    }

    /**
     * {@inheritDoc}
     */
    public static function merge($policyA, $policyB, $mode = PasswdPolicy::MODE_COMBINE)
    {
        switch ($mode) {
            case PasswdPolicy::MODE_COMBINE:
                $aAll = count(array_filter($policyA['classes'])) === $policyA['diversity'];
                $bAll = count(array_filter($policyB['classes'])) === $policyB['diversity'];
                $all = $aAll && $bAll;

                $configuration = null;
                $classes = array_merge(array_filter($policyA['classes']), array_filter($policyB['classes']));
                foreach (array_merge(array_keys($policyA['classes']), array_keys($policyB['classes'])) as $class) {
                    if (!array_key_exists($class, $classes)) {
                        $classes[$class] = false;
                    }
                }

                if ($all) {
                    $diversity = count(array_filter($classes));
                } elseif ($policyA['diversity'] > $policyB['diversity']) {
                    $diversity = $policyA['diversity'];
                } else {
                    $diversity = $policyB['diversity'];
                }

                $configuration = [
                    'classes' => $classes,
                    'diversity' => $diversity,
                ];
                break;
            case PasswdPolicy::MODE_MAXIMUM:
                $configuration = self::strongestOrWeakestPolicy($policyA, $policyB, true);
                break;
            case PasswdPolicy::MODE_MINIMIM:
                $configuration = self::strongestOrWeakestPolicy($policyA, $policyB, false);
                break;
            default:
                throw new InvalidMergeModeException($mode . ' is not a valid merge mode');
        }

        return $configuration;
    }

    /**
     * Return either the strongest or weakest policy configuration
     *
     * @param array $policyA A policy configuration
     * @param array $policyB A policy configuration
     * @param bool $strongest If true return the strongest, otherwise return the weakest
     *
     * @return array
     */
    protected static function strongestOrWeakestPolicy($policyA, $policyB, $strongest = true)
    {
        if ($policyA === $policyB) {
            return $policyA;
        }
        if ($policyA['diversity'] > $policyB['diversity']) {
            return $strongest ? $policyA : $policyB;
        }
        if ($policyA['diversity'] < $policyB['diversity']) {
            return $strongest ? $policyB : $policyA;
        }
        if (count(array_filter($policyA['classes'])) > count(array_filter($policyB['classes']))) {
            return $strongest ? $policyB : $policyA;
        }

        return $strongest ? $policyA : $policyB;
    }
}
