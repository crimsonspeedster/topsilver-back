<?php
namespace App\Models;

class Page extends ContentEntity
{
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = $this->getFillable();
    }

    public function getType(): string
    {
        return 'page';
    }
}
