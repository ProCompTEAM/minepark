<?php
namespace minepark\database\dao;

use minepark\database\Database;
use minepark\database\QueryBuilder;

abstract class BaseDao
{
    abstract public function create();

    abstract public function drop();

    protected $columns;

    public function initialize()
    {
        $this->columns = [];

        $this->create();
    }

    protected function sql(string $query)
    {
        return Database::getDatabase()->sql($query);
    }

    protected function isset(string $columnName)
    {
        return isset($this->columns[$columnName]);
    }

    protected function build(string $name, array $columns)
    {
        QueryBuilder::buildTable($name);
        QueryBuilder::addColumns($name, $columns);
    }

    protected function unset(string $name)
    {
        QueryBuilder::dropTable($name);
    }
}
?>