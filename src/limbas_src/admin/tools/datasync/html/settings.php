<?php
/**
 * @copyright Limbas GmbH <https://limbas.com>
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */


?>

<style>

.hiddenRow {
    padding: 0 !important;
}

</style>

<script>

    var saverules = new Array();
    function save_rules(tab,field,typ){
        eval("saverules['"+tab+"_"+field+"_"+typ+"'] = 1;");
    }

    function send_form(){

        saval = new Array();
        for (var e in saverules){
            saval.push(e);
        }
        document.form1.edit_template.value = 1;
        document.form1.rules.value = saval.join('|');

        var popup = new Array();
        $.each($(".popicon"), function() {
            if($(this).attr('src') == 'assets/images/legacy/outliner/minusonly.gif'){
                popup.push($(this).attr('tabid'));
            }
        });

        document.form1.popup.value = popup.join(';');
        document.form1.submit();
    }

    //--- Popup-funktion ----------
    var popups = new Array();
    function pops(tab){
        eval("var ti = 'table_"+tab+"';");
        eval("var pi = 'popicon_"+tab+"';");
        if(document.getElementById(ti).style.display){
            document.getElementById(ti).style.display='';
            eval("document."+pi+".src='assets/images/legacy/outliner/minusonly.gif';");
        }else{
            document.getElementById(ti).style.display='none';
            eval("document."+pi+".src='assets/images/legacy/outliner/plusonly.gif';");
        }
    }

    function lmb_validate_all(){

        var bzm = 0;
        $('.validate-check').filter('i[data-check=false]').each(function(){
            //var el = $(this);
            lmb_validate(this,1);
            return false;
        });

    }

    function lmb_validate(el,recursiv) {

        el = $(el);
        if(isNaN(recursiv)){recursiv = null;}

        syncid = el.data('id');

        if(!recursiv){
            $("#r"+syncid).collapse("show");
        }

        el.data('check', true);
        el.removeClass("lmb-refresh");
        el.addClass("fa-spin fa-spinner");

        postfunc = function(result){lmb_validatePost(result,el,syncid,recursiv);};
        ajaxGet(null,'main_dyns_admin.php','syncValidate&syncid='+syncid,null,'postfunc',null,null,null,1);
    }

    var c_danger = 1000;
    var c_warning = 100;
    function lmb_validatePost(result,el,syncid,recursiv){

        result = JSON.parse(result);

        el.removeClass("fa-spin fa-spinner");
        el.addClass("lmb-refresh");

        if(result['abs_sumdiff'] == 'no valid data' || result['abs_sumdiff'] > c_danger) {
            var color = 'danger';
        }else if(result['abs_sumdiff'] == 0){
            var color = 'success';
        }else if(result['abs_sumdiff'] > c_warning){
            var color = 'warning';
        }else{
            var color = 'info';
        }

        $('#status_'+syncid).parent().parent().addClass("alert-"+color);
        $('#validate_name').children().addClass(color+'-link');
        $('#status_'+syncid).html(result['abs_sumdiff']);
        $('#status_'+syncid).addClass('cursor-pointer');

        lmb_validateDetail(syncid,result);

        if(recursiv){
            lmb_validate_all();
        }

    }

    function lmb_validateDetail(syncid,result) {

        var detailElement = $('.validate-detail').filter('div[data-id='+syncid+']');
        var text = '<div class="row row-cols-2">';
        for (var i in result['diff']) {
            if(i == 'abs_sumdiff'){continue;}
            var c = result['diff'][i];

            if(c == 'no valid data' || c > c_danger) {
                var color = 'danger';
            }else if(c == 0){
                var color = 'success';
            }else if(c > c_warning){
                var color = 'warning';
            }else{
                var color = 'info';
            }

            text += '<div class="col-sm-3 cursor-pointer" data-bs-toggle="modal" data-bs-target="#SynValidatePhase2Modal" onclick="lmb_validatePhase2('+syncid+',\''+i+'\')"><i class="fa fa-circle text-'+color+'" aria-hidden="true"></i> ' + i + ' </div><div class="col-sm-3">' + result['diff'][i] + ' (' + result['master'][i] + ' / ' + result['slave'][i] + ')</div>';

            i++;
        }
        text += '</div>';
        detailElement.html(text);
    }


    function lmb_validatePhase2(syncid,table) {
        postfunc = function(result){lmb_validatePhase2Detail(result,syncid,table);};
        ajaxGet(null,'main_dyns_admin.php','syncValidate&phase=2&syncid='+syncid+'&table='+table,null,'postfunc',null,null,null,1);
    }

    function lmb_validatePhase2Detail(result,syncid,table) {

        $('#SynValidatePhase2ModalTitle').append(' ('+table+')');

        result = JSON.parse(result);

        //var text = '';
        //for (var i in result['master']) {
        //    text += result['master'][i]+', ';
        //    i++;
        //}
        $('#detailMasterPhase2').html(Object.values(result['master']).join(", "));
        $('#detailClientPhase2').html(Object.values(result['client']).join(", "));

    }


