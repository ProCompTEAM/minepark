<?php
namespace minepark\mdc\sources;

use minepark\mdc\dtos\PasswordDto;
use minepark\mdc\dtos\UserDto;

class UsersSource extends RemoteSource
{
    public const ROUTE = "users";

    public function getName() : string
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

    public function createUserWithDto(UserDto $userDto)
    {
        $this->createRequest("create", $userDto);
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

    public function updateUserJoinStatus(string $userName)
    {
        $this->createRequest("update-join-status", $userName);
    }

    public function updateUserQuitStatus(string $userName)
    {
        $this->createRequest("update-quit-status", $userName);
    }

    protected function createDto(array $data) : UserDto
    {
        $dto = new UserDto();
        $dto->set($data);
        return $dto;
    }
}
?>