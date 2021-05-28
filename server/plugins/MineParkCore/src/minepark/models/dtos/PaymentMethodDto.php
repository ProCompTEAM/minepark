<?php
namespace minepark\models\dtos;

class PaymentMethodDto extends BaseDto
{
    public string $name;

    public int $method;
}