<?php

declare(strict_types=1);

/*
 * Hivelvet open source platform - https://riadvice.tn/
 *
 * Copyright (c) 2022 RIADVICE SUARL and by respective authors (see below).
 *
 * This program is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free Software
 * Foundation; either version 3.0 of the License, or (at your option) any later
 * version.
 *
 * Hivelvet is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Hivelvet; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Fake;

use Base;
use Enum\UserRole;
use Enum\UserStatus;
use Faker\Factory as Faker;
use models\User;
use ReflectionException;

class UserFaker
{
    private static array $storage = [];

    /**
     * @param null   $role
     * @param string $status
     * @param null   $storageName
     *
     * @throws ReflectionException
     *
     * @return User
     */
    public static function create($role = null, $status = UserStatus::ACTIVE, $storageName = null)
    {
        // To make testing easier, the user is password is the same as its role
        $faker          = Faker::create();
        $user           = new User();
        $user->email    = $faker->email;
        $user->username = $faker->userName;
        // pick a random role if not provided
        if (null === $role) {
            $role = array_rand(UserRole::values());
        }
        $user->role_id  = UserRole::LECTURER_ID;
        $user->password = $faker->password;
        if (UserRole::ADMINISTRATOR === $role) {
            $user->role_id  = UserRole::ADMINISTRATOR_ID;
            $user->password = $role . $role;
        }
        $user->status = $status;

        $user->save();
        if (null !== $storageName) {
            self::$storage[$storageName] = $user;
        }

        return $user;
    }

    /**
     * Creates a user and authenticates it.
     *
     * @param $role
     * @param string $status
     * @param null   $storageName
     *
     * @throws ReflectionException
     *
     * @return User
     */
    public static function createAndLogin($role, $status = UserStatus::ACTIVE, $storageName = null)
    {
        $user = self::create($role, $status, $storageName);

        self::loginUser($user);

        return $user;
    }

    /**
     * @param User $user
     */
    public static function loginUser($user): void
    {
        $password = $role = $user->role;
        if (UserRole::ADMINISTRATOR === $role) {
            $password = $role . $role;
        }
        Base::instance()->mock('POST /account/login', [
            'email'    => $user->email,
            'password' => $password,
        ]);
    }

    public static function logout(): void
    {
        Base::instance()->mock('GET /account/logout');
    }

    /**
     * @param $storageName
     *
     * @return User
     */
    public static function get($storageName)
    {
        return self::$storage[$storageName];
    }
}
