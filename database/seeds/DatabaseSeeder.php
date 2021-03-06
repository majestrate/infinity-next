<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {
	
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$this->call('UserSeeder');
		$this->call('BoardSeeder');
		
		$this->call('PermissionSeeder');
		$this->call('PermissionGroupSeeder');
		
		$this->call('RoleSeeder');
		$this->call('UserRoleSeeder');
		$this->call('RolePermissionSeeder');
		
		$this->call('OptionSeeder');
		$this->call('OptionGroupSeeder');
	}
	
}


use App\User;

class UserSeeder extends Seeder {
	
	protected static $potential_pass_words = [
		"alarmed", "blue",   "cheap", "digital", // 4
		"eminent", "free",   "gross", "hairy",   // 8
		"illegal", "jolly",  "lazy",  "minor",   // 12
		"nimble",  "perky",  "quiet", "real",    // 16
		"stiff",   "tragic", "ugly",  "vital",   // 20
		"wet",     "yellow", "zesty", "key",     // 24
		"open",    "xeno", // 26
	];
	
	public function run()
	{
		$this->command->info("Seeding admin user.");
		
		if (User::count() === 0)
		{
			// Generate a password.
			$password = [];
			for ($i = 0; $i < 4; ++$i)
			{
				$password[] = static::$potential_pass_words[array_rand(static::$potential_pass_words)];
			}
			$password = implode($password, " ");
			
			// Create the user.
			$user = User::firstOrNew([
				'user_id'  => 1,
			]);
			
			$user->username = "Admin";
			$user->password = bcrypt($password);
			$user->save();
			
			$this->command->info("User \"Admin\" has been created with the following password:\n{$password}");
		}
		else
		{
			$this->command->info("Skipped. Users exist.");
		}
	}
}


use App\Board;

class BoardSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info("Seeding boards.");
		
		if (Board::count() < 1)
		{
			$board = Board::firstOrCreate([
				'board_uri'   => "test",
				'title'       => "Test",
				'description' => "Discover software features on your own",
				'created_by'  => 1,
				'operated_by' => 1,
			]);
			
			$this->command->info("Success. Board exists now. Accessible at /test/.");
		}
		else
		{
			$this->command->info("Skipped. Site has a board.");
		}
	}
}


use App\Permission;

class PermissionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info("Seeding permissions.");
		
		$permission_count = Permission::count();
		
		foreach ($this->slugs() as $slug)
		{
			Permission::updateOrCreate([
				'permission_id' => $slug['permission_id'],
			], $slug);
		}
		
		$permission_count = Permission::count() - $permission_count;
		
		$this->command->info("Done. Seeded {$permission_count} new permission(s).");
	}
	
	private function slugs()
	{
		return [
			['base_value' => 0, 'permission_id' => "board.config",],
			['base_value' => 1, 'permission_id' => "board.logs",],
			['base_value' => 0, 'permission_id' => "board.create",],
			['base_value' => 0, 'permission_id' => "board.delete",],
			['base_value' => 0, 'permission_id' => "board.reassign",],
			['base_value' => 0, 'permission_id' => "board.reports",],
			['base_value' => 1, 'permission_id' => "board.post.create",],
			['base_value' => 1, 'permission_id' => "board.post.delete.self",],
			['base_value' => 0, 'permission_id' => "board.post.delete.other",],
			['base_value' => 0, 'permission_id' => "board.post.edit.self",],
			['base_value' => 0, 'permission_id' => "board.post.edit.other",],
			['base_value' => 0, 'permission_id' => "board.post.nocaptcha",],
			['base_value' => 0, 'permission_id' => "board.post.sticky",],
			['base_value' => 0, 'permission_id' => "board.post.lock",],
			['base_value' => 0, 'permission_id' => "board.post.bumplock",],
			['base_value' => 0, 'permission_id' => "board.post.lock_bypass",],
			['base_value' => 1, 'permission_id' => "board.post.report",],
			['base_value' => 0, 'permission_id' => "board.user.role",],
			['base_value' => 0, 'permission_id' => "board.user.ban.reason",],
			['base_value' => 0, 'permission_id' => "board.user.ban.free",],
			['base_value' => 0, 'permission_id' => "board.user.unban",],
			['base_value' => 0, 'permission_id' => "board.image.ban",],
			['base_value' => 0, 'permission_id' => "board.image.upload",],
			['base_value' => 0, 'permission_id' => "board.image.delete.self",],
			['base_value' => 0, 'permission_id' => "board.image.delete.other",],
			['base_value' => 1, 'permission_id' => "board.image.spoiler.upload",],
			['base_value' => 0, 'permission_id' => "board.image.spoiler.other",],
			
			['base_value' => 0, 'permission_id' => "site.user.create",],
			['base_value' => 0, 'permission_id' => "site.user.merge",],
			['base_value' => 0, 'permission_id' => "site.user.raw_ip",],
			['base_value' => 0, 'permission_id' => "site.post.report",],
			['base_value' => 0, 'permission_id' => "site.pm",],
			['base_value' => 0, 'permission_id' => "site.reports",],
			
			['base_value' => 0, 'permission_id' => "sys.boards",],
			['base_value' => 0, 'permission_id' => "sys.cache",],
			['base_value' => 0, 'permission_id' => "sys.config",],
			['base_value' => 0, 'permission_id' => "sys.logs",],
			['base_value' => 0, 'permission_id' => "sys.roles",],
			['base_value' => 0, 'permission_id' => "sys.payments",],
			['base_value' => 0, 'permission_id' => "sys.permissions",],
			['base_value' => 0, 'permission_id' => "sys.tools",],
			['base_value' => 0, 'permission_id' => "sys.users",],
			
		];
	}
}


