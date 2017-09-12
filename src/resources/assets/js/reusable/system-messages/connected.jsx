import ImmutablePropTypes from 'react-immutable-proptypes'
import PropTypes from 'prop-types'
import React from 'react'

import { connectRedux, delayDispatch, rebind } from '../dispatch'
import { loadStateShape } from '../shapes'
import { systemMessagesData } from '../../lookups/lookups-system-messages'
import { SystemMessages } from './index'


@connectRedux()
export class RegionSystemMessages extends React.Component {
    static mapStateToProps(state, ownProps) {
        return {
            messages: systemMessagesData.getMessagesOnly(state, ownProps.section),
            loadState: state.lookups.loadState
        }
    }

    static propTypes = {
        region: PropTypes.string.isRequired,
        section: PropTypes.string.isRequired,
        loadState: loadStateShape,
        messages: ImmutablePropTypes.list
    }

    constructor(props) {
        super(props)
        rebind(this, 'onMessageDismiss')
    }

    checkLoading() {
        if (this.props.messages) {
            return true
        }
        const { region, section, loadState } = this.props
        if (loadState.available) {
            delayDispatch(this, systemMessagesData.loadForRegion(region, section))
        }
    }

    render() {
        if (!this.checkLoading()) {
            return <div className="h"></div>
        }

        return <SystemMessages messages={this.props.messages} onDismiss={this.onMessageDismiss} />
    }

    onMessageDismiss(messageId) {
        this.props.dispatch(systemMessagesData.dismiss(this.props.section, messageId))
    }
}
