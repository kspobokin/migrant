<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['title', 'file_path', 'contractor1_id', 'contractor2_id', 'template_id', 'content'];

    public function contractor1()
    {
        return $this->belongsTo(Contractor::class, 'contractor1_id');
    }

    public function contractor2()
    {
        return $this->belongsTo(Contractor::class, 'contractor2_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
