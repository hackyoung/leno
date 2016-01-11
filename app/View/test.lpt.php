<extend name="Layout.default">
	<implement name="content" >
		<div id="editor"></div>
	</implement>
</extend>
<script>
$(document).ready(function() {
	new leno.editor({
		id: 'editor'
	});
});
</script>
