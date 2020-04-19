<?php

namespace NibyNool\PasswordPolicy;

use NibyNool\PasswordPolicy\Policies;

class PasswdPolicy
{
    /** @var string MODE_COMBINE Mode name for combining policies*/
    const MODE_COMBINE = 'combine';

    /** @var string MODE_MINIMUM Mode name for selecting the least restrictive password requirement */
    const MODE_MINIMIM = 'minimum';

    /** @var string MODE_MAXIMUM Mode name for selecting the most restrictive password requirement */
    const MODE_MAXIMUM = 'maximum';

    /** @var array $policies Array of policies to validate a password*/
    public $policies = [];

    /**
     * Class constructor that accepts an array of policy configurations
     *
     * @param mixed $config
     */
    public function __construct($config = null)
    {
        if ($config !== null) {
            foreach ($config as $policy => $configuration) {
                $this->addConfig($policy, $configuration);
            }
        }
    }

    /**
     * Add a configuration policy, merging with existing policies if required
     *
     * @param string $policy A policy name that maps to a policy class
     * @param mixed $configuration A policy configuration
     * @param string $mode Mode used to merge config if a previous config exists for the policy
     *
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     *
     * @return self
     */
    public function addConfig($policy, $configuration, $mode = self::MODE_COMBINE)
    {
        try {
            $configuration = json_decode($configuration, true, 512);
        } catch (\JsonException $exception) {
        }

        $policyClass = 'Policies\\' . $policy;

        if (array_key_exists($policy, $this->policies) && method_exists($policyClass, 'merge')) {
            $configuration = $policyClass::merge($this->policies[$policyClass], $configuration, $mode);
        }

        $this->policies[$policy] = new $policyClass($configuration);

        return $this;
    }

    /**
     * Remove a policy and its configuration
     *
     * @param string $policy Policy name to be removed
     *
     * @return self
     */
    public function removeConfig($policy)
    {
        unset($this->policies[$policy]);

        return $this;
    }

    /**
     * Validate a password against all policies that have been configured
     *
     * @param string $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        foreach ($this->policies as $policy) {
            $policy->validatePassword($password);
        }

        return true;
    }

    /**
     * Create an instance of this class with an array of settings from a database
     *
     * This function assumes a specific format for the DB results.
     *
     * @see ../README.md
     *
     * @param array $dbResultSet The results from the database
     * @param string $mode The mode to use when combining policies
     *
     * @return self
     */
    public static function init($dbResultSet, $mode = self::MODE_COMBINE)
    {
        $policyInstance = new self();
        foreach ($dbResultSet as $row) {
            foreach ($row as $column => $config) {
                $policyClass = 'Policies\\' . $column;
                if (class_exists($policyClass)) {
                    $policyInstance->addConfig($column, $config, $mode);
                }
            }
        }

        return $policyInstance;
    }
}
