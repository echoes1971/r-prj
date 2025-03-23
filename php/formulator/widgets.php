<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: widgets.php $
 * @package rproject::plugins::widgets
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

class WTObject {
	var $id;
	var $style;
	var $_class;
	function __construct($id="", $style=array(), $class="") {
		$this->id=$id;
		$this->style=$style;
		$this->_class=$class;
	}
	function getOnload() { return ""; }
	function render() { return "";}
}

class WTPage extends WTObject {
	var $headers;
	var $onload;
	var $items;
	var $title;
	var $skin;
	function __construct($id, $title="", $skin="default", $style=array(), $class="") {
		parent::__construct($id, $style, $class);
		
		$this->title = $title;
		$this->skin = $skin;
		$this->items = array();
		$this->headers = array();
		$this->onload = array();
	}
	
	function addHeader($_html) {
		$this->headers[] = $_html;
	}
	function addOnload($_js) {
		$this->onload[]=$_js;
	}
	function addItem($_html, $row=-1) {
		if($row==-1)
			$this->items[]=$_html;
		else
			$this->items[$row]=$_html;
	}
	function addJS($_js) {
		$this->addHeader("<script type=\"text/javascript\">$_js</script>");
	}
	function renderHeader() {
		$ret  = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		$ret .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
		$ret .= "<head>\n";
		$ret .= "<title>".$this->title."</title>\n";
		$ret .= "<link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\" />\n";
		$ret .= "<link rel=\"stylesheet\" href=\"skins/".$this->skin."/style.css\" type=\"text/css\" media=\"all\" />\n";
		if(count($this->headers)>0) {
			$ret .= implode("",$this->headers);
		}
		$ret .= "<style type=\"text/css\">\n";
		$ret .= implode("\n",$this->style);
		$ret .= "</style>\n";
		$ret .= "</head>\n";
		$ret .= "<body ";
		if( count($this->onload)>0 ) {
			$ret .= "onload=\"" . implode(";",$this->onload) . ";\" ";
		}
		$ret .= ">\n";
		return $ret;
	}
	function renderFooter() {
		$ret = "</body></html>";
		return $ret;
	}
	function render() {
		$ret = $this->renderHeader();
		$ret .= implode("",$this->items);
		$ret .= $this->renderFooter();
		return $ret;
	}

}

class WTPanel extends WTObject {
	var $items;
	var $title;
	function __construct($id, $style=array(), $title='',$class="") {
		parent::__construct($id, $style,$class);
		
		$this->title = $title;
		$this->items = array();
	}
	
	function addItem($_html, $row=0) {
		if(!array_key_exists($row, $this->items)) $this->items[$row]=array();
		$this->items[$row][]=$_html;
	}
	function renderHeader() {
		$ret  = "<div id=\"".$this->id."\" class=\"WTPanel\"";
		if(count($this->style)>0) $ret .= "style=\"".implode("",$this->style)."\" ";
		$ret .= "><table class=\"WTPanel\"><tr class=\"WTPanel\"><td class=\"WTPanel\" valign=\"top\">";
		if($this->title>'') {
			$ret .= "<table style=\"width:100%;border-collapse:collapse;\"><tr><td>";
			$ret .= "<div class=\"WTPanelTitle\">";
			$ret .= $this->title;
			$ret .= "<br /><hr /></div>";
			$ret .= "</td></tr></table>";
		}
		$ret .= "";
		return $ret;
	}
	function renderFooter() {
		$ret  = "</td></tr></table></div>";
		return $ret;
	}
	function render() {
		$ret = $this->renderHeader();
		for($row=0; $row<count($this->items); $row++) {
			$td_width = count($this->items[$row])>0 ? 100/count($this->items[$row]) : 100;
			$ret .= "<table style=\"width:100%;border-collapse:collapse;\"><tr><td width=\"$td_width%\">";
			$ret .= implode("</td><td width=\"$td_width%\">",$this->items[$row]);
			$ret .= "</td></tr></table>";
		}
		$ret .= $this->renderFooter();
		return $ret;
	}
}

