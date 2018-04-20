<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use EDD_DB;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use RebelCode\EddBookings\RestApi\Resource\ServiceResource;
use stdClass;
use WP_Post;
use WP_Query;

/**
 * The API controller for services.
 *
 * @since [*next-version*]
 */
class ServicesController implements ControllerInterface
{
    /* @since [*next-version*] */
    use CreateResourceCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ResourceFactoryInterface $resourceFactory The resource factory.
     */
    public function __construct(ResourceFactoryInterface $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($params = [])
    {
        $queryArgs = [
            'post_ID'   => $this->_containerHas($params, 'id')
                ? $this->_containerGet($params, 'id')
                : null,
            'post_type' => 'download',
        ];

        $query = new WP_Query($queryArgs);

        return array_map([$this, '_createResourceFromWpPost'], $query->posts);
    }

    /**
     * Creates a service resource instance from the given post.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $post The resource data.
     *
     * @return ResourceInterface
     */
    protected function _createResourceFromWpPost($post)
    {
        return $this->_createResource($post->to_array());
    }
}
