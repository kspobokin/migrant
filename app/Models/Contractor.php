<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Contractor extends Authenticatable
{
    use Notifiable;

    protected $guard = 'counterparty';
    protected $fillable = [
        'last_name_ru', 'first_name_ru', 'patronymic_ru', 'last_name_lat', 'first_name_lat', 'patronymic_lat',
        'email', 'phone', 'inn', 'insurance_policy', 'registration_address', 'type', 'role', 'extra_fields', 'password'
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['extra_fields' => 'array'];

    public function documents1()
    {
        return $this->hasMany(Document::class, 'contractor1_id');
    }

    public function documents2()
    {
        return $this->hasMany(Document::class, 'contractor2_id');
    }
}
