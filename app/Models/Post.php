<?php
namespace App\Models;

class Post extends ContentEntity
{
    public function getType(): string
    {
        return 'post';
    }
}
