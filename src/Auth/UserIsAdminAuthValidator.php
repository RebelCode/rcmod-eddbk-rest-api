<?php

namespace RebelCode\EddBookings\RestApi\Auth;

use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\AbstractValidatorBase;
use Dhii\Validation\ValidatorInterface;
use Exception as RootException;

/**
 * An authorization validator that validates whether a user is an administrator.
 *
 * @since [*next-version*]
 */
class UserIsAdminAuthValidator extends AbstractValidatorBase implements ValidatorInterface
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * The WordPress capability that determines if a user is an admin.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $adminCapability;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $adminCapability The WordPress capability that determines if a user is an admin.
     */
    public function __construct($adminCapability)
    {
        $this->adminCapability = $adminCapability;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getValidationErrors($userId)
    {
        // If an admin, return without errors
        if ($this->_isUserAdmin($userId)) {
            return [];
        }

        // Otherwise, create error list
        $errors = [
            $this->__('User is not an administrator')
        ];

        // Check for additional reasons

        // If user is not logged in, add an addition error
        if ($userId === 0) {
            $errors[] = $this->__('Not a user or not logged in (ID: 0)');
        }

        return $errors;
    }

    /**
     * Checks if a WordPress user is an admin, by ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $userId The ID of the user to check.
     *
     * @return bool True if the user is a WordPress admin, false if not.
     */
    protected function _isUserAdmin($userId)
    {
        return $this->_wpUserCan($userId, $this->_normalizeString($this->adminCapability));
    }

    /**
     * Checks if a WordPress user, by ID, has a specific capability.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $userId     The ID of the user to check.
     * @param string|Stringable     $capability The capability to check for.
     *
     * @return bool True if the user has the capability, false if not.
     */
    protected function _wpUserCan($userId, $capability)
    {
        return user_can($userId, $capability);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createValidationFailedException(
        $message = null,
        $code = null,
        RootException $previous = null,
        ValidatorInterface $validator = null,
        $subject = null,
        $validationErrors = null
    ) {
        return parent::_createValidationFailedException(
            null, $code, $previous, $validator, $subject, $validationErrors
        );
    }
}
