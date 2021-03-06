<?php namespace App\Http\Requests;

use App\Ban;
use App\Board;
use App\Post;
use App\Services\UserManager;
use App\Support\IP\CIDR;

use Auth;
use View;

class PostRequest extends Request {
	
	const VIEW_BANNED = "errors.banned";
	
	/**
	 * Input items that should not be returned when reloading the page.
	 *
	 * @var array
	 */
	protected $dontFlash = ['password', 'password_confirmation', 'captcha'];
	
	/**
	 * The board pertinent to the request.
	 *
	 * @var App\Board
	 */
	protected $board;
	
	/**
	 * A ban pulled during validation checks.
	 *
	 * @var App\Ban
	 */
	protected $ban;
	
	/**
	 * The user.
	 *
	 * @var App\Trait\PermissionUser
	 */
	protected $user;
	
	/**
	 * Fetches the user and our board config.
	 *
	 * @return void
	 */
	public function __construct(Board $board, UserManager $manager)
	{
		$this->board = $board;
		$this->user  = $manager->user;
	}
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
		if (isset($input['files']) && is_array($input['files']))
		{
			// Having an [null] file array passes validation.
			$input['files'] = array_filter($input['files']);
		}
		
		if (isset($input['capcode']) && $input['capcode'])
		{
			$user = $this->user;
			
			if ($user && !$user->isAnonymous())
			{
				$role = $user->roles->where('role_id', $input['capcode'])->first();
				
				if ($role && $role->capcode != "")
				{
					$input['capcode_id'] = (int) $role->role_id;
					$input['author']     = $user->username;
				}
			}
			else
			{
				unset($input['capcode']);
			}
		}
		
		return $input;
	}
	
	/**
	 * Returns if the client has access to this form.
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		return true;
	}
	
	/**
	 * Returns validation rules for this request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$board = $this->board;
		$user  = $this->user;
		$rules = [
			// Nothing, by default.
			// Post options are contingent on board settings and user permissions.
		];
		
		// Modify the validation rules based on what we've been supplied.
		if ($board && $user)
		{
			$rules['body'] = [
				"max:" . $board->getConfig('postMaxLength', 65534),
			];
			
			if (!$board->canAttach($user))
			{
				$rules['body'][]  = "required";
				$rules['files'][] = "array";
				$rules['files'][] = "max:0";
			}
			else
			{
				$attachmentsMax = $board->getConfig('postAttachmentsMax', 1);
				
				$rules['body'][]  = "required_without:files";
				$rules['files'][] = "array";
				$rules['files'][] = "min:1";
				$rules['files'][] = "max:{$attachmentsMax}";
				
				// Create an additional rule for each possible file.
				for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment)
				{
					$rules["files.{$attachment}"] = [
						"mimes:jpeg,gif,png,svg,pdf,epub,webm,mp4,ogg",
						
						## TODO ##
						// Make maximum filesize a config option.
						"between:0,8000",// . $controller->option('attachmentFilesize'),
					];
				}
			}
		}
		
		return $rules;
	}
	
	/**
	 * Get the response for a forbidden operation.
	 *
	 * @param  array $errors
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function response(array $errors)
	{
		$redirectURL = $this->getRedirectUrl();
		
		if ($this->ban)
		{
			return redirect($this->ban->getRedirectUrl());
		}
		
		return redirect($redirectURL)
			->withInput($this->except($this->dontFlash))
			->withErrors($errors, $this->errorBag);
	}
	
	/**
	 * Validate the class instance.
	 * This overrides the default invocation to provide additional rules after the controller is setup.
	 *
	 * @return void
	 */
	public function validate()
	{
		$board = $this->board;
		$user  = $this->user;
		
		if (is_null($board) || is_null($user))
		{
			return parent::validate();
		}
		
		
		$validator = $this->getValidatorInstance();
		
		// Ban check.
		$ban = Ban::getBan($this->ip(), $board->board_uri);
		
		if ($ban)
		{
			$messages = $validator->errors();
			$messages->add("body", trans("validation.custom.banned"));
			$this->ban = $ban;
			$this->failedValidation($validator);
			return;
		}
		
		// Board-level setting validaiton.
		$validator->sometimes('captcha', "required|captcha", function($input) use ($board) {
			return !$board->canPostWithoutCaptcha($this->user);
		});
		
		if (!$validator->passes())
		{
			$this->failedValidation($validator);
		}
		else
		{
			if (!$this->user->canAdminConfig() && $board->canPostWithoutCaptcha($this->user))
			{
				// Check last post time for flood.
				$floodTime = site_setting('postFloodTime');
				
				if ($floodTime > 0)
				{
					$lastPost = Post::getLastPostForIP();
					
					if ($lastPost)
					{
						$floodTimer = clone $lastPost->created_at;
						$floodTimer->addSeconds($floodTime);
						
						if ($floodTimer->isFuture())
						{
							$messages = $validator->errors();
							$messages->add("body", trans("validation.custom.post_flood", [
								'time_left' => $floodTimer->diffInSeconds(),
							]));
							$this->failedValidation($validator);
						}
					}
				}
			}
			
			
			// This is a hack, but ...
			// If a file is uploaded that has a specific filename, it breaks the process.
			
			$input = $this->all();
			
			// Process uploads.
			if (isset($input['files']))
			{
				$uploads = $input['files'];
				
				if(count($uploads) > 0)
				{
					foreach ($uploads as $uploadIndex => $upload)
					{
						if(method_exists($upload, "getPathname") && !file_exists($upload->getPathname()))
						{
							$messages = $validator->errors();
							$messages->add("files.{$uploadIndex}", trans("validation.custom.file_corrupt", [
								"filename" => $upload->getClientOriginalName(),
							]));
							$this->failedValidation($validator);
							break;
						}
					}
				}
			}
		}
	}
}