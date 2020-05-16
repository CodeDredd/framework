<?php

namespace Illuminate\Http\Client;

use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\Assert as PHPUnit;

class Testing
{
    use Macroable;

    /**
     * The factory instance.
     *
     * @var \Illuminate\Http\Client\Factory|null
     */
    protected $factory;

    /**
     * Create a new HTTP Testing instance.
     *
     * @param  \Illuminate\Http\Client\Factory|null  $factory
     * @return void
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * Assert that a request / response pair was not recorded matching a given truth test.
     *
     * @param  callable  $callback
     * @return void
     */
    public function assertNotSent($callback)
    {
        PHPUnit::assertFalse(
            $this->factory->recorded($callback)->count() > 0,
            'Unexpected request was recorded.'
        );
    }

    /**
     * Assert that no request / response pair was recorded.
     *
     * @return void
     */
    public function assertNothingSent()
    {
        PHPUnit::assertEmpty(
            $this->factory->getRecorded(),
            'Requests were recorded.'
        );
    }

    /**
     * Assert that every created response sequence is empty.
     *
     * @return void
     */
    public function assertSequencesAreEmpty()
    {
        foreach ($this->factory->getResponseSequences() as $responseSequence) {
            PHPUnit::assertTrue(
                $responseSequence->isEmpty(),
                'Not all response sequences are empty.'
            );
        }
    }

    /**
     * Assert that a request / response pair was recorded matching a given truth test.
     *
     * @param  callable  $callback
     * @return void
     */
    public function assertSent($callback)
    {
        PHPUnit::assertTrue(
            $this->factory->recorded($callback)->count() > 0,
            'An expected request was not recorded.'
        );
    }

    /**
     * Assert how many requests have been recorded.
     *
     * @param $count
     * @return void
     */
    public function assertSentCount($count)
    {
        PHPUnit::assertCount($count, $this->factory->getRecorded());
    }
}
