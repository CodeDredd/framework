<?php

namespace Illuminate\Bus;

use Closure;
use Illuminate\Collections\Arr;
use Illuminate\Collections\Collection;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\SerializableClosure;
use Throwable;

class PendingBatch
{
    /**
     * The jobs that belong to the batch.
     *
     * @var \Illuminate\Collections\Collection
     */
    public $jobs;

    /**
     * The batch options.
     *
     * @var array
     */
    public $options = [];

    /**
     * Create a new pending batch instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @param  \Illuminate\Collections\Collection  $jobs
     * @return void
     */
    public function __construct(Container $container, Collection $jobs)
    {
        $this->container = $container;
        $this->jobs = $jobs;
    }

    /**
     * Add a callback to be executed after the batch has finished executing.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function success(Closure $callback)
    {
        $this->options['success'][] = new SerializableClosure($callback);

        return $this;
    }

    /**
     * Get the "success" callbacks that have been registered with the pending batch.
     *
     * @return array
     */
    public function successCallbacks()
    {
        return $this->options['success'] ?? [];
    }

    /**
     * Add a callback to be executed after the first failing job in the batch.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function catch(Closure $callback)
    {
        $this->options['catch'][] = new SerializableClosure($callback);

        return $this;
    }

    /**
     * Get the "catch" callbacks that have been registered with the pending batch.
     *
     * @return array
     */
    public function catchCallbacks()
    {
        return $this->options['catch'] ?? [];
    }

    /**
     * Indicate that the batch should not be cancelled when a job within the batch fails.
     *
     * @param  bool  $allowFailures
     * @return $this
     */
    public function allowFailures($allowFailures = true)
    {
        $this->options['allowFailures'] = $allowFailures;

        return $this;
    }

    /**
     * Determine if the pending batch allows jobs to fail without cancelling the batch.
     *
     * @return bool
     */
    public function allowsFailures()
    {
        return Arr::get($this->options, 'allowFailures', false) === true;
    }

    /**
     * Specify the queue connection that the batched jobs should run on.
     *
     * @param  string  $connection
     * @return $this
     */
    public function onConnection(string $connection)
    {
        $this->options['connection'] = $connection;

        return $this;
    }

    /**
     * Get the connection used by the pending batch.
     *
     * @return string|null
     */
    public function connection()
    {
        return $this->options['connection'] ?? null;
    }

    /**
     * Specify the queue that the batched jobs should run on.
     *
     * @param  string  $connection
     * @return $this
     */
    public function onQueue(string $queue)
    {
        $this->options['queue'] = $queue;

        return $this;
    }

    /**
     * Get the queue used by the pending batch.
     *
     * @return string|null
     */
    public function queue()
    {
        return $this->options['queue'] ?? null;
    }

    /**
     * Dispatch the batch.
     *
     * @return \Illuminate\Support\Batch
     */
    public function dispatch()
    {
        $repository = $this->container->make(BatchRepository::class);

        try {
            $batch = $repository->store($this);

            $batch = $batch->add($this->jobs);
        } catch (Throwable $e) {
            if (isset($batch)) {
                $repository->delete($batch->id);
            }

            throw $e;
        }

        return $batch;
    }
}