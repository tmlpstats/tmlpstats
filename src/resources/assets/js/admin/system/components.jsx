import React from 'react'
import { Link, withRouter } from 'react-router'
import PropTypes from 'prop-types'
import Immutable from 'immutable'

import Api from '../../api'
import {
    Form, formActions, SimpleField, SimpleFormGroup,
    SimpleSelect, NullableTextControl, NullableTextAreaControl, BooleanSelect } from '../../reusable/form_utils'
import { ALERT_TYPES } from '../../reusable/ui_basic'
import { objectAssign } from '../../reusable/ponyfill'
import { buildTable } from '../../reusable/tabular'
import debounceRender from '../../reusable/debounce'
import { connectRedux, rebind, delayDispatch } from '../../reusable/dispatch'
import { loadStateShape } from '../../reusable/shapes'
import { SystemMessages } from '../../reusable/system-messages'
import { systemMessagesData } from '../../lookups/lookups-system-messages'
import gql from 'graphql-tag'
import { graphql } from 'react-apollo'

const MODEL = 'admin.system.currentMessage'
const SYSTEM_MESSAGES_BASE = '/admin/system/system_messages'
// Avoid a potentially laggy set of constant re-rendering markdown
const DebouncedMessages = debounceRender(SystemMessages, 300, {maxWait: 4000})

function IDComponent(props) {
    const id = props.data
    return <th><Link to={`${SYSTEM_MESSAGES_BASE}/${id}`}>{id}</Link></th>
}

const SystemMessagesTable = buildTable({
    name: 'admin_systemMessages',
    columns: [
        {key: 'id', label: 'ID', component: IDComponent},
        {
            key: 'active', label: 'Active',
            selector: (message) => message.active? 'Y' : 'N'
        },
        {key: 'section', label:'Section'},
        {
            key: 'query', label: 'Shown For',
            selector: (message) => {
                if (message.region) {
                    return `Region ${message.region}`
                } else if (message.center) {
                    return `Center ${message.center}`
                }
                return 'All Regions'
            }
        },
        {key: 'title', label:'Title'}
    ],
})

function checkLoading(component) {
    const { systemMessages, loadState } = component.props
    if (systemMessages.size) {
        return true
    }
    if (loadState.available) {
        delayDispatch(component, systemMessagesData.manager.runNetworkAction('load', {}, {
            setLoaded: true,
            api: Api.Admin.System.allSystemMessages,
            successHandler(data, { dispatch }) {
                let m = Immutable.Map()
                data.forEach((d) => {
                    m = m.set(d.id, d)
                })
                dispatch(systemMessagesData.replaceItems(m))
            }
        }))
    }
    return false
}

function systemMessagesMapState(state) {
    const { currentMessage } = state.admin.system
    const { loadState, saveState } = state.lookups
    return {
        systemMessages: systemMessagesData.selector(state),
        loadState, saveState, currentMessage
    }
}

const connector = connectRedux(systemMessagesMapState)

@connector
export class AdminSystemMessages extends React.Component {
    static propTypes = {
        systemMessages: PropTypes.instanceOf(Immutable.Map),
        loadState: loadStateShape,
        saveState: loadStateShape,
        currentMessage: PropTypes.object
    }

    render() {
        const { systemMessages, currentMessage } = this.props
        if (!checkLoading(this)) {
            return <div>Loading messages</div>
        }

        return (
            <div>
                <SystemMessagesTable data={systemMessages} />
                <h4>new message</h4>
                <SystemMessagesForm currentMessage={currentMessage} dispatch={this.props.dispatch} />
            </div>
        )
    }
}

@connector
export class EditSystemMessage extends React.Component {
    static propTypes = {
        currentMessage: PropTypes.object,
        systemMessages: PropTypes.instanceOf(Immutable.Map),
    }

    checkLoading() {
        if (!checkLoading(this)) {
            return false
        }
        const { params, currentMessage } = this.props
        if (currentMessage.id != params.id) {
            let message = this.props.systemMessages.get(parseInt(params.id))
            if (message) {
                delayDispatch(this, formActions.load(MODEL, message))
            }
            return false
        }
        return true
    }

    render() {
        const { currentMessage } = this.props
        if (!this.checkLoading()) {
            return <div>Loading messages</div>
        }
        return (
            <div>
                <h2>edit message {currentMessage.id}</h2>
                <SystemMessagesForm currentMessage={currentMessage} dispatch={this.props.dispatch} />
            </div>
        )
    }
}

@withRouter
class SystemMessagesForm extends React.Component {
    constructor(props) {
        super(props)
        rebind(this, 'onSubmit')
    }

    render() {
        const { currentMessage } = this.props
        return (
            <Form model={MODEL} onSubmit={this.onSubmit} className="form-horizontal">
                <SimpleField model={MODEL+'.section'} label="Section" />
                <SimpleField model={MODEL+'.region'} label="Region ID" />
                <SimpleFormGroup label="Center ID" divClass="col-md-3">
                    <NullableTextControl model={MODEL+'.center'} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Active" divClass="col-md-2">
                    <BooleanSelect model={MODEL+'.active'} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Level" divClass="col-md-3">
                    <SimpleSelect model={MODEL+'.level'} items={ALERT_TYPES} />
                </SimpleFormGroup>
                <SimpleField model={MODEL+'.title'} label="Title" />
                <SimpleFormGroup label="Content (markdown)">
                    <NullableTextAreaControl model={MODEL+'.content'} rows={10} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Preview">
                    <DebouncedMessages messages={Immutable.List.of(currentMessage)} />
                </SimpleFormGroup>
                <button type="submit" className="btn btn-primary">Save</button>
            </Form>
        )
    }

    onSubmit(currentMessage) {
        // clone so we can fix
        currentMessage = objectAssign({}, currentMessage)
        const centerRegion = ['center', 'region']
        centerRegion.forEach((k) => {
            currentMessage[k] = currentMessage[k] || null  // Override with null
        })
GetUserQuery
        this.props.dispatch(systemMessagesData.manager.runNetworkAction('save', {data: currentMessage}, {
            api: Api.Admin.System.writeSystemMessage,
            successHandler: (data, { dispatch }) => {
                this.props.router.push(SYSTEM_MESSAGES_BASE)
                currentMessage.id = parseInt(data.storedId)
                dispatch(systemMessagesData.replaceItem(currentMessage))
                dispatch(formActions.load(MODEL, {active: true}))
            }
        }))
    }
}


const GetAppsQuery = gql`
    query GetAppsQuery($center: String) {
        applications(centerId: $center) {
            regDate
            appOutDate
            person {
                firstName
                lastName
                ssz
            }
        }
    }
`

@graphql(GetAppsQuery)
export class TeamApps extends React.Component {
    render() {
        const { applications, loading } = this.props.data
        if (loading) {
            return <div>Loading...</div>
        }
        let items = applications.map((app) => {
            return (
                <li key={app.id}>
                    {app.person.firstName} {app.person.lastName}
                </li>
            )
        })
        return <ul>{items}</ul>
    }

    static propTypes = {
        data: PropTypes.object
    }
}
