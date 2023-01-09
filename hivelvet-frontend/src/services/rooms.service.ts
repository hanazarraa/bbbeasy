/**
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

import { axiosInstance } from '../lib/AxiosInstance';
import { apiRoutes } from '../routing/backend-config';

class RoomsService {
    list_rooms(user_id) {
        return axiosInstance.get(apiRoutes.LIST_ROOMS_URL + user_id);
    }

    add_room(data: object, user_id: number) {
        return axiosInstance.post(apiRoutes.ADD_ROOM_URL, {
            data,
            user_id,
        });
    }

    edit_room(data: object, id: number) {
        return axiosInstance.put(apiRoutes.EDIT_ROOM_URL + id, {
            data,
        });
    }

    delete_room(id: number) {
        return axiosInstance.delete(apiRoutes.DELETE_ROOM_URL + id);
    }
    getRoomByLink(link: string) {
        return axiosInstance.get(apiRoutes.GET_ROOM_BY_LINK + link);
    }
    start_room(id: number) {
        return axiosInstance.get(apiRoutes.START_ROOM_URL + id);
        // return window.location.replace("https://lb-demo.riadvice.tn/bigbluebutton/api/join?fullName=User+931289&meetingID=random-8875110&password=mp&redirect=true&checksum=6334420dbcf56054f22c83323b0e025ee752fe0b")
        //  return axiosInstance.get("https://lb-demo.riadvice.tn/bigbluebutton/api/join?fullName=User+931289&meetingID=random-1661405&password=mp&redirect=true&checksum=6334420dbcf56054f22c83323b0e025ee752fe0b");
    }
    join_room(id: number) {
        return axiosInstance.get(apiRoutes.JOIN_ROOM_URL + id);
    }
    get_meeting_infos(id: number) {
        return axiosInstance.get(apiRoutes.GET_MEETING_INFO_URL + id);
    }
}

export default new RoomsService();
