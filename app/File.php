<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    protected $fillable = ['filename', 'version', 'user', 'category_id', 'url', 'display_name'];


    public function category ()
    {
        $this->belongsTo('App\Category');
    }

    public function user ()
    {
        $this->belongsTo('App\User', 'user', 'name');
    }
}
