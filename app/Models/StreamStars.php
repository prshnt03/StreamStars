<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamStars extends Model
{
    use HasFactory;
    protected $fillable = ['id','user_id','channel_name','stream_title','game_name','viewers_count','started_at'];
}