class WTForm extends WTObject {
	var $fields;
	var $actions;
	var $method;
	function __construct($id, $method="POST", $style=array(),$class="") {
		parent::__construct($id, $style,$class);
		
		$this->fields = array();
		$this->actions = array();
		$this->method = $method;
	}
	
	function addField($_label, $_field, $row=0) {
		if(!array_key_exists($row, $this->fields)) $this->fields[$row]=array();
		$this->fields[$row][]="<th class=\"WTForm\">$_label</th>";
		$this->fields[$row][]="<td class=\"WTForm\">$_field</td>";
	}
	function addAction($label, $url, $row=0, $img="") {
		$this->actions[$row][] = array($label, $url, $img);
	}
	
	function addHidden($id, $value) {
		$this->addField("","<input type=\"hidden\" name=\"$id\" id=\"$id\" value=\"".htmlentities($value)."\" />");
	}
	
	function render() {
		$ret  = "<form id=\"".$this->id."\" class=\"WTForm\" ";
		$ret .= "method=\"".$this->method."\" ";
		$ret .= "action=\"".$this->actions[0][0][1]."\" ";
		if(count($this->style)>0) $ret .= "style=\"".implode("",$this->style)."\" ";
		$ret .= ">";
		if(count($this->fields)>0) {
			$ret .= "<table style=\"width:100%;border-collapse:collapse;\">";
			for($row=0; $row<count($this->fields); $row++) {
				$ret .= "<tr>";
				$ret .= implode("",$this->fields[$row]);
				$ret .= "</tr>";
			}
			$ret .= "</table>";
		}
// 		$ret .= "<br />";
		for($row=0; $row<count($this->actions); $row++) {
			$td_width = count($this->actions[$row])>0 ? 100/count($this->actions[$row]) : 100;
			$ret .= "<table style=\"width:100%;border-collapse:collapse;\"><tr><td align=\"center\" width=\"$td_width%\">";
			$tmp = array();
			for($i=0; $i<count($this->actions[$row]); $i++) {
				$tmp[]="<input type=\"button\" name=\"wtAction\" value=\"".$this->actions[$row][$i][0]."\" onclick=\"javascript:document.getElementById('".$this->id."').action='".$this->actions[$row][$i][1]."';document.getElementById('".$this->id."').submit();\" />";
			}
			$ret .= implode("</td><td align=\"center\" width=\"$td_width%\">",$tmp);
			$ret .= "</td></tr></table>";
// 			if($row<(count($this->actions)-1)) $ret .= "<br/>";
		}
		$ret .= "</form>";
// 		$ret .= "</div>";
		return $ret;
	}

}

class WTClock extends WTObject {
	var $twentyfour_hours;
	function __construct($twentyfour_hours=true, $id="", $style=array(),$class="") {
		parent::__construct($id, $style,$class);
		
		$this->twentyfour_hours=$twentyfour_hours;
	}
	
	function render() {
		$ret = "<div ";
		if($this->id>'') $ret .= "id=\"".$this->id."\" ";
		if(count($this->style)>0) $ret .= "style=\"".implode("",$this->style)."\" ";
		$ret.="><script  type=\"text/javascript\">function js_clock(){var clock_time = new Date();var clock_hours = clock_time.getHours();var clock_minutes = clock_time.getMinutes();var clock_seconds = clock_time.getSeconds();";
		if(!$this->twentyfour_hours) $ret.="var clock_suffix = \"AM\";if (clock_hours > 11){clock_suffix = \"PM\";clock_hours = clock_hours - 12;}";
		$ret.="if (clock_hours == 0){clock_hours = 12;}if (clock_hours < 10){clock_hours = \"0\" + clock_hours;}if (clock_minutes < 10){clock_minutes = \"0\" + clock_minutes;}if (clock_seconds < 10){clock_seconds = \"0\" + clock_seconds;}var clock_div = document.getElementById('".$this->id."');clock_div.innerHTML = clock_hours + \":\" + clock_minutes + \":\" + clock_seconds + \" \"";
		if(!$this->twentyfour_hours) $ret .= "+ clock_suffix";
		$ret .= ";setTimeout(\"js_clock()\", 1000);}js_clock();</script></div>";
		return $ret;
	}
}

