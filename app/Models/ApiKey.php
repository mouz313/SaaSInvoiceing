<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'key_prefix',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The merchant user owning this API key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new cryptographically secure API key.
     *
     * @param  array<int, string>  $abilities
     * @return array{key: string, model: ApiKey}
     */
    public static function generate(User $user, string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): array
    {
        $plainToken = 'ih_live_'.Str::random(40);
        $prefix = substr($plainToken, 0, 12);
        $hash = hash('sha256', $plainToken);

        $model = static::create([
            'user_id' => $user->id,
            'name' => $name,
            'key_hash' => $hash,
            'key_prefix' => $prefix,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [
            'key' => $plainToken,
            'model' => $model,
        ];
    }

    /**
     * Find an active API key model by plain token.
     */
    public static function findToken(string $plainToken): ?self
    {
        $hash = hash('sha256', $plainToken);

        $key = static::where('key_hash', $hash)->first();

        if (! $key) {
            return null;
        }

        if ($key->expires_at && $key->expires_at->isPast()) {
            return null;
        }

        return $key;
    }

    /**
     * Determine if token has a specific ability.
     */
    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    /**
     * Touch last used at timestamp.
     */
    public function touchLastUsed(): void
    {
        $this->updateQuietly(['last_used_at' => now()]);
    }
}
