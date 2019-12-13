<?php

require_once 'GrEx_Database.php';

$arrValSel = array("1", "4", "8", "12", "24", "48");

if (isset($_POST['action'])) {
    $val = $_POST['action'];    
} elseif (isset($_POST['hours'])) {
    $val = $_POST['hours']; 
} else {
    $val = 1; 
}
?>
<table border="1" id="table_data">
    <tbody>
        <tr>
            <td></td>
            <td colspan="15">Код дороги</td>
            <td>Время</td>
        </tr>
        <tr>
            <td>KOD</td>
            <td>R01</td>
            <td>R24</td>
            <td>R28</td>
            <td>R63</td>
            <td>R17</td>
            <td>R51</td>
            <td>R58</td>
            <td>R61</td>
            <td>R76</td>
            <td>R80</td>
            <td>R83</td>
            <td>R88</td>
            <td>R92</td>
            <td>R94</td>
            <td>R96</td>
            <td>
                <select size='1' name='hours' id='select_hours' onchange='ajaxFunc()'>
                <?php foreach ($arrValSel as $option) : ?>
                <?php $selected = ($val == $option) ? "selected" : "";?>
                    <option value='<?php echo $option;?>' <?php echo $selected; ?> > <?php echo $option; ?></option>
                <?php endforeach;?>
                </select>
            </td>
        </tr>
                
<?php

Database::connect();
$sql = "select KOD, R01, R24, R28, R63, R17, R51, R58, R61, R76, R80, R83, R88, R92, R94, R96, HHS from DBO0.NSI_MES_MONITOR where HHS = $val";
$resultKod = Database::select($sql);
echo "<input type='hidden' name='hours' value='$val'>";
foreach ($resultKod as $valueKod) {
    echo '<tr>';
    foreach ($valueKod as $key => $value) {
        if ($key == "KOD" || $key == "HHS") {
            echo "<td>$value</td>";
        } else {
            echo "<td><input type='text' name='arrData[]' value='$value' size='7'></td>";
        }
    }
    echo '</tr>';
}

?>
    </tbody>
</table>
