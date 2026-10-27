<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now()->toDateString());
            })
            ->exists();
    }

    /**
     * Determine whether the user may open the paid lessons of a course.
     *
     * Access is granted by an active subscription to a plan that includes this
     * course, so a cheaper tier cannot open courses of a higher tier. A plan
     * without courses grants nothing (deny by default).
     */
    public function hasAccessToCourse(Course $course): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now()->toDateString());
            })
            ->whereHas('planRecord.courses', function (Builder $query) use ($course): void {
                $query->whereKey($course->getKey());
            })
            ->exists();
    }

    /**
     * @return HasMany<CourseEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot(['enrolled_at', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<CourseLessonCompletion, $this>
     */
    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(CourseLessonCompletion::class);
    }

    /**
     * @return BelongsToMany<CourseLesson, $this>
     */
    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(CourseLesson::class, 'course_lesson_completions')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    /**
     * Send the password reset notification using Ilmora's branded email that
     * points back to the single-page application.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<LessonNote, $this>
     */
    public function lessonNotes(): HasMany
    {
        return $this->hasMany(LessonNote::class);
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<CourseReview, $this>
     */
    public function courseReviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? '/storage/'.$this->avatar : null;
    }
}
