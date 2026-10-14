<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'reference_id',
        'reference_type',
        'data',
        'is_read',
        'read_at',
        'is_shown_popup',
    ];

    protected $casts = [
        'data'           => 'array',
        'is_read'        => 'boolean',
        'is_shown_popup' => 'boolean',
        'read_at'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnshownPopup($query)
    {
        return $query->where('is_shown_popup', false);
    }

    /**
     * Tạo thông báo gửi cho nhân viên/KTV
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $type = 'general',
        $reference = null,
        array $extraData = []
    ): self {
        $referenceId = null;
        $referenceType = null;
        if ($reference && is_object($reference) && method_exists($reference, 'getKey')) {
            $referenceId = $reference->getKey();
            $referenceType = get_class($reference);
        }

        return self::create([
            'user_id'        => $userId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'action_url'     => $actionUrl,
            'reference_id'   => $referenceId,
            'reference_type' => $referenceType,
            'data'           => $extraData,
            'is_read'        => false,
            'is_shown_popup' => false,
        ]);
    }
}
