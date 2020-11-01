<?php
namespace minepark\database\dao;

use minepark\database\dao\BaseDao;
use minepark\database\QueryBuilder;

class AuthDao extends BaseDao
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