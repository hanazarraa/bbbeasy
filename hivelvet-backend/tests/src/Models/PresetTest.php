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

use Base;
use Fake\PresetFaker;
use Fake\UserFaker;
use Registry;
use Test\Scenario;

/**
 * class PresetTest.
 *
 * @internal
 * @coversNothing
 */
final class PresetTest extends Scenario
{
    protected $group = 'Preset Model';

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testPresetCreation($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::build();

        $preset->save();
        $test->expect(0 !== $preset->id, 'Preset mocked and saved to the database');

        return $test->results();
    }

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testfindByID($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::create();

        $test->expect($preset->findByID($preset->id)->id === $preset->id, 'findByID(' . $preset->id . ') found preset');
        $test->expect(!$preset->findByID(404)->id, 'findByID(404) did not find preset');

        return $test->results();
    }

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testgetPresetInfos($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::create();
        $data   = [
            'key'  => $preset->id,
            'name' => $preset->name,
        ];
        $test->expect($data === $preset->getPresetInfos(), 'getPresetInfos() returned preset');

        return $test->results();
    }

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testcollectAll($f3)
    {
        $test   = $this->newTest();
        $preset = new Preset(Registry::get('db'));
        $preset->erase(['']);
        $preset1 = PresetFaker::create();
        $preset2 = PresetFaker::create();

        $data = [
            [
                'id'       => $preset1->id,
                'name'     => $preset1->name,
                'settings' => $preset1->settings,
                'user_id'  => $preset1->user_id,
            ],
            [
                'id'       => $preset2->id,
                'name'     => $preset2->name,
                'settings' => $preset2->settings,
                'user_id'  => $preset2->user_id,
            ],
        ];

        $test->expect($data === $preset->collectAll(), 'collectAll() returned all presets');

        return $test->results();
    }

    /**
     * @param Base $f3
     *
     * @return array
     */
    public function testcollectAllByUserId($f3)
    {
        $test = $this->newTest();

        $preset = new Preset(Registry::get('db'));
        $preset->erase(['']);
        $user1   = UserFaker::create();
        $preset1 = PresetFaker::create(['user_id' => $user1->id]);
        PresetFaker::create();

        $data = [
            [
                'id'       => $preset1->id,
                'name'     => $preset1->name,
                'settings' => $preset1->settings,
                'user_id'  => $preset1->user_id,
            ],
        ];
        $test->expect($data === $preset->collectAllByUserId($user1->id), 'collectAllByUserId() returned all presets with user_id =' . $user1->id);

        return $test->results();
    }
}
