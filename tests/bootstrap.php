<?php

use React\Promise\PromiseInterface;
use React\EventLoop\LoopInterface;
require_once __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();


        if (func_num_args() > 0) {
            $mock
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->equalTo(func_get_arg(0)));
        } else {
            $mock
                ->expects($this->once())
                ->method('__invoke');
        }

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    protected function expectCallableOnceParameter($type)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf($type));

        return $mock;
    }

    /**
     * @link https://github.com/reactphp/react/blob/master/tests/React/Tests/Socket/TestCase.php (taken from reactphp/react)
     */
    protected function createCallableMock()
    {
        return $this->getMock('CallableStub');
    }

    protected function expectPromiseResolve($promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());

        return $promise;
    }

    protected function expectPromiseReject($promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        return $promise;
    }

    protected function waitForPromise(PromiseInterface $promise, LoopInterface $loop)
    {
        $fulfillment = null;
        $value = null;

        $promise->then(
            function ($result) use (&$fulfillment, &$value) { $fulfillment = true; $value = $result; },
            function ($error) use (&$fulfillment, &$value) { $fulfillment = false; $value = $error; }
        );

        while ($fulfillment === null) {
            $loop->tick();
        }

        if (!$fulfillment) {
            throw $value;
        }

        return $value;
    }
}

class CallableStub
{
    public function __invoke()
    {
    }
}

