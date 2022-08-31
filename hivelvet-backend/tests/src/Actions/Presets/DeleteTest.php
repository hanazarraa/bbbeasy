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
final class DeleteTest extends Scenario
{
    final protected const DELETE_PRESET_ROUTE = 'DELETE /presets/';
    protected $group                          = 'Actions Preset Delete';

    /**
     * @param $f3
     *
     * @throws ReflectionException
     *
     * @return array
     */
    public function testValidpreset($f3)
    {
        $test   = $this->newTest();
        $preset = PresetFaker::create();
        $f3->mock(self::DELETE_PRESET_ROUTE . $preset->id);
        $test->expect($this->compareArrayToResponse(['result' => 'preset successfully deleted']), 'Delete existing preset with id "' . $preset->id . '" successfully');

        $test->expect(!($preset->load(['id = ?', $preset->id])), 'preset with id "' . $preset->id . '"  deleted from DB');

        return $test->results();
    }

    /**
     * @param $f3
     *
     * @throws ReflectionException
     *
     * @return array
     */
    public function testNonExistingPreset($f3)
    {
        $test  = $this->newTest();
        $faker = Faker::create();

        $f3->mock(self::DELETE_PRESET_ROUTE . $nonExistingId = $faker->numberBetween(1000));
        $test->expect($this->compareTemplateToResponse('not_found_error.json'), 'Delete non existing preset with id "' . $nonExistingId . '" show an error');

        return $test->results();
    }
}
