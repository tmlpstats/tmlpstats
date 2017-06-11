import _ from 'lodash'
import React, { PropTypes, PureComponent, Component } from 'react'
import { actions as formActions } from 'react-redux-form'

import { Glyphicon } from '../../reusable/ui_basic'
import { SimpleSelect, SimpleDateInput } from '../../reusable/form_utils'
import { rebind } from '../../reusable/dispatch'

const STATUS_UNKNOWN=0,
    STATUS_REG=1,
    STATUS_OUT=2,
    STATUS_IN=3,
    STATUS_APPR=4,
    STATUS_WD=5

// Since the status is not stored currently inside the model, we will just infer it from the existing properties
function inferStatus(app) {
    if (app.appStatus) {
        return app.appStatus
    }

    if (app.apprDate) {
        return STATUS_APPR
    }
    if (app.appInDate) {
        return STATUS_IN
    }
    if (app.appOutDate) {
        return STATUS_OUT
    }
    if (app.regDate) {
        return STATUS_REG
    }

    // This is a new application, default to reg
    if (!app.id) {
        return STATUS_REG
    }

    // Default to reg if something isn't right
    console.log(`Unknown application status for ${app.id}`)
    return STATUS_REG
}

function inferWithdrawn(app) {
    return (app.withdrawn || app.wdDate)
}

const STATUS_BEHAVIOUR = [
    {status: STATUS_REG, key: 'regDate', title: 'Registered'},
    {status: STATUS_OUT, key: 'appOutDate', title: 'App Out'},
    {status: STATUS_IN, key: 'appInDate', title: 'App In'},
    {status: STATUS_APPR, key: 'apprDate', title: 'Approved', dateTitle: 'Date Approved'},
]

export function getStatusString(app) {
    if (inferWithdrawn(app)) {
        return 'Withdrawn'
    }
    // If not withdrawn, fall to checking status codes
    const statusCode = inferStatus(app)
    const status = _.find(STATUS_BEHAVIOUR, function(o) {
        return o.status == statusCode
    })

    return status ? status.title : 'Invalid'
}

export default class AppStatus extends Component {
    static propTypes = {
        model: PropTypes.string.isRequired,
        dispatch: PropTypes.func.isRequired,
        currentApp: PropTypes.object,
    }
    static getStatusString = getStatusString

    constructor(props) {
        super(props)
        rebind(this, 'onStatusClick')
    }

    render() {
        const { model, currentApp } = this.props
        var currentStatus = inferStatus(currentApp)
        const withdrawn = inferWithdrawn(currentApp)

        var buttons = []
        var rows = []
        // Create rows for all the statuses which are shown
        STATUS_BEHAVIOUR.forEach((item) => {
            if (item.status > currentStatus) {
                return
            }
            const dateTitle = item.dateTitle || (item.title + ' Date')
            rows.push(
                <div key={item.key}>
                    <SimpleDateInput
                            model={model+'.'+item.key} label={dateTitle}
                            labelClass="col-sm-3" divClass="col-sm-6"
                            required={true} />
                </div>
            )
        })

        // Trailer is either buttons or Withdrawn info
        let trailer
        if (withdrawn) {
            const { withdraw_codes } = this.props.lookups
            trailer = (
                <Withdrawn status={currentStatus} model={model}
                        buttonClick={this.onStatusClick} withdraw_codes={withdraw_codes} />
            )
        } else {
            trailer = <StatusButtons status={currentStatus} buttonClick={this.onStatusClick} />
        }

        return (
            <div className="panel panel-default">
                <div className="panel-body">
                    <div className="row">
                        <div className="col-sm-offset-1 col-sm-11">
                            <div className="btn-group" style={{paddingBottom: '15px'}}>{buttons}</div>
                        </div>
                    </div>
                    <div>
                        {rows}
                        {trailer}
                    </div>
                </div>
            </div>
        )
    }

    onStatusClick(event) {
        if (event.target && event.target.dataset) {
            const { currentApp } = this.props
            const status = event.target.dataset['status']
            let updates

            if (status == STATUS_WD) {
                updates = {
                    withdrawn: true,
                    wdDate: '',
                    withdrawCode: ''
                }
            } else {
                updates = {
                    withdrawn: false, wdDate: null, withdrawCode: null,
                    appStatus: status
                }
                STATUS_BEHAVIOUR.forEach((item) => {
                    const backupKey = '_backup_' + item.key
                    if (status < item.status) {
                        if (currentApp[item.key]) {
                            updates[backupKey] = currentApp[item.key]
                        }
                        updates[item.key] = null
                    } else if (currentApp[backupKey]) {
                        updates[backupKey] = null
                        updates[item.key] = currentApp[backupKey]
                    } else if (status == item.status && !currentApp[item.key]) {
                        // Setting to empty string forces a validation failure
                        updates[item.key] = ''
                    }
                })
            }

            this.props.dispatch(formActions.merge(this.props.model, updates))
        }
    }
}

class Withdrawn extends PureComponent {
    render() {
        const { model, withdraw_codes, buttonClick, status } = this.props
        return (
            <div>
                <SimpleDateInput model={model+'.wdDate'} label="Withdraw Date" labelClass="col-sm-3" divClass="col-sm-6" required={true} />

                <div className="form-group">
                    <label className="col-sm-3 control-label">Withdraw Reason</label>
                    <div className="col-sm-9">
                        <SimpleSelect model={model+'.withdrawCode'} keyProp="id" labelProp="display" items={withdraw_codes} emptyChoice="Choose One" required={true} />
                    </div>
                </div>
                <div className="col-sm-offset-3 col-sm-9">
                    <button type="button" className="btn btn-default" data-status={status} onClick={buttonClick}>
                        <Glyphicon icon="arrow-left" />
                        Un-withdraw
                    </button>
                </div>
            </div>
        )
    }
}

class StatusButtons extends PureComponent {
    render() {
        const { status, buttonClick } = this.props
        let i
        for (i = 0; i < STATUS_BEHAVIOUR.length; i++) {
            if (STATUS_BEHAVIOUR[i].status == status) {
                break
            }
        }

        const Button = (props) => {
            const extraClass = props.extraClass || 'btn-default'
            return <button type="button" className={'btn ' + extraClass} data-status={props.status} onClick={buttonClick}>{props.children}</button>
        }

        let nextButton, prevButton
        if (status > STATUS_REG) {
            const behavior = STATUS_BEHAVIOUR[i-1]
            prevButton = <Button status={behavior.status}><Glyphicon icon="arrow-left" /> {behavior.title}</Button>
        }
        if (status < STATUS_APPR) {
            const behavior = STATUS_BEHAVIOUR[i+1]
            nextButton = <Button status={behavior.status}><Glyphicon icon="arrow-right" /> {behavior.title}</Button>
        }
        return (
            <div className="col-sm-offset-3 col-sm-9">
                {prevButton}
                {nextButton}
                &nbsp;
                <Button status={STATUS_WD} extraClass="btn-warning"><Glyphicon icon="arrow-right" /> Withdraw</Button>
            </div>
        )
    }
}
