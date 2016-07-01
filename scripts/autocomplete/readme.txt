Sources:
=================

http://www.devbridge.com/projects/autocomplete/jquery/
https://github.com/devbridge/jQuery-Autocomplete

What's New in 1.1
=================

$('#query').autocomplete(options) now returns an Autocomplete instance only for the first matched element.

Autocomplete functionality can be disabled or enabled programmatically.
var ac = $('#query').autocomplete(options);
ac.disable();
ac.enable();

Options can be changed programmatically at any time, only options that are passed get set:
ac.setOptions({ zIndex: 1001 });

If you need to pass additional parameters, you can set them via setOptions too:
ac.setOptions({ params: { first:'John', last:'Doe' } });

New parameters when initializing autocomplete. They can also be set via setOptions.
- zIndex: default value is 9999.
- fnFormatResult: function that formats values that are displayed in the autosuggest list. It takes three parameters: suggested value, data and current query value. Default function for this:
var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
function fnFormatResult(value, data, currentValue) {
var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
return value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
}

Installation
=================

Include jQuery in your header. After it's included, add autocomplete script.
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.autocomplete.js"></script>

How to Use
=================

Here is an Ajax Autocomplete sample for the text field with id "query"
<input type="text" name="q" id="query" />

Create an instance of the Autocomplete object. You can have multiple instances on a single page.

Important: Autocomplete must be initialized after DOM has finished loading.
Otherwise you will get an error in IE.
var options, a;
jQuery(function(){
   options = { serviceUrl:'service/autocomplete.ashx' };
   a = $('#query').autocomplete(options);
});

You can add extra options:
var a = $('#query').autocomplete({
serviceUrl:'service/autocomplete.ashx',
minChars:2,
delimiter: /(,|;)\s*/, // regex or character
maxHeight:400,
width:300,
zIndex: 9999,
deferRequestBy: 0, //miliseconds
params: { country:'Yes' }, //aditional parameters
noCache: false, //default is false, set to true to disable caching
// callback function:
onSelect: function(value, data){ alert('You selected: ' + value + ', ' + data); },
// local autosugest options:
lookup: ['January', 'February', 'March', 'April', 'May'] //local lookup values
});

Use lookup option only if you prefer to inject an array of autocompletion options, rather than sending Ajax queries.

Web page that provides data for Ajax Autocomplete, in our case autocomplete.ashx will receive GET request with querystring ?query=Li, and it must return JSON data in the following format:
{
query:'Li',
suggestions:['Liberia','Libyan Arab Jamahiriya','Liechtenstein','Lithuania'],
data:['LR','LY','LI','LT']
}

Notes:

    query - original query value
    suggestions - comma separated array of suggested values
    data (optional) - data array, that contains values for callback function when data is selected.

Styling
=================

Script generates the following HTML (sample query Li). Active element when you navigate up and down is marked with class "selected". You can style it any way you wish.
<div class="autocomplete-w1">
  <div style="width:299px;" id="Autocomplete_1240430421731" class="autocomplete">
    <div><strong>Li</strong>beria</div>
    <div><strong>Li</strong>byan Arab Jamahiriya</div>
    <div><strong>Li</strong>echtenstein</div>
    <div class="selected"><strong>Li</strong>thuania</div>
  </div>
</div>

Here is style used in the sample above:
.autocomplete-w1 { background:url(img/shadow.png) no-repeat bottom right; position:absolute; top:0px; left:0px; margin:6px 0 0 6px; /* IE6 fix: */ _background:none; _margin:1px 0 0 0; }
.autocomplete { border:1px solid #999; background:#FFF; cursor:default; text-align:left; max-height:350px; overflow:auto; margin:-6px 6px 6px -6px; /* IE6 specific: */ _height:350px;  _margin:0; _overflow-x:hidden; }
.autocomplete .selected { background:#F0F0F0; }
.autocomplete div { padding:2px 5px; white-space:nowrap; overflow:hidden; }
.autocomplete strong { font-weight:normal; color:#3399FF; }

If you will use this CSS, please make sure to correct path to the shadow.png image. Image is included in the package. It uses CSS Drop Shadow technique by Sergio Villarreal.

Copyright
=================
Ajax Autocomplete for jQuery is freely distributable under the terms of an MIT-style license.

Currently supported browsers: IE 7+, FF 2+, Safari 3+, Opera 9+

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
Contact

Contact me tomas@devbridge.com for feedback and bug reports.

If you'd like to speak with us about your software development project, then please contact us.
