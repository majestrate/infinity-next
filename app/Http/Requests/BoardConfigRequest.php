<?php namespace App\Http\Requests;

use App\Board;
use App\OptionGroup;
use App\Services\UserManager;

use Auth;
use View;

class BoardConfigRequest extends Request {
	
	/**
	 * Input items that should not be returned when reloading the page.
	 *
	 * @var array
	 */
	protected $dontFlash = ['password', 'password_confirmation', 'captcha'];
	
	/**
	 * A list of applicable board option groups.
	 *
	 * @var App\OptionGroup
	 */
	protected $boardOptionGroups;
	
	/**
	 * The board pertinent to the request.
	 *
	 * @var App\Board
	 */
	public $board;
	
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
	public function __construct(UserManager $manager)
	{
		$this->user = $manager->user;
	}
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
		if ($this->board)
		{
			foreach ($this->getBoardOptions() as $optionGroup)
			{
				foreach ($optionGroup->options as $option)
				{
					if (isset($input[$option->option_name]))
					{
						$input[$option->option_name] = $option->getSanitaryInput($input[$option->option_name]);
					}
				}
			}
		}
		
		return $input;
	}
	
	/**
	 * Supplies validation rules for this request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [
			'boardBasicTitle'    => "required|string|between:1,255",
			'boardBasicDesc'     => "string|between:1,255",
			'boardBasicIndexed'  => "boolean",
			'boardBasicWorksafe' => "boolean",
		];
		
		foreach ($this->getBoardOptions() as $optionGroup)
		{
			foreach ($optionGroup->options as $option)
			{
				$rules[$option->option_name] = $option->getValidation();
			}
		}
		
		return $rules;
	}
	
	/**
	 * Determines if the client has access to this form.
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		return $this->user->canEditConfig($this->board);
	}
	
	/**
	 *
	 *
	 * @return array
	 */
	public function getBoardOptions()
	{
		if (isset($this->board))
		{
			if (!isset($this->boardOptionGroups))
			{
				$this->boardOptionGroups = OptionGroup::getBoardConfig($this->board);
			}
			
			return $this->boardOptionGroups;
		}
		
		return [];
	}
	
	
	public function setBoard(Board $board)
	{
		return $this->board = $board;
	}
}