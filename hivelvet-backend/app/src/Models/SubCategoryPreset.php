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

use Models\Base as BaseModel;

/**
 * Class SubCategoryPreset.
 *
 * @property int      $id
 * @property int      $sub_category_id
 * @property int      $preset_id
 * @property json     $data
 * @property DateTime $created_on
 * @property DateTime $updated_on
 */
class SubCategoryPreset extends BaseModel
{
    protected $table = 'preset_subcategories';

    public function findByPresetAndSubCategory($preset_id, $subcategory_id)
    {
        $this->load(['sub_category_id = ? && preset_id = ?', $subcategory_id, $preset_id]);

        return $this;
    }

    public function presetAndsubCategoryExists($preset_id, $subcategory_id)
    {
        return $this->load(['sub_category_id = ? && preset_id = ?', $subcategory_id, $preset_id]);
    }

    public function findByID($id)
    {
        $this->load(['id = ? ', $id]);

        return $this;
    }
}
