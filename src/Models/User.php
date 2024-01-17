<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Folder;
use App\Models\Document;
use App\Models\Link;
use App\Models\Team;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $fillable=[
        'first_name',
        'last_name',
        'email',
        'password',
        'google_id',
        'code',
        'register_status',
        'register_af_url',
        'affiliate_status',
        'url',
        'team_role',
        'team_status',
        'country',
        'address',
        'city',
        'postcode',
        'about',
        'profile_image',
        'avatar',

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'user_id', 'id');
    }

    public function maintainer(): HasOne
    {
        return $this->hasOne(Maintainer::class, 'user_id', 'id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getCurrentInitiatePlan()
    {
        return $this->hasOne(UserPackage::class)->where('user_packages.status', INITIATE)->whereDate('end_date', '>=', now());
    }

    public function currentPlan()
    {
        return $this->hasOne(UserPackage::class)->whereIn('user_packages.status', [ACTIVE, INITIATE])->where('user_packages.is_trail', '!=', ACTIVE)->whereDate('end_date', '>=', now());
    }

    public function trailPlan()
    {
        return $this->hasOne(UserPackage::class)->whereIn('user_packages.status', [ACTIVE, INITIATE])->where('user_packages.is_trail', ACTIVE)->whereDate('end_date', '>=', now());
    }

    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getImageAttribute(): string
    {
        if ($this->fileAttach) {
            return $this->fileAttach->FileUrl;
        }
        return asset('assets/images/no-image.jpg');
    }

    public function fileAttach()
    {
        return $this->morphOne(FileManager::class, 'origin');
    }
    
    
    public function Fodlers(){
        return $this->hasMany(Fodler::class);
    }
    public function Documents(){
        return $this->hasMany(Document::class);
    }

    
    public function Link(){
        return $this->hasOne(Link::class);
    }

    public function Team(){
        return $this->hasOne(Team::class,'owner_id');
     }
}
