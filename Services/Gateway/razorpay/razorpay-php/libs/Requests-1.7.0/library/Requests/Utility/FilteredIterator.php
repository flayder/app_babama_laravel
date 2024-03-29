<?php

declare(strict_types=1);
/**
 * Iterator for arrays requiring filtered values.
 */

/**
 * Iterator for arrays requiring filtered values.
 */
class Requests_Utility_FilteredIterator extends ArrayIterator
{
    /**
     * Callback to run as a filter.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Create a new iterator.
     *
     * @param array    $data
     * @param callable $callback Callback to be called on each value
     */
    public function __construct($data, $callback)
    {
        parent::__construct($data);

        $this->callback = $callback;
    }

    /**
     * Get the current item's value after filtering.
     *
     * @return string
     */
    public function current()
    {
        $value = parent::current();
        $value = call_user_func($this->callback, $value);

        return $value;
    }
}
