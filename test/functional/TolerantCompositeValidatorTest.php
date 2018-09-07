<?php

namespace RebelCode\EddBookings\RestApi\Auth\FuncTest;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Exception as RootException;
use IteratorAggregate;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\EddBookings\RestApi\Auth\TolerantCompositeValidator as TestSubject;
use Traversable;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractCompositeValidatorBaseTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\EddBookings\RestApi\Auth\TolerantCompositeValidator';

    /**
     * Creates a new instance of the test subject.
     *
     * @since 0.1
     *
     * @param string[]|null $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
                     ->setConstructorArgs([$message])
                     ->getMock();

        return $mock;
    }

    /**
     * Creates a new validator.
     *
     * @since [*next-version*]
     *
     * @param string[]|null $methods The methods to mock.
     *
     * @return ValidatorInterface|MockObject The new validator.
     */
    public function createValidator($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('Dhii\Validation\ValidatorInterface')
                     ->setMethods($methods)
                     ->getMock();

        return $mock;
    }

    /**
     * Creates an iterator aggregate.
     *
     * @since [*next-version*]
     *
     * @param Traversable $iterator The iterator to aggregate.
     *
     * @return IteratorAggregate|MockObject The new iterator aggregate.
     */
    public function createIteratorAggregate(Traversable $iterator)
    {
        $mock = $this->getMockBuilder('IteratorAggregate')
                     ->setMethods(['getIterator'])
                     ->getMock();

        $mock->method('getIterator')
             ->will($this->returnValue($iterator));

        return $mock;
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return object The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->mock($paddingClassName);
    }

    /**
     * Creates a new validation failed exception.
     *
     * @since [*next-version*]
     *
     * @return ValidationFailedExceptionInterface|RootException|MockObject
     */
    public function createValidationFailedException($message = null, $code = null, $previous = null, $validator = null, $subject = null, $errors = null)
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Dhii\Validation\Exception\ValidationFailedExceptionInterface'])
                     ->getValidator($this->returnValue($validator))
                     ->getValidationErrors(function () use ($errors) {return $errors;})
                     ->getSubject(function () use ($subject) {return $subject;})
                     ->getMessage($this->returnValue($message))
                     ->getCode($this->returnValue($code))
                     ->getPrevious($this->returnValue($previous))
                     ->new();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = new TestSubject([]);

        $this->assertInstanceOf(static::TEST_SUBJECT_CLASSNAME, $subject, 'Could not create a valid instance');
    }

    /**
     * Tests whether the constructor correctly initializes the instance.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $validator1 = $this->createValidator(['validate']);
        $validator2 = $this->createValidator(['validate']);

        $subject = new TestSubject([$validator1, $validator2]);
        $reflect = $this->reflect($subject);

        $expected = [$validator1, $validator2];
        $actual   = $reflect->_getChildValidators();

        $this->assertEquals($expected, $actual, 'List of validators does not match constructor argument.');
    }

    /**
     * Tests whether the validation is successful when the first child validator fails but the second succeeds.
     *
     * @since [*next-version*]
     */
    public function testValidateFirstFail()
    {
        $val = uniqid('subject');

        $validator1 = $this->createValidator(['validate']);
        $validator2 = $this->createValidator(['validate']);

        $message = uniqid('failure-message');
        $exception = $this->createValidationFailedException('message', null, null, $validator1, $val, [$message]);

        $validator1->expects($this->exactly(1))
                   ->method('validate')
                   ->with($val)
                   ->will($this->throwException($exception));
        $validator2->expects($this->exactly(1))
                   ->method('validate')
                   ->with($val);

        $subject = new TestSubject([$validator1, $validator2]);

        $subject->validate($val);
    }

    /**
     * Tests whether the validation is successful when the first child validator succeeds.
     *
     * Also asserts that the second validator is never invoked.
     *
     * @since [*next-version*]
     */
    public function testValidateSecondFail()
    {
        $val = uniqid('subject');

        $validator1 = $this->createValidator(['validate']);
        $validator2 = $this->createValidator(['validate']);

        $validator1->expects($this->exactly(1))
                   ->method('validate')
                   ->with($val);
        $validator2->expects($this->never())
                   ->method('validate')
                   ->with($val);

        $subject = new TestSubject([$validator1, $validator2]);

        $subject->validate($val);
    }

    /**
     * Tests whether the correct validation errors are included in the exception when all child validators fail.
     *
     * @since [*next-version*]
     */
    public function testValidateAllFail()
    {
        $val = uniqid('subject');
        $message1 = uniqid('failure-message');
        $message2 = uniqid('failure-message');
        $validator1 = $this->createValidator(['validate']);
        $validator2 = $this->createValidator(['validate']);
        $exception1 = $this->createValidationFailedException('message', null, null, $validator1, $val, [$message1]);
        $exception2 = $this->createValidationFailedException('message', null, null, $validator1, $val, [$message2]);

        $validator1->expects($this->exactly(1))
                   ->method('validate')
                   ->with($val)
                   ->will($this->throwException($exception1));
        $validator2->expects($this->exactly(1))
                   ->method('validate')
                   ->with($val)
                   ->will($this->throwException($exception2));

        $subject = new TestSubject([$validator1, $validator2]);

        $this->setExpectedException('Dhii\Validation\Exception\ValidationFailedExceptionInterface');

        try {
            $subject->validate($val);
        } catch (ValidationFailedExceptionInterface $e) {
            $reasons = [];
            foreach ($e->getValidationErrors() as $_error) {
                $reasons[] = $_error;
            }

            $this->assertEquals([$message1, $message2], $reasons, 'Incorrect reason list produced');
            throw $e;
        }
    }
}
