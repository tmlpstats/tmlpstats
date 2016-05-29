import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField } from '../../reusable/form_utils'
import { APPLICATIONS_FORM_KEY } from './reducers'
import { loadApplications } from './actions'

class ApplicationsBase extends SubmissionBase {
    componentDidMount() {
        if (this.props.loading.state == 'new') {
            this.props.dispatch(loadApplications(this.props.params.centerId))
        }
    }

    getAppById(appId) {
        return this.props.applications.find(
            (app) => appId == app.tmlpRegistrationId
        )
    }

    getAppIndex(appId) {
        var apps = this.props.applications
        for (var i = 0; i < apps.length; i++) {
            if (apps[i].tmlpRegistrationId == appId) {
                return i
            }
        }
        return null
    }
}

class ApplicationsIndexView extends ApplicationsBase {
    render() {
        if (!this.props.loading.loaded) {
            return <div>Loading...</div>
        }
        var apps = []
        var baseUri = this.baseUri()

        this.props.applications.forEach((app) => {
            apps.push(
                <tr key={app.tmlpRegistrationId}>
                    <td><Link to={`${baseUri}/applications/edit/${app.tmlpRegistrationId}`}>{app.firstName} {app.lastName}</Link></td>
                    <td>{app.regDate}</td>
                </tr>
            )
        })
        return (
            <div>
                <h3>Manage Registrations</h3>
                <table className="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>{apps}</tbody>
                </table>
            </div>
        )
    }
}


class ApplicationsEditView extends ApplicationsBase {
    saveApp(data) {
        // TODO an AJAX call for the save event
        this.props.router.push(this.baseUri() + '/applications')
    }
    render() {
        if (!this.props.loading.loaded) {
            return <div>{this.props.loading.state}...</div>
        }
        var appIndex = this.getAppIndex(this.props.params.appId)
        if (appIndex === null){
            return <div>App {this.props.params.appId} not found????</div>
        }
        var modelKey = APPLICATIONS_FORM_KEY + `[${appIndex}]`
        return (
            <div>
                <h3>Edit Application</h3>
                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveApp.bind(this)}>
                    <SimpleField label="First Name" model={modelKey+'.firstName'} />
                    <SimpleField label="Last Name" model={modelKey+'.lastName'} />
                    <SimpleField label="Team Year" model={modelKey+'.teamYear'} />
                    <SimpleField label="" model={modelKey+'.regDate'} />
                    <SimpleField label="Comment" model={modelKey+'.comment'} customField={true}>
                        <textarea className="form-control" rows="3"></textarea>
                    </SimpleField>
                    <div className="form-group">
                        <div className="col-sm-offset-2 col-sm-8">
                            <button className="btn btn-primary" type="submit">Save</button>
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
