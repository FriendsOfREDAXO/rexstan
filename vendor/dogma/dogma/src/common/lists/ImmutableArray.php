<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use ArrayAccess;
use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;
use function array_chunk;
use function array_column;
use function array_combine;
use function array_count_values;
use function array_diff;
use function array_diff_assoc;
use function array_diff_key;
use function array_diff_uassoc;
use function array_diff_ukey;
use function array_filter;
use function array_flip;
use function array_intersect;
use function array_intersect_assoc;
use function array_intersect_key;
use function array_intersect_uassoc;
use function array_intersect_ukey;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pad;
use function array_product;
use function array_rand;
use function array_reduce;
use function array_replace;
use function array_reverse;
use function array_search;
use function array_slice;
use function array_splice;
use function array_sum;
use function array_udiff;
use function array_udiff_assoc;
use function array_udiff_uassoc;
use function array_uintersect;
use function array_uintersect_assoc;
use function array_uintersect_uassoc;
use function array_unique;
use function array_unshift;
use function array_values;
use function arsort;
use function asort;
use function count;
use function end;
use function in_array;
use function is_array;
use function iterator_to_array;
use function krsort;
use function ksort;
use function max;
use function min;
use function range;
use function reset;
use function shuffle;
use function uasort;
use function uksort;

/**
 * @implements ArrayAccess<mixed, mixed>
 * @implements IteratorAggregate<mixed, mixed>
 */
class ImmutableArray implements Countable, IteratorAggregate, ArrayAccess
{
    use StrictBehaviorMixin;
    use ImmutableArrayAccessMixin;

    /** @internal */
    private const PRESERVE_KEYS = true;

    /** @var mixed[] */
    private $items;

    /**
     * @param mixed[] $items
     */
    final public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param mixed ...$values
     * @return static
     */
    public static function create(...$values): self
    {
        return new static($values);
    }

    /**
     * @param iterable|mixed[] $that
     * @return self
     */
    public static function from(iterable $that): self
    {
        return new static(self::convertToArray($that));
    }

    /**
     * @param iterable|mixed[] $keys
     * @param iterable|mixed[] $values
     * @return self
     */
    public static function combine(iterable $keys, iterable $values): self
    {
        $keys = self::convertToArray($keys);
        $values = self::convertToArray($values);

        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException('Count of keys and values must be the same.');
        }

