<?php
echo $this->class->getName() . "\n";
$comment = new \Leno\Doc\CommentParser($this->class->getDocComment());
echo $comment->getDescription() . "\n";
echo "---------------------------------------\n";
foreach($this->class->getMethods() as $method) {
    echo (new \Leno\Doc\Helper\MethodHelper($method))->profile($this->class->getName()) . "\n";
    /*
    $comment = new \Leno\Doc\CommentParser($method->getDocComment());
    echo $comment->getDescription() . "\n";
     */
}
echo "---------------------------------------\n";
