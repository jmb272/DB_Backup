<?php
/**
 * DB Backup.
 * By James Bailey
 *
 * Original Author: Unknown.
 *
 * Description:
 * If a website's MySQL database is required with no access to phpMyAdmin
 * or an alternative DB interface, this script may be used to extract
 * all the tables & data reliably.
 */

// Set your IP Address.
define('MY_IP', '127.0.0.1');

// Forbid access to anyone else.
if ($_SERVER['REMOTE_ADDR'] != MY_IP) exit;

backup_tables('localhost', 'username', 'password', 'database');

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$tables = '*')
{
    $link = mysql_connect($host,$user,$pass);
    mysql_select_db($name,$link);

    // Get a list of all tables.
    if($tables == '*') {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result)) $tables[] = $row[0];
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    // Cycle through.
    foreach($tables as $table)
    {
        $result = mysql_query('SELECT * FROM '.$table);
        $num_fields = mysql_num_fields($result);

        $return.= 'DROP TABLE '.$table.';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while($row = mysql_fetch_row($result)) {
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        
        $return.="\n\n\n";
    }

    // Save file.
    $handle = fopen('db-backups/db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
    fwrite($handle,$return);
    fclose($handle);
}