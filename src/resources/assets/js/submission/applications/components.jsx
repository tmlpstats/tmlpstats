import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { Form, SimpleField, SimpleSelect, SimpleFormGroup, BooleanSelect, AddOneLink } from '../../reusable/form_utils'
import { Promise, objectAssign } from '../../reusable/ponyfill'
import { ModeSelectButtons, LoadStateFlip, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'

import { SubmissionBase, React } from '../base_components'
import { APPLICATIONS_FORM_KEY } from './reducers'
import { appsSorts, appsCollection, messages } from './data'
import { centerQuarterData } from '../core/data'
import { getLabelTeamMember } from '../core/selectors'
import { loadApplications, saveApplication, chooseApplication } from './actions'
import AppStatus, { getStatusString } from './AppStatus'

class ApplicationsBase extends SubmissionBase {
    componentDidMount() {
        this.setupApplications()
    }

    setupApplications() {
        if (this.props.loading.state == 'new') {
            return this.props.dispatch(loadApplications(this.props.params.centerId, this.reportingDateString()))
        }
        return Promise.resolve(null)
    }

    getAppById(appId) {
        return this.props.applications.collection[appId]
    }
}

class ApplicationsIndexView extends ApplicationsBase {
    renderWithdrawsTable(apps) {
        if (!apps.length) {
            return <div />
        }

        const baseUri = this.baseUri()
        const withdraws = []

        apps.forEach((app) => {
            let key = app.id
            withdraws.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/applications/edit/${key}`}>{app.firstName} {app.lastName}</Link></td>
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
        if (!this.props.loading.loaded) {
            return this.renderBasicLoading()
        }
        var changeSort = (newSort) => this.props.dispatch(appsCollection.changeSortCriteria(newSort))
        var apps = []
        var withdraws = []
        var baseUri = this.baseUri()
        appsCollection.iterItems(this.props.applications, (app, key) => {
            const status = getStatusString(app)
            if (app.withdrawCode) {
                withdraws.push(app)
                return
            }

            apps.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/applications/edit/${key}`}>{app.firstName} {app.lastName}</Link></td>
                    <td>{app.teamYear}</td>
                    <td>{app.regDate}</td>
                    <td>{status}</td>
                </tr>
            )
        })

        return (
            <div>
                <h3>Manage Registrations</h3>
                <ModeSelectButtons items={appsSorts} current={this.props.applications.meta.sort_by}
                                   onClick={changeSort} ariaGroupDesc="Sort Preferences" />
                <table className="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>{apps}</tbody>
                </table>
                <AddOneLink link={`${baseUri}/applications/add`} />
                {this.renderWithdrawsTable(withdraws)}
            </div>
        )
    }
}

class _EditCreate extends ApplicationsBase {
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
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" />
                    <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" />
                    <SimpleField label="Team Year" model={modelKey+'.teamYear'} divClass="col-md-4" customField={true}>
                        <select className="form-control">
                            <option value="1">Team 1</option>
                            <option value="2">Team 2</option>
                        </select>
                    </SimpleField>
                    <SimpleField label="Email" model={modelKey+'.email'} divClass="col-md-8" />
                    <SimpleField label="Comment" model={modelKey+'.comment'} customField={true}>
                        <textarea className="form-control" rows="3"></textarea>
                    </SimpleField>
                    <div className="form-group">
                        <label className="col-sm-2">Committed Team Member</label>
                        <div className="col-sm-10">
                            <SimpleSelect
                                    model={modelKey+'.committedTeamMember'} items={this.props.lookups.team_members}
                                    keyProp="teamMemberId" getLabel={getLabelTeamMember} emptyChoice="Choose One" />
                        </div>
                    </div>
                    <div className="form-group">
                        <label className="col-sm-2">Application Status</label>
                        <div className="col-sm-10">
                            <AppStatus model={modelKey} currentApp={this.props.currentApp} lookups={this.props.lookups} dispatch={this.props.dispatch} />
                        </div>
                    </div>
                    {this.renderTravelRoom(modelKey)}
                    <div className="form-group">
                        <div className="col-sm-offset-2 col-sm-8">
                            <LoadStateFlip loadState={this.props.saveApp}>
                                <button className="btn btn-primary" type="submit">Save</button>
                            </LoadStateFlip>
                        </div>
                    </div>
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
        let body = ''
        if (this.isNewApp()) {
            body = <SimpleSelect model={modelKey+'.incomingQuarter'} items={centerQuarters}
                                 keyProp="quarterId" getLabel={centerQuarterData.getLabel} />
        } else {
            const cq = cqData[currentApp.incomingQuarter]
            body = (cq)? centerQuarterData.getLabel(cq) : 'Unknown'
        }
        return (
            <div className="form-group">
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
        this.props.dispatch(saveApplication(this.props.params.centerId, this.reportingDateString(), data)).then((result) => {
            if (!result) {
                return
            }

            if (result.messages && result.messages.length) {
                scrollIntoView('submission-flow')

                // Redirect to edit view if there are warning messages
                if (this.isNewApp() && result.valid) {
                    this.props.router.push(this.baseUri() + '/applications/edit/' + result.storedId)
                }
            } else if (result.valid) {
                this.props.router.push(this.baseUri() + '/applications')
            }
        })
    }

}

class ApplicationsEditView extends _EditCreate {
    componentDidMount() {
        super.setupApplications().then(() => {
            var appId = this.props.params.appId
            if (!this.props.currentApp || this.props.currentApp.id != appId) {
                let app = this.getAppById(appId)
                if (app){
                    app = objectAssign({}, app, {committedTeamMember: app.committedTeamMember || ''})
                    this.props.dispatch(chooseApplication(appId, app))
                }
            }
        })
    }

    title() {
        return 'Edit Application'
    }

    render() {
        const appId = this.props.params.appId

        if (!this.props.loading.loaded
            || !this.props.currentApp
            || this.props.currentApp.id != appId
            || !this.props.lookups
            || !this.props.lookups.validRegQuarters
            || !this.props.lookups.team_members
        ) {
            return <div>{this.props.loading.state}...</div>
        }
        return super.render()
    }
}

class ApplicationsAddView extends _EditCreate {
    componentDidMount() {
        super.setupApplications().then(() => {
            if (this.props.currentApp) {
                const { centerQuarters } = this.getCenterQuarters()
                const blankApp = {
                    firstName: '',
                    lastName: '',
                    teamYear: 1,
                    regDate: this.reportingDateString(),
                    committedTeamMember: '',
                    incomingQuarter: centerQuarters[0].quarterId
                }
                this.props.dispatch(chooseApplication('', blankApp))
            }
        })
    }

    title() {
        return 'Create Application'
    }

    render() {
        if (!this.props.loading.loaded
            || !this.props.lookups
            || !this.props.lookups.validRegQuarters
            || !this.props.lookups.team_members
        ) {
            return <div>{this.props.loading.state}...</div>
        }
        return super.render()
    }

}

const mapStateToProps = (state) => {
    const s = state.submission
    return objectAssign({lookups: s.core.lookups, centerQuarters: s.core.centerQuarters}, s.applications)
}
const connector = connect(mapStateToProps)

export const ApplicationsIndex = connector(ApplicationsIndexView)
export const ApplicationsEdit = connector(withRouter(ApplicationsEditView))
export const ApplicationsAdd = connector(withRouter(ApplicationsAddView))
