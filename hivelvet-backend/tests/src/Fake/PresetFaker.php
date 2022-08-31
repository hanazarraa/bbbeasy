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

use Faker\Factory as Faker;
use Models\Preset;

class PresetFaker
{
    private static array $storage = [];

    public static function create($fields = [], $storageName = null)
    {
        $preset = self::build($fields);

        $preset->save();

        if (null !== $storageName) {
            self::$storage[$storageName] = $preset;
        }

        return $preset;
    }

    public static function build($fields = [])
    {
        $faker            = Faker::create();
        $preset           = new Preset();
        $preset->name     = $fields['name'] ?: $faker->unique()->name;
        $preset->settings = $fields['settings'] ?: json_encode([]);
        $preset->user_id  = $fields['user_id'] ?: UserFaker::create()->id;

        return $preset;
    }

    /**
     * @param $storageName
     *
     * @return Preset
     */
    public static function get($storageName)
    {
        return self::$storage[$storageName];
    }
}
