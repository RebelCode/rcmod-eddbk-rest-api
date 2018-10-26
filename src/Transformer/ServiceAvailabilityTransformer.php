<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use InvalidArgumentException;
use IteratorIterator;
use stdClass;
use Traversable;

/**
 * A transformer that transforms a service's availability.
 *
 * @since [*next-version*]
 */
class ServiceAvailabilityTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer to use for the availability's rules.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $ruleTransformer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $ruleTransformer The transformer to use for the availability's rules.
     */
    public function __construct(TransformerInterface $ruleTransformer)
    {
        $this->ruleTransformer = $ruleTransformer;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        try {
            $iterator = $this->_normalizeIterable($source);
            $rules = $this->_transformRules($iterator, $this->ruleTransformer);
        } catch (InvalidArgumentException $exception) {
            $rules = [];
        }

        return [
            'rules' => $rules,
        ];
    }

    /**
     * Transforms all the rules in a given list.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $rules       The list of rules to transform.
     * @param TransformerInterface       $transformer The transformer to use for each rule.
     *
     * @return array The transformed rules.
     */
    protected function _transformRules($rules, TransformerInterface $transformer)
    {
        $result = [];

        foreach ($rules as $_rule) {
            $result[] = $transformer->transform($_rule);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }
}
