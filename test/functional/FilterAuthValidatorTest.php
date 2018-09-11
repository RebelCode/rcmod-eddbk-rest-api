<?php

namespace RebelCode\EddBookings\RestApi\Auth\FuncTest;

use Dhii\Event\EventFactoryInterface;
use Dhii\Validation\Exception\ValidationFailedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\RestApi\Auth\FilterAuthValidator as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class FilterAuthValidatorTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\EddBookings\RestApi\Auth\FilterAuthValidator';

    /**
     * Creates a mock event manager instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|EventManagerInterface
     */
    protected function createEventManager()
    {
        return $this->getMockForAbstractClass('Psr\EventManager\EventManagerInterface');
    }

    /**
     * Creates a mock event factory instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|EventFactoryInterface
     */
    protected function createEventFactory()
    {
        return $this->getMockForAbstractClass('Dhii\Event\EventFactoryInterface');
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = new TestSubject(
            $this->createEventManager(),
            $this->createEventFactory(),
            'event',
            'param_key'
        );

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME,
            $subject,
            'Could not create a valid instance'
        );
    }

    /**
     * Tests whether the constructor correctly initializes the instance.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $eventManager = $this->createEventManager();
        $eventFactory = $this->createEventFactory();
        $event        = uniqid('event-');
        $key          = uniqid('key-');
        $default      = (bool) rand(0, 1);

        $subject = new TestSubject(
            $eventManager,
            $eventFactory,
            $event,
            $key,
            $default
        );
        $reflect = $this->reflect($subject);

        $this->assertSame($eventManager, $reflect->_getEventManager());
        $this->assertSame($eventFactory, $reflect->_getEventFactory());
        $this->assertEquals($key, $reflect->paramKey);
        $this->assertEquals($default, $reflect->paramDefault);
    }

    /**
     * Tests the validation to assert whether it succeeds when the event param is true.
     *
     * @since [*next-version*]
     */
    public function testValidateSuccess()
    {
        $eventManager = $this->createEventManager();
        $eventFactory = $this->createEventFactory();
        $event        = uniqid('event-');
        $key          = uniqid('key-');
        $default      = (bool) rand(0, 1);

        $subject = new TestSubject(
            $eventManager,
            $eventFactory,
            $event,
            $key,
            $default
        );

        $event = $this->getMockBuilder('Psr\EventManager\EventInterface')
                      ->setMethods(['getParam'])
                      ->getMockForAbstractClass();

        $event->expects($this->once())
              ->method('getParam')
              ->with($key)
              ->willReturn(true);

        $eventFactory->expects($this->once())
                     ->method('make')
                     ->willReturn($event);

        $eventManager->expects($this->once())
                     ->method('trigger')
                     ->willReturn($event);

        $subject->validate(null);
    }

    /**
     * Tests the validation to assert whether it succeeds when the event param is false.
     *
     * @since [*next-version*]
     */
    public function testValidateFail()
    {
        $eventManager = $this->createEventManager();
        $eventFactory = $this->createEventFactory();
        $event        = uniqid('event-');
        $key          = uniqid('key-');
        $default      = (bool) rand(0, 1);

        $subject = new TestSubject(
            $eventManager,
            $eventFactory,
            $event,
            $key,
            $default
        );

        $event = $this->getMockBuilder('Psr\EventManager\EventInterface')
                      ->setMethods(['getParam'])
                      ->getMockForAbstractClass();

        $event->expects($this->once())
              ->method('getParam')
              ->with($key)
              ->willReturn(false);

        $eventFactory->expects($this->once())
                     ->method('make')
                     ->willReturn($event);

        $eventManager->expects($this->once())
                     ->method('trigger')
                     ->willReturn($event);
        try {
            $subject->validate(null);
            $this->fail('Expected validation failure exception was not thrown.');
        } catch (ValidationFailedException $exception) {
            $errors = $exception->getValidationErrors();
            $count  = count($errors);

            $this->assertTrue($count > 0, 'Thrown validation failure exception has no error messsages');
        }
    }
}
