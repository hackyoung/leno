<!doctype HTML>
<html>
<?php $this->view("v", new \Leno\View\View("Element.head", $__head__)) ?>
<?php $this->e("v")->display(); ?>
	<body>
<?php $this->view("v", new \Leno\View\View("Element.header"), true) ?>
<?php $this->e("v")->display(); ?>
<?php echo $content; ?>
<?php $this->view("v", new \Leno\View\View("Element.footer"), true) ?>
<?php $this->e("v")->display(); ?>
	</body>
</html>
