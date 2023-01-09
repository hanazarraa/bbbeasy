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
use BigBlueButton\Enum\Role;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Responses\CreateMeetingResponse;
use BigBlueButton\Responses\GetMeetingInfoResponse;
use BigBlueButton\Util\UrlBuilder;
use Enum\ResponseCode;
use Models\Preset;
use Models\Room;
use Utils\BigBlueButtonRequester;

/**
 * Class Start.
 */
class Start extends BaseAction
{
    use RequirePrivilegeTrait;

    /**
     * @param \Base $f3
     * @param array $params
     *
     * @throws \Exception
     */
    public function execute($f3, $params): void
    {
        $id           = $params['id'];
        $errorMessage = 'Room Meeting could not be started';

        $room = new Room();
        $room = $room->getById($id);
        if ($room->valid()) {
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

            $moderatorPw = '';
            $preset      = new Preset();
            $p           = $preset->findById($room->getPresetID($room->id)['preset_id']);

            $preset = $preset->getMyPresetInfos($preset);

            $anyonestart       = false;
            $join_as_moderator = false;

            foreach ($preset['categories'] as $category) {
                if ('General' === $category['name'] && $category['enabled']) {
                    foreach ($category['subcategories'] as $subcategory) {
                        if ('anyone_can_start' === $subcategory['name'] && $subcategory['value']) {
                            $anyonestart = true;
                        }
                        if ('all_join_as_moderator' === $subcategory['name'] && $subcategory['value']) {
                            $join_as_moderator = true;
                        }
                    }
                }
            }

            if ($response->success()) {
                $moderatorPw = $response->getMeeting()->getModeratorPassword();
                $attendeePW  = $response->getMeeting()->getAttendeePassword();
            } else {
                // meeting not found
                if ('notFound' === $response->getMessageKey()) {
                    //   echo "not found";
                    // create new meeting with the same meetingId
                    // test if the current user is the room owner

                    if ($room->getRoomInfos($room->id)['user_id'] === $this->session->get('user.id') || $anyonestart) {
                        $createParams = new CreateMeetingParameters($meetingId, 'meeting-' . $meetingId);

                        $createParams->setModeratorPassword(bin2hex(openssl_random_pseudo_bytes(8)));
                        $createParams->setAttendeePassword(bin2hex(openssl_random_pseudo_bytes(8)));
                        $createParams->setRecord('true');

                        $createParams->setModeratorOnlyMessage('to invite someone you can use this link http://hivelvet.test:3300/hv/' . $room->short_link);
                        $createQuery      = $createParams->getHTTPQuery();
                        $createBuildQuery = $urlBuilder->buildQs(ApiMethod::CREATE, $createQuery);

                        $this->logger->info('Received request to create a new meeting.', ['meetingID' => $meetingId]);
                        $result = $bbbRequester->proxyApiRequest(ApiMethod::CREATE, $createBuildQuery, 'POST');
                        if (!$bbbRequester->isValidResponse($result)) {
                            $this->logger->error('Could not create a meeting due to an error.');
                            $this->renderXmlString($result);

                            return;
                        }
                        $response = new CreateMeetingResponse(new \SimpleXMLElement($result['body']));
                        if ($response->failed()) {
                            $this->logger->warning('Meeting could be created.');
                            $this->renderXmlString($result['body']);

                            return;
                        }
                        $this->logger->info('Meeting successfully created.', ['meetingID' => $meetingId, 'internal_meeting_id' => $response->getInternalMeetingId()]);

                        $moderatorPw = $createParams->getModeratorPassword();
                        $attendeePW  = $createParams->getAttendeePassword();
                    } else {
                        $this->renderJson(['errors' => ['meeting' => 'The Meeting has not started yet']], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

                        return;
                    }
                }
            }

            if ($room->getRoomInfos($room->id)['user_id'] === $this->session->get('user.id') || $join_as_moderator) {
                $joinParams = new JoinMeetingParameters($meetingId, $this->session->get('user.username'), Role::MODERATOR);
            } else {
                $joinParams = new JoinMeetingParameters($meetingId, $this->session->get('user.username'), Role::VIEWER);
            }
            $joinParams->setRedirect('true');
            $joinParams->setCustomParameter('AllJoinAsParam', true);
            $joinQuery = $joinParams->getHTTPQuery();

            $joinBuildQuery = $urlBuilder->buildQs(ApiMethod::JOIN, $joinQuery);

            $this->logger->info('Meeting join request is going to redirect to the web client.', ['meetingID' => $meetingId]);
            $result = $bbbRequester->proxyApiRequest(ApiMethod::JOIN, $joinBuildQuery, 'GET', true);

            $this->renderJson($result);
        } else {
            $this->logger->error($errorMessage);
            $this->renderJson([], ResponseCode::HTTP_NOT_FOUND);
        }
    }
}
