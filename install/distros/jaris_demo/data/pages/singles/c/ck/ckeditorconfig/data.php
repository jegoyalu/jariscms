<?php exit; ?>


row: 0

	field: title
		CKEditor Config
	field;

	field: content
		<?php
		if(empty($_REQUEST["group"]))
		{
		    exit;
		}
		
		$config = unserialize(Jaris\Settings::get("toolbar_items", "ckeditor"));
		
		$output = "";
		
		if(empty($config[$_REQUEST["group"]]))
		{
		    $output .= "CKEDITOR.editorConfig = function( config ) {
		    	config.toolbarGroups = [
		    		{ name: 'styles', groups: [ 'styles' ] },
		    		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		    		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		    		'/',
		    		{ name: 'insert', groups: [ 'insert' ] },
		    		{ name: 'links', groups: [ 'links' ] },
		    		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		    		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		    		{ name: 'forms', groups: [ 'forms' ] },
		    		{ name: 'tools', groups: [ 'tools' ] },
		    		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		    		{ name: 'colors', groups: [ 'colors' ] },
		    		{ name: 'others', groups: [ 'others' ] },
		    		{ name: 'about', groups: [ 'about' ] }
		    	];
		
		    	config.removeButtons = 'Font,HiddenField,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,CreateDiv,Language,BidiRtl,BidiLtr,Save,NewPage,Preview,Print,About,TextColor,BGColor,Flash,Smiley,Iframe,PageBreak,Scayt';
		    };//end";
		}
		else
		{
		    $output .= trim($config[$_REQUEST["group"]]) . "//end";
		}
		
		print str_replace(
		    "};//end",
		    "    config.allowedContent = true;\n        "
		        . "config.protectedSource.push( /<\?[\s\S]*?\?>/g );   "
		        . "// PHP code\n};", 
		    $output
		);
		    ?>
	field;

	field: rendering_mode
		javascript
	field;

	field: is_system
		1
	field;

	field: users
		N;
	field;

	field: groups
		N;
	field;

	field: categories
		N;
	field;

row;


