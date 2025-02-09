<?php
/**
 * @copyright Limbas GmbH <https://limbas.com>
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */




$template_gtabid = $greport["root_template"][$report_id];
$root_template_id = $greport["root_template_id"][$report_id];
$template = lmb_report_getRootTemplate($root_template_id,$template_gtabid);


$paper_size = array('A4'=>'DIN A4','A3'=>'DIN A3','A2'=>'DIN A2','Letter'=>'Letter','custom'=>$lang[3089]);
$orientation = array('P'=>'portrait','L'=>'landscape');

if(is_numeric($greport["page_style"][$report_id][0])) {
    $paper_size_selected['custom'] = 'selected';
}else{
    $paper_size_selected[$greport["page_style"][$report_id][0]] = 'selected';
}

$used_font_selected[$greport["used_fonts"][$report_id][0]] = 'selected';
$template_selected[$greport["root_template"][$report_id]] = 'selected';
$orientation_selected[$greport["orientation"][$report_id]] = 'selected';

if(!$greport["dpi"][$report_id]){
    $greport["dpi"][$report_id] = '72';
}


?>

<script src="main.php?action=syntaxcheckjs"></script>
<script src="assets/vendor/tinymce/tinymce.min.js?v=<?=$umgvar["version"]?>"></script>
<script src="assets/js/admin/report/report_editor.js?v=<?=$umgvar["version"]?>"></script>

