<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use DateTime;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use RebelCode\Time\CreateDateTimeZoneCapableTrait;
use RebelCode\Transformers\Exception\CreateCouldNotTransformExceptionCapableTrait;

/**
 * A transformer that transforms timestamps into datetime strings with specific timezones.
 *
 * @since [*next-version*]
 */
class TimestampTimezoneTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use CreateDateTimeZoneCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotTransformExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The date time format to use.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $format;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $format The date time format to use.
     */
    public function __construct($format)
    {
        $this->format = $this->_normalizeString($format);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        try {
            $timezone = $this->_createDateTimeZone($source[0]);
        } catch (Exception $exception) {
            throw $this->_createCouldNotTransformException('Failed to create timezone object');
        }

        try {
            $dateTime = new DateTime('@' . $source[0], $timezone);
        } catch (Exception $exception) {
            throw $this->_createCouldNotTransformException('Failed to create datetime object');
        }

        return $dateTime->format($this->format);
    }
}
