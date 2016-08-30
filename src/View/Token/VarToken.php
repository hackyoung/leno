<?php
namespace Leno\View\Token;

class VarToken extends \Leno\View\Token
{
    protected $reg = '/\{\$.*\}/U'; // 如{$hello.world.world}

    protected function replaceMatched($matched) : string
    {
        preg_match('/\{\$.*\}/U', $matched, $attrarr);
        $var = preg_replace('/[\{\}\$]/', '', $attrarr[0]);
        $v = $this->varString($var);
        $v = '<?php echo ('.$v.' ?? \'\'); ?>';
        return $v;
    }
}
