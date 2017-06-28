// Taken from https://github.com/podefr/react-debounce-render
//
// This code licensed under original MIT license:
// https://github.com/podefr/react-debounce-render/blob/master/LICENSE.md

import React, { Component } from 'react'
import _ from 'lodash'

export default function debounceRender(ComponentToDebounce, ...debounceArgs){
    return class DebouncedContainer extends Component {
        constructor(props) {
            super(props);

            this.state = props;
            this.shouldRender = false;
        }

        componentWillReceiveProps(props) {
            this.shouldRender = false;
            this.updateState(props);
        }

        updateState = _.debounce(props => {
            this.shouldRender = true;
            this.setState(props);
        }, ...debounceArgs);

        shouldComponentUpdate() {
            return this.shouldRender;
        }

        render() {
            return <ComponentToDebounce { ...this.state } />;
        }
    }
}
