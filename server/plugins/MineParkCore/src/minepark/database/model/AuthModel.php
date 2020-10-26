<?php
namespace minepark\database\model;

use minepark\Core;
use minepark\database\QueryBuilder;

class AuthModel extends Model
{
    public const NAME = "Auth";

    public function create()
    {
        $this->columns = [
            "UserName" => QueryBuilder::COLUMN_DESCRIPTION_VARCHAR,
            "KeyHash" => QueryBuilder::COLUMN_DESCRIPTION_VARCHAR,
            "Address" => QueryBuilder::COLUMN_DESCRIPTION_VARCHAR
        ];

        $this->build(self::NAME, $this->columns);
    }

    public function drop()
    {
        $this->unset(self::NAME);
    }
}
?>