import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { Form, SimpleField, SimpleSelect, SimpleFormGroup, BooleanSelect, AddOneLink, formActions } from '../../reusable/form_utils'
import { objectAssign } from '../../reusable/ponyfill'
import { rebind, delayDispatch } from '../../reusable/dispatch'
import { collectionSortSelector, SORT_BY } from '../../reusable/sort-helpers'
import { Alert, ModeSelectButtons, ButtonStateFlip, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'

import { SubmissionBase, React } from '../base_components'
import { APPLICATIONS_FORM_KEY } from './reducers'
import { appsSorts, appsCollection } from './data'
import { centerQuarterData } from '../core/data'
import { getLabelTeamMember } from '../core/selectors'
import { loadApplications, saveApplication, chooseApplication } from './actions'
import AppStatus from './AppStatus'

const getSortedApplications = collectionSortSelector(appsSorts)

const mapStateToProps = (state) => {
    const s = state.submission
    return {lookups: s.core.lookups, centerQuarters: s.core.centerQuarters, ...s.applications}
}
const connector = connect(mapStateToProps)


class ApplicationsBase extends SubmissionBase {
    constructor(props) {
        super(props)
        this.checkLoading()
    }

    checkLoading() {
        const { loading, params } = this.props
        if (loading.state == 'new') {
            const { centerId, reportingDate } = params
            delayDispatch(this, loadApplications(centerId, reportingDate))
        }
        return (loading.state == 'loaded')
    }

    appsBaseUri() {
        return this.baseUri() + '/applications'
    }

    getAppById(appId) {
        return this.props.applications.data[appId]
    }
}

@connector
export class ApplicationsIndex extends ApplicationsBase {
    constructor(props) {
        super(props)
        rebind(this, 'changeSort')
    }

    renderWithdrawsTable(apps) {
        if (!apps.length) {
            return <div />
        }

        const baseUri = this.appsBaseUri()
        const withdraws = []

        apps.forEach((app) => {
            let key = app.id
            withdraws.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/edit/${key}`}>{app.firstName} {app.lastName}</Link></td>
                    <td>{app.teamYear}</td>
                    <td>{app.regDate}</td>
                    <td>{app.wdDate}</td>
                    <td>{this.props.lookups.withdraw_codes_by_id[app.withdrawCode].display}</td>
                </tr>
            )
        })

        return (
            <div>
            <br/>
                <h4>Withdraws</h4>
                <table className="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Registered</th>
                            <th>Withdrawn</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>{withdraws}</tbody>
                </table>
            </div>
        )
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading(this.props.loading)
        }
        var apps = []
        var withdraws = []
        const baseUri = this.appsBaseUri()
        const { applications } = this.props
        const sortedApps = getSortedApplications(applications)

        sortedApps.forEach((app) => {
            const key = app.id
            const status = AppStatus.getStatusString(app)
            if (app.withdrawCode) {
                withdraws.push(app)
                return
            }

            apps.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/edit/${key}`}>{app.firstName} {app.lastName}</Link></td>
                    <td>{app.teamYear}</td>
                    <td>{app.regDate}</td>
                    <td>{app.apprDate}</td>
                    <td>{status}</td>
                </tr>
            )
        })

        return (
            <div>
                <h3>Manage Registrations</h3>
                <ModeSelectButtons items={appsSorts} current={applications.meta.get(SORT_BY)}
                                   onClick={this.changeSort} ariaGroupDesc="Sort Preferences" />
                <table className="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Registered</th>
                            <th>Approved</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>{apps}</tbody>
                </table>
                <AddOneLink link={`${baseUri}/add`} />
                {this.renderWithdrawsTable(withdraws)}
            </div>
        )
    }

    changeSort(newSort) {
        this.props.dispatch(appsCollection.setMeta('sort_by', newSort))
    }
}

