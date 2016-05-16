import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'
import { bindActionCreators } from 'redux'
import { Form, Field, actions as formActions } from 'react-redux-form'

import { SubmissionBase, React} from './Base'
import { SimpleField } from '../reusable/FormUtils'
import { initializeApplications, APPLICATIONS_FORM_KEY } from '../../states/SubmissionStates'

class ApplicationsBase extends SubmissionBase {
    componentDidMount() {
        if (!this.props.loaded) {
            this.loadApplications()
        }
    }

    loadApplications() {
        return Api.Application.allForCenter({
            center: this.props.params.centerId
        }).done((data) => {
            this.props.initializeApplications(data)
        })
    }

    getAppById(appId) {
        return this.props.applications.find(
            (app) => appId == app.id
        )
    }

    getAppIndex(appId) {
        var apps = this.props.applications
        for (var i = 0; i < apps.length; i++) {
            if (apps[i].id == appId) {
                return i
            }
        }
        return null
    }
}

class BareApplicationsIndex extends ApplicationsBase {
    render() {
        var apps = []
        if (this.props.loaded) {
            var baseUri = this.baseUri()

            this.props.applications.forEach((app) => {
                var person = app.registration.person
                apps.push(
                    <tr key={app.id}>
                        <td><Link to={`${baseUri}/applications/edit/${app.id}`}>{person.firstName} {person.lastName}</Link></td>
                        <td>{app.registration.regDate}</td>
                    </tr>
                )
            })
        } else {
            apps.push(
                <tr><td colspan="3">Loading....</td></tr>
            )
        }

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
        );
    }
}


class BareApplicationsEdit extends ApplicationsBase {
    saveApp(data) {
        console.log("Saved with data", data)
        // TODO an AJAX call for the save event
        this.props.router.push(this.baseUri() + '/applications')
    }
    render() {
        if (!this.props.loaded) {
            return <div>loading...</div>
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
                    <SimpleField label="First Name" model={modelKey+'.registration.person.firstName'} />
                    <SimpleField label="Last Name" model={modelKey+'.registration.person.lastName'} />
                    <SimpleField label="Team Year" model={modelKey+'.registration.teamYear'} />
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

const mapDispatchToProps = (dispatch) => bindActionCreators({ initializeApplications }, dispatch)

const connector = connect(mapStateToProps, mapDispatchToProps)

export const ApplicationsIndex = connector(BareApplicationsIndex)
export const ApplicationsEdit = connector(withRouter(BareApplicationsEdit))