        return new static(array_combine($keys, $values));
    }

    /**
     * @param iterable|mixed[] $that
     * @return mixed[]
     */
    private static function convertToArray(iterable $that): array
    {
        if (is_array($that)) {
            return $that;
        } elseif ($that instanceof self) {
            return $that->toArray();
        } elseif ($that instanceof Traversable) {
            return iterator_to_array($that);
        } else {
            throw new InvalidTypeException(Type::PHP_ARRAY, $that);
        }
    }

    /**
     * @param int|string $start
     * @param int|string $end
     * @param int $step >= 1
     * @return static
     */
    public static function range($start, $end, int $step = 1): self
    {
        Check::min($step, 1);

        return new static(range($start, $end, $step));
    }

    /**
     * @return Iterator<mixed>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    public function getReverseIterator(): ReverseArrayIterator
    {
        return new ReverseArrayIterator($this->items);
    }

    public function keys(): self
    {
        return new static(array_keys($this->toArray()));
    }

    public function values(): self
    {
        return new static(array_values($this->toArray()));
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return mixed[]
     */
    public function toArrayRecursive(): array
    {
        $res = $this->toArray();
        foreach ($res as $key => $value) {
            $res[$key] = ($value instanceof self ? $value->toArray() : $value);
        }

        return $res;
    }

    /**
     * @return mixed
     */
    public function randomKey()
    {
        return array_rand($this->toArray());
    }

    /**
     * @return mixed
     */
    public function randomValue()
    {
        return $this[$this->randomKey()];
    }

    /**
     * @deprecated will be removed. use Call::with() instead
     */
    public function doForEach(callable $function): void
    {
        foreach ($this as $value) {
            $function($value);
        }
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    public function isNotEmpty(): bool
    {
        return count($this->items) !== 0;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool
    {
        return in_array($value, $this->toArray(), true);
    }

    /**
     * @param mixed[] $values
     * @return bool
     */
    public function containsAny(array $values): bool
    {
        return array_intersect($this->items, $values) !== [];
    }

    /**
     * @param mixed[] $values
     * @return bool
     */
    public function containsAll(array $values): bool
    {
        return count(array_unique(array_intersect($this->items, $values))) === count($values);
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function indexOf($value, int $from = 0)
    {
        if ($from > 0) {
            return $this->drop($from)->indexOf($value);
        }

        $result = array_search($value, $this->toArray(), true);
        if ($result === false) {
            return null;
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function indexesOf($value): self
    {
        return new static(array_keys($this->items, $value, true));
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function lastIndexOf($value, ?int $end = null)
    {
        if ($end !== null) {
            return $this->take($end)->indexesOf($value)->last();
        }

        return $this->indexesOf($value)->last();
    }

    /**
     * @return mixed|null
     */
    public function indexWhere(callable $function, int $from = 0)
    {
        foreach ($this->drop($from) as $key => $value) {
            if ($function($value)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function lastIndexWhere(callable $function, ?int $end = null)
    {
        return $this->take($end)->reverse()->indexWhere($function);
    }

    /**
     * @param int|string $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param mixed[] $keys
     * @return bool
     */
    public function containsAnyKey(array $keys): bool
    {
        return array_intersect(array_keys($this->items), $keys) !== [];
    }

    /**
     * @param mixed[] $keys
     * @return bool
     */
    public function containsAllKeys(array $keys): bool
    {
        return count(array_intersect(array_keys($this->items), $keys)) === count($keys);
    }

    public function exists(callable $function): bool
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return true;
            }
        }

        return false;
    }

    public function forAll(callable $function): bool
    {
        foreach ($this as $value) {
            if (!$function($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public function find(callable $function)
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return $value;
            }
        }

        return null;
    }

    public function prefixLength(callable $function): int
    {
        return $this->segmentLength($function, 0);
    }

    public function segmentLength(callable $function, int $from = 0): int
    {
        $i = 0;
        $that = $this->drop($from);
        foreach ($that as $value) {
            if (!$function($value)) {
                break;
            }
            $i++;
        }

        return $i;
    }

    // stats -----------------------------------------------------------------------------------------------------------

    public function count(?callable $function = null): int
    {
        if ($function === null) {
            return count($this->toArray());
        }
        $count = 0;
        foreach ($this as $value) {
            if ($function($value)) {
                $count++;
            }
        }

        return $count;
    }

    public function size(): int
    {
        return count($this->items);
    }

    public function countValues(): self
    {
        return new static(array_count_values($this->toArray()));
    }

    /**
     * @return mixed|null
     */
    public function max()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return max($this->toArray());
    }

    /**
     * @return mixed|null
     */
    public function min()
    {
        if ($this->isEmpty()) {
            return null;
        }
        return min($this->toArray());
    }

    /**
     * @return mixed|null
     */
    public function maxBy(callable $function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        $max = $this->map($function)->max();

        return $this->find(static function ($value) use ($max, $function) {
            return $function($value) === $max;
        });
    }

    /**
     * @return mixed|null
     */
    public function minBy(callable $function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        $min = $this->map($function)->min();

        return $this->find(static function ($value) use ($min, $function) {
            return $function($value) === $min;
        });
    }

    /**
     * @return int|float
     */
    public function product()
    {
        return array_product($this->toArray());
    }

    /**
     * @return int|float
     */
    public function sum()
    {
        return array_sum($this->toArray());
    }

    // comparison ------------------------------------------------------------------------------------------------------

    /**
     * @param iterable|mixed[] $array
     * @return bool
     */
    public function containsSlice(iterable $array): bool
    {
        return $this->indexOfSlice($array, 0) !== null;
    }

    /**
     * @param iterable|mixed[] $array
     * @return int|null
     */
    public function indexOfSlice(iterable $array, int $from = 0): ?int
    {
        $that = $this->drop($from);
        while ($that->isNotEmpty()) {
            if ($that->startsWith($array)) {
                return $from;
            }
            $from++;
            $that = $that->tail();
        }

        return null;
    }

    /**
     * @param iterable|mixed[] $array
     * @return bool
     */
    public function corresponds(iterable $array, callable $function): bool
    {
        $iterator = $this->getIterator();
        $iterator->rewind();
        foreach ($array as $value) {
            if (!$iterator->valid() || !$function($iterator->current(), $value)) {
                return false;
            }
            $iterator->next();
        }

        return !$iterator->valid();
    }

    /**
     * @param iterable|mixed[] $array
     * @return bool
     */
    public function hasSameElements(iterable $array): bool
    {
        return $this->corresponds($array, static function ($a, $b) {
            return $a === $b;
        });
    }

    /**
     * @param iterable|mixed[] $slice
     * @return bool
     */
    public function startsWith(iterable $slice, int $from = 0): bool
    {
        /** @var Iterator<mixed, mixed> $iterator */
        $iterator = $this->drop($from)->getIterator();
        $iterator->rewind();
        foreach ($slice as $value) {
            if ($iterator->valid() && $value === $iterator->current()) {
                $iterator->next();
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param iterable|mixed[] $slice
     * @return bool
     */
    public function endsWith(iterable $slice): bool
    {
        $slice = Arr::toArray($slice);

        return $this->startsWith($slice, $this->count() - count($slice));
    }

    // transforming ----------------------------------------------------------------------------------------------------

    /**
     * @param mixed $init
     * @return mixed|null
     */
    public function fold(callable $function, $init = null)
    {
        return $this->foldLeft($function, $init);
    }

    /**
     * @param mixed $init
     * @return mixed|null
     */
    public function foldLeft(callable $function, $init = null)
    {
        return array_reduce($this->toArray(), $function, $init);
    }

    /**
     * @param mixed $init
     * @return mixed|null
     */
    public function foldRight(callable $function, $init = null)
    {
        foreach ($this->getReverseIterator() as $value) {
            $init = $function($value, $init);
        }

        return $init;
    }

    /**
     * @return mixed|null
     */
    public function reduce(callable $function)
    {
        return $this->reduceLeft($function);
    }

    /**
     * @return mixed|null
     */
    public function reduceLeft(callable $function)
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->tail()->foldLeft($function, $this->head());
    }

    /**
     * @return mixed|null
     */
    public function reduceRight(callable $function)
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->init()->foldRight($function, $this->last());
    }

    /**
     * @param mixed $init
     * @return static
     */
    public function scanLeft(callable $function, $init): self
    {
        $res = [];
        $res[] = $init;
        foreach ($this as $value) {
            $res[] = $init = $function($init, $value);
        }

        return new static($res);
    }

    /**
     * @param mixed $init
     * @return static
     */
    public function scanRight(callable $function, $init): self
    {
        $res = [];
        $res[] = $init;
        foreach ($this->getReverseIterator() as $value) {
            $init = $function($value, $init);
            array_unshift($res, $init);
        }

        return new static($res);
    }

    // slicing ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed|null
     */
    public function head()
    {
        if (count($this->items) === 0) {
            return null;
        }

        return reset($this->items);
    }

    /**
     * Alias of head()
     * @return mixed|null
     */
    public function first()
    {
        if (count($this->items) === 0) {
            return null;
        }

        return reset($this->items);
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        if (count($this->items) === 0) {
            return null;
        }

        return end($this->items);
    }

    public function init(): self
    {
        return $this->slice(0, -1);
    }

    public function tail(): self
    {
        return $this->drop(1);
    }

    public function inits(): self
    {
        $res = [$this];
        $that = $this;
        while ($that->isNotEmpty()) {
            $res[] = $that = $that->init();
        }

        return new static($res);
    }

    public function tails(): self
    {
        $res = [$this];
        $that = $this;
        while ($that->isNotEmpty()) {
            $res[] = $that = $that->tail();
        }

        return new static($res);
    }

    /**
     * @return mixed[] (mixed $head, static $tail)
     */
    public function headTail(): array
    {
        return [$this->head(), $this->tail()];
    }

    /**
     * @return mixed[] (static $init, mixed $last)
     */
    public function initLast(): array
    {
        return [$this->init(), $this->last()];
    }

    public function slice(int $from, ?int $length = null): self
    {
        return new static(array_slice($this->toArray(), $from, $length, self::PRESERVE_KEYS));
    }

    /**
     * @param int<1, max> $size
     */
    public function chunks(int $size): self
    {
        $res = new static(array_chunk($this->toArray(), $size, self::PRESERVE_KEYS));

        return $res->map(static function ($array) {
            return new static($array);
        });
    }

    public function sliding(int $size, int $step = 1): self
    {
        $res = [];
        for ($i = 0; $i <= $this->count() - $size + $step - 1; $i += $step) {
            $res[] = $this->slice($i, $size);
        }

        return new static($res);
    }

    public function drop(int $count): self
    {
        return $this->slice($count, $this->count());
    }

    public function dropRight(int $count): self
    {
        return $this->slice(0, $this->count() - $count);
    }

    public function dropWhile(callable $function): self
    {
        $res = [];
        $go = false;
        foreach ($this as $key => $value) {
            if (!$function($value)) {
                $go = true;
            }
            if ($go) {
                $res[$key] = $value;
            }
        }

        return new static($res);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function padTo(int $length, $value): self
    {
        return new static(array_pad($this->toArray(), $length, $value));
    }

    /**
     * @return static[] (static $l, static $r)
     */
    public function span(callable $function): array
    {
        $l = [];
        $r = [];
        $toLeft = true;
        foreach ($this as $key => $value) {
            $toLeft = $toLeft && $function($value);
            if ($toLeft) {
                $l[$key] = $value;
            } else {
                $r[$key] = $value;
            }
        }

        return [new static($l), new static($r)];
    }

    public function take(?int $count = null): self
    {
        return $this->slice(0, $count);
    }

    public function takeRight(int $count): self
    {
        return $this->slice(-$count, $count);
    }

    public function takeWhile(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            if (!$function($value)) {
                break;
            }
            $res[$key] = $value;
        }

        return new static($res);
    }

    public function rotateLeft(int $positions = 1): self
    {
        if ($this->items === []) {
            return $this;
        }
        $positions %= count($this->items);

        return new static(array_merge(array_slice($this->items, $positions), array_slice($this->items, 0, $positions)));
    }

    public function rotateRight(int $positions = 1): self
    {
        if ($this->items === []) {
            return $this;
        }
        $positions %= count($this->items);

        return new static(array_merge(array_slice($this->items, -$positions), array_slice($this->items, 0, -$positions)));
    }

    // filtering -------------------------------------------------------------------------------------------------------

    public function collect(callable $function): self
    {
        return $this->map($function)->filter();
    }

    public function filter(?callable $function = null): self
    {
        if ($function) {
            return new static(array_filter($this->toArray(), $function));
        } else {
            return new static(array_filter($this->toArray()));
        }
    }

    public function filterKeys(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            if ($function($key)) {
                $res[$key] = $value;
            }
        }

        return new static($res);
    }

    public function filterNot(callable $function): self
    {
        return $this->filter(static function ($value) use ($function) {
            return !$function($value);
        });
    }

    /**
     * @return static[] (static $fist, static $second)
     */
    public function partition(callable $function): array
    {
        $a = [];
        $b = [];
        foreach ($this as $key => $value) {
            if ($function($value)) {
                $a[$key] = $value;
            } else {
                $b[$key] = $value;
            }
        }

        return [new static($a), new static($b)];
    }

    // mapping ---------------------------------------------------------------------------------------------------------

    public function flatMap(callable $function): self
    {
        return $this->map($function)->flatten();
    }

    public function flatten(): self
    {
        $res = [];
        foreach ($this as $values) {
            if (is_array($values)) {
                foreach (Arr::flatten($values) as $value) {
                    $res[] = $value;
                }
            } elseif ($values instanceof self) {
                foreach ($values->flatten() as $value) {
                    $res[] = $value;
                }
            } else {
                $res[] = $values;
            }
        }

        return new static($res);
    }

    public function groupBy(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            $groupKey = $function($value);
            $res[$groupKey][$key] = $value;
        }
        $r = new static($res);

        return $r->map(static function ($array) {
            return new static($array);
        });
    }

    public function map(callable $function): self
    {
        return new static(array_map($function, $this->toArray()));
    }

    public function mapPairs(callable $function): self
    {
        return $this->remap(static function ($key, $value) use ($function) {
            return [$key => $function($key, $value)];
        });
    }

    public function remap(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            foreach ($function($key, $value) as $newKey => $newValue) {
                $res[$newKey] = $newValue;
            }
        }

        return new static($res);
    }

    public function flip(): self
    {
        return new static(array_flip($this->toArray()));
    }

    public function transpose(): self
    {
        $arr = $this->toArray();
        if ($this->isEmpty()) {
            return new static($arr);
        }

        array_unshift($arr, null);
        $arr = array_map(...$arr);
        foreach ($arr as $key => $value) {
            $arr[$key] = (array) $value;
        }

        return new static($arr);
    }

    public function transposeSafe(): self
    {
        if ($this->isEmpty()) {
            return new static([]);
        }
        $arr = [];
        foreach ($this->items as $i => $items) {
            foreach ($items as $j => $item) {
                $arr[$j][$i] = $item;
            }
        }
        return new static($arr);
    }

    /**
     * @param mixed $valueKey
     * @param mixed|null $indexKey
     * @return static
     */
    public function column($valueKey, $indexKey = null): self
    {
        return new static(array_column($this->toArrayRecursive(), $valueKey, $indexKey));
    }

    // sorting ---------------------------------------------------------------------------------------------------------

    public function reverse(): self
    {
        return new static(array_reverse($this->toArray(), self::PRESERVE_KEYS));
    }

    public function shuffle(): self
    {
        $arr = $this->toArray();
        shuffle($arr);

        return new static($arr);
    }

    public function sort(int $flags = Sorting::REGULAR): self
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            arsort($arr, $flags);
        } else {
            asort($arr, $flags);
        }

        return new static($arr);
    }

    public function sortKeys(int $flags = Sorting::REGULAR): self
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            krsort($arr, $flags);
        } else {
            ksort($arr, $flags);
        }

        return new static($arr);
    }

    public function sortWith(callable $function, int $flags = Order::ASCENDING): self
    {
        $arr = $this->toArray();
        uasort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }

        return new static($arr);
    }

    public function sortKeysWith(callable $function, int $flags = Order::ASCENDING): self
    {
        $arr = $this->toArray();
        uksort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }

        return new static($arr);
    }

    public function distinct(int $sortFlags = Sorting::REGULAR): self
    {
        $arr = $this->toArray();
        $arr = array_unique($arr, $sortFlags);

        return new static($arr);
    }

    // merging ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed ...$values
     * @return static
     */
    public function append(...$values): self
    {
        return $this->appendAll($values);
    }

    /**
     * @param iterable|mixed[] $values
     * @return static
     */
    public function appendAll(iterable $values): self
    {
        $res = $this->toArray();
        foreach ($values as $value) {
            $res[] = $value;
        }

        return new static($res);
    }

    /**
     * @param mixed ...$values
     * @return static
     */
    public function prepend(...$values): self
    {
        return $this->prependAll($values);
    }

    /**
     * @param iterable|mixed[] $values
     * @return static
     */
    public function prependAll(iterable $values): self
    {
        if (!is_array($values)) {
            $values = self::convertToArray($values);
        }
        foreach ($this as $value) {
            $values[] = $value;
        }

        return new static($values);
    }

    /**
     * @param mixed $find
     * @param mixed $replace
     * @return static
     */
    public function replace($find, $replace): self
    {
        $arr = $this->toArray();
        $arr = array_replace($arr, [$find => $replace]);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] $replacements
     * @return self
     */
    public function replaceAll(iterable $replacements): self
    {
        if (!is_array($replacements)) {
            $replacements = self::convertToArray($replacements);
        }
        $arr = $this->toArray();
        $arr = array_replace($arr, $replacements);

        return new static($arr);
    }

    /**
     * @return static
     */
    public function remove(int $from, int $length = 0): self
    {
        $arr = $this->toArray();
        array_splice($arr, $from, $length);

        return new static($arr);
    }

    /**
     * @param mixed[] $patch
     * @return static
     */
    public function patch(int $from, array $patch, ?int $length = null): self
    {
        $arr = $this->toArray();
        if ($length === null) {
            $length = count($patch);
        }
        array_splice($arr, $from, $length, $patch);

        return new static($arr);
    }

    /**
     * @param mixed[] $patch
     * @return self
     */
    public function insert(int $from, array $patch): self
    {
        $arr = $this->toArray();
        array_splice($arr, $from, 0, $patch);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] $that
     * @return self
     */
    public function merge(iterable $that): self
    {
        if (!is_array($that)) {
            $that = self::convertToArray($that);
        }
        $self = $this->toArray();

        return new static(array_merge($self, $that));
    }

    // diffing ---------------------------------------------------------------------------------------------------------

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diff(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diffWith(callable $function, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_udiff(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diffKeys(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff_key(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diffKeysWith(callable $function, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_diff_ukey(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diffPairs(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff_assoc(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function diffPairsWith(?callable $function, ?callable $keysFunction, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        if ($function && $keysFunction) {
            $args[] = $function;
            $args[] = $keysFunction;
            $arr = array_udiff_uassoc(...$args);
        } elseif ($function && !$keysFunction) {
            $args[] = $function;
            $arr = array_udiff_assoc(...$args);
        } elseif (!$function && $keysFunction) {
            $args[] = $keysFunction;
            $arr = array_diff_uassoc(...$args);
        } else {
            $arr = array_diff_assoc(...$args);
        }

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersect(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersectWith(callable $function, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_uintersect(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersectKeys(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect_key(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersectKeysWith(callable $function, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_intersect_ukey(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersectPairs(iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect_assoc(...$args);

        return new static($arr);
    }

    /**
     * @param iterable|mixed[] ...$args
     * @return self
     */
    public function intersectPairsWith(?callable $function, ?callable $keysFunction, iterable ...$args): self
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        if ($function && $keysFunction) {
            $args[] = $function;
            $args[] = $keysFunction;
            $arr = array_uintersect_uassoc(...$args);
        } elseif ($function && !$keysFunction) {
            $args[] = $function;
            $arr = array_uintersect_assoc(...$args);
        } elseif (!$function && $keysFunction) {
            $args[] = $keysFunction;
            $arr = array_intersect_uassoc(...$args);
        } else {
            $arr = array_intersect_assoc(...$args);
        }

        return new static($arr);
    }

}
