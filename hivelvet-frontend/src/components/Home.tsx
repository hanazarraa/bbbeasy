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

import React, { Component } from 'react';
import AuthService from '../services/auth.service';

import { Row } from 'antd';
import { Navigate } from 'react-router-dom';
import { userInfo } from 'os';

type Props = {
    user: any;
    isLogged: boolean;
};

class Home extends Component<Props> {
    username = '';
    constructor(props: Props) {
        super(props);
    }

    render() {
        this.username = this.props.user != null ? this.props.user.username : '';

        return (
            <Row justify="center">
                <h1>logged as {this.username} </h1>
            </Row>
        );
    }
}

export default Home;
