<?php
namespace App\Models;

class Page extends ContentEntity
{
    public function getType(): string
    {
        return 'page';
    }
}
