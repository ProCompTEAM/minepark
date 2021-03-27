<?php
namespace minepark\external;

use minepark\Core;
use minepark\Tasks;
use minepark\defaults\Files;

class WebApi
{
    public const API_ENABLED = true;
    public const API_VERSION = 1;

    public const PORT = 19132;

    public const ACCEPT_SECONDS_TIMEOUT = 3;

    private $socket;

    public function __construct()
    {
        if(!self::API_ENABLED) {
            return;
        }

        $this->log("Loading WebApi on port " . self::PORT . "...");

        $this->socket = socket_create_listen(self::PORT, 1);
        socket_set_nonblock($this->socket);

        Tasks::registerRepeatingAction(self::ACCEPT_SECONDS_TIMEOUT, [$this, "accept"]);
    }

    public function getCore() : Core
    {
        return Core::getActive();
    }
    
    public function accept()
    {
        $connection = socket_accept($this->socket);
        
        if($connection !== false) {
            $this->log("Detected connection from " . $this->getAddress($connection));
            $this->handle($connection);
            socket_close($connection);
        }
    }

    private function log(string $message)
    {
        $this->getCore()->getLogger()->info("[webapi] " . $message);

        $note = date("d.m.Y H:i:s") . " > " . $message . PHP_EOL;
        file_put_contents(Files::WEBAPI_LOG_FILE, $note, FILE_APPEND);
    }

    private function getAddress($connection) : string
    {
        socket_getpeername($connection, $address);
        return $address;
    }

    private function handle($connection)
    {
        @socket_read($connection, 2048);
        socket_write($connection, $this->getData());
    }

    private function getData() : string
    {
        return json_encode([
            "api" => self::API_VERSION,
            "online" => $this->getOnline(),
            "max" => $this->getMaxOnline(),
            "players" => $this->getOnlinePlayers()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function getOnlinePlayers() : array
    {
        $players = [];

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $playerArr = [
                "name" => $player->getName(),
                "x" => floor($player->getX()),
                "y" => floor($player->getY()),
                "z" => floor($player->getZ()),
                "level" => $player->getLevel()->getName()
            ];

            array_push($players, $playerArr);
        }

        return $players;
    }

    private function getOnline() : int
    {
        return count($this->getCore()->getServer()->getOnlinePlayers());
    }

    private function getMaxOnline() : int
    {
        return $this->getCore()->getServer()->getMaxPlayers();
    }
}
?>