class WTObjTree extends WTObject {
	var $root_id;
	function __construct($root_id, $id="", $style=array(),$class="") {
		parent::__construct($id, $style,$class);
		
		$this->root_id=$root_id;
	}
	
	function getStyle() {
		$ret = "";
		$ret.="div.".$this->id." {\n";
		$ret.=" border: 0px dotted black;\n";
		$ret.=" padding:0em;\n";
		$ret.="}\n";
		$ret.="div.".$this->id."_open {\n";
		$ret.=" display:inline;\n";
		$ret.="}\n";
		$ret.="div.".$this->id."_label {\n";
		$ret.=" border: 0px dotted black;\n";
		$ret.=" display:inline;\n";
		$ret.=" padding:0em 0.1em;\n";
		$ret.="}\n";
		$ret.="div.".$this->id."_content {\n";
		$ret.=" margin:0px;\n";
		$ret.=" margin-left:0.5em;\n";
		$ret.=" border: 0px dotted black;\n";
// 		$ret.=" border-left:1px dotted #c0c0c0;\n";
		$ret.=" padding:0px;\n";
		$ret.="}\n";
		$ret.="#".$this->id."_selected {\n";
		$ret.=" display:inline;\n";
		$ret.="}\n";
/*div.<?php echo $this->id; ?> {
  border: 0px dotted black;
  padding:0em;
}
div.<?php echo $this->id; ?>_open {
	display:inline;
}
div.<?php echo $this->id; ?>_label {
  border: 0px dotted black;
  display:inline;
  padding:0em 0.1em;
}
div.<?php echo $this->id; ?>_content {
  margin:0px;
  margin-left:0.5em;
  border: 0px dotted black;
//  border-left:1px dotted #c0c0c0;
  padding:0px;
}
*/
/*		ob_start();
		?><?php
		$ret .= ob_get_contents();
		ob_end_clean();*/
		return $ret;
	}
	
