// POLYFILL
import { Promise } from 'es6-promise'

// NORMAL CODE
import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, SimpleSelect, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, LoadStateFlip } from '../../reusable/ui_basic'

import { APPLICATIONS_FORM_KEY } from './reducers'
import { appsSorts, appsCollection } from './data'
import { loadApplications, saveApplication, chooseApplication } from './actions'
import AppStatus from './AppStatus'

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
    render() {
        if (!this.props.loading.loaded) {
            return this.renderBasicLoading()
        }
        var changeSort = (newSort) => this.props.dispatch(appsCollection.changeSortCriteria(newSort))
        var apps = []
        var baseUri = this.baseUri()
        appsCollection.iterItems(this.props.applications, (app, key) => {
            apps.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/applications/edit/${key}`}>{app.firstName} {app.lastName}</Link></td>
                    <td>{app.regDate}</td>
                    <td>{app.teamYear}</td>
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
                            <th>Registered</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>{apps}</tbody>
                </table>
                <AddOneLink link={`${baseUri}/applications/add`} />
            </div>
        )
    }
}

const getLabelTeamMember = (item) => {
    const p = item.teamMember.person
    return p.firstName + " " + p.lastName
}

class _EditCreate extends ApplicationsBase {
    render() {
        const modelKey = APPLICATIONS_FORM_KEY
        return (
            <div>
                <h3>{this.title()}</h3>
                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveApp.bind(this)}>
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" />
                    <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" />
                    <SimpleField label="Team Year" model={modelKey+'.teamYear'} divClass="col-md-4" customField={true}>
                        <select>
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
    // saveApp for now is the same between edit and create flows
    saveApp(data) {
        this.props.dispatch(saveApplication(this.props.params.centerId, this.reportingDateString(), data)).done((result) => {
            if (result.success && result.storedId) {
                data = Object.assign({}, data, {id: result.storedId})
                this.props.dispatch(appsCollection.replaceItem(data))
            }
            this.props.router.push(this.baseUri() + '/applications')
        })
    }
}

class ApplicationsEditView extends _EditCreate {
    componentDidMount() {
        super.setupApplications().then(() => {
            var appId = this.props.params.appId
            if (!this.props.currentApp || this.props.currentApp.id != appId) {
                this.props.dispatch(chooseApplication(appId, this.getAppById(appId)))
            }
        })
    }

    title() {
        return 'Edit Application'
    }

    render() {
        const appId = this.props.params.appId

        if (!this.props.loading.loaded || !this.props.currentApp || this.props.currentApp.id != appId) {
            return <div>{this.props.loading.state}...</div>
        }
        return super.render()
    }

}

class ApplicationsAddView extends _EditCreate {
    componentDidMount() {
        super.setupApplications().then(() => {
            if (this.props.currentApp) {
                this.props.dispatch(chooseApplication('', {firstName: '', lastName: '', teamYear: '', regDate: this.reportingDateString()}))
            }
        })
    }

    title() {
        return 'Create Application'
    }

    render() {
        return super.render()
    }
}

const mapStateToProps = (state) => {
    return Object.assign({lookups: state.submission.core.lookups}, state.submission.applications)
}
const connector = connect(mapStateToProps)

export const ApplicationsIndex = connector(ApplicationsIndexView)
export const ApplicationsEdit = connector(withRouter(ApplicationsEditView))
export const ApplicationsAdd = connector(withRouter(ApplicationsAddView))
