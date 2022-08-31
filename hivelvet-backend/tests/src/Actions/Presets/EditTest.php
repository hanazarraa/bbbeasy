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

use Fake\PresetFaker;
use Faker\Factory as Faker;
use ReflectionException;
use Test\Scenario;

/**
 * @internal
 * @coversNothing
 */
final class EditTest extends Scenario
{
    final protected const EDIT_PRESET_ROUTE = 'PUT /presets/';
    protected $group                        = 'Actions Preset Edit';

    /**
     * @param $f3
     *
     * @throws ReflectionException
     *
     * @return array
     */

    /**
     * @param $f3
     *
     * @throws ReflectionException
     *
     * @return array
     */
    public function testExistingname($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::create();
        $data   = [
            'data' => [
                'name' => $preset->name,
            ],
        ];
        $f3->mock(self::EDIT_PRESET_ROUTE . $preset->id, null, null, $this->postJsonData($data));
        $test->expect($this->compareTemplateToResponse('Preset/exist_name_error.json'), 'edit preset with an existing name"' . $preset->name . '" show an error');

        return $test->results();
    }

    public function testValidPreset($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::create();
        $data   = [
            'data' => [
                'name' => 'test1',
            ],
        ];
        $f3->mock(self::EDIT_PRESET_ROUTE . $preset->id, null, null, $this->postJsonData($data));
        $test->expect($this->compareTemplateToResponse('Preset/edit_success.json'), 'Update existing preset with id "' . $preset->id . '" using new name "' . $preset->name . '" successfully');

        // Assering that the changes took place at the model layer
        $preset->load(['id = ?', $preset->id]);
        $test->expect('test1' === $preset->name, 'preset with id "' . $preset->id . '" "name" updated in the DB.');

        return $test->results();
    }

    public function testNonExistingPreset($f3)
    {
        $test  = $this->newTest();
        $faker = Faker::create();
        $data  = [
            'data' => [
                'name' => 'test2',
            ],
        ];
        $nonExistingId = $faker->numberBetween(1000);
        $f3->mock(self::EDIT_PRESET_ROUTE . $nonExistingId, null, null, $this->postJsonData($data));
        $test->expect($this->compareTemplateToResponse('not_found_error.json'), 'Delete non existing preset with id "' . $nonExistingId . '" show an error');

        return $test->results();
    }
}
