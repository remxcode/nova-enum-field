<?php

namespace JoshGaber\NovaUnit\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;
use PHPUnit\Framework\Assert;

class MockFilter
{
    public function __construct(private Filter $filter) {}

    public function assertSelectFilter(): void
    {
        Assert::assertInstanceOf(Filter::class, $this->filter);
        Assert::assertNotInstanceOf(BooleanFilter::class, $this->filter);
    }

    public function assertBooleanFilter(): void
    {
        Assert::assertInstanceOf(BooleanFilter::class, $this->filter);
    }

    public function assertHasOption(mixed $value): void
    {
        Assert::assertContains($value, $this->optionValues());
    }

    public function assertOptionMissing(mixed $value): void
    {
        Assert::assertNotContains($value, $this->optionValues());
    }

    public function apply(string $modelClass, mixed $value): FilterResult
    {
        $query = $modelClass::query();

        return new FilterResult(
            $this->filter->apply(new Request, $query, $value)->get()
        );
    }

    private function optionValues(): array
    {
        return array_values($this->filter->options(new Request));
    }
}

class FilterResult
{
    public function __construct(private Collection $models) {}

    public function assertCount(int $count): void
    {
        Assert::assertCount($count, $this->models);
    }

    public function assertContains(mixed $model): void
    {
        Assert::assertTrue(
            $this->models->contains(fn ($item): bool => $item->is($model)),
            'Failed asserting that the filter result contains the expected model.'
        );
    }

    public function assertMissing(mixed $model): void
    {
        Assert::assertFalse(
            $this->models->contains(fn ($item): bool => $item->is($model)),
            'Failed asserting that the filter result does not contain the model.'
        );
    }
}
