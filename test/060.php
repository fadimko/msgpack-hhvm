<?php
if(!extension_loaded('msgpack')) {
    dl('msgpack.' . PHP_SHLIB_SUFFIX);
}

function test($type, $variable, $test = null) {
    $serialized = msgpack_serialize($variable);

    $unpacker = new MessagePackUnpacker();

    $length = strlen($serialized);

    for ($i = 0; $i < $length;) {
        $len = rand(1, 10);
        $str = substr($serialized, $i, $len);

        $unpacker->feed($str);
        if ($unpacker->execute())
        {
            $unserialized = $unpacker->data();
            var_dump($unserialized);
            $unpacker->reset();
        }

        $i += $len;
    }

    if (!is_bool($test))
    {
        echo $unserialized === $variable ? 'OK' : 'ERROR', PHP_EOL;
    }
    else
    {
        echo $test || $unserialized == $variable ? 'OK' : 'ERROR', PHP_EOL;
    }
}

test('null', null);

test('bool: true', true);
test('bool: false', false);

test('zero: 0', 0);
test('small: 1', 1);
test('small: -1', -1);
test('medium: 1000', 1000);
test('medium: -1000', -1000);
test('large: 100000', 100000);
test('large: -100000', -100000);

test('double: 123.456', 123.456);

test('empty: ""', "");
test('string: "foobar"', "foobar");

test('array', array(), false);
test('array(1, 2, 3)', array(1, 2, 3), false);
test('array(array(1, 2, 3), arr...', array(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9)), false);

test('array("foo", "foo", "foo")', array("foo", "foo", "foo"), false);
test('array("one" => 1, "two" => 2))', array("one" => 1, "two" => 2), false);
test('array("kek" => "lol", "lol" => "kek")', array("kek" => "lol", "lol" => "kek"), false);
test('array("" => "empty")', array("" => "empty"), false);

$a = array('foo');
test('array($a, $a)', array($a, $a), false);
test('array(&$a, &$a)', array(&$a, &$a), false);

$a = array(null);
$b = array(&$a);
$a[0] = &$b;

test('cyclic', $a, true);

$a = array(
    'a' => array(
        'b' => 'c',
        'd' => 'e'
        ),
    'f' => array(
        'g' => 'h'
        )
    );

test('array', $a, false);

class Obj {
    public $a;
    protected $b;
    private $c;

    function __construct($a, $b, $c) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

test('object', new Obj(1, 2, 3), false);

test('object', array(new Obj(1, 2, 3), new Obj(4, 5, 6)), false);

$o = new Obj(1, 2, 3);

test('object', array(&$o, &$o), false);