use App\PermissionGroup;
use App\PermissionGroupAssignment;

class PermissionGroupSeeder extends Seeder
{
	
	public function run()
	{
		$this->command->info('Seeding permission groups and relationships.');
		
		PermissionGroupAssignment::truncate();
		
		foreach ($this->slugs() as $slug)
		{
			$permissionGrouppermissions = $slug['permissions'];
			unset($slug['permissions']);
			
			$permissionGroup = PermissionGroup::firstOrNew([
				'group_name' => $slug['group_name'],
			]);
			
			$permissionGroup->group_name      = $slug['group_name'];
			$permissionGroup->display_order   = $slug['display_order'];
			$permissionGroup->is_system_only  = !!(isset($slug['is_system_only']) ? $slug['is_system_only'] : false);
			$permissionGroup->is_account_only = !!(isset($slug['is_account_only']) ? $slug['is_account_only'] : false);
			
			$permissionGroup->save();
			
			foreach ($permissionGrouppermissions as $permissionGroupIndex => $permissionGroupPermission)
			{
				$permissionGrouppermissionModel = PermissionGroupAssignment::firstOrNew([
					'permission_id'       => $permissionGroupPermission,
					'permission_group_id' => $permissionGroup->permission_group_id,
				]);
				
				if ($permissionGrouppermissionModel->exists)
				{
					PermissionGroupAssignment::where([
						'permission_id'       => $permissionGroupPermission,
						'permission_group_id' => $permissionGroup->permission_group_id,
					])->update([
						'display_order' => $permissionGroupIndex * 10,
					]);
				}
				else
				{
					$permissionGrouppermissionModel->display_order = $permissionGroupIndex * 10;
					$permissionGrouppermissionModel->save();
				}
				
				$permissionGrouppermissionModels[] = $permissionGrouppermissionModel;
			}
		}
	}
	