	function getJS() {
		$ret="";
		ob_start();
?>
function <?php echo $this->id; ?>_obj_select(obj_id,obj_name) {
  var myselected = document.getElementById('<?php echo $this->id; ?>_selected');
  myselected.innerHTML=obj_name;
  var myselectedinput = document.getElementById('<?php echo $this->id; ?>_input');
  if(myselectedinput.value>'') {
	var myolddiv = document.getElementById('<?php echo $this->id; ?>_'+myselectedinput.value+'_label');
	myolddiv.style.fontWeight='normal';
  }
  var mynewdiv = document.getElementById('<?php echo $this->id; ?>_'+obj_id+'_label');
	mynewdiv.style.fontWeight='bold';
  myselectedinput.value=obj_id;
}
function <?php echo $this->id; ?>_obj_showHide(obj_id,obj_name) {
  var mydiv = document.getElementById('<?php echo $this->id; ?>_'+obj_id+'_content');
  var mydivopen = document.getElementById('<?php echo $this->id; ?>_'+obj_id+'_open');
  if(mydiv.style.display=='') {
  	if(mydiv.innerHTML>'') {
		mydiv.style.display='none';
		mydivopen.innerHTML='&nbsp;+&nbsp;';
  	} else {
  		var lista = <?php echo $this->id; ?>_searchChilds(obj_id);
  		var myhtml = '';
		var righe = lista.length;
		if(righe==0) {
			mydivopen.innerHTML='&nbsp;&nbsp;&nbsp;';
			/* mydivopen.style.display='none'; */
		} else {
			mydivopen.innerHTML='&nbsp;-&nbsp;';
		}
		for(var r=0; r<righe; r++) { // >
			myhtml += <?php echo $this->id; ?>_render(lista[r])+"\n";
		}
		mydiv.innerHTML=myhtml;
  	}
  } else if(mydiv.style.display=='none') {
	mydiv.style.display='';
	mydivopen.innerHTML='&nbsp;-&nbsp;';
  }
}
function <?php echo $this->id; ?>_searchChilds(obj_id) {
	var dbe = new DBEntity("DBEObject","objects");
	if(obj_id) dbe.setValue('father_id',obj_id);
	var ret = dbmgr.Search( dbe, true, false, "name" );
	if(dbmgr.hasErrors()) {
		alert('Search childs error: '+dbmgr.getErrorMessage());
	}
	return ret;
}
function <?php echo $this->id; ?>_search(obj_id) {
	var dbe = new DBEntity("DBEObject","objects");
	if(obj_id) dbe.setValue('id',obj_id);
	var ret = dbmgr.Search( dbe, true, false, "name" );
	if(ret.length==1) return ret[0];
	if(dbmgr.hasErrors()) {
		alert('Search error: '+dbmgr.getErrorMessage());
	}
	alert('Search error: found '+ret.length+' entries.');
	return null;
}
function <?php echo $this->id; ?>_render(obj) {
  if(obj==null) { document.write('null'); return; }
  try {
    var myhtml = "<div id=\"<?php echo $this->id; ?>_"+obj.getValue('id')+"\" class=\"<?php echo $this->id; ?>\">";
    myhtml += "<div id=\"<?php echo $this->id; ?>_"+obj.getValue('id')+"_open\" class=\"<?php echo $this->id; ?>_open\" onclick=\"javascript:<?php echo $this->id; ?>_obj_showHide(\'"+obj.getValue('id')+"\',\'"+obj.getValue('name')+"\')\" onmouseover=\"javascript:this.style.cursor='pointer';\" onmouseout=\"javascript:this.style.cursor='normal';\">&nbsp;+&nbsp;</div>";
    var myicon = <?php echo $this->id; ?>_icons(obj.dbename);
    if(myicon>'') myhtml += "<img border=\"0\" src=\""+myicon+"\" />";
    myhtml += "<div id=\"<?php echo $this->id; ?>_"+obj.getValue('id')+"_label\" class=\"<?php echo $this->id; ?>_label\" onclick=\"javascript:<?php echo $this->id; ?>_obj_select(\'"+obj.getValue('id')+"\',\'"+obj.getValue('name')+"\')\" onmouseover=\"javascript:this.style.cursor='pointer';\" onmouseout=\"javascript:this.style.cursor='normal';\">";
    myhtml += obj.getValue('name');
//    myhtml += obj.to_string();
    myhtml += "</div>";

    myhtml += "<div id=\"<?php echo $this->id; ?>_"+obj.getValue('id')+"_content\" class=\"<?php echo $this->id; ?>_content\"></div>";

    myhtml += "</div>";
    return myhtml;
  } catch(e) {
    alert('Render error: '+e+'\n'+obj);
  }
}

var <?php echo $this->id; ?>_root_id='<?php echo $this->root_id; ?>';
var dbconn = new DBConnection("http<?php echo $_SERVER["HTTPS"]>''?'s':''; ?>://<?php echo $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".ROOT_FOLDER; ?>xmlrpc_server.php",false);
var dbmgr = new DBMgr(dbconn,false);
dbmgr.setSynchronous(true);
dbmgr.connect();

var myrootid = '<?php echo $this->root_id; ?>';
var myuser = dbmgr.getLoggedUser();

var myrootobj = <?php echo $this->id; ?>_search(myrootid);

<?php
		$ret .= ob_get_contents();
		ob_end_clean();
		$formulator = array_key_exists('formulator',$_SESSION) ?$_SESSION['formulator'] : null;
		if($formulator===null)
			return $ret;
		$ret.="function ".$this->id."_icons(dbename) {\n";
		$ret.=" if(false) {\n";
		foreach($formulator->getAllClassnames() as $className) {
			$myform = $formulator->getInstance($className);
			$mydbe = $myform->getDBE();
			if($mydbe===null) continue;
			if(!($myform->getDetailIcon()>'')) continue;
			$ret.=" } else if(dbename=='".$mydbe->getTypeName()."') {\n";
			$ret.="  return '".getSkinFile($myform->getDetailIcon())."';\n";
		}
		$ret.=" } else {\n";
		$ret.="  return '';\n";
		$ret.=" }\n";
		$ret.="}\n";
		return $ret;
	}
	
	function render() {
		$ret = "<div ";
		if($this->id>'') $ret .= "id=\"".$this->id."\" ";
		if($this->_class>'') $ret .= "class=\"".$this->_class."\" ";
		if(count($this->style)>0) $ret .= "style=\"".implode("",$this->style)."\" ";
		$ret.=">";
		$ret.="<style type=\"text/css\">\n";
		$ret.=$this->getStyle();
		$ret.="</style>\n";
		$ret.="<script type=\"text/javascript\">";
		$ret.=$this->getJS();
		$ret.="</script>";
		$ret.="<div style=\"display:inline;\">Selected:</div><div id=\"".$this->id."_selected\"></div><br/><br/>";
		$ret.="<script type=\"text/javascript\">";
		$ret.="document.write(".$this->id."_render(myrootobj) );";
		$ret.="</script>";
		$ret.="<br/>";
		$ret.="<input type=\"hidden\" id=\"".$this->id."_input\" value=\"\" />";
/*		ob_start();
		?><?php
		$ret.=ob_get_contents();
		ob_end_clean();*/
		$ret.="</div>";
		return $ret;
	}
}



class WTMenu extends WTObject {
	var $_html;
	var $parent_id;
	var $items;
	var $js;
	function __construct($id, $_html, $parent_id=null, $js=null, $style=array(),$class="") {
		parent::__construct($id, $style,$class);
		
		$this->_html=$_html;
		$this->parent_id=$parent_id;
		$this->js=$js==null ? $this->id."_SwitchItems();" : $js;
// 		$this->js=$js==null ? "if(document.getElementById('{$id}Items').style.display=='none'){document.getElementById('{$id}Items').style.display=''} else {document.getElementById('{$id}Items').style.display='none'};" : $js;
		$this->items = array();
	}
	
