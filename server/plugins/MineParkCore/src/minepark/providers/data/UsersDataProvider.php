<?php
namespace minepark\providers\data;

use minepark\models\dtos\ChatMessageDto;
use minepark\models\dtos\ExecutedCommandDto;
use minepark\models\dtos\PasswordDto;
use minepark\models\dtos\UserDto;
use minepark\models\dtos\UserSettingsDto;
use minepark\providers\base\DataProvider;

class UsersDataProvider extends DataProvider
{
    public const ROUTE = "users";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function isUserExist(string $userName) : bool
    {
        return (bool) $this->createRequest("exist", $userName);
    }

    public function getUser(string $userName) : ?UserDto
    {
        $requestResult = $this->createRequest("get-user", $userName);
        return $requestResult ? $this->createDto($requestResult) : null;
    }

    public function getUserSettings(string $userName) : ?UserSettingsDto
    {
        $requestResult = $this->createRequest("get-user-settings", $userName);
        return $requestResult ? $this->createSettingsDto($requestResult) : null;
    }

    public function getUserPassword(string $userName) : string
    {
        return (string) $this->createRequest("get-password", $userName);
    }

    public function isUserPasswordExist(string $userName) : bool
    {
        return (bool) $this->createRequest("exist-password", $userName);
    }

    public function setUserPassword(PasswordDto $passwordDto)
    {
        $this->createRequest("set-password", $passwordDto);
    }

    public function resetUserPassword(string $userName)
    {
        $this->createRequest("reset-password", $userName);
    }

    public function createUserInternal(string $userName) : UserDto
    {
        $requestResult = $this->createRequest("create-internal", $userName);

        return $this->createDto($requestResult);
    }

    public function updateUserData(UserDto $userDto)
    {
        $this->createRequest("update", $userDto);
    }

    public function updateUserSettings(UserSettingsDto $settingsDto)
    {
        $this->createRequest("update-settings", $settingsDto);
    }

    public function updateUserJoinStatus(string $userName)
    {
        $this->createRequest("update-join-status", $userName);
    }

    public function updateUserQuitStatus(string $userName)
    {
        $this->createRequest("update-quit-status", $userName);
    }

    public function saveExecutedCommand(string $userName, string $command)
    {
        $dto = $this->createExecutedCommandDto($userName, $command);
        $this->createRequest("save-executed-command", $dto);
    }

    public function saveChatMessage(string $userName, string $message)
    {
        $dto = $this->createChatMessageDto($userName, $message);
        $this->createRequest("save-chat-message", $dto);
    }

    protected function createDto(array $data) : UserDto
    {
        $dto = new UserDto();
        $dto->set($data);
        return $dto;
    }

    private function createSettingsDto(array $data) : UserSettingsDto
    {
        $dto = new UserSettingsDto();
        $dto->set($data);
        return $dto;
    }

    private function createExecutedCommandDto(string $userName, string $command) : ExecutedCommandDto
    {
        $dto = new ExecutedCommandDto;
        $dto->sender = $userName;
        $dto->command = $command;
        return $dto;
    }

    private function createChatMessageDto(string $userName, string $message) : ChatMessageDto
    {
        $dto = new ChatMessageDto;
        $dto->sender = $userName;
        $dto->message = $message;
        return $dto;
    }
}