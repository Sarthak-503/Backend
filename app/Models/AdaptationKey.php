<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;


class AdaptationKey extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;
    use SoftDeletes;

    public $translatedAttributes = ['title', 'desc', 'purpose'];

    protected $fillable = [
        'id',
        'adaptation_id',
        'key',
        'code',
        'title',
        'desc',
        'purpose',
        'related_keys',
        'parent_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        // 'parent_id',
        'desc',
        'purpose',
        'adaptation_id',
        'translations',
        'parent'
        // 'relatedKeys'
    ];

    public function relatedKeys($lang = null)
    {
        $relatedKeys = [];

        if ($this->related_keys) {
            $relatedKeyCodes = explode(',', $this->related_keys);

            foreach ($relatedKeyCodes as $code) {
                $relatedKey = AdaptationKey::where('key', $code)->first();
                if($relatedKey) {
                    $translations = [];
                    foreach ($relatedKey->translations as $translation) {
                        $translations[] = [
                            'locale' => $translation->locale,
                            'title' => $translation->title,
                        ];
                    }

                    $relatedKeys[] = [
                        'key' => $relatedKey->key,
                        // 'name' => $relatedKey->translateOrDefault($lang)->title,
                        'translations' => $translations,
                    ];
                }
            }
        }

        return $relatedKeys;
    }

    public function parent() {
        return $this->belongsTo(AdaptationKey::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(AdaptationKey::class, 'parent_id')->with('children');
    }

    public function i18n() {
        return $this->hasMany(AdaptationKeyI18n::class, 'adaptation_key_id');
    }
}
