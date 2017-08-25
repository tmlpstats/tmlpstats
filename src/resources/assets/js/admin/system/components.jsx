import React from 'react'
import PropTypes from 'prop-types'
import Immutable from 'immutable'

import Api from '../../api'
import {
    Form, formActions, SimpleField, SimpleFormGroup,
    SimpleSelect, NullableTextControl, NullableTextAreaControl, BooleanSelect } from '../../reusable/form_utils'
import { ALERT_TYPES } from '../../reusable/ui_basic'
import { objectAssign } from '../../reusable/ponyfill'
import debounceRender from '../../reusable/debounce'
import { connectRedux, rebind, delayDispatch } from '../../reusable/dispatch'
import { loadStateShape } from '../../reusable/shapes'
import { SystemMessages } from '../../reusable/system-messages'

import { systemMessagesData } from '../../lookups/lookups-system-messages'
const MODEL = 'admin.system.currentMessage'

// Avoid a potentially laggy set of constant re-rendering markdown
const DebouncedMessages = debounceRender(SystemMessages, 300, {maxWait: 4000})

@connectRedux()
export class AdminSystemMessages extends React.Component {
    static mapStateToProps(state) {
        const { currentMessage } = state.admin.system
        const { loadState, saveState } = state.lookups
        return {
            systemMessages: systemMessagesData.selector(state),
            loadState, saveState, currentMessage
        }
    }

    static propTypes = {
        systemMessages: PropTypes.instanceOf(Immutable.Map),
        loadState: loadStateShape,
        saveState: loadStateShape,
        currentMessage: PropTypes.object
    }

    constructor(props) {
        super(props)
        rebind(this, 'onSubmit', 'onSelect')
    }

    checkLoading() {
        const { systemMessages, loadState } = this.props
        if (systemMessages.size) {
            return true
        }
        if (loadState.available) {
            delayDispatch(this, systemMessagesData.manager.runNetworkAction('load', {}, {
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

    render() {
        const { systemMessages, loadState, currentMessage } = this.props
        if (!this.checkLoading()) {
            return <div>Loading messages</div>
        }

        let items = []
        systemMessages.forEach((message) => {
            let query = 'All Regions'
            if (message.region) {
                query = `Region ${message.region}`
            } else if (message.center) {
                query = `Center ${message.center}`
            }
            items.push(
                <tr key={message.id}>
                    <td><a href="#" onClick={this.onSelect} data-mid={message.id}>{message.id}</a></td>
                    <td>{message.active? 'Y' : 'N'}</td>
                    <td>{message.section}</td>
                    <td>{query}</td>
                    <td>{message.title}</td>
                </tr>
            )
        })

        return (
            <div>
                <table className="table">
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>Active</td>
                            <td>Section</td>
                            <td>Shown For</td>
                            <td>Title</td>
                        </tr>
                    </thead>
                    <tbody>
                        {items}
                    </tbody>
                </table>
                <h4>{currentMessage.id? `edit message ${currentMessage.id}` : 'new message'}</h4>
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
            </div>
        )
    }

    onSelect(e) {
        const id = e.target.dataset.mid
        let message = this.props.systemMessages.get(parseInt(id))
        if (message) {
            this.props.dispatch(formActions.load(MODEL, message))
        }
    }

    onSubmit(currentMessage) {
        // clone so we can fix
        currentMessage = objectAssign({}, currentMessage)
        const centerRegion = ['center', 'region']
        centerRegion.forEach((k) => {
            currentMessage[k] = currentMessage[k] || null  // Override with null
        })

        this.props.dispatch(systemMessagesData.manager.runNetworkAction('save', {data: currentMessage}, {
            api: Api.Admin.System.writeSystemMessage,
            successHandler(data, { dispatch }) {
                currentMessage.id = data.storedId
                dispatch(systemMessagesData.replaceItem(currentMessage))
                dispatch(formActions.load(MODEL, {active: true}))
            }
        }))
    }
}
