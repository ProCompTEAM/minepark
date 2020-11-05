<?php
namespace minepark\database;

use minepark\database\Database;

class QueryBuilder
{
    public const COLUMN_DESCRIPTION_VARCHAR = "varchar(255) DEFAULT NULL";
    public const COLUMN_DESCRIPTION_INTEGER = "int(11) NOT NULL";

    public const SEPERATOR = ';' . PHP_EOL;

    private $query;

    public static function buildTable(string $name)
    {
        $id = $name . 'Id';

        Database::getDatabase()->sql("CREATE TABLE IF NOT EXISTS $name (
            $id int(11) NOT NULL AUTO_INCREMENT,
            PRIMARY KEY ($id))" . self::SEPERATOR);
    }

    public static function dropTable(string $name)
    {
        Database::getDatabase()->sql("DROP TABLE IF EXISTS $name" . self::SEPERATOR);
    }

    public static function addColumns(string $tableName, array $columns)
    {
        foreach($columns as $col => $description) {
            Database::getDatabase()->sql("ALTER TABLE $tableName ADD COLUMN $col $description", true);
        }
    }

    public static function single(string $tableName, string $column, string $filter = "") : string
    {
        $filterQuery = empty($filter) ? "" : "where $filter";
        return (string) Database::getDatabase()->sql("SELECT $column from $tableName $filterQuery". self::SEPERATOR);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function commit()
    {
        return Database::getDatabase()->sql($this->query);
    }
}
?>