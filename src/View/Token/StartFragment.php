<?php
namespace Leno\View\Token;

class StartFragment extends \Leno\View\Token
{
    protected $reg ='/\<fragment.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $type = $this->attrValue('type', $matched);
        $show = $this->attrValue('show', $matched);
        if(!in_array($show, ['true', 'false'])) {
            $show = 'false';
        }
        if(empty($type)) {
            $type = \Leno\View::TYPE_REPLACE;
        }
        return '<?php $this->startFragment('.$this->right($name).', \''.$type.'\', '.$show.'); ?>';
    }
}
