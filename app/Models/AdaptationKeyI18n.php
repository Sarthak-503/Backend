<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdaptationKeyI18n extends Model
{
    use HasFactory;

    protected $table = 'adaptation_keys_i18n';
    public $timestamps = false;

    protected $fillable = [
        'adaptation_key_id',
        'locale',
        'title',
        'desc',
        'purpose'
    ];

    protected $hidden = [
        'id',
        'adaptation_key_id'
    ];

    public function adaptationKey()
    {
        return $this->belongsTo(AdaptationKey::class);
    }
}
