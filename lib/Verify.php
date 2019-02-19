<?php

namespace lib;

class Verify
{

    protected $hostList = ['gmail.com', 'yahoo.com', 'yandex.ru', 'mail.ru'];
    protected $notFoundHostList = [];
    protected $pattern = "/^[\._a-zA-Z0-9-]+@[\.a-zA-Z0-9-]+\.[a-zA-Z]{2,6}$/";
    protected $mysqli;
    protected $limit;
    protected $table;
    protected $col_id;
    protected $col_mail;
    protected $col_status;

    public function __construct(\mysqli $mysqli, $table , $col_id, $col_mail, $col_status)
    {
        $this->mysqli = $mysqli;
        $this->table = $table;
        $this->col_id = $col_id;
        $this->col_mail = $col_mail;
        $this->col_status = $col_status;
    }

    public function addHostList($hostList)
    {
        $this->hostList = array_merge($this->hostList, $hostList);
        return $this;

    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    private function verifyEmail($email)
    {
        try{
            $expEmail = explode("@", $email);
            $host = $expEmail[1];
                  
            // Сперва смотрим соответствие адреса паттерну
            if(preg_match($this->pattern, $email)){
                // Если всё ок, проверяем на наличие хоста в списке рабочих
                if(in_array($host, $this->hostList)){
                    // Можно не слать запрос
                    return 1;
                    // Смотрим в списке хостов которых не нашли
                }elseif(in_array($host, $this->notFoundHostList)){
                    // Можно не слать запрос
                    return 2;
                }else{
                    // Если ни там ни там нет, то проверяем и добавляем в соответствующий массив
                    if (!getmxrr($host, $mxhosts)){
                        $this->notFoundHostList[] = $host;
                        return 2; 
                    }else{
                        $this->hostList[] = $host;
                        return 1; 
                    }
                }
            }else{
                return 2;
            }
        }catch(\Exception $e){
            printf("Ошибка: %s, номер ошибки: %s, текст ошибки: %s", $e->getMessage(), $this->mysqli->connect_errno, $this->mysqli->connect_error);
            return 0;
        }
    }

    public function run()
    {
        try{        
            $limit = $this->limit;
            $table = $this->table;
            $id = $this->col_id;
            $mail = $this->col_mail;
            $status = $this->col_status;
            do{ 
                $sqlValues = "";
                $sql = "SELECT * FROM {$table} WHERE {$status} = 0 LIMIT {$limit}";
                $result = $this->mysqli->query($sql);
                
                // Нужно для того чтобы в последней иттерации не ставить символ (,) в запросе
                $i = 0;
                while ($row = $result->fetch_assoc()) {
                    $verify = $this->verifyEmail($row[$mail]);
                    $sqlValues .= "({$row[$id]},{$verify})";
                    $sqlValues .=  ++$i == $result->num_rows ? "" : ",";
                }
                
                $updQuery = "INSERT INTO {$table} ({$id}, {$status})
                                VALUES {$sqlValues} ON DUPLICATE KEY UPDATE {$status} = VALUES({$status})";
                
                $this->mysqli->query($updQuery);  
                gc_collect_cycles();
                
            }while($limit == $result->num_rows);
        
            return ["memory_usage" => memory_get_usage()];
            
        }catch(\Exception $e){
            printf("Ошибка: %s, номер ошибки: %s, текст ошибки: %s", $e->getMessage(), $this->mysqli->connect_errno, $this->mysqli->connect_error);
        }
    }
}