<?php
require_once('GrEx_config.php');
//require_once('log.php');

class Database {
 
 private static $connect;

 public static function connect() {
   global $DB, $DBPASS, $DBUSER;
   self::$connect = odbc_connect($DB, $DBUSER, $DBPASS);
   if (!self::$connect) {
    //log::WriteLog("�� ������� ������������ � ���� ������ Doclad"); 
    echo "<p><b>Error connect Database</b></p>";
    exit();
    return false;

    //throw new Exception("���������� ����������� � ����� ������", E_USER_ERROR);
   }
    
 }
 
  public static function connectProm() {
   global $DB_PROM, $DBPASS, $DBUSER;
   self::$connect = odbc_connect($DB_PROM, $DBUSER, $DBPASS);
   if (!self::$connect) {
    //log::WriteLog("�� ������� ������������ � ���� ������ Doclad"); 
    echo "<p><b>Error connect Database</b></p>";
    exit();
    return false;

    //throw new Exception("���������� ����������� � ����� ������", E_USER_ERROR);
   }
    
 }
 
 public static function disconnect() {
   if (self::$connect) {
      odbc_close(self::$connect);
   }
 }
 
 //select �� ����------------------------
 public static function select($sql) {
 
  $result=odbc_exec(self::$connect,$sql);  
  if (!is_resource($result)) {
    //����� ������
    $err = odbc_errormsg(self::$connect);
    //log::WriteLog("������ ��� ���������� ������� ".$sql."\n".$err); 
    throw new Exception($err);
  } 
  
  $arReturn = array();
  
  while ($row=@odbc_fetch_array($result)) {
    $arReturn[] = $row;  
  }
  
  //$result->close();
  
  return $arReturn; 
 }
 
 //�������� ������������� ������, ��� ��� id - ��������
 public static function select1($sql, $field1, $field2) {
    
  $result=odbc_exec(self::$connect,$sql);  
  if (!is_resource($result)) {
    //����� ������
    $err = odbc_errormsg(self::$connect);
    //log::WriteLog("������ ��� ���������� ������� ".$sql."\n".$err); 
    throw new Exception($err);
  } 
 
  $arReturn = array();
  
  while ($row=@odbc_fetch_array($result)) {
    $val = self::Encoding($row[$field2]);
    $arReturn[$row[$field1]] = $val;  
  }
  
  return $arReturn; 
 }
 
 //$tab - ��� �������
 //$arCond - ������������� ������ ��� "��� ���� - ��������" ��� where
 //$arfields - ������ ����� ��� �������
 public static function selectW($tab, $arfields, $arCond) {
  $arWhere = array();
  foreach ($arCond as $field => $val) {
    //if (!is_numeric($val)) {
    //    $val = "'".$val."'";
    //}
    
    $arWhere[] = $field." = ".$val; 
  }
  
  $sql = "SELECT ".join(", ", $arfields)." FROM ".$tab;
  $sql = $sql." WHERE ". join(" AND ", $arWhere); 
  //echo $sql."<br><br>";
  
  $result=odbc_exec(self::$connect,$sql);  
  if (!is_resource($result)) {
    //����� ������
    $err = odbc_errormsg(self::$connect);
    //log::WriteLog("������ ��� ���������� ������� ".$sql."\n".$err); 
    throw new Exception($err);
  } 
  
  $arReturn = array();
  
  while ($row=@odbc_fetch_array($result)) {
    $arReturn[] = $row;    
  }
  
  return $arReturn; 
   
 }
 
 public static function upd_ins($sql) {
   //log::WriteLog("���������� ������� ".$sql."\n"); 
   $result = odbc_exec(self::$connect,$sql);
   
   if (!is_resource($result)) {
    //����� ������
     $err = odbc_errormsg(self::$connect);
     //log::WriteLog("������ ��� ���������� �������: ".$err); 
   }  
 } 
 
 //�������� �� ����-----------------------
 //$table - ������� �� ������� �������
 //$arCond - ������������� ������ ��� "��� ���� - ��������" ��� where
 public static function delete($table, $arCond) {
  $arWhere = array();
  foreach ($arCond as $field => $val) {
    if (!is_numeric($val)) {
        $val = "'".$val."'";
    }
    
    $arWhere[] = $field." = ".$val; 
  }
 
  $sql = "DELETE FROM ".$table." WHERE " . join(' AND ', $arWhere);
  $result=odbc_exec(self::$connect,$sql);  
  
  if (!is_resource($result)) {
    //����� ������
    $err = odbc_errormsg(self::$connect);
    //log::WriteLog("������ ��� ���������� ������� ".$sql."\n".$err); 
    throw new Exception($err);
  }
     
 }
 
 static function Encoding($per) {
    return iconv('windows-1251', 'UTF-8', $per);  
  }
}
?>