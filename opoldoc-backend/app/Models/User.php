<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'parent_user_id',
        'email',
        'password_hash',
        'role',
        'status',
        'firstname',
        'lastname',
        'middlename',
        'birthdate',
        'sex',
        'address',
        'contact_number',
        'license_number',
        'specialization',
        'signature_path',
        'prof_path',
        'employee_number',
        'hire_date',
        'is_dependent',
        'account_activated',
        'relationship',
        'is_first_login',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password_hash',
        'password_reset_token',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'hire_date' => 'date',
        'is_dependent' => 'bool',
        'account_activated' => 'bool',
        'is_first_login' => 'bool',
        'password_reset_expires_at' => 'datetime',
    ];

    protected $appends = [
        'must_change_credentials',
        'current_role',
        'signature_url',
        'prof_path_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (! in_array($user->role, ['doctor', 'receptionist'], true)) {
                return;
            }

            $current = is_string($user->employee_number) ? trim($user->employee_number) : '';
            if ($current !== '') {
                return;
            }

            $user->employee_number = self::nextEmployeeNumberForRole((string) $user->role);
        });
    }

    private static function employeePrefixForRole(string $role): ?string
    {
        return match (strtolower($role)) {
            'doctor' => 'DTR',
            'receptionist' => 'RCP',
            default => null,
        };
    }

    private static function nextEmployeeNumberForRole(string $role): ?string
    {
        $prefix = self::employeePrefixForRole($role);
        if (! $prefix) {
            return null;
        }

        return DB::transaction(function () use ($prefix) {
            $latest = DB::table('users')
                ->select('employee_number')
                ->where('employee_number', 'like', $prefix.'-%')
                ->orderBy('employee_number', 'desc')
                ->lockForUpdate()
                ->first();

            $latestValue = is_object($latest) ? ($latest->employee_number ?? null) : null;
            $latestValue = is_string($latestValue) ? trim($latestValue) : '';

            $next = 1;
            if ($latestValue !== '') {
                $pattern = '/^'.preg_quote($prefix, '/').'-([0-9]{4})$/';
                if (preg_match($pattern, $latestValue, $m)) {
                    $next = ((int) $m[1]) + 1;
                }
            }

            return $prefix.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_user_id', 'user_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_user_id', 'user_id');
    }

    public function accessiblePatientIds(): array
    {
        $selfId = (int) $this->user_id;

        if ($this->role !== 'patient') {
            return [$selfId];
        }

        if ((bool) $this->is_dependent) {
            return [$selfId];
        }

        $childIds = $this->children()->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        array_unshift($childIds, $selfId);

        return array_values(array_unique($childIds));
    }

    public function canAccessPatientId(int $patientId): bool
    {
        return in_array((int) $patientId, $this->accessiblePatientIds(), true);
    }

    public function doctorSchedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'user_id');
    }

    public function patientVerifications()
    {
        return $this->hasMany(PatientVerification::class, 'patient_id', 'user_id');
    }

    public function processedVerifications()
    {
        return $this->hasMany(PatientVerification::class, 'verified_by', 'user_id');
    }

    public function getMustChangeCredentialsAttribute(): bool
    {
        return (bool) $this->is_first_login;
    }

    public function getCurrentRoleAttribute(): array
    {
        return [
            'role_name' => $this->role,
        ];
    }

    public function getPersonalInformationAttribute(): object
    {
        $fullName = trim(implode(' ', array_filter([
            $this->firstname,
            $this->middlename,
            $this->lastname,
        ], function ($v) {
            return (string) $v !== '';
        })));

        if ($fullName === '') {
            $fullName = 'User #'.$this->user_id;
        }

        return (object) [
            'full_name' => $fullName,
            'first_name' => $this->firstname,
            'middle_name' => $this->middlename,
            'last_name' => $this->lastname,
            'birthdate' => $this->birthdate,
            'sex' => $this->sex,
            'address' => $this->address,
            'mobile_number' => $this->contact_number,
        ];
    }

    public function getSignatureUrlAttribute(): ?string
    {
        $path = $this->signature_path;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return url('/signatures/'.$this->user_id);
    }

    public function getProfPathUrlAttribute(): ?string
    {
        $path = $this->prof_path ?? null;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return url('/profiles/'.$this->user_id);
    }
}
