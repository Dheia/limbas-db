<?php
/*
 * Copyright notice
 * (c) 1998-2016 Limbas GmbH - Axel westhagen (support@limbas.org)
 * All rights reserved
 * This script is part of the LIMBAS project. The LIMBAS project is free software; you can redistribute it and/or modify it on 2 Ways:
 * Under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * Or
 * In a Propritary Software Licence http://limbas.org
 * The GNU General Public License can be found at http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 * Version 3.0
 */

/*
 * ID: 110
 */


?>

<Script language="JavaScript">

if(browser_ns5){document.captureEvents(Event.MOUSEDOWN | Event.MOUSEUP);}
document.onmouseup = endDrag;

var dy = 0;
var current = null;
var zIndexTop = 1;
var currentfield = null;

function startDrag(evt) {
	if(browser_ns5){
		var obj = evt.target;
		current = obj.style;
		dy = evt.pageY - parseInt(current.top);
	}else{
		var obj = window.event.srcElement;
		current = obj.style;
		dy = window.event.clientY - parseInt(current.top);
	}

	zIndexTop++;
	current.zIndex = zIndexTop;

	if(obj.id.substr(0,5) == 'field'){
		if(browser_ns5){document.captureEvents(Event.MOUSEMOVE)}
		document.onmousemove = drag;
	}

	return false;
}

function drag(evt) {
	if (current != null) {
		if(browser_ns5){
			current.top = evt.pageY - dy;
		}else{
			current.top = window.event.clientY - dy;
		}
	}
	return false;
}

function endDrag(e) {
	if(!currentfield){return;}
	if(current){current.top = 0;}
	if(browser_ns5){document.releaseEvents(Event.MOUSEMOVE);}
	document.onmousemove = null;
	document.onmousedown = null;
	current = null;
	document.getElementById('field'+currentfield).style.fontWeight = '';
	document.getElementById('field'+currentfield).style.color = 'blue';
	
	return false;
}

/* --- delete field ----------------------------------- */
function delete_field(id,name,physical) {
	ph = '';
	if(physical){
		physical = 1;
		ph = '( <?=$lang[1727]?> ) ';
	}else{
		physical = 0;
		ph = '( <?=$lang[2811]?> ) ';
	}
	var desc = confirm("<?=$lang[2019]?> "+ph+'\n### '+name+" ###");
	if(desc){
		document.location.href='main_admin.php?&action=setup_gtab_ftype&tab_group=<?=$tab_group?>&del_tabelle=<?echo urlencode($table_gtab[$bzm]);?>&column='+name+'&column_id='+id+'&del=1&atid=<?=$atid?>&drop_physical='+physical;
	}
}

/* --- convert field ----------------------------------- */
function convert_field(convert,fieldid,name,size) {
	var mess = '<?=$lang[2021]?>';
	if(convert == 33 || convert == 34){mess = 'be care of if your referential integrity!';}
	var desc = confirm("<?=$lang[2020]?> "+name+" ?\n"+mess);
	
	if(desc){
		document.form1.fieldid.value = fieldid;
		document.form1.convert_value.value = convert;
		document.form1.convert_size.value = size;
		document.form1.submit();
	}
}

/* --- extend field ----------------------------------- */
function extend_field(extend,fieldid) {
	document.form1.fieldid.value = fieldid;
	document.form1.extend_value.value = extend;
	document.form1.submit();
}

/* --- view rule ----------------------------------- */
function viewrule_field(val,fieldid) {
	document.form1.fieldid.value = fieldid;
	document.form1.view_rule.value = val+" ";
	document.form1.submit();
}

/* --- edit rule ----------------------------------- */
function editrule_field(val,fieldid) {
	document.form1.fieldid.value = fieldid;
	document.form1.edit_rule.value = val+" ";
	document.form1.submit();
}

/* --- Element-Aktivierung ----------------------------------- */
function aktivate(id) {
	document.getElementById('field'+id).style.top = 20;
	document.getElementById('field'+id).style.fontWeight = 'bold';
	document.getElementById('field'+id).style.color = 'black';
	document.onmousedown = startDrag;
	currentfield = id;
}

function move_field(id){
	if(currentfield != id && currentfield && id){
		document.form1.move_to.value = id;
		document.form1.fieldid.value = currentfield;
		document.form1.submit();
	}
	currentfield = null;
}

// Ajax edit field
function ajaxEditField(fieldid,act){
	ajaxGet(null,"main_dyns_admin.php","editTableField&gtabid=<?=$atid?>&fieldid=" + fieldid + "&tab_group=<?=$tab_group?>&act=" + act,null,"ajaxEditFieldPost","form2");
}

function ajaxEditFieldPost(result){
	element = document.getElementById("lmbAjaxContainer");
	element.style.visibility = '';
	element.innerHTML = result;
	limbasSetCenterPos(element);
	element.style.left = '180px';
	ajaxEvalScript(result);
	
	//hide_selects(1);
}


function change_memoindex(fieldid,el){
	if(!el.checked){
		var ok = confirm('<?=$lang[1718]?>');
		if(ok){
			document.form1.fieldid.value = fieldid;
			document.form1.memoindex.value=1;
			document.form1.submit();
		}else{el.checked = 1;}
	}else{
		document.form1.fieldid.value = fieldid;
		document.form1.memoindex.value=1;
		document.form1.submit();
	}
}

function change_wysiwyg(fieldid,el){
	document.form1.fieldid.value = fieldid;
	document.form1.wysiwyg.value=1;
	document.form1.submit();

}

