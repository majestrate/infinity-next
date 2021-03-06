<?php namespace App;

use App\Board;
use App\Post;
use App\Report;
use App\Contracts\PermissionUser as PermissionUserContract;
use App\Traits\PermissionUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

//use Laravel\Cashier\Billable;
//use Laravel\Cashier\Contracts\Billable as BillableContract;
use InfinityNext\Braintree\Billable;
use InfinityNext\Braintree\Contracts\Billable as BillableContract;

class User extends Model implements AuthenticatableContract, BillableContract, CanResetPasswordContract, PermissionUserContract {
	
	use Authenticatable, Billable, CanResetPassword, PermissionUser;
	
	/**
	 * Distinguishes this model from an Anonymous user.
	 *
	 * @var boolean
	 */
	protected $anonymous = false;
	
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'user_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['username', 'email', 'password', 'password_legacy'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['email', 'password', 'remember_token'];
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// When creating a user, make empty email fields into NULL.
		static::creating(function($user)
		{
			if ($user->email == "")
			{
				$user->email = NULL;
			}
			
			return true;
		});
	}
	
	public function boards()
	{
		return $this->hasMany('\App\Board', 'operated_by', 'user_id');
	}
	
	public function createdBoards()
	{
		return $this->hasMany('\App\Board', 'created_by', 'user_id');
	}
	
	public function payments()
	{
		return $this->hasMany('\App\Payment', 'customer_id', 'user_id');
	}
	
	public function roles()
	{
		return $this->belongsToMany('\App\Role', 'user_roles', 'user_id', 'role_id');
	}
	
	
	/**
	 * Supplies the hashed password for this user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		$hash = $this->password;
		
		if (is_null($hash))
		{
			$legacyData  = json_decode($this->password_legacy);
			return $legacyData->hash;
		}
		
		return $hash;
	}
	
	/**
	 * Returns a hasher based on password data, allowing support for old encryption types.
	 *
	 * @return \Illuminate\Contracts\Hashing\Hasher|false
	 */
	public function getAuthObject()
	{
		$hasher = false;
		
		if (!is_null($this->password_legacy))
		{
			$legacyData   = json_decode($this->password_legacy);
			$legacyHasher = "App\\Services\\Hashing\\{$legacyData->hasher}Hasher";
			
			$hasher = new $legacyHasher;
			
			foreach ($legacyData->options as $option => $value)
			{
				$hasher->{$option} = $value;
			}
		}
		
		return $hasher;
	}
	
	/**
	 * Fetches all reports that this user can view (not submitted reports).
	 *
	 * @return Collection
	 */
	public function getReportedPostsViewable()
	{
		$posts = Post::whereHasReportsFor($this)
			->withEverything()
			->get();
		
		foreach ($posts as $post)
		{
			foreach ($post->reports as $report)
			{
				$report->setRelation('post', $post);
			}
		}
		
		return $posts;
	}
}