<?php
namespace minepark\mdc\sources;

use minepark\mdc\dto\BaseDto;
use minepark\mdc\dto\PasswordDto;
use minepark\mdc\dto\UserDto;

class UsersSource extends RemoteSource
{
    public function getName() : string
    {
        return "users";
    }

    public function isUserExist(string $userName) : bool
    {
        return (bool) $this->createRequest("exist", $userName);
    }

    public function getUser(string $userName) : UserDto
    {
        $requestResult = $this->createRequest("get-user", $userName);

        return $this->createDto($requestResult);
    }

    public function getUserPassword(string $userName) : string
    {
        return (string) $this->createRequest("get-password", $userName);
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

    public function deleteUser(string $userName)
    {
        $this->createRequest("delete", $userName);
    }

    public function updateUserJoinStatus(string $userName)
    {
        $this->createRequest("update-join-status", $userName);
    }

    public function updateUserQuitStatus(string $userName)
    {
        $this->createRequest("update-quit-status", $userName);
    }

    protected function createDto(array $data)
    {
        $dto = new UserDto();
        $dto->set($data);
        return $dto;
    }
}
?>