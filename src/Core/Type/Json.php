<?php
namespace Leno\Core\Type;

class Json extends \Leno\Core\Validator implements \Leno\Core\TypeStorage
{
    public function toStore($value)
    {
        if(is_array($value) || $value instanceof \JsonSerializable) {
            return json_encode($value);
        } elseif(is_string($value)) {
            return $value;
        }
        throw new \Exception('Invalid Type');
    }

    public function fromStore($value)
    {
        if(is_string($value)) {
            try {
                return json_decode($value, true);
            } catch(\Exception $ex) {
                throw new \Exception('Invalid JSON:'.$ex->getMessage());
                return;
            }
        }
        return $value;
    }
}