class _EditCreate extends ApplicationsBase {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { lookups } = this.props
        return (lookups.validRegQuarters && lookups.team_members)
    }

    render() {
        const modelKey = APPLICATIONS_FORM_KEY
        const app = this.props.currentApp
        let messages = []
        if (app && app.id) {
            messages = this.props.messages[app.id]
        } else if (this.isNewApp() && this.props.messages['create']) {
            messages = this.props.messages['create']
        }

        return (
            <div>
                <h3>{this.title()}</h3>

                <MessagesComponent messages={messages} />

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveAppData.bind(this)}>
                    {this.renderStartingQuarter(modelKey)}
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" required={true} />
                    <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" required={true} />
                    <SimpleField label="Team Year" model={modelKey+'.teamYear'} divClass="col-md-1" required={true} customField={true}>
                        <select className="form-control">
                            <option value="1">Team 1</option>
                            <option value="2">Team 2</option>
                        </select>
                    </SimpleField>
                    <SimpleField label="Email" model={modelKey+'.email'} divClass="col-md-6" />
                    <SimpleField label="Comment" model={modelKey+'.comment'} divClass="col-md-6" customField={true}>
                        <textarea className="form-control" rows="3"></textarea>
                    </SimpleField>
                    <div className="required form-group">
                        <label className="col-md-2 control-label">Committed Team Member</label>
                        <div className="col-md-6">
                            <SimpleSelect
                                    model={modelKey+'.committedTeamMember'} items={this.props.lookups.team_members}
                                    keyProp="teamMemberId" getLabel={getLabelTeamMember} emptyChoice="Choose One" />
                        </div>
                    </div>
                    <div className="form-group">
                        <label className="col-md-2 control-label">Application Status</label>
                        <div className="col-md-9">
                            <AppStatus model={modelKey} currentApp={this.props.currentApp} lookups={this.props.lookups} dispatch={this.props.dispatch} />
                        </div>
                    </div>
                    {this.renderTravelRoom(modelKey)}
                    <ButtonStateFlip loadState={this.props.saveApp} offset='col-sm-offset-2 col-sm-8' wrapGroup={true}>Save</ButtonStateFlip>
                </Form>
            </div>
        )
    }

    // Return true if this is a new app
    isNewApp() {
        const { currentApp } = this.props
        return (!currentApp.id || parseInt(currentApp.id) < 0)
    }

    getCenterQuarters() {
        const cqData = this.props.centerQuarters.data
        const centerQuarters = this.props.lookups.validRegQuarters.map(id => cqData[id])

        return { cqData, centerQuarters }
    }

    renderStartingQuarter(modelKey) {
        const { currentApp } = this.props
        const { cqData, centerQuarters } = this.getCenterQuarters()
        let body
        const CHANGING_QUARTER_KEY = '_changingQuarter'
        const isNewApp = this.isNewApp()
        if (isNewApp || currentApp[CHANGING_QUARTER_KEY]) {
            body = <SimpleSelect model={modelKey+'.incomingQuarter'} items={centerQuarters}
                                 keyProp="quarterId" getLabel={centerQuarterData.getLabel} />
        } else {
            const clickHandler = () => {
                this.props.dispatch(formActions.change(`${modelKey}.${CHANGING_QUARTER_KEY}`, true))
            }
            const cq = cqData[currentApp.incomingQuarter]
            const label = (cq)? centerQuarterData.getLabel(cq) : 'Unknown'
            body = (
                <span>
                    {label}
                    <a href="#" onClick={clickHandler}>Change Starting Quarter</a>
                </span>
            )
        }

        if (currentApp._changingQuarter) {
            body = (
                <div>
                    <Alert alert="info">
                        Changing Starting Quarter to a later weekend can only happen once for
                        an applicant.
                    </Alert>
                    {body}
                </div>
            )
        }

        let requiredClass = ''
        if (this.isNewApp()) {
            requiredClass = 'required'
        }

        return (
            <div className={requiredClass + ' form-group'}>
                <label className="col-md-2 control-label">Starting Quarter</label>
                <div className="col-md-6">{body}</div>
            </div>
        )
    }

    renderTravelRoom(modelKey) {
        return (
            <div>
                <SimpleFormGroup label="Travel Booked" divClass="col-md-6 boolSelect">
                    <BooleanSelect model={modelKey+'.travel'} style={{maxWidth: '4em'}} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Room Booked" divClass="col-md-6 boolSelect">
                    <BooleanSelect model={modelKey+'.room'} />
                </SimpleFormGroup>
            </div>
        )
    }

    // saveAppData for now is the same between edit and create flows
    saveAppData(data) {
        const { router, dispatch, params: {centerId, reportingDate} } = this.props
        dispatch(saveApplication(centerId, reportingDate, data)).then((result) => {
            if (!result) {
                return
            }

            if (result.messages && result.messages.length) {
                scrollIntoView('react-routed-flow')

                // Redirect to edit view if there are warning messages
                if (this.isNewApp() && result.valid) {
                    router.push(`${this.appsBaseUri()}/edit/${result.storedId}`)
                }
            } else if (result.valid) {
                router.push(this.appsBaseUri())
            }
        })
    }
}

@connector
@withRouter
export class ApplicationsEdit extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentApp, params: { appId } } = this.props
        if (!currentApp || currentApp.id != appId) {
            let app = this.getAppById(appId)
            if (app) {
                app = objectAssign({}, app, {committedTeamMember: app.committedTeamMember || ''})
                delayDispatch(this, chooseApplication(appId, app))
            }
            return false
        }
        return true
    }

    title() {
        return 'Edit Application'
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading(this.props.loading)
        }
        return super.render()
    }
}

@connector
@withRouter
export class ApplicationsAdd extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        if (!this.props.currentApp || this.props.currentApp.id) {
            const { centerQuarters } = this.getCenterQuarters()
            const blankApp = {
                firstName: '',
                lastName: '',
                teamYear: 1,
                regDate: this.reportingDateString(),
                incomingQuarter: centerQuarters[0].quarterId,
                committedTeamMember: '',
            }
            delayDispatch(this, chooseApplication('', blankApp))
            return false
        }
        return true
    }

    title() {
        return 'Create Application'
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading(this.props.loading)
        }
        return super.render()
    }

}
