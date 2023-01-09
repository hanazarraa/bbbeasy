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

namespace Actions\Rooms;

use Actions\Base as BaseAction;
use Actions\RequirePrivilegeTrait;
use BigBlueButton\Core\ApiMethod;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Responses\GetMeetingInfoResponse;
use BigBlueButton\Util\UrlBuilder;
use Models\Preset;
use Models\Room;
use Utils\BigBlueButtonRequester;

class GetMeetingInfo extends BaseAction
{
    use RequirePrivilegeTrait;

    /**
     * @param \Base $f3
     * @param array $params
     *
     * @throws \Exception
     */
    public function show($f3, $params): void
    {
        $id = $params['id'];

        $room = new Room();
        $room = $room->getById($id);
        if ($room->valid()) {
            $canstart     = false;
            $serverSecret = $this->f3->get('bbb.shared_secret');
            $serverUrl    = $this->f3->get('bbb.server');
            $urlBuilder   = new UrlBuilder($serverSecret, $serverUrl);
            $bbbRequester = new BigBlueButtonRequester();

            // get room meeting id
            $meetingId = $room->meeting_id;

            // call meeting info to check if meeting is running
            $getInfosParams     = new GetMeetingInfoParameters($meetingId);
            $getInfosQuery      = $getInfosParams->getHTTPQuery();
            $getInfosBuildQuery = $urlBuilder->buildQs(ApiMethod::GET_MEETING_INFO, $getInfosQuery);
            $this->logger->info('Received request to fetch meeting info.', ['meetingID' => $meetingId]);
            $result = $bbbRequester->proxyApiRequest(ApiMethod::GET_MEETING_INFO, $getInfosBuildQuery, 'GET');

            if (!$bbbRequester->isValidResponse($result)) {
                $this->logger->error('Could not fetch a meeting due to an error.');
                $this->renderXmlString($result);

                return;
            }

            $this->logger->info('Meeting info successfully fetched from server.', ['meetingID' => $meetingId]);
            $response = new GetMeetingInfoResponse(new \SimpleXMLElement($result['body']));
            if ($response->success()) {
                $moderatorPw = $response->getMeeting()->getModeratorPassword();
                $attendeePW  = $response->getMeeting()->getAttendeePassword();
            } else {
                if ('notFound' === $response->getMessageKey()) {
                    $anyonestart = false;
                    $preset      = new Preset();
                    $p           = $preset->findById($room->getPresetID($room->id)['preset_id']);

                    $preset = $preset->getMyPresetInfos($preset);

                    foreach ($preset['categories'] as $category) {
                        if ('General' === $category['name'] && $category['enabled']) {
                            foreach ($category['subcategories'] as $subcategory) {
                                if ('anyone_can_start' === $subcategory['name'] && $subcategory['value']) {
                                    $anyonestart = true;
                                }
                            }
                        }
                    }

                    if ($room->getRoomInfos($room->id)['user_id'] === $this->session->get('user.id') || $anyonestart) {
                        $canstart = true;
                    }
                }
            }
            $meeting = (array) $response->getRawXml();

            $meeting['canstart'] = $canstart;

            $this->renderJson(['meeting' => $meeting]);
        }
    }
}