	private function slugs()
	{
		return [
			[
				'group_name'    => "board_controls",
				'display_order' => 100,
				
				'permissions'   => [
					"board.config",
					"board.create",
					"board.delete",
					"board.reassign",
					"board.logs",
				],
			],
			[
				'group_name'    => "board_images",
				'display_order' => 200,
				
				'permissions'   => [
					"board.image.ban",
					"board.image.delete.other",
					"board.image.delete.self",
					"board.image.spoiler.other",
					"board.image.spoiler.upload",
					"board.image.upload",
				],
			],
			[
				'group_name'    => "board_posts",
				'display_order' => 300,
				
				'permissions'   => [
					"board.post.create",
					"board.post.delete.self",
					"board.post.edit.other",
					"board.post.lock",
					"board.post.bumplock",
					"board.post.sticky",
					"board.post.lock_bypass",
					"board.post.report",
					"site.post.report",
				],
			],
			[
				'group_name'    => "board_moderation",
				'display_order' => 350,
				'permissions'   => [
					"board.post.delete.other",
					"board.post.edit.self",
					"board.post.nocaptcha",
					"board.reports",
					"site.reports",
				],
			],
			[
				'group_name'    => "board_users",
				'display_order' => 400,
				
				'permissions'   => [
					"board.user.ban.free",
					"board.user.ban.reason",
					"board.user.role",
					"board.user.unban",
				],
			],
			
			[
				'group_name'    => "site_tools",
				'is_system_only'=> true,
				'display_order' => 500,
				
				'permissions'   => [
					"site.pm",
					"site.user.create",
					"site.user.merge",
					"site.user.raw_ip",
				],
			],
			
			[
				'group_name'    => "system_tools",
				'is_system_only'=> true,
				'display_order' => 600,
				
				'permissions'   => [
					"sys.boards",
					"sys.cache",
					"sys.config",
					"sys.logs",
					"sys.payments",
					"sys.permissions",
					"sys.roles",
					"sys.tools",
					"sys.users",
				],
			],
		];
	}
}


use App\Role;

class RoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding roles.');
		
		foreach ($this->slugs() as $slug)
		{
			Role::updateOrCreate([
				'role_id'   => $slug['role_id'],
				'role'      => $slug['role'],
				'board_uri' => $slug['board_uri'],
				'caste'     => $slug['caste'],
			], $slug);
		}
		
		foreach (Board::get() as $board)
		{
			$boardRole = [
				'role'       => "owner",
				'board_uri'  => $board->board_uri,
				'caste'      => NULL,
				'inherit_id' => Role::$ROLE_OWNER,
				'name'       => "Board Owner",
				'capcode'    => "Board Owner",
				'system'     => false,
			];
			
			Role::updateOrCreate([
				'role'      => $boardRole['role'],
				'board_uri' => $boardRole['board_uri'],
				'caste'     => $boardRole['caste'],
			], $boardRole);
		}
		
	}
	
	private function slugs()
	{
		return [
			[
				'role_id'    => Role::$ROLE_ANONYMOUS,
				'role'       => "anonymous",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Anonymous",
				'capcode'    => NULL,
				'system'     => true,
			],
			[
				'role_id'    => Role::$ROLE_ADMIN,
				'role'       => "admin",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Administrator",
				'capcode'    => "Administrator",
				'system'     => true,
			],
			[
				'role_id'    => Role::$ROLE_MODERATOR,
				'role'       => "moderator",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Global Volunteer",
				'capcode'    => "Global Volunteer",
				'system'     => true,
			],
			[
				'role_id'    => Role::$ROLE_OWNER,
				'role'       => "owner",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Board Owner",
				'capcode'    => "Board Owner",
				'system'     => true,
			],
			[
				'role_id'    => Role::$ROLE_VOLUTNEER,
				'role'       => "volunteer",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Board Volunteer",
				'capcode'    => "Board Volunteer",
				'system'     => true,
			],
			[
				'role_id'    => Role::$ROLE_UNACCOUNTABLE,
				'role'       => "unaccountable",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => 1,
				'name'       => "Proxy User",
				'capcode'    => NULL,
				'system'     => true,
			],
		];
	}
}


use App\UserRole;

class UserRoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding role to user associations.');
		
		$userRoles = [
			[
				'user_id'  => 1,
				'role_id'  => Role::$ROLE_ADMIN,
			]
		];
		foreach (Board::get() as $board)
		{
			$userRoles[] = [
				'user_id' => $board->operated_by,
				'role_id' => $board->getOwnerRole()->role_id,
			];
		}
		
		foreach ($userRoles as $userRole)
		{
			UserRole::firstOrCreate($userRole);
		}
	}
}


use App\RolePermission;

class RolePermissionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding permission to role associations.');
		
		// Insert default permissions.
		foreach ($this->slugs() as $slug)
		{
			RolePermission::firstOrCreate([
				'role_id'       => $slug['role_id'],
				'permission_id' => $slug['permission_id'],
				'value'         => $slug['value'],
			]);
		}
		
		
		// Give admin permissions.
		$permissions = Permission::get();
		
		if (count($permissions))
		{
			foreach ($permissions as $permission)
			{
				$permission = RolePermission::firstOrNew([
					'role_id'       => Role::$ROLE_ADMIN,
					'permission_id' => $permission->permission_id,
				]);
				
				$permission->value = 1;
				$permission->save();
			}
		}
	}
	
	private function slugs()
	{
		return [
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "board.create"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "board.image.delete.self"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "board.image.spoiler.upload"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "board.post.delete.self"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "board.post.report"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "site.post.report"],
			['role_id' => Role::$ROLE_ANONYMOUS, 'value' => 1, 'permission_id' => "site.user.create"],
			
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.create",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.delete.other",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.delete.self",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.edit.other",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.edit.self",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.lock",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.bumplock",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.lock_bypass",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.post.sticky",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.image.ban",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.image.delete.other",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.image.delete.self",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.image.spoiler.other",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.image.spoiler.upload",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.user.ban.reason",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.user.ban.free",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.user.unban",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.user.role",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "board.reports",],
			['role_id' => Role::$ROLE_MODERATOR, 'value' => 1, 'permission_id' => "site.reports",],
			
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.config",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.delete.other",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.delete.self",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.edit.other",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.edit.self",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.lock",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.bumplock",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.lock_bypass",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.post.sticky",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.image.ban",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.image.delete.other",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.image.delete.self",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.image.spoiler.other",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.image.spoiler.upload",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.user.ban.reason",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.user.ban.free",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.user.unban",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "board.reports",],
			['role_id' => Role::$ROLE_OWNER,     'value' => 1, 'permission_id' => "site.user.create"],
			
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.delete.other",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.delete.self",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.edit.other",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.edit.self",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.lock",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.bumplock",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.lock_bypass",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.post.sticky",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.image.ban",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.image.delete.other",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.image.delete.self",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.image.spoiler.other",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.image.spoiler.upload",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.user.ban.reason",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.user.ban.free",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.user.unban",],
			['role_id' => Role::$ROLE_VOLUTNEER, 'value' => 1, 'permission_id' => "board.reports",],
		];
	}
}


use App\Option;

class OptionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding system options.');
		
		$option_count = Option::count();
		
		foreach ($this->slugs() as $slugType => $slugs)
		{
			foreach ($slugs as $slug)
			{
				$slug['option_type'] = $slugType;
				$option = Option::updateOrCreate([
					'option_name' => $slug['option_name'],
				], $slug);
			}
		}
		
		$option_count = Option::count() - $option_count;
		
		$this->command->info("Done. Seeded {$option_count} new permission(s).");
	}
	
	private function slugs()
	{
		return [
			'site' => [
				[
					'option_name'           => "adventureEnabled",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				[
					'option_name'           => "adventureIcons",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				
				[
					'option_name'           => "attachmentFilesize",
					'default_value'         => "1024",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "attachmentThumbnailSize",
					'default_value'         => "250",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 50 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				
				[
					'option_name'           => "banMaxLength",
					'default_value'         => "30",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => -1 ] ),
					'data_type'             => "integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "banSubnets",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				
				[
					'option_name'           => "boardCreateMax",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "boardCreateTimer",
					'default_value'         => 15,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "boardListShow",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => "boolean",
				],
				[
					'option_name'           => "boardUriBanned",
					'default_value'         => "",
					'format'                => "textbox",
					'data_type'             => "string",
				],
				[
					'option_name'           => "postFloodTime",
					'default_value'         => 30,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "globalReportText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
			],
			
			'board' => [
				[
					'option_name'           => "boardCustomCSS",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "boardReportText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "boardSidebarText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "postAttachmentsMax",
					'default_value'         => "5",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 10 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min|max:$max'
				],
				[
					'option_name'           => "postMaxLength",
					'default_value'         => null,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'integer|min:$min|max:$max|greater_than:postMinLength',
				],
				[
					'option_name'           => "postMinLength",
					'default_value'         => null,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'integer|min:$min|max:$max',
				],
				[
					'option_name'           => "postsPerPage",
					'default_value'         => "10",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 5, 'max' => 20 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "postsThreadId",
					'default_value'         => false,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => "boolean",
				],
				
				
				[
					'option_name'           => "epheSageThreadReply",
					'default_value'         => 350,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheSageThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheSageThreadPage",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadReply",
					'default_value'         => 700,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadPage",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadReply",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadPage",
					'default_value'         => 16,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
			],
		];
	}
}


use App\OptionGroup;
use App\OptionGroupAssignment;

class OptionGroupSeeder extends Seeder
{
	
	public function run()
	{
		$this->command->info('Seeding option groups and relationships.');
		
		OptionGroupAssignment::truncate();
		
		foreach ($this->slugs() as $slug)
		{
			$optionGroupOptions = $slug['options'];
			unset($slug['options']);
			
			$optionGroup = OptionGroup::firstOrNew([
				'group_name' => $slug['group_name'],
			]);
			
			$optionGroup->debug_only = $slug['debug_only'];
			$optionGroup->display_order = $slug['display_order'];
			
			$optionGroup->save();
			
			foreach ($optionGroupOptions as $optionGroupIndex => $optionGroupOption)
			{
				$optionGroupOptionModel = OptionGroupAssignment::firstOrNew([
					'option_name'     => $optionGroupOption,
					'option_group_id' => $optionGroup->option_group_id,
				]);
				
				if ($optionGroupOptionModel->exists)
				{
					OptionGroupAssignment::where([
						'option_name'     => $optionGroupOption,
						'option_group_id' => $optionGroup->option_group_id,
					])->update([
						'display_order' => $optionGroupIndex * 10,
					]);
				}
				else
				{
					$optionGroupOptionModel->display_order = $optionGroupIndex * 10;
					$optionGroupOptionModel->save();
				}
				
				$optionGroupOptionModels[] = $optionGroupOptionModel;
			}
		}
	}
	
	private function slugs()
	{
		return [
			[
				'group_name'    => "attachments",
				'debug_only'    => false,
				'display_order' => 100,
				
				'options'       => [
					"attachmentFilesize",
					"attachmentThumbnailSize",
				],
			],
			[
				'group_name'    => "bans",
				'debug_only'    => false,
				'display_order' => 200,
				
				'options'       => [
					"banMaxLength",
					"banSubnets",
				],
			],
			[
				'group_name'    => "boards",
				'debug_only'    => false,
				'display_order' => 300,
				
				'options'       => [
					"boardCreateMax",
					"boardCreateTimer",
					"boardUriBanned",
				],
			],
			[
				'group_name'    => "adventures",
				'debug_only'    => false,
				'display_order' => 350,
				
				'options'       => [
					"adventureEnabled",
					"adventureIcons",
				],
			],
			[
				'group_name'    => "board_ephemerality",
				'debug_only'    => false,
				'display_order' => 300,
				
				'options'       => [
					"epheSageThreadReply",
					"epheSageThreadDays",
					"epheSageThreadPage",
					"epheLockThreadReply",
					"epheLockThreadDays",
					"epheLockThreadPage",
					"epheDeleteThreadReply",
					"epheDeleteThreadDays",
					"epheDeleteThreadPage",
				],
			],
			[
				'group_name'    => "board_posts",
				'debug_only'    => false,
				'display_order' => 400,
				
				'options'       => [
					"postAttachmentsMax",
					"postMaxLength",
					"postMinLength",
					'postFloodTime',
				],
			],
			[
				'group_name'    => "board_threads",
				'debug_only'    => false,
				'display_order' => 500,
				
				'options'       => [
					"postsPerPage",
					"postsThreadId",
				],
			],
			[
				'group_name'    => "navigation",
				'debug_only'    => false,
				'display_order' => 600,
				
				'options'       => [
					"boardListShow",
				],
			],
			[
				'group_name'    => "style",
				'debug_only'    => false,
				'display_order' => 1000,
				
				'options'       => [
					"boardCustomCSS",
				],
			],
			[
				'group_name'    => "sidebar",
				'debug_only'    => false,
				'display_order' => 1100,
				
				'options'       => [
					"boardSidebarText",
				],
			],
			[
				'group_name'    => "reports",
				'debug_only'    => false,
				'display_order' => 1200,
				
				'options'       => [
					"boardReportText",
					"globalReportText",
				]
			],
		];
	}
}
