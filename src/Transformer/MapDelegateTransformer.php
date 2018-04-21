<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\KeyAwareInterface;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Transformer\Exception\CreateTransformerExceptionCapableTrait;
use stdClass;

/**
 * Implementation of a transformer that delegates transformations using an internal map of transformers.
 *
 * @since [*next-version*]
 */
class MapDelegateTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use DelegateTransformerTrait;

    /* @since [*next-version*] */
    use MapDelegateTransformerTrait;

    /* @since [*next-version*] */
    use DataStoreAwareContainerTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTransformerExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer instance to fallback to, if any.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $fallbackT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|ContainerInterface|stdClass $transformers The transformers, keyed according to the map
     *                                                                    entry that they should transform.
     * @param TransformerInterface|null                     $fallbackT9r  The transformer instance to fallback to if
     *                                                                    no transformer can be used, if any.
     */
    public function __construct($transformers, TransformerInterface $fallbackT9r = null)
    {
        $this->_setDataStore($transformers);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        return $this->_transform($source);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTransformerMap($source)
    {
        return $this->_getDataStore();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTransformerKey($source)
    {
        if (is_string($source) || $source instanceof Stringable) {
            return $source;
        }

        if ($source instanceof KeyAwareInterface) {
            return $source->getKey();
        }

        throw $this->_createOutOfRangeException(
            $this->__('Source is not a string, stringable object or key-aware instance'),
            null,
            null,
            $source
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _onNoDelegateTransformer($source)
    {
        return ($this->fallbackT9r !== null)
            ? $this->fallbackT9r->transform($source)
            : $source;
    }
}