function newwin(FIELDID,ATID,POOL,TYP) {
fieldselect = open("main_admin.php?<?=SID?>&action=setup_fieldselect&fieldid=" + FIELDID + "&atid=" + ATID + "&pool=" + POOL + "&field_pool=" + POOL + "&typ=" + TYP ,"Auswahlfelder","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=540,height=500");
}
function newwin2(FELD,TABELLE,VIEW) {
genlink = open("main_admin.php?<?=SID?>&action=setup_genlink&tab=auftrag_ftype&&tab_group=<?echo $tab_group;?>tab=" + TABELLE + "&fieldid=" + FELD + "&atid=" + VIEW + "&typ=gtab_ftype" ,"Link_Generator","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=420,height=150");
}
function newwin3(FELD,TABID,ATID,ARGTYP) {
argument = open("main_admin.php?<?=SID?>&action=setup_argument&tab_group=<?echo $tab_group;?>&atid=" + ATID + "&tab_id=" + TABID + "&fieldid=" + FELD + "&typ=gtab_ftype" + "&argument_typ=" + ARGTYP ,"Link_Generator","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=420,height=300");
}
function newwin4(FELD,TAB,ATID) {
verknfield = open("main_admin.php?<?=SID?>&action=setup_verknfield&tab_group=<?echo $tab_group;?>&typ=gtab_ftype&tabid=" + ATID + "&tab=" + TAB + "&fieldid=" + FELD + "" ,"Verknuepfung","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=420,height=300");
}
function newwin5(FELD,ATID,VERKNID) {
verkn_editor = open("main_admin.php?<?=SID?>&action=setup_verkn_editor&tabid=" + ATID + "&fieldid=" + FELD + "&verkntabid=" + VERKNID + "" ,"Verknuepfung_Editor","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=550,height=600");
}
function newwin6(FELD,TAB,ATID) {
upload_editor = open("main_admin.php?<?=SID?>&action=setup_upload_editor&tab_group=<?echo $tab_group;?>&tabid=" + ATID + "&tab=" + TAB + "&fieldid=" + FELD + "" ,"Verknuepfung","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=420,height=300");
}
function newwin7(FIELDID,TABID) {
grouping_edior = open("main_admin.php?<?=SID?>&action=setup_grouping_editor&tabid=" + TABID + "&fieldid=" + FIELDID + "" ,"Grouping_Edito","toolbar=0,location=0,status=0,menubar=0,scrollbars=1,resizable=0,width=420,height=300");
}


function viewsysfield(){
document.getElementById("sys0").style.display="none";
document.getElementById("sys1").style.display="";
document.getElementById("sys2").style.display="";
document.getElementById("sys3").style.display="";
document.getElementById("sys4").style.display="";
document.getElementById("sys5").style.display="";
document.getElementById("sys6").style.display="";
document.getElementById("sys7").style.display="";
document.getElementById("sys8").style.display="";
}

function checkfiledtype(el,el2){
	// || value == "49"  versiondesc
	
	
	if(el){
		var value = el[el.selectedIndex].value;
		if(el[el.selectedIndex].id){var defaultsize=el[el.selectedIndex].id;}
	}
	if(el2){var value2 = el2[el2.selectedIndex].value;}



	if(!value2){
		if(value == "46"){
			document.getElementById("inherit_typ").style.display = "";
			document.getElementById("argument_typ").style.display = "none";
		}else if(value == "29" || value == "53"){
			document.getElementById("inherit_typ").style.display = "none";
			document.getElementById("argument_typ").style.display = "";
		}else{
			document.getElementById("inherit_typ").style.display = "none";
			document.getElementById("argument_typ").style.display = "none";
		}
	}else{
		value = value2;
	}
	
	document.getElementById("typ_size").style.visibility='hidden';
	
	if(defaultsize){
		document.getElementById("typ_size").value=defaultsize;
		document.getElementById("typ_size").style.visibility='visible';
	}

}


var aktive_inherit = 0;
function checkinherittype(value){
	if(aktive_inherit){document.getElementById("inherit_field_"+aktive_inherit).style.display = "none";}
	document.getElementById("inherit_field_"+value).style.display = "";
	aktive_inherit = value;
}

var activ_menu = null;
function divclose(){
	if(!activ_menu){
		hide_trigger();
		document.getElementById("lmbAjaxContainer").style.visibility="hidden";
	}
	activ_menu = 0;
}

function hide_trigger(){
	var ar = document.getElementsByTagName("span");
	for (var i = ar.length; i > 0;) {
		cc = ar[--i];
		if(cc.id.substring(0,13) == "field_trigger"){
			cc.style.display='none';
		}
	}
}




function LIM_deactivate(elid){
	if(document.getElementById("tab"+elid)){
		document.getElementById("tab"+elid).style.display = 'none';
	}
}


function LIM_activate(el,elid){
	
	LIM_deactivate('1');
	LIM_deactivate('2');
	
	if(!el){el = document.getElementById('menu'+elid);}
	
	limbasSetLayoutClassTabs(el,'tabpoolItemInactive','tabpoolItemActive');
	if(document.getElementById("tab"+elid)){
		document.getElementById("tab"+elid).style.display = '';
	}
}

</SCRIPT>


<div id="lmbAjaxContainer" class="ajax_container"
	style="position: absolute; visibility: hidden;" OnClick="activ_menu=1;"></div>

