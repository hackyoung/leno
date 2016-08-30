<?php
namespace Leno\View\Token;

class View extends \Leno\View\Token
{
    protected $reg = '/\<view.*\/\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $data = $this->attrValue('data', $matched);
        $extend_data = $this->attrValue('extend_data', $matched);
        return sprintf(
            '<?php $this->startView(%s %s %s); $this->endView(); ?>',
            $this->right($name),
            (!empty($data)) ? ', '.$this->right($data) : ', []',
            $extend_data === 'true' ? ', true' : ''
        );
    }
}
