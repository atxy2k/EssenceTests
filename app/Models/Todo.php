<?php namespace App\Models;

use App\Enums\StatusTodoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    protected $fillable = ['title','description', 'created_by_id', 'updated_by_id','responsible_id','status'];
    protected $guarded = ['id'];
    protected $casts = [
        'status' => StatusTodoEnum::class
    ];

    public function created_by() : BelongsTo
    {
        return $this->belongsTo(User::class,'created_by_id');
    }

    public function updated_by() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function responsible() : BelongsTo
    {
        return $this->belongsTo(User::class,'responsible_id');
    }

}

