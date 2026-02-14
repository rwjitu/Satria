<?php

/**
 * GenericModel
 * Just Generic Model without feedback (quiet)
 * Only for insert, update and delete
 */
class QgenericModel
{


    public static function insert($table, $insert)
    {
        $keys=array_keys($insert);
        $value=array_values($insert);
        $value=str_ireplace("'", "\'", $value);

        $field="`".implode('`, `', $keys)."`";
        $values="'".implode('\', \'', $value)."'";
        
        $sql = "INSERT into $table ($field) values ($values)";
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare($sql);
        $query->execute();
        $count =  $query->rowcount();
        if ($query->rowCount() == 1) {
            return true;
        }

        // default return
        return false;
    }

    public static function update($table, $update, $cond, $feedback=true){
        foreach($update as $keys=>$values){
            if(strpos($values, 'CONCAT')!==FALSE)
                $sql[]="`$keys`= $values";
            else
                $sql[]="`$keys`= '$values'";
        }
        $sql = implode(", ", $sql);
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE `$table` set $sql where $cond";
        //echo $sql;
        $query = $database->prepare($sql);
        $query->execute();
        if ($query->rowcount() == 1 ) {
          return true;
        }

        // default return
        return false;
  }

  /**
     * Delete a specific row
     * @param int $uid id of the note
     * @return bool feedback (was the note deleted properly ?)
     */
    public static function remove($table, $uid, $value)
    {
  
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM `$table` WHERE `$uid` = :value LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':value' => $value));

        if ($query->rowCount() == 1) {
            return true;
        }

        // default return
        return false;
    }

}
