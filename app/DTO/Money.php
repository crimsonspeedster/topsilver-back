<?php
namespace App\DTO;

class Money
{
    public function __construct(
        public string $amount,
        public string $currency,
    ) {}

    public function format(): string
    {
        return "{$this->currency}" . number_format($this->amount, 0, '.', '');
    }
}