<?php
/* --- Tabellen-Liste --------------------------------------------- */
$bzm = $atid;
if($table_gtab[$bzm]) {

	if($table_typ[$bzm] == 5){$isview = 1;}
	
	/* --- Spaltenüberschriften --------------------------------------- */
	?>
<FORM ACTION="main_admin.php" METHOD=post NAME="form1">
	<input type="hidden" name="<?echo $_SID;?>" value="<?echo session_id();?>"> 
    <input type="hidden" name="action" value="setup_gtab_ftype"> 
	<input type="hidden" name="new_gtab" value="<?echo $table_gtab[$bzm]?>"> 
	<input type="hidden" name="new_conf_gtab" value="<?echo $conf_gtab[$bzm]?>"> 
	<input type="hidden" name="tab_group" value="<?echo $tab_group?>"> 
	<input type="hidden" name="tabelle"> <input type="hidden" name="fieldid"> 
	<input type="hidden" name="spelling"> <input type="hidden" name="desc"> 
	<input type="hidden" name="uniquefield"> 
	<input type="hidden" name="column">
	<input type="hidden" name="columnid"> 
	<input type="hidden" name="keyfield"> <input type="hidden" name="mainfield"> 
	<input type="hidden" name="fieldindex"> <input type="hidden" name="atid" VALUE="<?echo $bzm;?>"> 
	<input type="hidden" name="def"> <input type="hidden" name="def_bool"> 
    <input type="hidden" name="verk"> 
    <input type="hidden" name="artleiste"> <input type="hidden" name="groupable">
	<input type="hidden" name="dynsearch"> 
	<input type="hidden" name="move_to"> <input type="hidden" name="argument_edit"> 
	<input type="hidden" name="argument_search"> 
	<input type="hidden" name="convert_value"> <input type="hidden" name="convert_size"> 
	<input type="hidden" name="extend_value"> 
	<input type="hidden" name="new_keyid"> 
	<input type="hidden" name="memoindex"> 
	<input type="hidden" name="nformat"> 
	<input type="hidden" name="ncurrency"> 
	<input type="hidden" name="wysiwyg"> 
	<input type="hidden" name="select_cut">
	<input type="hidden" name="trigger"> 
	<input type="hidden" name="quicksearch"> <input type="hidden" name="view_rule"> 
	<input type="hidden" name="edit_rule"> <input type="hidden" name="ajaxsave">
	<input type="hidden" name="collreplace">
	<input type="hidden" name="solve_dependency">
	
	<div class="lmbPositionContainerMain">

		<TABLE class="tabfringe" BORDER="0" cellspacing="1" cellpadding="2">

			<TR class="tabHeader">
				<TD class="tabHeaderItem" colspan="24" HEIGHT="20">
    <?php
    echo $table_gtab[$bzm]." (".$beschreibung_gtab[$bzm].")";
    if($isview){echo "&nbsp;&nbsp;&nbsp;<a href=\"main_admin.php?&action=setup_gtab_view&viewid=$atid\"><i border=\"0\" style=\"cursor:pointer\" class=\"lmb-icon lmb-organisation-edit\"></i></a>";}
    ?>
    
    </TD>
			</TR>

			<TR class="tabHeader">
				<TD class="tabHeaderItem"></TD>
				<TD class="tabHeaderItem">ID</TD>
				<TD class="tabHeaderItem"></TD>
				<?php if(!$isview){echo "<TD class=\"tabHeaderItem\">$lang[933]</TD>";}?>
				<TD class="tabHeaderItem"><?=$lang[922]?></TD>
				<TD class="tabHeaderItem"><?=$lang[923]?></TD>
				<TD class="tabHeaderItem"><?=$lang[924]?></TD>
				<TD class="tabHeaderItem"><?=$lang[925]?></TD>
				<TD class="tabHeaderItem"><?=$lang[2654]?></TD>
            <?if(!$isview){?><TD class="tabHeaderItem" ALIGN="right"><?=$lang[928]?></TD><?}?>
            <TD class="tabHeaderItem" ALIGN="right"><SPAN STYLE="width: 90px;"><?=$lang[929]?></SPAN></TD>
				<TD class="tabHeaderItem" ALIGN="center"><?=$lang[930]?></TD>
				<TD class="tabHeaderItem" ALIGN="center"><?=$lang[2504]?></TD>
				<TD class="tabHeaderItem" ALIGN="center"><?=$lang[2505]?></TD>
            <?if(!$isview){?><TD class="tabHeaderItem" ALIGN="center"><?=$lang[2570]?></TD><?}?>
            <?if($gtrigger[$bzm] AND !$isview){?><TD
					class="tabHeaderItem"><?=$lang[2506]?></TD><?}?>
            <?#$lang[926]?>
            <TD class="tabbHeaderItem"><?=$lang[2235]?></TD>
            <?if(!$isview){?><TD class="tabHeaderItem"><?=$lang[1884]?></TD><?}?>
            <?if(!$isview){?><TD class="tabHeaderItem"><?=$lang[927]?></TD><?}?>
            <?if(!$isview){?><TD class="tabHeaderItem"><?=$lang[2639]?></TD><?}?>
            <?if(!$isview){?><TD class="tabHeaderItem"><?=$lang[2640]?></TD><?}?>
            <TD class="tabHeaderItem">&nbsp;<?=$lang[932]?></TD>
				<TD class="tabHeaderItem"><?=$lang[2507]?></TD>
				<TD class="tabHeaderItem"><?=$lang[1459]?></TD>
				<TD class="tabHeaderItem"><?=$lang[2672]?></TD>
			</TR>

	<?php  
    /* --- Ergebnisliste --------------------------------------- */
    if($result_fieldtype[$table_gtab[$bzm]]["field_id"]){
	foreach ($result_fieldtype[$table_gtab[$bzm]]["field_id"] as $bzm1 => $val){
            ?>

            <TR
				OnMouseOver="this.style.backgroundColor='<?=$farbschema["WEB7"]?>'"
				OnMouseOut="this.style.backgroundColor=''">

				<TD VALIGN="TOP"><?#<IMG SRC="pic/edit2.gif" BORDER="0" style="cursor:pointer">?></TD>

				<TD VALIGN="TOP"><?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?></TD>
            
            <?php
            # --- edit ------
            echo "<TD VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            echo "<i OnClick=\"activ_menu=1;ajaxEditField('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."')\" class=\"lmb-icon lmb-cog-alt\" BORDER=\"0\" style=\"cursor:pointer;\"></i></A>";
            echo "</TD>";
            
	       if(!$isview){
	            # --- delete ------
	            if((lmb_strtoupper($table_gtab[$bzm]) == "LDMS_FILES" AND $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1] <= 33) or (lmb_strtoupper($table_gtab[$bzm]) == "LDMS_META" and $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1] <= 37)) {
                    echo "<TD></TD>";
                } else {
                    echo "<TD VALIGN=\"TOP\" ALIGN=\"CENTER\" style=\"cursor:pointer\">";
                    ?>
					<i class="lmb-icon lmb-trash" onclick="delete_field('<?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?>','<?=urlencode($result_fieldtype[$table_gtab[$bzm]]["field"][$bzm1])?>')" style="cursor:pointer" border="0"></i>
					<i class="lmb-icon lmb-minus-circle" onclick="delete_field('<?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?>','<?=urlencode($result_fieldtype[$table_gtab[$bzm]]["field"][$bzm1])?>',1)" style="cursor:pointer;height:13px;vertical-align:bottom" border="0"></i>
					<?php
                    echo "</TD>";
                }
            }
            if($result_fieldtype[$table_gtab[$bzm]]["view_dependency"][$bzm1]){$color = '#d041f4';}else{$color = 'blue';}
            ?>
            
            <TD VALIGN="TOP"
					OnMouseOver="this.style.backgroundColor = '<?=$farbschema["WEB6"]?>'"
					OnMouseOut="this.style.backgroundColor = ''"
					OnMouseUp="move_field('<?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?>')"
					OnMouseDown="aktivate('<?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?>');">
					<SPAN
					ID="field<?=$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]?>"
					STYLE="position: relative; top: 0; left: 0; cursor: n-resize; color: <?=$color?>">
            <?echo $result_fieldtype[$table_gtab[$bzm]]["field"][$bzm1];?>
            </SPAN>&nbsp;
				</TD>
				<TD VALIGN="TOP"><INPUT TYPE="TEXT" SIZE="25"
					NAME="DESC_<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>"
					VALUE="<?echo $lang[$result_fieldtype[$table_gtab[$bzm]]["beschreibung_feld"][$bzm1]];?>"
					ONCHANGE="this.form.fieldid.value='<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>';this.form.desc.value=this.form.DESC_<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>.value;this.form.submit();"></TD>
				<TD VALIGN="TOP"><INPUT TYPE="TEXT" SIZE="16"
					NAME="SPELLING_<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>"
					VALUE="<?echo $lang[$result_fieldtype[$table_gtab[$bzm]]["spelling"][$bzm1]];?>"
					ONCHANGE="this.form.fieldid.value='<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>';this.form.spelling.value=this.form.SPELLING_<?echo $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];?>.value;this.form.submit();"></TD>
				<TD VALIGN="TOP" nowrap>
			<?php
			# Typ
			if($result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]){
				echo $result_type["beschreibung"][$result_type["arg_result_datatype"][$result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]]]."&nbsp;-&nbsp;";
			}
			echo $result_fieldtype[$table_gtab[$bzm]]["beschreibung_typ"][$bzm1];
			echo "&nbsp;";
			if($result_fieldtype[$table_gtab[$bzm]]["inherit_tab"][$bzm1]){
				echo "<i>[$lang[2086]]</i>";
			}
			
			if($result_fieldtype[$table_gtab[$bzm]]["scale"][$bzm1]){
				$fsize = $result_fieldtype[$table_gtab[$bzm]]["precision"][$bzm1].",".$result_fieldtype[$table_gtab[$bzm]]["scale"][$bzm1];
			}else{
				$fsize = $result_fieldtype[$table_gtab[$bzm]]["precision"][$bzm1];
			}
			
			if($result_fieldtype[$table_gtab[$bzm]]["type_name"][$bzm1]){
				echo "<i>(".$result_fieldtype[$table_gtab[$bzm]]["type_name"][$bzm1]." ".$fsize.")</i>";
			}
			
			# size 
			echo "</TD><TD valign=\"top\">";
			if($result_type["hassize"][$result_fieldtype[$table_gtab[$bzm]]["datatype_id"][$bzm1]]){
				echo "<input type=\"text\" style=\"width:40px\" value=\"".$result_fieldtype[$table_gtab[$bzm]]["field_size"][$bzm1]."\" onchange=\"convert_field('".$result_fieldtype[$table_gtab[$bzm]]["datatype_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]]["field"][$bzm1]."',this.value);\">";
			}
			echo "</TD>";
			

			if(!$isview){
				echo "<TD  VALIGN=\"TOP\" ALIGN=\"RIGHT\" nowrap>";
				# defaultvalue
				if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 11){

	       			if($result_fieldtype[$table_gtab[$bzm]][verkntabid][$bzm1]){
	       				$sqlquery = "SELECT BESCHREIBUNG FROM LMB_CONF_TABLES WHERE TAB_ID = ".$result_fieldtype[$table_gtab[$bzm]]["verkntabid"][$bzm1];
	       				$rs = odbc_exec($db,$sqlquery) or errorhandle(odbc_errormsg($db),$sqlquery,$action,__FILE__,__LINE__);
	       				echo $lang[odbc_result($rs, "BESCHREIBUNG")]." | ";
	       			}
	       			if($result_fieldtype[$table_gtab[$bzm]][verknfieldid][$bzm1]){
	       				$sqlquery = "SELECT SPELLING FROM LMB_CONF_FIELDS WHERE TAB_ID = ".$result_fieldtype[$table_gtab[$bzm]][verkntabid][$bzm1]." AND FIELD_ID = ".$result_fieldtype[$table_gtab[$bzm]][verknfieldid][$bzm1];
	       				$rs = odbc_exec($db,$sqlquery) or errorhandle(odbc_errormsg($db),$sqlquery,$action,__FILE__,__LINE__);
	       				echo $lang[odbc_result($rs, "SPELLING")];
	       			}
	       			if($LINK[163]){
	       				echo "&nbsp;<i STYLE=\"cursor:pointer\" OnClick=\"newwin5('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$KEYID_gtab[$bzm]."','".$result_fieldtype[$table_gtab[$bzm]][verkntabid][$bzm1]."')\" class=\"lmb-icon ".$LINK[icon_url][163]."\" TITLE=\"".$lang[$LINK[desc][163]]."\" BORDER=\"0\"></i>";
	       			}

				}else{
					if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 32 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 31 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 18 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 6 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 8 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 10 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 9 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 11 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 12 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 13 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 19 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 18 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 44 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] < 100 AND !$result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]){
						echo "<INPUT TYPE=\"TEXT\" STYLE=\"width:100px;\" NAME=\"".$result_fieldtype[$table_gtab[$bzm]]["field"][$bzm1]."\" VALUE=\"".htmlentities($result_fieldtype[$table_gtab[$bzm]]["domain_default"][$bzm1],ENT_QUOTES,$umgvar["charset"])."\" OnChange=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.def.value=this.value+' '; this.form.column.value='".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."'; this.form.submit();\">";
					}elseif(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 10)
					{
						if($result_fieldtype[$table_gtab[$bzm]]["domain_default"][$bzm1] == "TRUE"){$checked = "CHECKED";}
						else{$checked = "";}
						echo "<INPUT TYPE=\"CHECKBOX\" VALUE=\"1\" NAME=\"".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."\" $checked OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.def_bool.value=this.value+' '; this.form.column.value='".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."'; this.form.submit();\">";
					}
					else{
						echo $result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1];
					}
				}
				echo "</TD>";
			}
			
            /* --- Argument --------------------------------------- */
            if($result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]){
            	echo "<TD  ALIGN=\"RIGHT\" NOWRAP>";
            	if($result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1] == 15){
                	if($result_fieldtype[$table_gtab[$bzm]]["argument_edit"][$bzm1] == 1){$argument_edit = "CHECKED";}else{$argument_edit = " ";}
               		echo $lang[1879]." <INPUT TYPE=\"CHECKBOX\" OnClick=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.argument_edit.value='$argument_edit';this.form.submit();\" $argument_edit>&nbsp;";
            	}
			    echo "<A HREF=\"JAVASCRIPT: newwin3('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$KEYID_gtab[$bzm]."','$bzm','".$result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]."');\"><i class=\"lmb-icon lmb-pencil\" BORDER=\"0\" TITLE=\"".str_replace("\"","&quot;",$result_fieldtype[$table_gtab[$bzm]]["argument"][$bzm1])."\" ALT=\"".str_replace("\"","&quot;",$result_fieldtype[$table_gtab[$bzm]]["argument"][$bzm1])."\"></i></A></TD>";
            /* --- Selectauswahl --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 16){
				echo "<TD ALIGN=\"RIGHT\" NOWRAP>";
            	echo "<INPUT TYPE=\"TEXT\" STYLE=\"width:50px;\" VALUE=\"".htmlentities($result_fieldtype[$table_gtab[$bzm]]["select_cut"][$bzm1],ENT_QUOTES,$umgvar["charset"])."\" OnChange=\"document.form1.select_cut.value=this.value;document.form1.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';document.form1.submit();\">";
	
			/* --- Selectauswahl --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 4){

				echo "<TD ALIGN=\"RIGHT\" NOWRAP>";
            	if($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 32){
            		echo "<INPUT TYPE=\"TEXT\" STYLE=\"width:50px;\" VALUE=\"".htmlentities($result_fieldtype[$table_gtab[$bzm]]["select_cut"][$bzm1],ENT_QUOTES,$umgvar["charset"])."\" OnChange=\"document.form1.select_cut.value=this.value;document.form1.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';document.form1.submit();\">";
            	}

			    if($result_fieldtype[$table_gtab[$bzm]][select_pool][$bzm1]){
			    	$sqlquery = "SELECT NAME FROM LMB_SELECT_P WHERE ID = ".$result_fieldtype[$table_gtab[$bzm]][select_pool][$bzm1];
					$rs = odbc_exec($db,$sqlquery) or errorhandle(odbc_errormsg($db),$sqlquery,$action,__FILE__,__LINE__);
			    	echo "&nbsp;&nbsp;".htmlentities(odbc_result($rs, "NAME"),ENT_QUOTES,$umgvar["charset"]);
			    	$pool = $result_fieldtype[$table_gtab[$bzm]][select_pool][$bzm1];
			    }else{
			    	$pool = 0;
			    }

                echo "&nbsp;<A HREF=\"JAVASCRIPT: newwin('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','$bzm','$pool','LMB_SELECT');\"><i class=\"lmb-icon lmb-pencil\" BORDER=\"0\"></i></A>";
                echo "</TD>";

            /* --- Attribut --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 19){
				echo "<TD ALIGN=\"RIGHT\" NOWRAP>";
			    if($result_fieldtype[$table_gtab[$bzm]]["select_pool"][$bzm1]){
			    	$sqlquery = "SELECT NAME FROM LMB_ATTRIBUTE_P WHERE ID = ".$result_fieldtype[$table_gtab[$bzm]]["select_pool"][$bzm1];
					$rs = odbc_exec($db,$sqlquery) or errorhandle(odbc_errormsg($db),$sqlquery,$action,__FILE__,__LINE__);
			    	echo "&nbsp;&nbsp;".htmlentities(odbc_result($rs, "NAME"),ENT_QUOTES,$umgvar["charset"]);
			    	$pool = $result_fieldtype[$table_gtab[$bzm]]["select_pool"][$bzm1];
			    }else{
			    	$pool = 0;
			    }

                echo "&nbsp;<A HREF=\"JAVASCRIPT: newwin('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','$bzm','$pool','LMB_ATTRIBUTE');\"><i class=\"lmb-icon lmb-pencil\" BORDER=\"0\"></i></A>";
                echo "</TD>";
            /* --- Verknüpfung --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 11){
            	echo "<TD  VALIGN=\"TOP\" ALIGN=\"RIGHT\" nowrap>".$result_fieldtype[$table_gtab[$bzm]]["verkntab"][$bzm1]."&nbsp;";
                if($result_fieldtype[$table_gtab[$bzm]]["verkntabletype"][$bzm1] == 3){echo "<i style=\"vertical-align:text-bottom\" class=\"lmb-icon lmb-switch\"></i>";}
            	elseif($result_fieldtype[$table_gtab[$bzm]]["verkntabletype"][$bzm1] == 2){echo "<i style=\"vertical-align:text-bottom\" class=\"lmb-icon lmb-long-arrow-left\"></i>";}
            	echo "</TD>";
            /* --- Generierter Link --------------------------------------- */
            #}elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 9){
            #    echo "<TD  NOWRAP>";
            #    if($result_fieldtype[$table_gtab[$bzm]][genlink][$bzm1]){echo "Link";}
			#    echo "<A HREF=\"JAVASCRIPT: newwin2('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".urlencode($table_gtab[$bzm])."','$bzm','".$result_fieldtype[$table_gtab[$bzm]][argument][$bzm1]."');\"><IMG SRC=\"pic/edit2.gif\" BORDER=\"0\" TITLE=\"".$result_fieldtype[$table_gtab[$bzm]][genlink][$bzm1]."\" ALT=\"".$result_fieldtype[$table_gtab[$bzm]][genlink][$bzm1]."\"></A></TD>";
            /* --- Zeitstempel --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 2){
            	echo "<TD ALIGN=\"RIGHT\"><INPUT TYPE=\"TEXT\" STYLE=\"width:100px;\" NAME=\"FORMAT_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" VALUE=\"".$result_fieldtype[$table_gtab[$bzm]][format][$bzm1]."\" OnChange=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.nformat.value=this.value+' ';this.form.submit();\">";
            /* --- Zeit --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 7){
			    echo "<TD ALIGN=\"RIGHT\"><INPUT TYPE=\"TEXT\" STYLE=\"width:100px;\" NAME=\"FORMAT_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" VALUE=\"".$result_fieldtype[$table_gtab[$bzm]][format][$bzm1]."\" OnChange=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.nformat.value=this.value+' ';this.form.submit();\"></TD>";
            /* --- Long --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 39){
            	if($result_fieldtype[$table_gtab[$bzm]][memoindex][$bzm1] == 1){$memoindexvalue = "CHECKED";} else{$memoindexvalue = "";}
            	if($result_fieldtype[$table_gtab[$bzm]][wysiwyg][$bzm1] == 1){$wysiwygvalue = "CHECKED";} else{$wysiwygvalue = "";}
            	echo "<TD  ALIGN=\"RIGHT\" NOWRAP>".$lang[1581]." <INPUT TYPE=\"CHECKBOX\" NAME=\"MEMOINDEX_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnClick=\"change_memoindex('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."',this);\" ".$memoindexvalue.">";
			    echo "<BR>".$lang[1885]." <INPUT TYPE=\"CHECKBOX\" NAME=\"WYSIWYG_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnClick=\"change_wysiwyg('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."',this);\" ".$wysiwygvalue."></TD>";
            /* --- NFORMAT --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 5 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 44){
				echo "<TD  VALIGN=\"TOP\" ALIGN=\"RIGHT\"><INPUT TYPE=\"TEXT\" STYLE=\"width:100px;\" NAME=\"FORMAT_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" VALUE=\"".$result_fieldtype[$table_gtab[$bzm]][format][$bzm1]."\" OnChange=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.nformat.value=this.value+' ';this.form.submit();\">";
			    /* --- Währung --------------------------------------- */
				if($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 30){
			    	echo "<SELECT STYLE=\"width:100px;\" ONCHANGE=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.ncurrency.value=this.value;this.form.submit();\"><OPTION VALUE=\" \">";
			    	asort($lmcurrency[currency]);
			    	foreach($lmcurrency[currency] as $ckey => $cval){
			    		if($lmcurrency[code][$ckey] == $result_fieldtype[$table_gtab[$bzm]][currency][$bzm1]){$sel = "SELECTED";}
			    		#elseif($lmcurrency[code][$ckey] == "EUR" AND !$result_fieldtype[$table_gtab[$bzm]][currency][$bzm1]){$sel = "SELECTED";}
			    		else{$sel = "";}
			    		echo "<OPTION VALUE=\"".$lmcurrency[code][$ckey]."\" $sel>".$lmcurrency[currency][$ckey];
			    	}
			    	echo "</SELECT>";
			    }
				echo "</TD>";
			/* --- Grouping --------------------------------------- */
            }elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 101){
                echo "<TD NOWRAP ALIGN=\"right\">";
                if($result_fieldtype[$table_gtab[$bzm]][genlink][$bzm1]){echo "Link";}
			    echo "&nbsp;<A HREF=\"JAVASCRIPT: newwin7('".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$bzm."');\"><i class=\"lmb-icon lmb-edit\" BORDER=\"0\"></i></A>";
            }else{
				echo "<TD >&nbsp;</TD>";
			}


            /* --- Konvertieren --------------------------------------- */
           # if(!$isview){
	       		if($isview){
					$result_type_allow_convert_ = array(1,5);
					$result_type_deny_convert_ = array(22);
	       			echo "<TD VALIGN=\"TOP\"><SELECT STYLE=\"width:150px\" OnChange=\"convert_field(this[this.selectedIndex].value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."',0);\"><OPTION>";
	       			foreach($result_type["id"] as $type_key => $type_value){
	       				if(in_array($result_type["field_type"][$type_key],$result_type_allow_convert_) AND !in_array($result_type["data_type"][$type_key],$result_type_deny_convert_)){
	       					echo "<OPTION VALUE=\"".$type_key."\">".$result_type[beschreibung][$type_key];
	       				}
	       			}
	       			echo "</SELECT></TD>";
	       		}elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 11){

	       		    if($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 24) {
					   $result_type_allow_convert_ = array(27);
	       		    }else{
	       		       $result_type_allow_convert_ = array(24);
	       		    }
					
	       			echo "<TD VALIGN=\"TOP\"><SELECT STYLE=\"width:150px\" OnChange=\"convert_field(this[this.selectedIndex].value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."',0);\"><OPTION>";
	       			foreach($result_type["id"] as $type_key => $type_value){
	       				if(in_array($result_type["data_type"][$type_key],$result_type_allow_convert_)){
	       					echo "<OPTION VALUE=\"".$type_key."\">".$result_type["beschreibung"][$type_key];
	       				}
	       			}
	       			echo "</SELECT></TD>";
	       		}elseif(($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 1 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 5 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 4 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 3 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 18 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 21) AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND !$result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1]){
					# multiselect convert
					if($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 18 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 31 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 32){
						$result_type_allow_convert_ = array(18,31,32);
					}else{
						$result_type_allow_convert_ = array(16,17,33,19,21,1,2,3,4,5,6,7,8,9,10,29,28,12,14,31,18,30,32,39,42,44,45,50);
					}
	       			echo "<TD VALIGN=\"TOP\"><SELECT STYLE=\"width:150px\" OnChange=\"convert_field(this[this.selectedIndex].value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."',0);\"><OPTION>";
	       			foreach($result_type["id"] as $type_key => $type_value){
	       				if(in_array($result_type["data_type"][$type_key],$result_type_allow_convert_)){
	       					echo "<OPTION VALUE=\"".$type_key."\">".$result_type["beschreibung"][$type_key];
	       				}
	       			}
	       			echo "</SELECT></TD>";
	       		}elseif($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 101){
	       			echo "<TD VALIGN=\"TOP\"><SELECT STYLE=\"width:150px\" OnChange=\"convert_field(this[this.selectedIndex].value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."',0);\"><OPTION>";
	       			$result_type_allow_convert_ = array(101,102);
	       			foreach($result_type["id"] as $type_key => $type_value){
	       				if(in_array($result_type["data_type"][$type_key],$result_type_allow_convert_)){
	       					echo "<OPTION VALUE=\"".$type_key."\">".$result_type[beschreibung][$type_key];
	       				}
	       			}
	       			echo "</SELECT></TD>";
	       		}elseif($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 30){echo "<TD >&nbsp;</TD>";}
	       		#}
            
 			# --- Extension ------
            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $ext_fk AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] < 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
            	echo "<SELECT STYLE=\"width:150px\" OnChange=\"extend_field(this[this.selectedIndex].value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."','".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."');\"><OPTION VALUE=\" \">";
            	foreach ($ext_fk as $key => $value){
            		echo "<OPTION VALUE=\"$value\" ";
            		if($result_fieldtype[$table_gtab[$bzm]]["ext_type"][$bzm1] == $value){echo "SELECTED";}
            		echo ">".$value."</option>";
            	}
            	echo "</SELECT>";
            }
            echo "</TD>";
            

 			# --- View-Rule ------
            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            echo "<INPUT STYLE=\"width:150px\" OnChange=\"viewrule_field(this.value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."');\" VALUE=\"".$result_fieldtype[$table_gtab[$bzm]]["view_rule"][$bzm1]."\">";
            echo "</TD>";
            
 			# --- Edit-Rule ------
 			if(!$isview){
	 			echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
	 			if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] < 100 AND $result_fieldtype[$table_gtab[$bzm]]["argument_typ"][$bzm1] != 47 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 20 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 47){
		            echo "<INPUT STYLE=\"width:150px\" OnChange=\"editrule_field(this.value,'".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."');\" VALUE=\"".$result_fieldtype[$table_gtab[$bzm]]["edit_rule"][$bzm1]."\">";
	 			}
	 			echo "</TD>";
 			
            
	 			# --- Trigger ------
	 			if($gtrigger[$bzm]){
		            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
		            if($LINK[226] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] < 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22){
		            	$fid = $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1];
		            	echo "<SELECT NAME=\"field_trigger_".$fid."[]\" STYLE=\"width:150px;\" OnChange=\"document.form1.trigger.value='".$fid."';if(document.form1.trigger.value=='".$fid."'){document.form1.submit();}\"><OPTION VALUE=\"\">";
		            	$trlist = array();
		            	foreach($gtrigger[$bzm]["id"] as $trid => $trval){
		            		if(in_array($trid,$result_fieldtype[$table_gtab[$bzm]]["trigger"][$bzm1])){$SELECTED = "SELECTED";$trlist[] = $gtrigger[$bzm]["trigger_name"][$trid];}else{$SELECTED = "";}
		            		echo "<OPTION VALUE=\"".$trid."\" $SELECTED>".$gtrigger[$bzm]["trigger_name"][$trid]." (".$gtrigger[$bzm]["type"][$trid].")</OPTION>";
		            	}
		            	echo "</SELECT>";
		            }
		            echo "</TD>";
	 			}
 			
 			}
            
			# --- Schlüssel ------
            #echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            #if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 2 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 4 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 6 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 10 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 9 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 11 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 13){
            #	if($result_fieldtype[$table_gtab[$bzm]][fieldkey][$bzm1] == 1){$fieldkeyvalue = "CHECKED";} else{$fieldkeyvalue = "";}
            #	echo "<CENTER><INPUT TYPE=\"CHECKBOX\" NAME=\"KEYFIELD_". $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnClick=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.keyfield.value='this.form.KEYFIELD_". $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1].".value'; this.form.submit();\"".$fieldkeyvalue."></CENTER>";}
            #echo "</TD>";

			# --- Bezeichner ------
            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 31 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 18 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 6 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 10 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 9 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 13 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
            	if($result_fieldtype[$table_gtab[$bzm]][mainfield][$bzm1] == 1){$mainfieldvalue = "CHECKED";} else{$mainfieldvalue = "";}
            	echo "<CENTER><INPUT TYPE=\"CHECKBOX\" NAME=\"MAINFIELD_". $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnClick=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.mainfield.value='this.form.MAINFIELD_". $result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1].".value'; this.form.submit();\"".$mainfieldvalue."></CENTER>";}
            echo "</TD>";

			# --- Index ------
			if(!$isview){
	            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
	            if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 11 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 6 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 39 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
	            	if($result_fieldtype[$table_gtab[$bzm]][indexed][$bzm1] == 1){$indexvalue = "CHECKED";} else{$indexvalue = "";}
	                echo "<CENTER><INPUT TYPE=\"CHECKBOX\" NAME=\"FIELDINDEX_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" $indexvalue OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.fieldindex.value='fieldindex_$indexvalue';this.form.column.value='".$result_fieldtype[$table_gtab[$bzm]][field][$bzm1]."';this.form.submit();\"></CENTER>";}
	            echo "</TD>";
			}

            # --- unique ------
            if(!$isview){
	            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
	            if(!$result_fieldtype[$table_gtab[$bzm]]["domain_admin_default"][$bzm1] AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 12 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 18 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 10 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 9 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 13 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 3 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 19){
	            	if($result_fieldtype[$table_gtab[$bzm]]["unique"][$bzm1] == 1){$unique = "CHECKED";}else{$unique = "";}
	            	echo "<CENTER><INPUT TYPE=\"CHECKBOX\" $unique NAME=\"UNIQUE_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.uniquefield.value='uniquefield_$unique'; this.form.submit();\"></CENTER>";}
	            echo "</TD>";
            }

			# --- dynamic search ------
			if(!$isview){
	            echo "<TD  VALIGN=\"TOP\" ALIGN=\"RIGHT\">";
	            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 11 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 12 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 32 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
	                if($result_fieldtype[$table_gtab[$bzm]]["dynsearch"][$bzm1] == 1){$dynsearch = "CHECKED";}else{$dynsearch = "";}
	                echo "<CENTER><INPUT TYPE=\"CHECKBOX\" $dynsearch NAME=\"DYNSEARCH_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.dynsearch.value='dynsearch_$dynsearch'; this.form.submit();\"></CENTER>";}
	            echo "</TD>";
			}
			
			# --- dynamic post ------
			if(!$isview){
	            echo "<TD VALIGN=\"TOP\" ALIGN=\"RIGHT\">";
	            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] <= 100 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 20 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 14 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 15 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 9 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 8 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 6 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 19 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
	                if($result_fieldtype[$table_gtab[$bzm]]["ajaxsave"][$bzm1] == 1){$ajaxsave = "CHECKED";}else{$ajaxsave = "";}
	                echo "<CENTER><INPUT TYPE=\"CHECKBOX\" $ajaxsave NAME=\"AJAXSAVE_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."';this.form.ajaxsave.value='ajaxsave_$ajaxsave'; this.form.submit();\"></CENTER>";}
	            echo "</TD>";
			}

            # --- Select ------
            echo "<CENTER><TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
            	if($result_fieldtype[$table_gtab[$bzm]]["artleiste"][$bzm1] == 1){$artleistevalue = "CHECKED";}else{$artleistevalue = "";}
            	echo "<INPUT TYPE=\"CHECKBOX\" $artleistevalue NAME=\"ARTLEISTE_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.artleiste.value='artleiste_$artleistevalue'; this.form.submit();\">";
            }
            # --- quicksearch ------
            echo "<CENTER><TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100 AND $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 16){
            	if($result_fieldtype[$table_gtab[$bzm]]["quicksearch"][$bzm1] == 1){$quicksearchvalue = "CHECKED";}else{$quicksearchvalue = "";}
            	echo "<INPUT TYPE=\"CHECKBOX\" $quicksearchvalue NAME=\"QUICKSEARCH_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.quicksearch.value='quicksearch_$quicksearchvalue'; this.form.submit();\">";
            }
            echo "</TD>";
            # --- Gruppierbar ------
            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100){
            	if($result_fieldtype[$table_gtab[$bzm]]["groupable"][$bzm1] == 1){$groupablevalue = "CHECKED";}else{$groupablevalue = "";}
	            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 11 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 3 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 22 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 13 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 31 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 32 OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 18  OR $result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] == 13 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 16){
	            	echo "";
	            }else{
	            	echo "<INPUT TYPE=\"CHECKBOX\" $groupablevalue NAME=\"GROUPABLE_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.groupable.value='groupable_$groupablevalue'; this.form.submit();\">";
	            }
            }
            echo "</TD>";
            # --- coll_replace ------
            if($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] != 100){
            echo "<TD  VALIGN=\"TOP\" ALIGN=\"CENTER\">";
	            if($result_fieldtype[$table_gtab[$bzm]]["collreplace"][$bzm1] == 1){$collreplacevalue = "CHECKED";}else{$collreplacevalue = "";}
	            if($result_fieldtype[$table_gtab[$bzm]]["datatype"][$bzm1] != 22 AND !$result_fieldtype[$table_gtab[$bzm]]["argument"][$bzm1] AND ($result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 4 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 5 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 1 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 2 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 10 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 21 OR $result_fieldtype[$table_gtab[$bzm]]["fieldtype"][$bzm1] == 18)){
	            	echo "<INPUT TYPE=\"CHECKBOX\" $collreplacevalue NAME=\"COLLREPLACE_".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."\" OnCLICK=\"this.form.fieldid.value='".$result_fieldtype[$table_gtab[$bzm]]["field_id"][$bzm1]."'; this.form.collreplace.value='collreplace_$collreplacevalue'; this.form.submit();\">";
	            }
	
	            echo "</TD>";
            }
            echo "</TR>";
	}
	}

	
	
	if(!$isview){
		?>
		
			
			
			<tr>
				<td colspan="25"><hr
						style="display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;"></td>
			</tr>
			<TR class="tabBody">
				<TD colspan="4">&nbsp;</TD>
				<TD VALIGN="TOP"><INPUT TYPE="TEXT" SIZE="16" NAME="field_name"
					ONCHANGE="this.form.spellingf.value=this.form.field_name.value; this.form.beschreibung.value=this.form.field_name.value;"></TD>
				<TD VALIGN="TOP"><INPUT TYPE="TEXT" SIZE="25" NAME="beschreibung"></TD>
				<TD VALIGN="TOP"><INPUT TYPE="TEXT" SIZE="16" NAME="spellingf"></TD>
				<TD VALIGN="TOP"><SELECT NAME="typ" style="width: 150px"
					OnChange="checkfiledtype(this,0)"><option></option>
		<?php
		
		
		/* --- Vernüpfungsparameter-Tabelle -------- */
		$sqlquery =  "SELECT VERKNPARAMS FROM LMB_CONF_FIELDS WHERE UPPER(MD5TAB) = '".lmb_strtoupper($table_gtab[$bzm])."' AND VERKNPARAMS > 0";
		$rs = odbc_exec($db,$sqlquery) or errorhandle(odbc_errormsg($db),$sqlquery,$action,__FILE__,__LINE__);
		if(odbc_result($rs, "VERKNPARAMS")){$verknparams = array(1,2,3,4,5,7,8,10,14,15,18,21);}
		$headerIds = array(1, 18, 48);

		# Feldtypen
		foreach ($result_type["id"] as $key => $value){
			if($result_type["id"][$key] == 49 AND !$tab_versioning[$atid]){continue;}
			if($verknparams AND !in_array($result_type["field_type"][$key],$verknparams)){continue;}
			if($result_type["hassize"][$key]){$hs = "ID=\"".$result_type["size"][$key]."\"";}else{$hs = "";}
			if(in_array($key, $headerIds)) {
                if ($key != $headerIds[0]) { echo "</OPTGROUP>"; }
                echo "<OPTGROUP label=\"" . $result_type["beschreibung"][$key] . "\">";
                continue;
            }
			echo "<OPTION VALUE=\"".$result_type["id"][$key]."\" $hs>".$result_type["beschreibung"][$key]."</OPTION>";
		}
		echo "</OPTGROUP>";
		?>
		</SELECT>

					<div id="argument_typ" style="display: none">
						<SELECT NAME="typ2" style="width: 150px"
							OnChange="checkfiledtype(0,this)">
	    <?php
		foreach ($result_type["id"] as $key => $value){
			if($result_type["id"][$key] <= 17 OR $result_type["id"][$key] == 41 OR $result_type["id"][$key] == 42 OR $result_type["id"][$key] == 21 OR $result_type["id"][$key] == 23){
				echo "<OPTION VALUE=\"".$result_type["id"][$key]."\">".$result_type["beschreibung"][$key];
			}
		}
		?>
	    </SELECT>
					</div>


					<div id="inherit_typ" style="display: none">
						<SELECT NAME="inherit_tab" style="width: 150px;"
							OnChange="checkinherittype(this[this.selectedIndex].value)"><OPTION>
	    <?php
		foreach ($gtab["tab_id"] as $key => $value){
			if($key != $atid){
				echo "<OPTION VALUE=\"".$key."\">".$gtab["desc"][$key];
			}
		}
		?>
	    </SELECT>
					</div>
	
	
	    <?php
		foreach ($gtab["tab_id"] as $key => $value){
			if($key != $atid){
				echo "<div id=\"inherit_field_$key\" style=\"display:none\">";
	   			echo "<SELECT NAME=\"inherit_field[$key]\" style=\"width:200px;\">\n<OPTION>";
	   			if($gfield[$key]["sort"]){
				foreach ($gfield[$key]["sort"] as $key1 => $value1){
					if($gfield[$key]["field_type"][$key1] != 14 AND $gfield[$key]["field_type"][$key1] != 15 AND $gfield[$key]["field_type"][$key1] != 16 AND $gfield[$key]["data_type"][$key1] != 31 AND $gfield[$key]["data_type"][$key1] != 18 AND $gfield[$key]["data_type"][$key1] != 14 AND $gfield[$key]["field_type"][$key1] != 19 AND $gfield[$key]["field_type"][$key1] != 6  AND $gfield[$key]["field_type"][$key1] < 100){
						echo "<OPTION VALUE=\"".$key1."\">".$gfield[$key]["spelling"][$key1];
					}
				}}
				echo "\n</SELECT></div>";
			}
		}
		?>
	    </TD>
				<TD VALIGN="TOP"><input type="text" id="typ_size" name="typ_size"
					style="width: 40px; visibility: hidden"></TD>

				<TD COLSPAN="14" VALIGN="TOP">
					<table>
						<tr>
							<td valign=top><INPUT TYPE="submit" NAME="add"
								VALUE="<?=$lang[937]?>">&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td><table cellpadding="0" cellspacing="0">
									<tr>
										<td valign="center"><?=$lang[1263]?></td>
										<td><INPUT TYPE="CHECKBOX" NAME="add_permission" VALUE="1" STYLE="border: none; background-color: transparent" CHECKED></td>
									</tr>
								</table></td>

						</tr>
					</table>
				</TD>
			</TR>
	<?}?>
	
	<TR>
				<TD COLSPAN="24" class="tabFooter"></TD>
			</TR>
			</FORM>
	<?
$bzm++;
}

?>
<TR>
				<TD COLSPAN="19" ALIGN="LEFT"><?=$message;?></TD>
			</TR>
		</TABLE>
	</div>