<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use SoftDeletes;
	
	protected $table = 'uploads';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

    /**
     * Get the user that owns upload.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get File path
     */
    public function path()
    {
        $date_uploaded = date("Y-m-d-His-",strtotime($this->created_at));
        return url("files/".$date_uploaded.$this->name);
    }

    public function path2()
    {
        return url("files/".$this->hash."/".$this->name);
    }
}
