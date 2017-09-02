import { Link, withRouter } from 'react-router'
import { defaultMemoize } from 'reselect'

import { Control, Form, SimpleField, Select, SimpleFormGroup, BooleanSelect, AddOneLink, formActions } from '../../reusable/form_utils'
import { objectAssign } from '../../reusable/ponyfill'
import { rebind, delayDispatch, connectRedux } from '../../reusable/dispatch'
import { SORT_BY } from '../../reusable/sort-helpers'
import { Alert, ModeSelectButtons, ButtonStateFlip, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'

import { loadTeamMembers } from '../team_members/actions'
import { teamMembersData } from '../team_members/data'
import { SubmissionBase, React } from '../base_components'
import { APPLICATIONS_FORM_KEY } from './reducers'
import { appsSorts, appsCollection } from './data'
import { centerQuarterData } from '../core/data'
import DeleteWarning from '../core/DeleteWarning'
import { loadApplications, saveApplication, chooseApplication } from './actions'
import AppStatus from './AppStatus'

const getSortedApplications = appsCollection.opts.getSortedApplications

const sharedMapState = (state) => {
    const submission = state.submission
    return {
        lookups: submission.core.lookups,
        centerQuarters: submission.core.centerQuarters,
        ...submission.applications
    }
}

class ApplicationsBase extends SubmissionBase {
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

    getCenterQuarters() {
        const cqData = this.props.centerQuarters.data
        const centerQuarters = this.props.lookups.validRegQuarters.map(id => cqData[id])

        return { cqData, centerQuarters }
    }
}

@connectRedux(sharedMapState)
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
        const { cqData } = this.getCenterQuarters()

        apps.forEach((app) => {
            let key = app.id

            const cq = cqData[app.incomingQuarter]
            const quarterLabel = (cq) ? centerQuarterData.getMonthDistinctionLabel(cq) : 'Unknown'

            withdraws.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/edit/${key}`}>{nameRow(app)}</Link></td>
                    <td>{app.teamYear}</td>
                    <td>{quarterLabel}</td>
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
                            <th>Weekend</th>
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
        const { cqData } = this.getCenterQuarters()

        sortedApps.forEach((app) => {
            const key = app.id
            const status = AppStatus.getStatusString(app)
            if (app.withdrawCode) {
                withdraws.push(app)
                return
            }

            const cq = cqData[app.incomingQuarter]
            const quarterLabel = (cq) ? centerQuarterData.getMonthDistinctionLabel(cq) : 'Unknown'

            apps.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/edit/${key}`}>{nameRow(app)}</Link></td>
                    <td>{app.teamYear}</td>
                    <td>{quarterLabel}</td>
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
                            <th>Weekend</th>
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

function editCreateMapState(state) {
    let mapping = sharedMapState(state)
    mapping.teamMembers = state.submission.team_members.teamMembers
    return mapping
}

const connector = connectRedux(editCreateMapState)

class _EditCreate extends ApplicationsBase {
    constructor(props) {
        super(props)
        this.selectableMembers = defaultMemoize((teamMembers) => {
            const tmp = teamMembersData.opts.getSortedMembers(teamMembers)
            return tmp.filter(tm => tm.id && parseInt(tm.id) > 0)
        })
        rebind(this, 'saveAppData', 'deleteApp')
    }
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }

        const { lookups, teamMembers } = this.props
        if (!teamMembers.loadState.loaded) {
            if (teamMembers.loadState.available) {
                // XXX even with this check for availability, we still sometimes double-load.
                // For now, this may be un-fixable without going a bit further with a thunk.
                const { centerId, reportingDate } = this.props.params
                delayDispatch(this, loadTeamMembers(centerId, reportingDate))
            }
            return false
        }

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

        const selectableMembers = this.selectableMembers(this.props.teamMembers)

        return (
            <div>
                <h3>{this.title()}</h3>

                <MessagesComponent messages={messages} />

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveAppData.bind(this)}>
                    {this.renderStartingQuarter(modelKey)}
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" required={true} />
                    <SimpleField label="Last Initial" model={modelKey+'.lastName'} divClass="col-md-6" required={true} />
                    <SimpleFormGroup label="Team Year" divClass="col-md-4" required={true} labelFor="f-app-teamYear">
                        <Control.select model={modelKey+'.teamYear'} className="form-control" style={{maxWidth: '10em'}} id="f-app-teamYear">
                            <option value="1">Team 1</option>
                            <option value="2">Team 2</option>
                        </Control.select>
                    </SimpleFormGroup>
                    <SimpleField label="Comment" model={modelKey+'.comment'} divClass="col-md-6" customField={true}>
                        <textarea className="form-control" rows="3"></textarea>
                    </SimpleField>
                    <SimpleFormGroup label="Committed Team Member" required={true} labelFor="f-app-commitedTeamMember" divClass="col-md-6">
                        <Select
                                id="f-app-committedTeamMember"
                                model={modelKey+'.committedTeamMember'} items={selectableMembers}
                                keyProp="id" getLabel={teamMembersData.opts.getLabel} emptyChoice="Choose One" />
                    </SimpleFormGroup>
                    <div className="form-group">
                        <label className="col-md-2 control-label">Application Status</label>
                        <div className="col-md-9">
                            <AppStatus model={modelKey} currentApp={this.props.currentApp} lookups={this.props.lookups} dispatch={this.props.dispatch} />
                        </div>
                    </div>
                    {this.renderTravelRoom(modelKey)}
                    <ButtonStateFlip loadState={this.props.saveApp} offset='col-sm-offset-2 col-sm-8' wrapGroup={true}>Save</ButtonStateFlip>
                </Form>
                {this.renderDeleteFlow(modelKey)}
            </div>
        )
    }

    // Return true if this is a new app
    isNewApp() {
        const { currentApp } = this.props
        return (!currentApp.id || parseInt(currentApp.id) < 0)
    }

    renderStartingQuarter(modelKey) {
        const { currentApp } = this.props
        const { cqData, centerQuarters } = this.getCenterQuarters()
        let body
        const CHANGING_QUARTER_KEY = '_changingQuarter'
        const isNewApp = this.isNewApp()
        if (isNewApp || currentApp[CHANGING_QUARTER_KEY]) {
            body = <Select model={modelKey+'.incomingQuarter'} items={centerQuarters}
                                 keyProp="quarterId" getLabel={centerQuarterData.getLabel} />
        } else {
            const clickHandler = () => {
                this.props.dispatch(formActions.change(`${modelKey}.${CHANGING_QUARTER_KEY}`, true))
                return false // prevent navigation to hash URL
            }
            const cq = cqData[currentApp.incomingQuarter]
            const label = (cq)? centerQuarterData.getLabel(cq) : 'Unknown'
            body = (
                <p className="form-control-static">
                    {label}&nbsp;
                    <a href="#" onClick={clickHandler}>Change Starting Quarter</a>
                </p>
            )
        }

        if (currentApp[CHANGING_QUARTER_KEY]) {
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
        return dispatch(saveApplication(centerId, reportingDate, data)).then((result) => {
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

    renderDeleteFlow(modelKey) {
        const { currentApp, lookups } = this.props
        let extraConfirm, spiel
        if (!currentApp.id) {
            return
        } else if (currentApp.meta && currentApp.meta.canDelete) {
            spiel = <p>Deleting an application will cause the application to be permanently removed.</p>
        } else if (lookups.user.canOverrideDelete) {
            extraConfirm = nameRow(currentApp)
            spiel = (
                <div>
                    <p><b>Regional Statisticians</b> - This application cannot be deleted by a statistician.
                    You can override the delete, but please be advised that this
                    will <b>retroactively</b> change reports, including maybe for
                    last week.</p>

                    <p>If you wish to continue, please enter the full name '{extraConfirm}' below.</p>
                </div>
            )
        } else {
            return
        }
        return (
            <div style={{maxWidth: '80em', marginTop: '3em'}}>
                <DeleteWarning
                        model={modelKey} noun="Application" spiel={spiel}
                        extraConfirm={extraConfirm} onSubmit={this.deleteApp}
                        buttonState={this.props.saveApp} />
            </div>
        )
    }

    deleteApp(data) {
        data = objectAssign({}, data, {action: 'delete'})
        this.saveAppData(data)
    }
}

@withRouter
@connector
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

@withRouter
@connector
export class ApplicationsAdd extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentApp } = this.props
        if (!currentApp || currentApp.id || !currentApp.teamYear) {
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

function nameRow(app) {
    if (!app.firstName && !app.lastName) {
        return '<Name Blank>'
    }
    return (app.firstName || 'unknown') + ' ' + (app.lastName || 'unknown')
}
