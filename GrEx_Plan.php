<?php

// Описание файлов программы:
// GrEx_Plan.php - страница, на которой указываются значения Плана. Точка входа для просмотра плановых показателей
// GrEx_Database.php - работа с БД
// GrEx_config.php - конфигурационный файл системы
// GrEx_action.php - файл, который вызывается при AJAX
// GrEx_Monitoring.php - файл, показывающий результаты выполнения плана (фактические значения)

require_once('GrEx_config.php');
require_once 'GrEx_Database.php';

$kodMess = array(2, 9, 208, 209, 221, 241, 242, 263, 264, 402, 403, 404, 405, 406, 410, 421, 422, 423, 424, 1042, 1353, 1356, 1359, 1397, 2320, 2321, 2454, 2976, 2977, 4624, 4770, 4771, 5353, 5354, 5393);
$kodR = array("R01", "R24", "R28", "R63", "R17", "R51", "R58", "R61", "R76", "R80", "R83", "R88", "R92", "R94", "R96");

if (isset($_POST['save_tabl'])) {
    Database::connect();
    $hours = $_POST['hours'];
    $arrData = array_chunk($_POST['arrData'], 15);
    $i = 0;
    foreach ($kodMess as $valueMess) {
        $j = 0;
        foreach ($arrData[$i] as $valueData) {
            $valueData = ($valueData == "") ? "null" : $valueData;
            $sql_update = "update DBO0.NSI_MES_MONITOR set $kodR[$j] = $valueData where KOD = $valueMess and HHS = $hours";
            Database::upd_ins($sql_update);
            $j++;
        }
        $i++;
    }
    echo "<b>Данные успешно сохранены!</b>";
}
?>
<html>
    <head>
        <title>Значения плановых показателей!</title>
        <script src="GrEx_jquery.js"></script>
        <meta charset="UTF-8">
        <script type="text/javascript">            
            function ajaxFunc(){
                var id_value = $('select[name="hours"]').val();
                if(!id_value){
                        $('#table_data').html('');
                }else{
                        $.ajax({
                                type: "POST",
                                url: "GrEx_action.php",
                                data: { action: id_value, id_value: id_value },
                                cache: false,
                                success: function(responce){ $('#table_data').html(responce); }
                        });
                };
            };
        </script>
    </head>
    <body>
        <p><b>Плановые показатели</b></p>
        <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
                        
<?php
    require_once 'GrEx_action.php';
?>
        <input type="submit" value="Сохранить" name="save_tabl">
        </form>
        
    </body>
</html>

<?php
Database::disconnect();
?>
