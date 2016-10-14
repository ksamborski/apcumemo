#apcumemo
PHP library for memoization function results based on it's arguments.

## Requirements

You need to have acpu extension installed for php >= 7.0.0

## Installation

```bash
composer require ksamborski/apcu-memo
```

## Basic usage

Functional paradigm teaches us that pure functions are the way to go. They provide two main features:

  - because their result depends only on their arguments they can be easly tested (you don't need any mocks or stubs)
  - because they have no side effects they can be easly cached

Consider this example:

```php

use APCuMemo\APCuMemo;

function sumrange($a, $b)
{
    return APCuMemo::memoize(
        function(...$_) use ($a, $b) {
            return array_reduce(range($a, $b), function ($product, $item) { return $product + $item; }, 1);
        },
        [ 'ttl' => 5 ],
        "sumrange",
        $a,
        $b
    );
}

$start = microtime(true);
echo sumrange((int) $_GET['a'], (int) $_GET['b']);
$elapsed = microtime(true) - $start;
echo " " . ($elapsed * 1000) . " ms";
```

We have a simple function that sums integers from $a to $b. It can take some
time but when we compute it for the first time then we can cache it. Notice the
'ttl' parameter. It is set to 5 seconds and means that if nobody asks us for
the same range within 5 seconds it will forget the result. But every request
for cached value will reset the ttl. That way we can have only mostly used values in cache.

Let's see it in action. For $a = 1 and $b = 1831233 it will return something like:
 
```bash
1676708065762 283.94412994385 ms
```

And the followed requests will return:

```bash
1676708065762 0.030040740966797 ms
```