	function addItem($id, $_html, $js="", $style=null) {
		$this->items[]=new WTMenu($id, $_html, $this->id
				, $this->id."_HideItems();".$js
// 				, "document.getElementById('".$this->id."Items').style.display='none';".$js
				, $style==null ? $this->style : $style);
	}
	function addSubMenu($subMenu) {
// 		$subMenu->js = $this->id."_ShowItems();".$subMenu->js;
		$subMenu->parent_id=$this->id;
		$this->items[]=$subMenu;
	}
	function hasSubMenu() { return count($this->items)>0; }
	
	function render() {
		$ret  = "<div id=\"".$this->id."\" ".(count($this->style)>0?"style=\"".implode("",$this->style)."\" ":'')." ";
		$ret .= "class=\"". ($this->parent_id==null ? "WTMenuRoot" : "WTMenuItem") . "\" ";
// 		if(count($this->items)>0) {
		if($this->js>'') {
			$ret .= "onmouseover=\"javascript:this.style.cursor='pointer';\" onmouseout=\"javascript:this.style.cursor='normal';\" ";
			$ret .= "onclick=\"javascript:".$this->js."\" ";
		}
		$ret .= ">";
		$ret .= $this->_html;
		$ret .= "</div>";
		if(count($this->items)>0) {
			$ret .= "<div id=\"".$this->id."Items\" class=\"". ($this->parent_id==null ? "WTMenuContainerRoot" : "WTMenuContainer") . "\">";
			foreach($this->items as $menuItem) {
				if($this->parent_id!=null)
					$menuItem->js = ($menuItem->hasSubMenu() ?
									'' : $this->parent_id."_CloseMenu();").$menuItem->js;
				$ret .= $menuItem->render();
			}
			$ret .= "</div>";
			$ret .= "<script type=\"text/javascript\">";
			$ret .= "function ".$this->id."_IsShown() { return document.getElementById('".$this->id."Items').style.display!='none'; }";
			$ret .= "function ".$this->id."_HideItems() { document.getElementById('".$this->id."Items').style.display='none'; }";
			$ret .= "function ".$this->id."_ShowItems() { document.getElementById('".$this->id."Items').style.display=''; }";
			$ret .= "function ".$this->id."_SwitchItems() { if(!".$this->id."_IsShown()) { ".$this->id."_ShowItems() } else { ".$this->id."_HideItems() } }";
			$ret .= "function ".$this->id."_CloseMenu() { ".$this->id."_HideItems();".($this->parent_id!=null?$this->parent_id."_CloseMenu();":'')." }";
			$ret .= "</script>";
			$ret .= "<script type=\"text/javascript\">".$this->id."_HideItems();</script>";
		}
		return $ret;
	}
	
}

class WTPopupDiv extends WTObject {
	var $id;
	var $title;
	var $contenuto;
	var $path_immagini;

