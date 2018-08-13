<?php

namespace RebelCode\EddBookings\RestApi\Auth;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\CreateValidationFailedExceptionCapableTrait;
use Dhii\Validation\ValidatorInterface;

/**
 * An authorization validator that validates whether a user is an administrator.
 *
 * @since [*next-version*]
 */
class UserIsAdminAuthValidator implements ValidatorInterface
{
    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateValidationFailedExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

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
    public function validate($userId)
    {
        $errors = [];

        if (!user_can($userId, 'manage_options')) {
            $errors[] = 'User does not have admin capability "manage_options"';
        }

        if ($userId === 0) {
            $errors[] = 'User is not logged in (ID 0)';
        }

        if (!empty($errors)) {
            throw $this->_createValidationFailedException(
                $this->__('User is not an administrator user'), null, null, $this, $userId, $errors
            );
        }
    }
}