<div class="container-fluid p-3">

    <form id="form1">
        <div class="row">
        <div class="col-md-8" id="editor-div">
            <div class="w-100">
                <?php
                $formname = 'g_0_0';
                global $lang;
                ?>
                <input type="hidden" name="action" value="setup_report_frameset">
                <input type="hidden" name="actid" value="reportEditorSaveReport">
                <input type="hidden" name="gtabid" value="<?=$template_gtabid?>">
                <input type="hidden" name="ID" id="formid" value="<?=$root_template_id?>">
                <input type="hidden" name="report_id" value="<?=$report_id?>">
                <input type="hidden" name="content" id="content">

                <textarea id="<?=$formname?>" NAME="<?=$formname?>">
                    <?=htmlentities($template['content'],ENT_QUOTES,$GLOBALS["umgvar"]["charset"])?>
                </textarea>

                <?php
                echo lmb_ini_wysiwyg($formname,null,null,1,650);
                ?>

            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h1><?=$greport['name'][$report_id]?></h1>
                    <div class="row">
                        <div class="col-sm-4"><?=$lang[1162]?></div>
                        <div class="col-sm-8 fw-bold">
                            <?=$gtab['desc'][$greportlist['gtabid'][$report_id]]?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><?=$lang[403]?></div>
                <div class="card-body">
                    <form>
                        <div class="mb-1 row">
                            <label for="template_table" class="col-sm-4 col-form-label">Template</label>
                            <div class="col-sm-8">
                                <select class="form-select form-select-sm" id="template_table" name="template_table">
                                    <?php
                                    foreach($gtab["desc"] as $key => $value){
                                        if($gtab["typ"][$key] == 8) {
                                            echo "<option value=\"$key\" {$template_selected[$key]}>$value</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-1 row">
                            <label for="paper_size" class="col-sm-4 col-form-label"><?=$lang[3090]?></label>
                            <div class="col-sm-8">
                                <select class="form-select form-select-sm" id="paper_size" name="paper_size">
                                    <?php
                                        foreach($paper_size as $key => $value){
                                            echo  "<option value=\"$key\" {$paper_size_selected[$key]}>$value</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="paper_size" class="col-sm-4 col-form-label"><?=$lang[3091]?></label>
                            <div class="col-sm-8">
                                <select class="form-select form-select-sm" id="orientation" name="orientation">
                                    <?php
                                        foreach($orientation as $key => $value){
                                            echo  "<option value=\"$key\" {$orientation_selected[$key]}>$value</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-1 row d-none" id="custom_size">
                            <label for="custom_size_w" class="col-sm-4 col-form-label"><?=$lang[1141]?> / <?=$lang[1142]?></label>
                            <div class="col-sm-4">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm" id="custom_size_w" name="custom_size_w" value="<?=$greport["page_style"][$report_id][0]?>">
                                    <span class="input-group-text">mm</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm" id="custom_size_h" name="custom_size_h" value="<?=$greport["page_style"][$report_id][1]?>">
                                    <span class="input-group-text">mm</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-1 row">
                            <label for="dpi" class="col-sm-4 col-form-label">DPI</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control form-control-sm" id="dpi" name="dpi" value="<?=$greport["dpi"][$report_id]?>">
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="margin" class="col-sm-4 col-form-label"><?=$lang[1111]?></label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control form-control-sm" id="margin" name="margin" value="<?=$greport["page_style"][$report_id][2]?>">
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="margin_top" class="col-sm-4 col-form-label"><?=$lang[1111]?></label>
                            <div class="col-sm-4 mb-1">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="lmb-icon lmb-arrow-up"></i></span>
                                    <input type="number" class="form-control form-control-sm" id="margin_top" name="margin_top" value="<?=$greport["page_style"][$report_id][2]?>">
                                </div>
                            </div>
                            <div class="col-sm-4 mb-1">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><b>|</b><i class="lmb-icon lmb-arrow-left"></i></span>
                                    <input type="number" class="form-control form-control-sm" id="margin_right" name="margin_right" value="<?=$greport["page_style"][$report_id][3]?>">
                                </div>
                            </div>
                            <div class="col-sm-4 offset-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="lmb-icon lmb-arrow-down"></i></span>
                                    <input type="number" class="form-control form-control-sm" id="margin_bottom" name="margin_bottom" value="<?=$greport["page_style"][$report_id][4]?>">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="lmb-icon lmb-arrow-right"></i><b>|</b></span>
                                    <input type="number" class="form-control form-control-sm" id="margin_left" name="margin_left" value="<?=$greport["page_style"][$report_id][5]?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-1 row">
                            <label for="default_class" class="col-sm-4 col-form-label"><?=$lang[1170]?></label>
                            <div class="col-sm-8">
                                <select id="default_font" name="default_font" class="form-select form-select-sm"><option></option>
                                <?php
                                $sysfonts = lmb_report_getsysfonts();
                                foreach ($sysfonts as $key => $val) {
                                    echo "<option value=\"$val\" $used_font_selected[$val]>$val</option>";
                                }
                                ?>
                                </select>
                            </div>
                        </div>


                        <div class="mb-1 row">
                            <label for="default_class" class="col-sm-4 col-form-label"><?=$lang[2984]?> <?=$lang[1170]?></label>
                            <div class="col-sm-8">
                                <select multiple id="extended_font" name="extended_font" class="form-select form-select-sm">
                                <?php
                                foreach ($sysfonts as $key => $val) {
                                    if($val == $greport["used_fonts"][$report_id][0]){continue;}
                                    echo "<option value=\"$val\" ";
                                    if(in_array($val,$greport["used_fonts"][$report_id])){echo "selected";}
                                    echo ">$val</option>";
                                }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="default_class" class="col-sm-4 col-form-label"><?= $lang[2581] ?></label>
                            <div class="col-sm-8">
                                <select id="default_class" name="default_class" class="form-select form-select-sm">
                                    <option value=""></option>
                                        <?php
                                        if (file_exists(EXTENSIONSPATH . 'css')) {
                                            $extfiles = read_dir(EXTENSIONSPATH . 'css', 0);
                                            $extfiles['name'][] = 'layout.css';
                                            $extfiles['typ'][] = 'file';
                                            $extfiles['path'][] = EXTENSIONSPATH . 'css/layout.css';
                                            $extfiles['ext'][] = 'css';

                                            if ($extfiles['name']) {
                                                foreach ($extfiles['name'] as $key1 => $filename) {
                                                    if ($extfiles['typ'][$key1] == 'file' AND $extfiles['ext'][$key1] == 'css') {
                                                        $path = lmb_substr($extfiles['path'][$key1], lmb_strlen($umgvar['pfad']), 100);
                                                        if ($greport['css'][$report_id] == $path . $filename) {
                                                            $selected = 'SELECTED';
                                                        } else {
                                                            $selected = '';
                                                        }
                                                        echo '<option value="' . $path . $filename . '" ' . $selected . '>' . str_replace('/EXTENSIONS/css/', '', $path) . $filename;
                                                    }
                                                }
                                            }
                                        }
                                        ?>

                                </select>
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="listmode" class="col-sm-4 col-form-label"><?=$lang[2649]?></label>
                            <div class="col-sm-8">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="listmode" name="listmode" <?=($greport["listmode"][$report_id])?'checked':''?>>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" id="btn-save" data-title="<?=$lang[842]?>" disabled><?=$lang[842]?></button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
