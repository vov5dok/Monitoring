<?php
session_start();
require_once 'GrEx_Database.php';

$arrOptionTime = array('Отключить', '1 ч.', '2 ч.', '3 ч.', '4 ч.');
$arrValSel = array("1", "4", "8", "12", "24", "48"); // Массив, включающий количество часов для сортировки таблицы

if (!isset($_GET['download-sub'])) {
  $_SESSION['count'] = 0;
  $checked = "checked";
  $delay = "";
} else {
  if (isset($_GET['send_socket']) && $_GET["send_socket"] == 1) {
      $checked = "checked";
  } else {
      $checked = "";
  }

  $dataStart = explode("T", $_GET['date-start']); //Дата начала периода
  $dataEnd = explode("T", $_GET['date-end']); // Дата конца периода

  $_GET['date-start'] = implode(" ", $dataStart);
  $_GET['date-end'] = implode(" ", $dataEnd);

  $dataStartSec = strtotime($_GET['date-start']);
  $dataEndSec = strtotime($_GET['date-end']);

  $dataStartSec += $_SESSION['count']*3600;
  $dataEndSec += $_SESSION['count']*3600;

  $_SESSION['count'] = $_SESSION['count'] + $_GET["delay"];
  $delay = ($_GET["delay"] == 0) ? "" : $_GET['delay'] * 3600 * $_SESSION['count'];

  $dateStartForm = str_replace(' ', 'T', date("Y-m-d H:i", $dataStartSec));
  $dateEndForm = str_replace(' ', 'T', date("Y-m-d H:i", $dataEndSec));

}

// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Значения фактических показателей!</title>
        <meta http-equiv="Refresh" content="<?=$delay?>" />
        <meta charset="UTF-8">
    </head>
    <body>
        <form action="GrEx_Monitoring.php" method="GET">
            <p><input type="checkbox" name="send_socket" value="1" <?php echo $checked; ?> >Отправить Socket</p>
            <p>С: <input type="datetime-local" name="date-start" placeholder="С" required value="<?php echo $dateStartForm;?>"></p>
            <p>По: <input type="datetime-local" name="date-end" placeholder="По" required value="<?php echo $dateEndForm;?>"></p>
            <p>Автообновление через:
              <select size="1" name="delay">

              <?php for ($i = 0; $i < count($arrOptionTime); $i++) :?>
              <?php $selected = ($i == $_GET["delay"]) ? "selected" : ""; ?>
                <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $arrOptionTime[$i]; ?></option>
              <?php endfor; ?>

            </select></p>
            <p><input type="submit" value="Загрузить" name="download-sub"></p>
        </form>
        