</script>


<div class="modal fade" id="SynValidatePhase2Modal" tabindex="-1">
  <div class="modal-dialog  modal-lg" >
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SynValidatePhase2ModalTitle"><?=$lang[3071]?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detailElementPhase2">
          <div class="row row-cols-2">
              <div class="col"><h5>Master</h5></div><div class="col"><h5>Client</h5></div>
              <div class="col" id="detailMasterPhase2"></div><div id="detailClientPhase2" class="col"></div>
          </div>
      </div>
    </div>
  </div>
</div>


<div class="container-fluid p-3">
    <FORM ACTION="main_admin.php" METHOD="post" NAME="form1">
        <input type="hidden" name="action" value="setup_datasync">
        <input type="hidden" name="template" value="<?=$template?>">
        <input type="hidden" name="tab" value="<?=$tab?>">
        <input type="hidden" name="drop_template" value="">
        <input type="hidden" name="drop_slave" value="">
        <input type="hidden" name="edit_template" value="">
        <input type="hidden" name="setting_template" value="">
        <input type="hidden" name="rules">
        <input type="hidden" name="popup" value="<?=$popup?>">



        <div class="modal fade" id="fileSyncModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">File Sync</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="fileSyncContent">
                        <?php files1($template,0,0,0);?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-primary" type="submit" onclick="send_form()" value="1"><?= $lang[33] ?></button>
                    </div>
                </div>
            </div>
        </div>


        <?php include 'tabs.php' ?>
        
        <div class="tab-content border border-top-0 bg-white">
            <div class="tab-pane active">

                <?php if ($tab == 1): ?>
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                        <tr>
                            <th class="border-top-0"></th>
                            <th class="border-top-0">Name</th>
                            <th class="border-top-0">HOST</th>
                            <th class="border-top-0">Limbas Username</th>
                            <th class="border-top-0">Limbas Password</th>
                            <th class="border-top-0">rsync User</th>
                            <th class="border-top-0">rsync Path</th>
                            <th class="border-top-0">rsync Params</th>
                            <th class="border-top-0"><?=$lang[632]?></th>
                            <th class="border-top-0"></th>
                        </tr>
                        </thead>
                        <?php
                        if($result_slave):

                            /* --- Ergebnisliste --------------------------------------- */
                            foreach ($result_slave['name'] as $skey => $sval) :
                                #$rowcol = lmb_getRowColor();
                                ?>

                                <tr>
                                    <TD><i class="lmb-icon lmb-trash" onclick="document.form1.drop_slave.value=<?=$skey?>;document.form1.submit();" style="cursor:pointer"></i></TD>
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$sval?>" name="slave[name][<?=$skey?>]" style="width:150px" onchange="document.getElementById('slave_<?=$skey?>').value=1"></TD>
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$result_slave['slave_url'][$skey]?>" name="slave[url][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1"></TD>
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$result_slave['slave_username'][$skey]?>" name="slave[username][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1"></TD>
                                    <TD><input class="form-control form-control-sm" type="password" value="<?=$result_slave['slave_pass'][$skey]?>" name="slave[pass][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1">
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$result_slave['slave_rs_user'][$skey]?>" name="slave[rs_user][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1">
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$result_slave['slave_rs_path'][$skey]?>" name="slave[rs_path][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1"></TD>
                                    <TD><input class="form-control form-control-sm" type="text" value="<?=$result_slave['slave_rs_params'][$skey]?>" name="slave[rs_params][<?=$skey?>]" onchange="document.getElementById('slave_<?=$skey?>').value=1">
                                    <TD><input class=" form-control-sm checkb" type="checkbox" name="slave[active][<?=$skey?>]" value="1" <?=$result_slave['slave_active'][$skey] ? 'checked' : '';?> onchange="document.getElementById('slave_<?=$skey?>').value=1">
                                        <input class="form-control form-control-sm" type="hidden" value="" name="slave[edit][<?=$skey?>]" id="slave_<?=$skey?>">
                                    </TD>
                                    <td></td>
                                </tr>

                            <?php endforeach;

                        endif;
                        ?>

                        <tfoot>
                        <tr class="border-bottom border-top">
                            <td></td>
                            <td colspan="5">
                                <button class="btn btn-sm btn-primary" type="submit" name="edit_slave" value="1"><?= $lang[522] ?></button>
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td><INPUT class="form-control form-control-sm" TYPE="TEXT" NAME="new_slavename"></td>
                            <td><INPUT class="form-control form-control-sm" TYPE="TEXT" NAME="new_slaveurl"></td>
                            <td><INPUT class="form-control form-control-sm" TYPE="TEXT" NAME="new_slaveuser"></td>
                            <td><INPUT class="form-control form-control-sm" TYPE="password" NAME="new_slavepass"></td>
                            <td><button class="btn btn-sm btn-primary" type="submit" name="add_slave" value="1"><?= $lang[540] ?></button></td>
                        </tr>
                        </tfoot>
                    </table>
                <?php else: ?>

                    <table class="table table-sm table-striped mb-0">

                        <?php if ($template):

                            foreach($tabgroup['id'] as $bzm => $val) {
                                foreach($gtab["tab_id"] as $key => $tabid){
                                    if($gtab["tab_group"][$key] != $tabgroup["id"][$bzm] OR $gtab["typ"][$key] == 5 OR !$gtab["datasync"][$tabid]){continue;}
                                    $hasgroup[$bzm] = 1;
                                }
                            }

                            ?>

                            <thead>
                            <tr><th colspan="6" class="border-top-0"><?=$result_template['name'][$template]?></th></tr>
                            </thead>




                            <?php

                            foreach($hasgroup as $bzm => $val) :

                                if(is_array($skipftype) && in_array($gfield[$tabid]["field_type"][$fkey],$skipftype)){continue;}

                                echo '<tr class="table-section"><td colspan="6">'.$tabgroup['name'][$bzm].' ('.$tabgroup['beschreibung'][$bzm].')</td></tr>';

                                foreach($gtab["tab_id"] as $key => $tabid):

                                    if($gtab["tab_group"][$key] != $tabgroup["id"][$bzm] OR $gtab["typ"][$key] == 5 OR !$gtab["datasync"][$tabid]){continue;}
                                    $icon = 'plusonly';
                                    if($is_popup){if(in_array($tabid,$is_popup)){$display = "";$icon = 'minusonly';}else{$display = "none";$icon = 'plusonly';}}else{$display = "none";}

                                    ?>
                                    <TR class="table-sub-section">
                                        <TD><IMG src="assets/images/legacy/outliner/<?=$icon?>.gif" tabid="<?=$key?>" NAME="popicon_<?=$key?>" CLASS="popicon" BORDER="0" STYLE="cursor:pointer" OnClick="pops('<?=$key?>')"></TD>
                                        <TD><?=$gtab['table'][$key]?> (<?=$gtab['desc'][$key]?>)

                                            <?php
                                            if($gtab['table'][$key] == 'LDMS_FILES') {
                                                echo '&nbsp;&nbsp;<button class="btn btn-sm btn-secondary" type="button" onclick="$(\'#fileSyncModal\').modal(\'show\');">'.$lang[2309].' '.$lang[2275].'</button>';
                                            }
                                            ?>

                                            &nbsp;</TD>
                                        <TD><?=$lang[1078]?>:&nbsp;<input type="text" class="form-control form-control-sm d-inline-block w-75" NAME="templ_params[<?=$tabid?>]" onchange="save_rules('<?=$tabid?>','',3)" VALUE="<?=$result_params['params'][$tabid]?>"></TD>
                                        <TD class="text-center">master</TD>
                                        <TD class="text-center">client</TD>
                                    </TR>

                                    <tbody id="table_<?=$tabid?>" class="border-0" style="display:<?=$display?>">
                                    <?php

                                    $skipftype = array(20,17,14,15);
                                    foreach($gfield[$tabid]["sort"] as $fkey => $fid):
                                        if($gfield[$tabid]["field_type"][$fkey] >= 100  OR $gfield[$tabid]["data_type"][$fkey] == 22 OR in_array($gfield[$tabid]["field_type"][$fkey],$skipftype) OR $gfield[$tabid]["field_name"][$fkey] == 'LMB_SYNC_SLAVE'){continue;}

                                        ?>


                                        <tr>

                                            <td><?=$fkey?></td>
                                            <td title="<?=$gfield[$tabid]["beschreibung"][$fkey]?>" nowrap><?=$gfield[$tabid]['field_name'][$fkey]?>&nbsp;(<?=$gfield[$tabid]["spelling"][$fkey]?>)</td>
                                            <td><?=$lmfieldtype["name"][$gfield[$tabid]["data_type"][$fkey]]?></td>

                                            <td class="text-center"><input TYPE="checkbox" NAME="templ_conf[<?=$tabid?>][<?=$fkey?>][1]" onclick="save_rules('<?=$tabid?>','<?=$fkey?>',1)" VALUE="1" <?=($result_conf[$template][$tabid][$fkey]['master']) ? 'checked' : '' ?>></td>

                                            <td class="text-center"><input TYPE="checkbox" NAME="templ_conf[<?=$tabid?>][<?=$fkey?>][2]" onclick="save_rules('<?=$tabid?>','<?=$fkey?>',2)" VALUE="1" <?=($result_conf[$template][$tabid][$fkey]['slave']) ? 'checked' : '' ?>></td>
                                        </tr>


                                    <?php endforeach;
                                    ?>
                                    </tbody>

                                <?php
                                endforeach;

                            endforeach;

                            ?>

                            <tfoot>

                            <tr>
                                <td colspan="6"><button class="btn btn-sm btn-primary" type="button" onclick="send_form()" value="1"><?= $lang[33] ?></button></td>
                            </tr>
                            </tfoot>

                        <?php elseif ($tab == 2): ?>

                            <thead>
                            <tr>
                                <th colspan="2" class="border-top-0"></th>
                                <th class="border-top-0">Name</th>
                                <th class="border-top-0">Conflict Mode</th>
                            </tr>
                            </thead>

                            <?php
                            if($result_template):

                                foreach($result_template['name'] as $tkey => $tval):

                                    $SELCTED = array();
                                    $SELCTED[$result_template['mode'][$tkey]] = "SELECTED";

                                    ?>


                                    <tr>
                                        <td style="width:20px"><i class="lmb-icon lmb-pencil" onclick="document.form1.template.value=<?=$tkey?>;document.form1.submit();" style="cursor:pointer"></i></a></td>
                                        <td style="width:20px"><i class="lmb-icon lmb-trash" onclick="document.form1.drop_template.value=<?=$tkey?>;document.form1.submit();" style="cursor:pointer"></i></td>
                                        <td><?=$tval?></td>
                                        <td><select class="form-select form-select-sm" name="template_mode[<?=$tkey?>]" onchange="document.form1.setting_template.value=<?=$tkey?>;document.form1.submit();">
                                                <option value="0" <?=$SELCTED[0]?>>master
                                                <option value="1" <?=$SELCTED[1]?>>slave
                                                <option value="2" <?=$SELCTED[2]?>>date
                                                <option value="3" <?=$SELCTED[3]?>>manuel
                                            </select></td>
                                    </tr>

                                <?php
                                endforeach;

                            endif;

                            ?>


                            <tfoot>

                            <tr>
                                <td colspan="2"></td>
                                <td><INPUT class="form-control form-control-sm" TYPE="TEXT" NAME="new_template"></td>
                                <td class="text-end"><button class="btn btn-sm btn-primary" type="submit" name="add_template" value="1"><?= $lang[540] ?></button></td>
                            </tr>
                            </tfoot>


                        <?php elseif ($tab == 4 AND $result_slave): ?>

                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><i class="lmb-icon lmb-refresh cursor-pointer" onclick="lmb_validate_all()" ></i></th>
                                </tr>
                                </thead>

                                <tbody>
                            <?php

                            foreach ($result_slave['name'] as $slyve_key => $slave_name) {
                                if(!$result_slave['slave_active'][$slyve_key]){continue;}
                                ?>

                                <tr>
                                    <td data-bs-toggle="collapse" data-bs-target="#r<?=$slyve_key?>">
                                       <div class="validate cursor-pointer"><?=$slave_name?></div>
                                    </td>
                                    <td>
                                        <div class="validate validate-status" id=status_<?=$slyve_key?>></div>
                                    </td>
                                    <td >
                                        <i class="validate validate-check lmb-icon lmb-refresh cursor-pointer" data-check="false" data-id="<?=$slyve_key?>" onclick="lmb_validate(this)" ></i>
                                    </td>
                                </tr>
                                <tr class="collapse accordion-collapse" id="r<?=$slyve_key?>" data-bs-parent=".table">
                                    <td></td><td><div class="validate-detail" data-id="<?=$slyve_key?>"></div></td><td></td>
                                </tr><tr></tr>


                            <?php }?>

                                </tbody>
                            </table>

                        <?php endif; ?>

                    </table>

                <?php endif; ?>


            </div>
        </div>

    </FORM>
</div>
