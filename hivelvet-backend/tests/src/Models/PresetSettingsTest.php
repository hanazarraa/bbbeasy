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

namespace Models;

use Fake\PresetSettingsFaker;
use Faker\Factory as Faker;
use Registry;
use Test\Scenario;

/**
 * @internal
 * @coversNothing
 */
final class PresetSettingsTest extends Scenario
{
    protected $group = 'PresetSetting Model';

    public function testPresetSettingCreation($f3)
    {
        $test                   = $this->newTest();
        $faker                  = Faker::create();
        $presetSetting          = new PresetSetting(Registry::get('db'));
        $presetSetting->name    = $faker->name;
        $presetSetting->enabled = $faker->boolean();
        $presetSetting->group   = $faker->sentence();
        $presetSetting->save();

        $test->expect(0 !== $presetSetting->id, '$presetSetting mocked and saved to the database');

        return $test->results();
    }

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testGetByName($f3)
    {
        $test          = $this->newTest();
        $presetsetting = PresetSettingsFaker::create();

        $test->expect($presetsetting->getByName($presetsetting->name)->id === $presetsetting->id, 'getByName(' . $presetsetting->group . ') found presetSetting');
        $test->expect(!$presetsetting->getByName('404')->id, 'getByName(404) did not find presetSetting');

        return $test->results();
    }

    public function testGetByGroup($f3)
    {
        $test          = $this->newTest();
        $presetsetting = PresetSettingsFaker::create();

        $test->expect($presetsetting->getByGroup($presetsetting->group)->id === $presetsetting->id, 'getByGroup(' . $presetsetting->group . ') found presetSetting');
        $test->expect(!$presetsetting->getByGroup('404')->id, 'getByGroup(404) did not find presetSetting');

        return $test->results();
    }

    /*public function testGetAllPresets($f3)
    {
        $test          = $this->newTest();
        $presetsetting = new PresetSetting(Registry::get('db'));
        $presetsetting->erase(['']);
        $presetsetting1 = PresetSettingsFaker::create();
        $presetsetting2 = PresetSettingsFaker::create();

        $data = [
            [
                'id'      => $presetsetting1->id,
                'group'   => $presetsetting1->group,
                'name'    => $presetsetting1->name,
                'enabled' => $presetsetting1->enabled,
            ],
            [
                'id'      => $presetsetting2->id,
                'group'   => $presetsetting2->group,
                'name'    => $presetsetting2->name,
                'enabled' => $presetsetting2->enabled,
            ],
        ];

        $test->expect($data === $presetsetting->getAllPresets(), 'getAllpresets() returned all presetsetting');

        return $test->results();
    }*/
}
