<?php

namespace App\Traits;

use ReflectionClass;

/**
 * Resets Filters
 *
 * The Reset button of a Livewire list: the class names its search, filter and sort properties in `FILTERS`; they go back
 * to their declared defaults. Pair with `<x-reset-filters />` in the card toolbar.
 *
 * @package App\Traits
 */
trait ResetsFilters
{
    /**
     * Back to a clean list: search, filters and sort at their defaults, first page
     *
     * @return void
     */
    public function resetFilters(): void
    {
        $this->reset(...static::FILTERS);
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Whether any search, filter or sort differs from its default (the Reset button shows only then)
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        $defaults = (new ReflectionClass($this))->getDefaultProperties();

        foreach (static::FILTERS as $property) {
            if ($this->{$property} !== $defaults[$property]) {
                return true;
            }
        }

        return false;
    }
}
