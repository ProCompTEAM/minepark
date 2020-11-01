<?php
namespace minepark\database;

use minepark\Core;

use minepark\database\dao\AuthDao;
use minepark\database\dao\BaseDao;

class Database
{
    public const DB_ADDRESS = "localhost:3306";
    public const DB_USER = "root";
    public const DB_PASSWORD = "9999";
    public const DB_NAME = "minepark";

    public $enableLogging;

    private $db;
    private $daos;

    public static function getDatabase() : Database
    {
        return Core::getDatabase();
    }

    public function loadAll()
    {
        $this->initializeDatabase();

        $this->info(mysqli_get_server_info($this->db));

        $this->enableLogging = true;

        $this->daos = [
            AuthDao::NAME => new AuthDao
        ];

        foreach($this->daos as $dao) {
            $dao->initialize();
        }

        $this->info("Database ready for work.");
    }

    public function unloadAll()
    {
        mysqli_close($this->db);
    }

    public function resetAll()
    {
        foreach($this->daos as $dao) {
            $dao->drop();
        }
    }

    public function from(string $name) : ?BaseDao
    {
        return $this->daos[$name];
    }

    public function sql(string $query, bool $ignoreErrors = false)
    {
        if($this->enableLogging) {
            $this->log($query);
        }

        $result = mysqli_query($this->db, $query);

        if(!$result && !$ignoreErrors) {
            $errorMessage = mysqli_error($this->db);

            if(strlen($errorMessage) > 0) {
                if($this->enableLogging) {
                    $this->log($errorMessage, "ERROR");
                }
                $this->error($errorMessage);
            }
        }

        return $result;
    }

    public function info(string $message)
    {
        Core::getActive()->getLogger()->info("[storage] " . $message);
    }

    public function error(string $message)
    {
        Core::getActive()->getLogger()->error("[storage] " . $message);
    }

    private function initializeDatabase()
    {
        $this->db = mysqli_connect(self::DB_ADDRESS, self::DB_USER, self::DB_PASSWORD, self::DB_NAME);
    }

    private function log(string $note, string $prefix = "")
    {
        $note = PHP_EOL . $prefix . " " . date("d.m.Y H:i:s") . " > " . PHP_EOL . $note;
        file_put_contents(Core::SQL_LOG_FILE, $note, FILE_APPEND);
    }
}
?>