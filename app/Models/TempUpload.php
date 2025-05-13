<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TempUpload extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $table = 'temp_uploads'; // или просто можно не использовать таблицу
    public $timestamps = false;
    protected $guarded = [];
//    protected $fillable = ['id'];
}
