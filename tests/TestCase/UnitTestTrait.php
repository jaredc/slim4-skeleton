<?php

namespace App\Test\TestCase;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

/**
 * Unit test.
 */
trait UnitTestTrait
{
    use ContainerTestTrait;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->bootApp();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        $this->shutdownApp();
    }

    /**
     * Add mock to container.
     *
     * @param string $class The class or interface
     *
     * @return void
     */
    protected function registerMock(string $class): void
    {
        $this->getApp()->add($class, $this->createMockObject($class));
    }

    /**
     * Mocking an interface.
     *
     * @param string $class The interface / class name
     *
     * @return MockObject The mock
     */
    protected function createMockObject(string $class): MockObject
    {
        $reflection = new ReflectionClass($class);
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            $methods[] = $method->name;
        }

        return $this->getMockBuilder($class)->disableOriginalConstructor()->setMethods($methods)->getMock();
    }

    /**
     * Create a mocked class method.
     *
     * @param array|callable $method The class and method
     *
     * @return InvocationMocker The mocked method
     */
    protected function mockMethod($method): InvocationMocker
    {
        /** @var MockObject $mock */
        $mock = $this->getApp()->get($method[0] ?? '');

        return $mock->method($method[1] ?? '');
    }
}
