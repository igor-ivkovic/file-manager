<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['category_name', 'parent_id', 'position', 'url', 'rights', 'children'];

    public function files()
    {
        return $this->hasMany('App\File');
    }
}
