//fixed 62 link>new window Chrome bug //link an image //td border hint //insHTML deletes selection in Chrome
//fixed 63 IE9 breaks whereAmI()
//Copyright © 2005-2011 John Goodman - www.unverse.net  *date 110623
//Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
//The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
//THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

whizzywig = {

    version: 'Whizzywig v64',
    btn: [],                                //object containing button strip information
    btn: {
        _w: 16,                             //Buttons width
        _h: 16,                             //Buttons height
        _f: "icons.png"                     //set to path to toolbar image
    },
    buttonPath: "",                         //path to custom toolbar button images; "textbuttons" means don't use images
    buttonExt: "",                          //extension (e.g. .png) for toolbar button images;  default '.gif'
    cssFile: "",                            //url of CSS stylesheet to attach to edit area
    imageBrowse: "",                        //path to page for image browser
    linkBrowse: "",                         //path to page for link browser
    idTa: "",                               //id of the textarea (param to makeWhizzyWig)

    //OTHER GLOBALS //Whizzy contentWindow, current sel, range, parent, DOM path, popwindow, window, IE?;
    oW: null,
    sel: null,
    rng: null,
    papa: null,
    trail: "",
    ppw: "",
    wn: window,
    msIE: ("Microsoft Internet Explorer"==navigator.appName),
    sels: "",
    buts: "",
    vals: [],
    opts: [],
    dobut: [],
    whizzies: [],
    taOrigSize: [],

    // make a WhizzyWig from the textarea
    makeWhizzyWig: function (txtArea, controls){
     this.idTa=txtArea;
     this.whizzies[this.whizzies.length]=this.idTa;
     if (!document.designMode) {
      if (this.idTa.nodeName=="TEXTAREA"){this.tagButs();}
      alert("Whizzywig "+this.t("editor not available for your browser"));
      return;
     }
     var taContent=this.o(this.idTa).defaultValue ? this.o(this.idTa).defaultValue : this.o(this.idTa).innerHTML ? this.o(this.idTa).innerHTML: ''; //anything in the textarea?
     if (!this.o(this.idTa).rows < 5){this.o(this.idTa).rows='15';}//IE won't use % from style
     taWidth=this.o(this.idTa).style.width ? this.o(this.idTa).style.width : this.o(this.idTa).cols + "ex";  //grab the width and...
     taHeight=this.o(this.idTa).style.height ? this.o(this.idTa).style.height : this.o(this.idTa).rows + "em";  //...height from the textarea
     this.taOrigSize[this.idTa] = {w:taWidth,h:taHeight};
     //Create whizzy container
     var wContainer = document.createElement('div');
     wContainer.id = 'CONTAINER'+this.idTa+'';
     wContainer.style.width = taWidth;
     var taObject = this.o(this.idTa);
     this.o(this.idTa).parentNode.replaceChild(wContainer, this.o(this.idTa));
     this.o("CONTAINER"+this.idTa).appendChild(taObject);
     //End whizzy container
     if (this.o(this.idTa).nodeName=="TEXTAREA"){
     this.o(this.idTa).style.color='#060';
     this.o(this.idTa).style.zIndex='2';
     }else{this.w('<input type="hidden" id="wzhid_'+this.idTa+'" name="'+this.idTa+'" />')}
     this.h(this.idTa);
     var frm=this.o(this.idTa).parentNode;
     while(frm.nodeName != 'FORM'){frm=frm.parentNode}//if not form, keep trying
     this.addEvt(frm,"submit",this.syncTextarea);
     this.w('<style type="text/css">button {vertical-align:middle;padding:0;margin:1px 0} button img{vertical-align:middle;margin:-1px} select{vertical-align:middle;margin:1px}  .wzCtrl {background:ButtonFace; border:2px outset ButtonShadow; padding:5px;} #sourceTa{color:#060;font-family:mono;}</style>');
     var dsels='formatblock fontname fontsize';
     var dbuts=' bold italic underline | left center right justify | number bullet indent outdent | undo redo | color hilite rule | link image table | clean html spellcheck fullscreen ';
     var tbuts=' tstart add_row_above add_row_below delete_row | add_column_before add_column_after delete_column | table_in_cell';
     var t_end=''; //table controls end, if needed
     btns=(dbuts+tbuts).split(' ');
     for (var i=0,pos=0;i<btns.length;i++) {
      if(btns[i] && btns[i]!='|' && btns[i]!='tstart'){this.btn[btns[i]]=this.btn._w*pos++}
     }
     controls=controls ? controls.toLowerCase() : "all";
     if(controls == "all"){controls=dsels +' newline '+ this.buts + dbuts + tbuts}
     else{controls += tbuts}
     this.w('<div onmouseover="whizzywig.c(\''+this.idTa+'\')"><div id="CONTROLS'+this.idTa+'" class="wzCtrl" unselectable="on">');
     gizmos=controls.split(' ');
     for (i=0;i<gizmos.length;i++){
      if (gizmos[i]){ //make buttons and selects for toolbar, in order requested
       if (gizmos[i] == 'tstart') {
        this.w('<div id="TABLE_CONTROLS'+this.idTa+'" style="display:none" unselectable="on">');
        t_end='</div>';
       }
       else if(gizmos[i]=='|'){this.w('&nbsp;<big style="padding-bottom:2em">|</big>&nbsp;')}
       else if(gizmos[i] == 'newline'){this.w('<br>')}
       else if((dsels+this.sels).indexOf(gizmos[i]) != -1){this.makeSelect(gizmos[i])}
       else if((dbuts+this.buts+tbuts).indexOf(gizmos[i]) != -1){this.makeButton(gizmos[i])}
      }
     }
     this.w(t_end)//table controls end
     this.w('<a href="http://www.unverse.net" style="color:buttonface" title="'+this.version+'">.</a> ');
     this.w(this.fGo('LINK'));
     if (this.linkBrowse){this.w('<input type="button" onclick=whizzywig.doWin("'+this.linkBrowse+'"); value="'+this.t("Browse")+'"> ')}
     this.w(this.t('Link address (URL)')+': <input type="text" id="lf_url'+this.idTa+'" size="60"><br><input type="button" value="http://" onclick="whizzywig.o(\'lf_url'+this.idTa+'\').value=\'http://\'+whizzywig.o(\'lf_url'+this.idTa+'\').value"> <input type="button" value="mailto:" onclick="whizzywig.o(\'lf_url'+this.idTa+'\').value=\'mailto:\'+whizzywig.o(\'lf_url'+this.idTa+'\').value"><input type="checkbox" id="lf_new'+this.idTa+'">'+this.t("Open link in new window")+this.fNo(this.t("OK"),"whizzywig.insertLink()"));//LINK_FORM end
     this.w(this.fGo('IMAGE'));
     if(this.imageBrowse){this.w('<input type="button" onclick=whizzywig.doWin("'+this.imageBrowse+'"); value="'+this.t("Browse")+'"> ')}
     this.w(this.t('Image address (URL)')+': <input type="text" id="if_url'+this.idTa+'" size="50"> <label title='+this.t("to display if image unavailable")+'><br>'+this.t("Alternate text")+':<input id="if_alt'+this.idTa+'" type="text" size="50"></label><br>'+this.t("Align")+':<select id="if_side'+this.idTa+'"><option value="none">_&hearts;_ '+this.t("normal")+'</option><option value="left">&hearts;= &nbsp;'+this.t("left")+'</option><option value="right">=&hearts; &nbsp;'+this.t("right")+'</option></select> '+this.t("Border")+':<input type="text" id="if_border'+this.idTa+'" size="20" value="0" title="'+this.t("number or CSS e.g. 3px maroon outset")+'"> '+this.t("Margin")+':<input type="text" id="if_margin'+this.idTa+'" size="20" value="0" title="'+this.t("number or CSS e.g. 5px 1em")+'">'+this.fNo(this.t("Insert Image"),"whizzywig.insertImage()"));//IMAGE_FORM end
     this.w(this.fGo('TABLE')+this.t("Rows")+':<input type="text" id="tf_rows'+this.idTa+'" size="2" value="3"> <select id="tf_head'+this.idTa+'"><option value="0">'+this.t("No header row")+'</option><option value="1">'+this.t("Include header row")+'</option></select> '+this.t("Columns")+':<input type="text" id="tf_cols'+this.idTa+'" size="2" value="3"> '+this.t("Border width")+':<input type="text" id="tf_border'+this.idTa+'" size="2" value="1"> '+this.fNo(this.t("Insert Table"),"whizzywig.makeTable()"));//TABLE_FORM end
     this.w(this.fGo('COLOR')+'<input type="hidden" id="cf_cmd'+this.idTa+'"><div style="background:#000;padding:1px;height:22px;width:125px;float:left"><div id="cPrvw'+this.idTa+'" style="background-color:red; height:100%; width:100%"></div></div> <input type=text id="cf_color'+this.idTa+'" value="red" size=17 onpaste=whizzywig.vC(value) onblur=whizzywig.vC(value)> <input type="button" onmouseover=whizzywig.vC() onclick=whizzywig.sC() value="'+this.t("OK")+'">  <input type="button" onclick="whizzywig.hideDialogs();" value="'+this.t("Cancel")+'"><br> '+this.t("click below or enter a")+' <a href="http://www.unverse.net/colortable.htm" target="_blank">'+this.t("color name")+'</a><br clear=all> <table border=0 cellspacing=1 cellpadding=0 width=480 bgcolor="#000000">'+"\n");
     var wC=new Array("00","33","66","99","CC","FF")  //color table
     for (i=0; i<wC.length; i++){
      this.w("<tr>");
      for (j=0; j<wC.length; j++){
       for (k=0; k<wC.length; k++){
        var clr=wC[i]+wC[j]+wC[k];
        this.w(' <td style="background:#'+clr+';height:12px;width:12px" onmouseover=whizzywig.vC("#'+clr+'") onclick=whizzywig.sC("#'+clr+'")></td>'+"\n");
       }
      }
      this.w('</tr>');
     }
     this.w("</table></div>\n"); //end color table,COLOR_FORM
     this.w("</div>\n"); //controls end
     this.w('<div class="wzCtrl" id="showWYSIWYG'+this.idTa+'" style="display:none"><input type="button" onclick="whizzywig.showDesign();" value="'+this.t("Hide HTML")+'">');
     this.tagButs();
     this.w('</div>'+"\n");
     this.w('<iframe style="background-color: #fff; border:1px inset ButtonShadow;width:100%;height:'+taHeight+'" src="javascript:;" id="whizzy'+this.idTa+'"></iframe></div>'+"\n", true); //finally write content to whizzy container
     var startHTML="<html>\n<head>\n<style>td,th{border:1px dotted #888}</style>\n";
     if(this.cssFile){startHTML += '<link media="all" type="text/css" href="'+this.cssFile+'" rel="stylesheet">\n'}
     startHTML += '</head>\n<body id="'+this.idTa+'" style="background-image:none">\n'+this.tidyD(taContent)+'</body>\n</html>';
     this.oW=this.o("whizzy"+this.idTa).contentWindow;
     var d=this.oW.document;
     try{d.designMode="on";} catch(e){ setTimeout('whizzywig.oW.designMode="on";', 100);}
     d.open(); d.write(startHTML); d.close();
     if(this.oW.addEventListener){this.oW.addEventListener("keypress", this.kb_handler, true)}//keyboard shortcuts for Moz
     else{d.body.attachEvent("onpaste",function(){setTimeout('whizzywig.cleanUp()',10)})}
     this.addEvt(d,"mouseup", this.whereAmI); this.addEvt(d,"keyup", this.whereAmI); this.addEvt(d,"dblclick", this.doDbl);
     //move textarea so html menu appears on top
     taObject = this.o(this.idTa);
     this.o("CONTAINER"+this.idTa).removeChild(this.o(this.idTa));
     this.o("CONTAINER"+this.idTa).appendChild(taObject);
     //end move
     this.idTa=null;
    }, //end makeWhizzyWig

    makeAll: function(controls){
     var i,ta=document.getElementsByTagName('TEXTAREA');
     for (i=0;i<ta.length;i++){
      if(!ta[i].id){ta[i].id=ta.name}
      this.makeWhizzyWig(ta[i].id,controls);
     }
    },

    addEvt: function(o,e,f){
        if(this.wn.addEventListener){o.addEventListener(e, f, false)}else{o.attachEvent("on"+e,f)}
    },

    doDbl: function(){
        if(whizzywig.papa.nodeName == 'IMG'){whizzywig.doImage()}else{if(whizzywig.papa.nodeName=='A'){whizzywig.doLink()}}
    },

    // assemble the button requested
    makeButton: function(button){
     var butHTML, ucBut=button.substring(0,1).toUpperCase();
     ucBut += button.substring(1);
     ucBut=this.t(ucBut.replace(/_/g,' '));
     if(!document.frames && (button=="spellcheck")){return}//Not allowed from Firefox
     if(this.o(this.idTa).nodeName!="TEXTAREA" && button=="html"){return}
     if(!this.buttonExt){this.buttonExt='.gif'}
     if (this.buttonPath == "textbuttons"){butHTML='<button type=button onClick=whizzywig.makeSo("'+button+'")>'+ucBut+"</button>\n"}
     else{butHTML='<button  title="'+ucBut+'" type=button onClick=whizzywig.makeSo("'+button+'")>'+(this.btn[button]!=undefined?'<div style="width:'+this.btn._w+'px;height:'+this.btn._h+'px;background-image:url('+this.btn._f+');background-position:-'+this.btn[button]+'px 0px"></div>':'<img src="'+this.buttonPath+button+this.buttonExt+'" alt="'+ucBut+'" onError="this.parentNode.innerHTML=this.alt">')+'</button>\n'}
     this.w(butHTML)
    },

    //new form
    fGo: function(id){
        return '<div id="'+id+'_FORM'+this.idTa+'" unselectable="on" style="display:none" onkeypress="if(event.keyCode==13) {return false;}"><hr>'+"\n"
    },

    //form do it/cancel buttons
    fNo: function(txt,go){
     return ' <input type="button" onclick="'+go+'" value="'+txt+'"> <input type="button" onclick="whizzywig.hideDialogs();" value='+this.t("Cancel")+"></div>\n";
    },

    //assemble the <select> requested
    makeSelect: function(select){
     var values,options,h,i;
     if (select == 'formatblock'){
     h="Heading";
     values=["<p>", "<p>", "<h1>", "<h2>", "<h3>", "<h4>", "<h5>", "<h6>", "<address>",  "<pre>"];
     options=[this.t("Choose style")+":", this.t("Paragraph"), this.t(h)+" 1 ", this.t(h)+" 2 ", this.t(h)+" 3 ", this.t(h)+" 4 ", this.t(h)+" 5 ", this.t(h)+" 6", this.t("Address"), this.t("Fixed width<pre>")];
     }else if (select == 'fontname') {
      values=["Arial, Helvetica, sans-serif", "Arial, Helvetica, sans-serif","'Arial Black', Helvetica, sans-serif", "'Comic Sans MS' fantasy", "Courier New, Courier, monospace", "Georgia, serif", "Impact,sans-serif","'Times New Roman', Times, serif", "'Trebuchet MS',sans-serif", "Verdana, Arial, Helvetica, sans-serif"];
      options=[this.t("Font")+":", "Arial","Arial Black", "Comic", "Courier", "Georgia", "Impact","Times New Roman", "Trebuchet","Verdana"]
     }else if(select == 'fontsize'){
      values=["3", "1", "2", "3", "4", "5", "6", "7"];
      options=[this.t("Font size")+":", "1 "+this.t("Small"), "2", "3", "4", "5", "6", "7 "+this.t("Big")]
     }else{
      values=this.vals[select];
      options=this.opts[select]
     }
     this.w('<select id="'+select+this.idTa+'" onchange="whizzywig.doSelect(this.id);">'+"\n");
     for (i=0;i<values.length;i++){this.w(' <option value="' + values[i] + '">' + options[i] + "</option>\n")}
     this.w("</select>\n")
    },

    tagButs: function(){
     this.w('<input type="button" onclick=\'whizzywig.doTag("<h1>")\' value="H1" title="<H1>"><input type="button" onclick=\'whizzywig.doTag("<h2>")\' value="H2" title="<H2>"><input type="button" onclick=\'whizzywig.doTag("<h3>")\' value="H3" title="<H3>"><input type="button" onclick=\'whizzywig.doTag("<h4>")\' value="H4" title="<H4>"><input type="button" onclick=\'whizzywig.doTag("<p>")\' value="P" title="<P>"><input type="button" onclick=\'whizzywig.doTag("<strong>")\' value="S" title="<STRONG>" style="font-weight:bold"><input type="button" onclick=\'whizzywig.doTag("<em>")\' value="E" title="<EM>" style="font-style:italic;"><input type="button" onclick=\'whizzywig.doTag("<li>")\' value="&bull;&mdash;" title="<LI>"><input type="button" onclick=\'whizzywig.doTag("<a>")\' value="@" title="<A HREF= >"><input type="button" onclick=\'whizzywig.doTag("<img>")\' value="[&hearts;]" title="<IMG SRC= >"><input type="button" onclick=\'whizzywig.doTag("<br />")\' value="&larr;" title="<BR />">');
    },

    xC: function(c,o){
        return this.oW.document.execCommand(c,false,o)
    },

    //format selected text or line in the whizzy
    makeSo: function(cm,op){
     this.hideDialogs();
     this.oW.focus();
     if(this.dobut[cm]) {this.insHTML(this.dobut[cm]); return;}
     if (/Firefox/.test(navigator.userAgent)) {this.xC("styleWithCSS",cm=="hilite")} //no spans for bold, italic, ok hilite
     if(cm=="justify"){cm="full"}
     if("leftrightcenterfull".indexOf(cm)!=-1){cm="justify"+cm}
     else if(cm=="number"){cm="insertorderedlist"}
     else if(cm=="bullet"){cm="insertunorderedlist"}
     else if (cm=="rule"){cm="inserthorizontalrule"}
     switch(cm){
      case "color":this.o('cf_cmd'+this.idTa).value="forecolor"; if(this.textSel()){this.s('COLOR_FORM'+this.idTa)} break;
      case "hilite":this.o('cf_cmd'+this.idTa).value=cm; if(this.textSel()){this.s('COLOR_FORM'+this.idTa)} break;
      case "image":this.doImage(); break;
      case "link":this.doLink(); break;
      case "html":this.showHTML(); break;
      case "table":this.doTable(); break;
      case "delete_row":this.doRow('delete','0'); break;
      case "add_row_above":this.doRow('add','0'); break;
      case "add_row_below":this.doRow('add','1'); break;
      case "delete_column":this.doCol('delete','0'); break;
      case "add_column_before":this.doCol('add','0'); break;
      case "add_column_after":this.doCol('add','1'); break;
      case "table_in_cell":this.hideDialogs(); this.s('TABLE_FORM'+this.idTa); break;
      case "clean":this.cleanUp(); break;
      case "spellcheck":this.spellCheck(); break;
      case "fullscreen":this.fullscreen(); break;
      default:this.xC(cm,op); break;
     }
     this.oW.focus();
    },

    //select on toolbar used - do it
    doSelect: function(selectname) {
     var idx=this.o(selectname).selectedIndex;
     var selected=this.o(selectname).options[idx].value;
     this.o(selectname).selectedIndex=0;
     selectname=selectname.replace(this.idTa,"");
     if (" _formatblock_fontname_fontsize".indexOf('_'+selectname) > 0) {
      var cmd=selectname;
      this.oW.focus();
      this.xC(cmd,selected);
     } else {
      this.insHTML(selected);
     }
     this.oW.focus();
    },

    // view Color
    vC: function(colour){
     if(!colour){colour=this.o('cf_color'+this.idTa).value}
     this.o('cPrvw'+this.idTa).style.backgroundColor=colour;
     this.o('cf_color'+this.idTa).value=colour
    },

    //set Color
    sC: function(color) {
     this.hideDialogs();
     var cmd=this.o('cf_cmd'+this.idTa).value;
     if(!color){color=this.o('cf_color'+this.idTa).value}
     if(this.rng){this.rng.select();}
     if(cmd=="hilite"){try{this.xC("hilitecolor",color)}catch(e){this.xC("backcolor",color)}}
     else{this.xC(cmd,color)}
     this.oW.focus();
    },

    doLink: function(){
     if(this.textSel()){
      if(this.papa.nodeName=='A'){this.o("lf_url"+this.idTa).value=this.papa.href}
      this.s('LINK_FORM'+this.idTa)
     }
    },

    insertLink: function(url) {
     if (this.rng){this.rng.select()}
     var a,i,mk='http://whizzy.wig/mark',
     URL=url ? url : this.o("lf_url"+this.idTa).value;
     if (URL.replace(/ /g,"")===""){this.xC('Unlink',null)}else{
      this.xC('CreateLink',mk);
      a=this.oW.document.body.getElementsByTagName("A");
      for (i=0;i<a.length;i++){
       if (a[i].href==mk){a[i].href=URL; if(this.o("lf_new"+this.idTa).checked){a[i].target="_blank"}break}
      }
     }
     this.hideDialogs();
    },

    doImage: function(){
     if (this.papa && this.papa.nodeName == 'IMG'){
      this.o("if_url"+this.idTa).value=this.papa.src;
      this.o("if_alt"+this.idTa).value=this.papa.alt;
      var position = this.papa.style.cssFloat?this.papa.style.cssFloat:this.papa.style.styleFloat;
      this.o("if_side"+this.idTa).selectedIndex=(position=="left")?1:(position=="right")?2:0;
      this.o("if_border"+this.idTa).value=this.papa.style.border?this.papa.style.border:this.papa.border>0?this.papa.border:0;
      this.o("if_margin"+this.idTa).value=this.papa.style.margin?this.papa.style.margin:this.papa.hspace>0?this.papa.hspace:0;
     }
     this.s('IMAGE_FORM'+this.idTa);
    },

    // insert image as specified
    insertImage: function(URL, side, border, margin, alt) {
     this.hideDialogs();
     if(!URL){URL=this.o("if_url"+this.idTa).value}
     if (URL) {
      if (!alt){alt=this.o("if_alt"+this.idTa).value ? this.o("if_alt"+this.idTa).value: URL.replace(/.*\/(.+)\..*/,"$1")}
      img='<img alt="' + alt + '" src="' + URL +'" ';
      if(!side){side=this.o("if_side"+this.idTa).value}
      if((side=="left") || (side=="right")){align='float:'+side+';'}else{align=''}
      if(!border){border=this.o("if_border"+this.idTa).value}
      if(border.match(/^\d+$/)){border+='px solid'}
      if(!margin){margin=this.o("if_margin"+this.idTa).value}
      if(margin.match(/^\d+$/)){margin+='px'}
      if(border || margin){img+=' style="border:'+border+';margin:'+margin+';'+align+ '"'}
      img+='/>';

      if(this.papa.nodeName=="IMG"){//Edit current image
       this.papa.src=URL;
       this.papa.alt=alt;
       this.papa.style.border=border;
       this.papa.style.margin=margin;
       this.papa.style.cssFloat=side; //Compliant browsers
       this.papa.style.styleFloat=side; //IE
      }
      else{//Insert new image
       this.insHTML(img);
      }
     }
    },

    //show table controls if in a table, else make table
    doTable: function(){
     if(this.trail && this.trail.indexOf('TABLE') > 0){this.s('TABLE_CONTROLS'+this.idTa)}
      else{this.s('TABLE_FORM'+this.idTa)}
    },

    //insert or delete a table row
    doRow: function(toDo,below) {
     var pa=this.papa,tRow,tCols,newRow,newCell;
     while(pa.tagName != "TR"){pa=pa.parentNode}
     tRow=pa.rowIndex;
     tCols=pa.cells.length;
     while(pa.tagName != "TABLE"){pa=pa.parentNode}
     if(toDo=="delete"){pa.deleteRow(tRow)}
     else{
      newRow=pa.insertRow(tRow+parseInt(below,10));//1=below 0=above
       for(i=0;i<tCols;i++){
        newCell=newRow.insertCell(i);
        newCell.innerHTML="#";
       }
     }
    },

    //insert or delete a column
    doCol: function(toDo,after) {
     var pa=this.papa,tCol,tRows,i,newCell;
     while(pa.tagName != 'TD'){pa=pa.parentNode}
     tCol=pa.cellIndex;
     while(pa.tagName != "TABLE"){pa=pa.parentNode}
     tRows=pa.rows.length;
     for(i=0;i<tRows;i++){
      if(toDo=="delete"){pa.rows[i].deleteCell(tCol)}
      else{
       newCell=pa.rows[i].insertCell(tCol+parseInt(after,10));//if after=0 then before
       newCell.innerHTML="#";
      }
     }
    },

    //insert a table
    makeTable: function() {
     this.hideDialogs();
     var rows=this.o('tf_rows'+this.idTa).value, cols=this.o('tf_cols'+this.idTa).value, border=this.o('tf_border'+this.idTa).value, head=this.o('tf_head'+this.idTa).value, table,i,j;
     if ((rows>0)&&(cols>0)){
      table='<table border="'+border+'">';
      for (i=1;i<=rows;i++){
       table=table+"<tr>";
       for (j=1;j<=cols;j++){
        if (i==1){
         if(head=="1"){table += "<th>Title"+j+"</th>"}//Title1 Title2 etc.
         else{table+="<td>"+j+"</td>"}
        }
        else if(j==1){table+="<td>"+i+"</td>"}
       else{table += "<td>#</td>"}
       }
       table+ "</tr>";
      }
      table+=" </table>";
      this.insHTML(table)
     }
    },

    //popup  for browse function
    doWin: function(URL) {
     this.ppw=this.wn.open(URL,'popWhizz','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=640,height=480,top=100');
     this.ppw.focus();
    },

    //check spelling with ieSpell if available
    spellCheck: function(){
     try {
      var axo=new ActiveXObject("ieSpell.ieSpellExtension");
      axo.CheckAllLinkedDocuments(document);
     } catch(e) {
      if(e.number==-2146827859) {
      if(confirm("ieSpell is not installed on your computer. \n Click [OK] to go to download page."))
       {this.wn.open("http://www.iespell.com/download.php","DownLoad")}
      }else{
       alert("Error Loading ieSpell: Exception " + e.number)
      }
     }
    },

    //Returns window width,height
    getWinSize: function(){
     var winW=window.innerWidth?window.innerWidth:document.documentElement.clientWidth?document.documentElement.clientWidth:document.body.clientWidth;
     var winH=window.innerHeight?window.innerHeight:document.documentElement.clientHeight?document.documentElement.clientHeight:document.body.clientHeight;
     return {w:winW,h:winH}
    },

    //Enable or disable fullscreen
    fullscreen: function(){
     var container=this.o("CONTAINER"+this.idTa);
     if(!this.isFullscreen()) {
      if(document.body.style.overflow){document.body.style.overflow="hidden";}else{document.documentElement.style.overflow="hidden";}
      document.body.style.visibility="hidden";
      container.style.visibility="visible"
      container.style.position="absolute";
      container.style.top=(window.pageYOffset?window.pageYOffset:document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop) + "px";
      container.style.left="0";
      container.style.width=this.getWinSize().w+"px";
      container.style.height=this.getWinSize().h+"px";
      this.o("whizzy"+this.idTa).style.backgroundColor="#fff";
      this.o("whizzy"+this.idTa).style.height=this.getWinSize().h-this.o("CONTROLS"+this.idTa).offsetHeight+"px";
     }
     else {
      if(document.body.style.overflow){document.body.style.overflow="visible";}else{document.documentElement.style.overflow="";}
      document.body.style.visibility="visible";
      container.style.position="relative";
      container.style.top=this.o(this.idTa).style.top?this.o(this.idTa).style.top:"0px";
      container.style.left=this.o(this.idTa).style.left?this.o(this.idTa).style.left:"0px";
      container.style.width=this.taOrigSize[this.idTa].w;
      container.style.height="auto";
      this.o("whizzy"+this.idTa).style.backgroundColor="transparent";
      this.o("whizzy"+this.idTa).style.height=this.taOrigSize[this.idTa].h;
     }
    },

    //Detect current whizzywig directory
    getDir: function(){
     var base=location.href,e=document.getElementsByTagName('base'),i;
     for(i=0;i<e.length;i++){
      if(e[i].href){base = e[i].href}
     }
     e=document.getElementsByTagName('script');
     for(i=0;i<e.length;i++) {
      if(e[i].src && /(^|\/)whizzywig\.js([?#].*)?$/i.test(e[i].src)){
       return e[i].src.replace(/whizzywig\.js/i.exec(e[i].src),'')
      }
      else if(e[i].src && /(^|\/)whizzywig[^\/].*?\.js([?#].*)?$/i.test(e[i].src)){
       return e[i].src.replace(/whizzywig[^\/].*?\.js/i.exec(e[i].src), '')
      }
     }
     return '';
    },

    //Check if whizzywig is on fullscreen mode
    isFullscreen: function(){
     if(this.o("CONTAINER"+this.idTa).style.width==this.getWinSize().w+"px"){return true}
     return false
    },

    hideDialogs: function(){
     this.h('LINK_FORM'+this.idTa); this.h('IMAGE_FORM'+this.idTa); this.h('COLOR_FORM'+this.idTa); this.h('TABLE_FORM'+this.idTa); this.h('TABLE_CONTROLS'+this.idTa);
    },

    showDesign: function(){
     this.oW.document.body.innerHTML=this.tidyD(this.o(this.idTa).value);
     this.h(this.idTa); this.h('showWYSIWYG'+this.idTa); this.s('CONTROLS'+this.idTa); this.s('whizzy'+this.idTa);
     if(this.o("whizzy"+this.idTa).contentDocument){this.o("whizzy"+this.idTa).contentDocument.designMode="on"}//FF loses it on hide
     this.oW.focus()
    },

    showHTML: function(){
     this.o(this.idTa).value=this.tidyH(this.oW.document);
     this.h('CONTROLS'+this.idTa); this.h('whizzy'+this.idTa); this.s(this.idTa); this.s('showWYSIWYG'+this.idTa);
     if(this.isFullscreen()){
      this.o(this.idTa).style.width=this.getWinSize().w+"px";
      this.o(this.idTa).style.height=(this.getWinSize().h-this.o('showWYSIWYG'+this.idTa).offsetHeight)+"px";
      this.o(this.idTa).style.borderWidth = "0px"
     }else{
      this.o(this.idTa).style.position="relative";
      this.o(this.idTa).style.width=this.taOrigSize[this.idTa].w;
      this.o(this.idTa).style.height=this.taOrigSize[this.idTa].h;
      this.o(this.idTa).style.borderWidth="1px"
     }
     this.o(this.idTa).focus()
    },

    //tidy up before we go-go
    syncTextarea: function(){
     for (var i=0;i<whizzywig.whizzies.length;i++){
      var t=whizzywig.whizzies[i];
      var d=whizzywig.o("whizzy"+t).contentWindow.document;
      if (whizzywig.o(t).style.display=='block'){d.body.innerHTML=whizzywig.o(t).value}
      var ret=(whizzywig.o(t).nodeName!="TEXTAREA") ? whizzywig.o('wzhid_'+whizzywig.o(t).id) : whizzywig.o(t);
      ret.value=whizzywig.tidyH(d)
     }
    },

    cleanUp: function(){
        this.xC("removeformat",null); this.tidyH(this.oW.document)
    },

    //FF designmode likes <B>,<I>...
    tidyD: function(h){
     h=h.replace(/<(\/?)strong([^>]*)>/gi,"<$1B$2>").replace(/<(\/?)em>/gi,"<$1I>");
     return h
    },

    //attempt valid xhtml
    tidyH: function(d){
     function lc(str){return str.toLowerCase()}
     function qa(str){return str.replace(/(\s+\w+=)([^"][^>\s]*)/gi,'$1"$2"');}
     function sa(str){return str.replace(/("|;)\s*[A-Z-]+\s*:/g,lc);}
     var sz=['medium','xx-small','x-small','small','medium','large','x-large','xx-large'],
     fs=d.getElementsByTagName("FONT"),i,ih;
     for (i=0;i<fs.length;i++){
      if (fs[i].face) {fs[i].style.fontFamily = fs[i].face; fs[i].removeAttribute('face')}
      if (fs[i].size) {fs[i].style.fontSize = sz[fs[i].size]; fs[i].removeAttribute('size')}
      if (fs[i].color) {fs[i].style.color = fs[i].color; fs[i].removeAttribute('color')}
     }
     ih=d.body.innerHTML;
     ih=ih.replace(/(<\/?)FONT([^>]*)/gi,"$1span$2")
     .replace(/(<\/?)[B](\s+[^>]*)?>/gi, "$1strong$2>")
     .replace(/(<\/?)[I](\s+[^>]*)?>/gi, "$1em$2>")
     .replace(/<\/?(COL|XML|ST1|SHAPE|V:|O:|F:|F |PATH|LOCK|IMAGEDATA|STROKE|FORMULAS)[^>]*>/gi, "")
     .replace(/\bCLASS="?(MSOw*|Apple-style-span)"?/gi,"")
     .replace(/<[^>]+=[^>]+>/g,qa) //quote all atts
     .replace(/<(TABLE|TD|TH|COL)(.*)(WIDTH|HEIGHT)=["'0-9A-Z]*/gi, "<$1$2")//no fixed size tables (%OK) [^A-Za-z>]
     .replace(/<([^>]+)>\s*<\/\1>/g, "")//empty tag
     .replace(/><(H|P|D|T|BLO|FOR|IN|SE|OP|UL|OL|LI|SC)/gi,">\n<$1")//newline adjacent blocks
     .replace(/(<BR ?\/?>)([^\r\n])/gi,"$1\n$2")//newline on BR
     .replace(/([^\n])<(P|DIV|TAB|FOR)/gi,"$1\n\n<$2") //add white space
     .replace(/([^\n])<\/(UL|OL|DL|DIV|TAB|FOR)/gi,"$1\n</$2") //end block
     .replace(/([^\n])(<\/TR)/gi,"$1\n $2") //end row
     .replace(/\n<(BLO|LI|OP|TR|IN|DT)/gi,"\n <$1") //indent..
     .replace(/\n<(TD|TH|DD)/gi,"\n  <$1") //..more
     .replace(window.location.href+'#','#') //IE anchor bug
     .replace(/<(IMG|INPUT|BR|HR|LINK|META)([^>]*)>/gi,"<$1$2 />") //self-close tags
     .replace(/(<\/?[A-Z]*)/g,lc) //lowercase tags...
     .replace(/STYLE="[^"]*"/gi,sa); //lc style atts
     return ih
    },

    //keyboard controls for Moz
    kb_handler: function(e){
     var cmd=false, prm=false,k;
     if(e && (e.ctrlKey && e.keyCode==e.DOM_VK_V)||(e.shiftKey && e.keyCode==e.DOM_VK_INSERT))
      {setTimeout('whizzywig.cleanUp()',10)}
     else if(e && e.keyCode==13 && !e.shiftKey &&whizzywig.papa.nodeName=="BODY"){cmd="formatblock";prm="<p>"}
     else if(e && e.ctrlKey){
      k=String.fromCharCode(e.charCode).toLowerCase();
      cmd=(k=='b')?'bold':(k=='i')?'italic':(k=='l')?'link':(k=='m')?'image':false;
     }
     if(cmd){
      whizzywig.makeSo(cmd,prm);
      e.preventDefault();//stop event bubble
      e.stopPropagation()
     }
    },

    //insert HTML into textarea
    doTag: function(html){
     var url,close='',before,after;
     if(!html){html=prompt("Enter some HTML or text to insert:", "")}
     this.o(this.idTa).focus();
     if(html=='<a>'){
      url=prompt("Link address:","http://");
      html='<a href="'+url+'">'
     }
     if(html=='<img>'){
      url=prompt("Address of image:","http://");
      var alt=prompt("Description of image");
      html ='<img src="'+url+'" alt="'+alt+'">';
     }
     if(html.indexOf('<')===0 && html.indexOf('br') != 1 && html.indexOf('img') != 1)
      {close=html.replace(/<([a-z0-6]+).*/,"<\/$1>")}
     if(html != '<strong>' && html != '<em>'){close+='\n'}
     if (document.selection){
      this.sel=document.selection.createRange();
      this.sel.text=html+this.sel.text+close
     }else{
       before=this.o(this.idTa).value.slice(0,this.o(this.idTa).selectionStart);
       this.sel=this.o(this.idTa).value.slice(this.o(this.idTa).selectionStart,this.o(this.idTa).selectionEnd);
       after=this.o(this.idTa).value.slice(this.o(this.idTa).selectionEnd);
       this.o(this.idTa).value =before+html+this.sel+close+after
     }
     this.o(this.idTa).focus()
    },

    //insert HTML at current selection
    insHTML: function(html){
     if(!html){html=prompt(this.t("Enter some HTML or text to insert:"), "")}
     if(html.indexOf('js:')===0){
      try{eval(html.replace(/^js:/,''))} catch(e){}
      return
     }
     this.whereAmI();
     try {this.xC("inserthtml",html+this.sel)}
     catch(e){if (document.selection) {
      if(this.papa&&this.papa.nodeName=='IMG'){this.papa.outerHTML=html+this.papa.outerHTML;}
      else if(this.rng){this.rng.select(); this.rng.pasteHTML(html+this.rng.htmlText)}
     } }
    },

    whereAmI: function(e){
     if(!e){e=whizzywig.wn.event}
     var mu=e&&e.type=='mouseup',pa,id;
     if (whizzywig.msIE){//Issue 11
      whizzywig.oW.document.getElementsByTagName("body")[0].focus();
      whizzywig.sel=whizzywig.oW.document.selection;
      whizzywig.rng=whizzywig.sel.createRange();
      whizzywig.papa=mu?e.srcElement:(whizzywig.sel.type == "Control")?whizzywig.rng.item(0):whizzywig.rng.parentElement();
     }else{
      whizzywig.sel=whizzywig.oW.getSelection();sn=whizzywig.sel.anchorNode;
      whizzywig.papa=mu?e.target:(sn.nodeName == '#text')?sn.parentNode:sn;
     }
     pa=whizzywig.papa;
     whizzywig.trail=whizzywig.papa.nodeName;
     while(!pa.nodeName.match(/^(HTML|BODY)/) && pa.className!="wzCtrl"){
      pa=pa.parentNode;
      whizzywig.trail=pa.nodeName+'>'+whizzywig.trail
     }
     if(pa.className=="wzCtrl"){whizzywig.trail=whizzywig.sel=whizzywig.rng=null}
     id=pa.nodeName=="HTML" ? pa.getElementsByTagName("BODY")[0].id : pa.id.replace("CONTROL","");
     whizzywig.c(id);
     whizzywig.wn.status=id+":"+whizzywig.trail;
     if(whizzywig.trail.indexOf('TABLE')>0){whizzywig.s('TABLE_CONTROLS'+whizzywig.idTa)}else{whizzywig.h('TABLE_CONTROLS'+whizzywig.idTa)}
    },

    //set current whizzy
    c: function(id){
     if(id==="" || this.whizzies.join().indexOf(id)=='-1'){return}
     if (id!=this.idTa){
      this.idTa=id;
      try {this.oW=this.o("whizzy"+id).contentWindow;} catch(e){alert('set current: '+id)}
      if(this.oW){if(this.oW.focus){this.oW.focus()}this.wn.status=this.oW.document.body.id}
     }
    },

    //write to whizzy container
    w: function(str,finalize){
     if(!this.w.temp){this.w.temp=""}
     this.w.temp+=str;
     if(finalize){
      this.o("CONTAINER"+this.idTa).innerHTML+=this.w.temp;
      this.w.temp=""
     }
    },

    textSel: function(){
        if(this.sel && this.sel.type != "None" && this.sel.type != "Caret"){return true}else{alert(this.t("Select some text first")); return false}
    },

    //show element
    s: function(id){
        this.o(id).style.display='block'
    },

    //hide element
    h: function(id){
        this.o(id).style.display='none'
    },

    //get element by ID
    o: function(id){
        return document.getElementById(id)
    },

    //translation
    t: function(key){
        return (this.wn.language && language[key]) ? language[key] :  key;
    }
};

whizzywig.btn._f = whizzywig.getDir()+"icons.png";
