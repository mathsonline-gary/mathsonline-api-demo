<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expired_tasks_excluded',
        'balloon_tips_enabled',
        'results_enabled',
        'confetti_enabled',
        'closed_captions_language',
    ];

    public $timestamps = false;

    protected $casts = [
        'student_id' => 'integer',
        'expired_tasks_excluded' => 'boolean',
        'balloon_tips_enabled' => 'boolean',
        'results_enabled' => 'boolean',
        'confetti_enabled' => 'boolean',
    ];

    /**
     * Get the student that owns the setting.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
