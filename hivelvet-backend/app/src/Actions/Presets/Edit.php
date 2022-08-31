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

use Actions\Base as BaseAction;
use Actions\RequirePrivilegeTrait;
use Enum\ResponseCode;
use Models\Preset;

class Edit extends BaseAction
{
    use RequirePrivilegeTrait;

    /**
     * @param Base  $f3
     * @param array $params
     */
    public function save($f3, $params): void
    {
        $body = $this->getDecodedBody();
        $form = $body['data'];
        // var_dump($body);
        $id = $params['id'];
        // var_dump($id);
        $preset  = new Preset();
        $presets = $preset->findByID($id);
        //  var_dump($form["name"]);
        // echo "name preset".$preset->name;
        if (!$presets->dry()) {
            // var_dump(json_decode($presets["settings"]) );
            $categories = json_decode($presets['settings']);
            $title      = $body['title'];
            var_dump($categories->{$title});
            $name = $form['name'];
            // var_dump($name);
            // echo ($body["title"]);
            if (isset($categories->{$title})) {
                echo $body['title'] . ' exists';
                // json_decode($categories->$title)->$name=$form["value"];
                $attr                 = json_decode($categories->{$title});
                $attr->{$name}        = $form['value'];
                $categories->{$title} = json_encode($attr);
                $presets['settings']  = json_encode($categories);
                var_dump($presets['settings']);
            // var_dump($presets["settings"]);
            } else {
                echo $body['title'] . 'not exists';
            }
            //  $data=$presetsubcategory->data;
            //   $data=json_decode($data,true);
            // $data["value"]=$form["value"];
            //   $presetsubcategory->data=json_encode($data);
            //   try {
            //    $presetsubcategory->save();
            //   } catch (\Exception $e) {
            // $this->logger->error('subcategory could not be updated', ['error' => $e->getMessage()]);
            // $this->renderJson(['errors' => $e->getMessage()], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

            //  return;
            // }

            //  $this->logger->info('preset successfully updated', ['preset' => $presetsubcategory->toArray()]);
            var_dump($presets['settings']);
            $presets->save();
            $this->renderJson(['result' => 'success', 'preset' => $presets['settings']]);
        }
    }

    /**
     * @param Base  $f3
     * @param array $params
     */
    public function rename($f3, $params): void
    {
        $body = $this->getDecodedBody();
        $form = $body['data'];

        $id     = $params['id'];
        $preset = new Preset();
        $preset = $preset->findByID($id);
        if (!$preset->dry()) {
            $preset->name = $form['name'];

            try {
                $preset->save();
            } catch (\Exception $e) {
                $this->logger->error('preset could not be updated', ['error' => $e->getMessage()]);
                $this->renderJson(['errors' => $e->getMessage()], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

                return;
            }

            $this->logger->info('preset successfully updated', ['preset' => $preset->toArray()]);
            $this->renderJson(['result' => 'success', 'preset' => $preset->toArray()]);
        } else {
            $this->renderJson([], ResponseCode::HTTP_NOT_FOUND);
        }
    }
}
