<?php
namespace Test\Playground;

use Combi\Base\Struct;
use Combi\Meta;
use Combi\Utils\Pack;
use Combi\Interfaces;

/**
 * Description of Model
 *
 * @author andares
 *
 * @property string $name
 * @property string $father
 * @property int $age
 */
class Person extends Struct
    implements \ArrayAccess, \JsonSerializable, \Serializable {

    use Meta\Fillable,
        Meta\Overloaded,
        Meta\ArrayAccess,
        Meta\JsonSerialize,
        Meta\ToBin,
        Meta\Serializable;

    protected static $_defaults = [
        'name'      => null,
        'father'    => null,
        'age'       => 18,
    ];

    protected static $_deprecated = [
        'father'    => 1,
    ];

    protected static function getEncoder(): Interfaces\Encoder {
        return Pack::getEncoder('msgpack');
    }

    protected function _confirm_age($value) {
        return $value > 38 ? 36 : $value;
    }
}

