<?php

class Php84Test {

    public function run(): void {

        // ❌ Removed earlier, must still be flagged
        each([1, 2, 3]);

        // ❌ create_function removed
        $fn = create_function('$a', 'return $a + 1;');
        echo $fn(2);

        // ❌ Curly brace string offset (invalid in PHP 8+)
        $str = "hello";
        echo $str[0];

        // ❌ Passing null to internal function (strict in PHP 8.1+)
        strlen(null);

        // ❌ Dynamic property (deprecated 8.2, stricter forward)
        $obj = new \stdClass();
        $obj->newProp = "test";

        // ❌ Implicit float to int issues (tightened behavior)
        $this->takesInt(10.5);

        // ❌ Undefined variable (will be flagged by PHPCS + others)
        echo $undefinedVar;

        // ❌ Old-style constructor (very old, still flagged)
        $this->Php84Test();

    }

    // ❌ Type mismatch (float passed to int)
    public function takesInt(int $num): int {
        return $num;
    }

    // ❌ Deprecated parameter order
    public function badParams($optional = null, $required = null) {
        return $required;
    }

    // ❌ Old constructor style
    public function Php84Test(): void {
        echo "Old constructor";
    }
}