	var $width;
	var $height;

	function __construct($id, $title, $contenuto, $width='400px', $height='150px', $path_immagini='', $style=array(),$class="WTpopupDiv") {
		parent::__construct($id, $style,$class);
		$this->title=$title;
		$this->contenuto=$contenuto;
		$this->path_immagini=$path_immagini;
		
		$this->width=$width;
		$this->height=$height;
	}
	
	function getStyle() {
		ob_start();
?>#<?php echo $this->id; ?> {
	visibility: hidden;
	display: block;
	position:absolute;
	top:0px;
	bottom:0px;
	z-index:1000;
	height:100%; width:100%; /* Per IE */
}
#<?php echo $this->id; ?>Contenuto {
	width:<?php echo $this->width; ?>;
	height:<?php echo $this->height; ?>;
	text-align:center;
}
#<?php echo $this->id; ?>ContenutoInterno {
	padding: 0.5em;
}
<?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	
	function getJS() {
		ob_start();
?>function <?php echo $this->id; ?>_pageDimensions(obj) {
	var d = new Object;
	var test1 = obj.body.scrollHeight;
	var test2 = obj.body.offsetHeight
	/*
		alert(document.documentElement.scrollTop || document.body.scrollTop);
		alert('body: '+obj.body.style.top+' '+obj.body.style.height);
		alert('Scroll: '+obj.body.scrollTop+' '+obj.body.scrollHeight);
		alert('Offset: '+obj.body.offsetTop+' '+obj.body.offsetHeight);
		alert('Client: '+obj.body.clientTop+' '+obj.body.clientHeight);
	*/
	if (test1 > test2) // all but Explorer Mac
	{
		d.x = obj.body.scrollWidth;
		d.y = obj.body.scrollHeight;
		d.top = obj.body.offsetTop;
		d.left = obj.body.offsetLeft;
	} else // Explorer Mac;
			//would also work in Explorer 6 Strict, Mozilla and Safari
	{
		d.x = obj.body.offsetWidth;
		d.y = obj.body.offsetHeight;
//		d.y = obj.body.offsetHeight + obj.documentElement.offsetHeight + 50;
		d.top = document.documentElement.scrollTop || document.body.scrollTop;
		d.left = document.documentElement.scrollLeft || document.body.scrollLeft;
	}
try {
	d.outerHeight=window.outerHeight;
	d.outerWidth=window.outerWidth;
	d.innerHeight=window.innerHeight;
	d.innerWidth=window.innerWidth;
}catch(e) {}
	return d;
}

/**
 * Mostra il popupDiv
 */
function <?php echo $this->id; ?>_popup(messaggio) {
	myDivContenuto = document.getElementById('<?php echo $this->id; ?>ContenutoInterno');
	
	myhtml = '<div>'+messaggio.replace(/\n/g, '<br\/>')+'</div>';
	myhtml = myhtml; // + "<?php echo str_replace("\"","\\\"",$this->getContenutoBottoni()); ?>";
	myDivContenuto.innerHTML = myhtml;
	<?php echo $this->id; ?>_mostra();
}
/**
 * Mostra il popupDiv
 */
function <?php echo $this->id; ?>_mostra() {
	mydiv = document.getElementById('<?php echo $this->id; ?>');
	myDivSfondo = document.getElementById('<?php echo $this->id; ?>Sfondo');
	myDivRiquadro = document.getElementById('<?php echo $this->id; ?>Riquadro');
	myDivContenuto = document.getElementById('<?php echo $this->id; ?>Contenuto');
	myDivIFrame = document.getElementById('<?php echo $this->id; ?>_iframe');
	d = <?php echo $this->id; ?>_pageDimensions(document);
//	console.log("d.outerWidth="+d.outerWidth+"; d.outerHeight="+d.outerHeight);
//	console.log("; d.innerWidth="+d.innerWidth+"d.innerHeight="+d.innerHeight);
//	console.log("d.x="+d.x+"; d.y="+d.y+"; d.top="+d.top+"; d.left="+d.left);
//	alert('x='+d.x+'; y='+d.y+'; top='+d.top+'; left='+d.left );
//	alert('outerWidth='+d.outerWidth+'; outerHeight='+d.outerHeight);
	if(mydiv.style.visibility == "visible") {
		mydiv.style.visibility='hidden';
	} else {
var mywidth = <?php echo str_replace('px','',$this->width); ?>;
var myheight = <?php echo str_replace('px','',$this->height); ?>;

//console.log("mywidth="+mywidth+"; myheight="+myheight+"; d.outerWidth="+d.outerWidth+"; d.outerHeight="+d.outerHeight);

if(mywidth>d.outerWidth) mywidth=d.outerWidth; //-50;
if(myheight>d.outerHeight) myheight=d.outerHeight-100;

//console.log("==> mywidth="+mywidth+"; myheight="+myheight+"; d.outerWidth="+d.outerWidth+"; d.outerHeight="+d.outerHeight);


try {
	myDivIFrame.style.width=mywidth-8;
	myDivIFrame.style.height=myheight-8-50;
} catch(e) { alert(e); }
		try { myDivSfondo.style.height=d.y;/*'100%';*/ } catch(e) { alert(e); }
		try { myDivSfondo.style.width=d.x;/*'100%';*/ } catch(e) { alert(e); }

		try { myDivRiquadro.style.left = ( (d.x-mywidth)/2 ) + 'px'; } catch(e) { alert(e); }
		try { myDivRiquadro.style.top = ( (d.outerHeight - myheight)/2 ) + 'px'; } catch(e) { alert(e); }
//                try { myDivRiquadro.style.top = ( (d.top + d.y - myheight)/2 ) + 'px'; } catch(e) { alert(e); }

		try { myDivContenuto.style.left = ( (d.x-mywidth)/2 ) + 'px'; } catch(e) { alert(e); }
		try {
			myDivContenuto.style.top = ( (d.innerHeight - myheight)/2 ) + 'px';
//			myDivContenuto.style.top = ( (d.outerHeight - myheight)/2 ) + 'px';
			
		} catch(e) { alert(e); }
		console.log("myDivContenuto.style.left="+myDivContenuto.style.left);
		console.log("myDivContenuto.style.top="+myDivContenuto.style.top);

		if(mywidth==d.outerWidth) {
			try {
				myDivContenuto.style.width = d.x+'px';
				myDivIFrame.style.width=(d.x-16)+'px';
			} catch(e) { alert(e); }
			console.log("myDivContenuto.style.width="+myDivContenuto.style.width);
			console.log("myDivIFrame.style.width="+myDivIFrame.style.width);
		}

//                try { myDivContenuto.style.top = ( (d.top + d.y - myheight)/2 ) + 'px'; } catch(e) { alert(e); }

		myDivContenuto.style.width=mywidth;
		myDivContenuto.style.height=myheight;

		mydiv.style.visibility='visible';
	}
}
<?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	function getContenutoBottoni() {
		ob_start();
		?><div id="<?php echo $this->id; ?>ContenutoBottoni" class="<?php echo $this->_class; ?>ContenutoBottoni"><input type="button" value="OK" onClick="javascript:<?php echo $this->id; ?>_mostra();" class="<?php echo $this->_class; ?>ContenutoBottoni" /></div><?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
/*	function getContenutoTitleBottoni() {
		return "";
	}*/
	function getContenutoTitleBottoni() {
		$ret = "&nbsp;".$this->title;
		ob_start();
		?><input title="Close" type="button" value="&times;" onClick="javascript:<?php echo $this->id; ?>_mostra();" class="<?php echo $this->_class; ?>ContenutoBottoni" /><?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	function getDiv() {
		ob_start();
		?><!-- PopupDiv: inizio. --><?php
//<!-- div id="<--?php echo $this->id; ?-->" style="z-index:-1;display:none;" -->
?><div id="<?php echo $this->id; ?>">
 <div id="<?php echo $this->id; ?>Sfondo" class="<?php echo $this->_class; ?>Sfondo">
  <div id="<?php echo $this->id; ?>Riquadro" class="<?php echo $this->_class; ?>Riquadro"></div>
 </div>
 <div id="<?php echo $this->id; ?>Contenuto" class="WTPanel">
  <table class="WTPanel">
   <tr class="WTPanel">
    <td class="WTPanel">
     <div id="<?php echo $this->id; ?>ContenutoTitle" onmouseover="javascript:this.style.cursor='move';" onmouseout="javascript:this.style.cursor='normal';" class="<?php echo $this->_class; ?>ContenutoTitle">&nbsp;<?php echo $this->title; ?></div><?php
	if($this->getContenutoTitleBottoni()>'') echo "<div id=\"".$this->id."ContenutoTitleBottoni\" class=\"".$this->_class."ContenutoTitleBottoni\">".$this->getContenutoTitleBottoni()."</div>";
	echo "<div id=\"".$this->id."ContenutoInterno\">".$this->contenuto."</div>";
	echo $this->getContenutoBottoni();
?></td></tr></table>
 </div>
</div>
<!-- PopupDiv: fine. --><?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	function getOnload() { return "new Draggable('".$this->id."Contenuto', { handle: document.getElementById('".$this->id."ContenutoTitle') })"; }
	function render() {
		return "<style type=\"text/css\">\n".$this->getStyle()."\n</style>\n<script type=\"text/javascript\">\n".$this->getJS()."\n</script>\n".$this->getDiv();
	}
}

class WTPopupIFrame extends WTPopupDiv {
	var $url;
	function __construct($id, $title, $url, $width='600px', $height='400px', $path_immagini='', $style=array(),$class="WTpopupIFrame") {
		parent::__construct($id, $title, "<iframe id=\"".$id."_iframe\" src=\"$url\"></iframe>", $width, $height, $path_immagini, $style,$class);
		$this->url=$url;
	}
	function getStyle() {
		ob_start();
?>
#<?php echo $this->id; ?>Riquadro {
	display:none;
}
#WTpopupIFrameContenuto {
	/*
	width: 100%;
	height: 100%;
	width: <?php echo $this->width; ?>;
	height: <?php echo $this->height; ?>;
	*/
}
/*#<?php echo $this->id; ?>ContenutoTitleBottoni {
	float: right;
}*/
#<?php echo $this->id; ?>ContenutoInterno {
	padding: 2px;
}
#<?php echo $this->id; ?>ContenutoBottoni {
	padding-top:0;
}
#<?php echo $this->id; ?>_iframe {
	width: <?php echo str_replace('px','',$this->width)-8; ?>px;
	height: <?php echo str_replace('px','',$this->height)-8-50; ?>px;
}
<?php
		$ret = ob_get_contents();
		ob_end_clean();
		return parent::getStyle()."\n".$ret;
	}
	function getJS() {
		ob_start();
		?>
function <?php echo $this->id; ?>_mostra_url(atitle,aurl) {
	mydivtitle = document.getElementById('<?php echo $this->id; ?>ContenutoTitle');
	mydivtitle.innerHTML = '&nbsp;'+atitle;
	mydiv = document.getElementById('<?php echo $this->id; ?>_iframe');
	mydiv.src = aurl;
	<?php echo $this->id; ?>_mostra();
}
		<?php
		$ret = ob_get_contents();
		ob_end_clean();
		return parent::getJS().$ret;
	}
	function getContenutoTitleBottoni() {
		$ret = "&nbsp;".$this->title;
		ob_start();
		?><input title="Close" type="button" value="&times;" onClick="javascript:<?php echo $this->id; ?>_mostra();" class="<?php echo $this->_class; ?>ContenutoBottoni" /><?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	function getContenutoBottoni() {
		ob_start();
		?><div id="<?php echo $this->id; ?>ContenutoBottoni" class="<?php echo $this->_class; ?>ContenutoBottoni" title="Close and refresh"><input type="button" value="Close" onClick="javascript:<?php echo $this->id; ?>_mostra();location.reload();" class="<?php echo $this->_class; ?>ContenutoBottoni" /></div><?php
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

}

?>