
<?php $this->start("content"); ?>
		<div id="editor"></div>
<?php $this->end(); ?>
<?php $this->extend("Layout.default"); ?><script>
$(document).ready(function() {
	new leno.editor({
		id: 'editor'
	});
});
</script>
