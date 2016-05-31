import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField } from '../../reusable/form_utils'
import { ModeSelectButtons } from '../../reusable/ui_basic'
import { APPLICATIONS_FORM_KEY } from './reducers'
import { appsSorts, appsCollection } from './data'
import { loadApplications, saveApplication, chooseApplication } from './actions'

class ApplicationsBase extends SubmissionBase {
    componentDidMount() {
        this.setupApplications()
    }

    setupApplications() {
        if (this.props.loading.state == 'new') {
            return this.props.dispatch(loadApplications(this.props.params.centerId))
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
            return <div>Loading...</div>
        }
        var changeSort = (newSort) => this.props.dispatch(appsCollection.changeSortCriteria(newSort))
        var apps = []
        var baseUri = this.baseUri()
        var pc = this.props.applications
        pc.sortedKeys.forEach((key) => {
            var app = pc.collection[key]
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
                <ModeSelectButtons items={Array.from(appsSorts.values())} current={this.props.applications.meta.sort_by}
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
            </div>
        )
    }
}


class ApplicationsEditView extends ApplicationsBase {
    componentDidMount() {
        super.setupApplications().then(() => {
            var appId = this.props.params.appId
            if (!this.props.currentApp || this.props.currentApp.tmlpRegistrationId != appId) {
                this.props.dispatch(chooseApplication(appId, this.getAppById(appId)))
            }
        })
    }
    saveApp(data) {
        this.props.dispatch(saveApplication(this.props.params.appId, '2016-05-27', data)).done(() => {
            this.props.router.push(this.baseUri() + '/applications')
        })
    }
    render() {
        var appId = this.props.params.appId

        if (!this.props.loading.loaded || !this.props.currentApp || this.props.currentApp.tmlpRegistrationId != appId) {
            return <div>{this.props.loading.state}...</div>
        }
        var modelKey = APPLICATIONS_FORM_KEY
        return (
            <div>
                <h3>Edit Application</h3>
                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveApp.bind(this)}>
                    <SimpleField label="First Name" model={modelKey+'.firstName'} />
                    <SimpleField label="Last Name" model={modelKey+'.lastName'} />
                    <SimpleField label="Team Year" model={modelKey+'.teamYear'} />
                    <SimpleField label="Registered" model={modelKey+'.regDate'} />
                    <SimpleField label="Comment" model={modelKey+'.comment'} customField={true}>
                        <textarea className="form-control" rows="3"></textarea>
                    </SimpleField>
                    <div className="form-group">
                        <div className="col-sm-offset-2 col-sm-8">
                            <button className="btn btn-primary" type="submit">Save</button>
                        </div>
                    </div>
                    <div className="form-group">
                        <div className="col-sm-2">

                        </div>
                    </div>
                </Form>
            </div>
        )
    }
}

const mapStateToProps = (state) => state.submission.application

const connector = connect(mapStateToProps)

export const ApplicationsIndex = connector(ApplicationsIndexView)
export const ApplicationsEdit = connector(withRouter(ApplicationsEditView))
