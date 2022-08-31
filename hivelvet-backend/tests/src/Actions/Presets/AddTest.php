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

namespace Actions\Presets;

use Models\Preset;
use Test\Scenario;

/**
 * @internal
 * @coversNothing
 */
final class AddTest extends Scenario
{
    final protected const ADD_PRESET_ROUTE = 'POST /add-presets';
    protected $group                       = 'Actions Preset Add';

    public function TestValidInput($f3)
    {
        $test   = $this->newTest();
        $preset = new Preset();
        $data   = ['user_id' => 1,
            'data'           => [
                'name' => 'test',
            ],
        ];
        $f3->mock(self::ADD_PRESET_ROUTE, null, null, $this->postJsonData($data));
        $test->expect($this->compareTemplateToResponse('preset/success.json'), 'Add preset successuly');

        $test->expect($preset->load(['name = ?', 'test']), 'preset Added to DB:' . $preset->name);

        return $test->results();
    }

    public function TestinValidInput($f3)
    {
        $test = $this->newTest();
        $data = [
            'data' => [
                'name' => 'test',
            ],
        ];
        $f3->mock(self::ADD_PRESET_ROUTE, null, null, $this->postJsonData($data));
        $test->expect($this->compareTemplateToResponse('preset/invalid_input.json'), 'preset could not be added');

        return $test->results();
    }

    public function testExistingName($f3)
    {
        $test    = $this->newTest();
        $preset1 = new Preset();
        $data1   = ['user_id' => 1,
            'data'            => [
                'name' => 'testtest',
            ],
        ];
        $f3->mock(self::ADD_PRESET_ROUTE, null, null, $this->postJsonData($data1));
        $test->expect($this->compareTemplateToResponse('preset/success.json'), 'Add preset successuly');
        $test->expect($preset1->load(['name = ?', 'testtest']), 'preset Added to DB:' . $preset1->name);

        $data2 = ['user_id' => 1,
            'data'          => [
                'name' => 'testtest',
            ],
        ];
        $f3->mock(self::ADD_PRESET_ROUTE, null, null, $this->postJsonData($data2));
        $test->expect($this->compareTemplateToResponse('preset/invalid_input.json'), 'preset could not be added: name already exist');

        return $test->results();
    }
}
