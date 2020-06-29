<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;

class Category extends Model
{
    protected $table = 'category_stubs';
    protected $fillable = ['name', 'description', 'is_active'];

    public static function createTable()
    {
        \Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string ('name');
            $table->text ('description')->nullable ();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('category_stubs');
    }
}