<?php
    // Если нажата кнопка "Загрузить", то скрипт выполняет свою работу
    if (isset($_GET['download-sub'])) {        
        $sendSocket = false;
        $socketStart = false;

        $_GET['date-start'] = date("Y-m-d H:i", $dataStartSec);
        $_GET['date-end'] = date("Y-m-d H:i", $dataEndSec);

        $dataStart = $_GET['date-start'];
        $dataEnd = $_GET['date-end'];

        echo "Показан результат за период с $dataStart по $dataEnd";

        $diffTime = abs($dataEndSec - $dataStartSec); //Разница в секундах
        $diffTime = $diffTime/3600; // Разница в часах
        
        if (isset($_GET['send_socket']) && $_GET['send_socket'] == "1") {
          $date = date("Y.m.d_H:i");
          
          $sendSocket = true; // Позволяет запускать сокет для "красных" показателей
          $address = '**.**.**.**'; //Адрес работы сервера (скрыт по известным причинам)
          $port = 7500; //Порт работы сервера
          $msgStart = "//sok"; // Стартовое сообщение сокета для сервера
          $msg = "****** $date"; // Сообщение сокета (скрыто по известным причинам)
        }         

        // В зависимости от периода выбираем количество часов
        if ($diffTime <= 1) {
            $val = 1;
        } elseif ($diffTime > 1 && $diffTime <= 4) {
            $val = 4;
        } elseif ($diffTime > 4 && $diffTime <= 8) {
            $val = 8;
        } elseif ($diffTime > 8 && $diffTime <= 12) {
            $val = 12;
        } elseif ($diffTime > 12 && $diffTime <= 24) {
            $val = 24;
        } else {
            $val = 48;
        }
        
        // Подключаемся к промышленной БД 
        Database::connectProm();
        $sqlFact = "SELECT distinct T1.KOD, road01, road24, road28, road63, road17, road51, road58, road61, road76, road80, road83, road88, road92, road94, road96 
        FROM DBO0.SYS1_CHECKPOINT T1
        left join
        ( Select  KOD,  sum(COUNT) AS road01
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 1 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T3
        on T1.KOD = T3.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road24
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 24 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T4
        on T1.KOD = T4.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road28
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 28 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T5
        on T1.KOD = T5.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road63
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 63 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T6
        on T1.KOD = T6.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road17
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 17 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T7
        on T1.KOD = T7.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road51
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 51 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T8
        on T1.KOD = T8.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road58
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 58 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T9
        on T1.KOD = T9.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road61
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 61 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T10
        on T1.KOD = T10.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road76
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 76 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T76
        on T1.KOD = T76.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road80
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 80 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T80
        on T1.KOD = T80.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road83
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 83 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T83
        on T1.KOD = T83.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road88
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 88 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T88
        on T1.KOD = T88.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road92
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 92 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T92
        on T1.KOD = T92.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road94
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 94 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T94
        on T1.KOD = T94.KOD
        left join
        ( Select  KOD,  sum(COUNT) AS road96
          from DBO0.SYS1_CHECKPOINT 
          where DOROGAID = 96 and DATE_SYS between '$dataStart:00.000000' and '$dataEnd:00.000000' 
          group by KOD
        ) T96
        on T1.KOD = T96.KOD
        where T1.kod in (2,9,208,209,221,241,242,263,264,402,403,404,405,406,410,421,422,423,424,1042,1353,1356,1359,1397,2320,2321,2454,2976,2977,4624,4770,4771,5353,5354,5393)";
    
        $resultFact = Database::select($sqlFact);
        Database::disconnect();
        
        // Подключаемся к тестовой БД
        Database::connect();
        $sqlPlan = "select KOD, R01, R24, R28, R63, R17, R51, R58, R61, R76, R80, R83, R88, R92, R94, R96, HHS from DBO0.NSI_MES_MONITOR where HHS = $val";
        $resultPlan = Database::select($sqlPlan);
        Database::disconnect();
?>
        <table border="1" id="table_data">
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
                <td><?=$val?></td>
            </tr>            
            
        <?php foreach ($resultFact as $keyFact => $valueFact) : ?>
            <tr>
                <?php foreach ($valueFact as $key => $valFact) : ?>
                  <?php 
                    if ($key === "KOD" || $key === "HHS") {
                        echo "<td>$valFact</td>";
                        continue;
                    } else {
                        $keyPlan = str_replace("ROAD", "", $key);
                        $keyPlan = "R" . $keyPlan;
                        $valPlan = $resultPlan[$keyFact][$keyPlan];
                    }
                    if ($valPlan == "") {
                        $valPlan = 0;
                    }
                    if ($valFact == "") {
                        $valFact = 0;
                    }

                    // Закрашиваем ячейки                    
                    if ($valFact == 0) {
                        $color = "";
                    } else {
                        if ($valPlan == 0) {
                            $valPlan = 1;
                        }      
                        if (($valFact/$valPlan) >= 0.7) {
                            $color = "bgcolor='#00ff14'"; //Зеленый
                        } elseif (($valFact/$valPlan) >= 0.1 && ($valFact/$valPlan) < 0.7) {
                            $color = "bgcolor='#ffdd00'"; //Желтый
                        } elseif (($valFact/$valPlan) < 0.1) {
                            $color = "bgcolor='#ff0000'"; //Красный

                            $socketStart = true;                                                                                    
                        }
                    }
                  ?>
                  <td <?=$color?>><?=$valFact?></td> 
                <?php endforeach; ?>
            </tr>
        <?php endforeach;?>
        </table>
    <?php } ?>
    <?php
      // Создаем socket, если стоит галочка "Отправить Socket"
      if (isset($sendSocket) && isset($socketStart) && $sendSocket && $socketStart) {                              
        if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) > 0) {
          // Подключение к socket-серверу
          $result = socket_connect($socket, $address, $port);
          if ($result === false) {
              echo "Ошибка при подключении к сокету";
          } else {
              // Отправка данных сокету
              if (false == (socket_write($socket, $msgStart))) {
                 echo "socket_write() не выполнена: причина: " . socket_strerror(socket_last_error()) . PHP_EOL;
              }

              sleep(1);

              if (false == (socket_write($socket, $msg))) {
                 echo "socket_write() не выполнена: причина: " . socket_strerror(socket_last_error()) . PHP_EOL;
              }

              socket_close($socket);
          }
        } else {
          echo "Ошибка создания сокета" . PHP_EOL;
        }
      }
    ?>
    </body>
</html>
