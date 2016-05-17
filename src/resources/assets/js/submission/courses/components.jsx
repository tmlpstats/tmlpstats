import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'
import { bindActionCreators } from 'redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, AddOneLink } from '../../reusable/form_utils'
import { COURSES_FORM_KEY } from './reducers'
import { initializeCourses } from './actions'

class CoursesBase extends SubmissionBase {
    componentDidMount() {
        if (!this.props.loaded) {
            this.loadCourses()
        }
    }

    loadCourses() {
        return Api.Course.allForCenter({
            center: this.props.params.centerId
        }).done((data) => {
            this.props.initializeCourses(data)
        })
    }

    getCourseById(courseId) {
        return this.props.courses.find(
            (course) => courseId == course.id
        )
    }

    getCourseIndex(courseId) {
        var courses = this.props.courses
        for (var i = 0; i < courses.length; i++) {
            if (courses[i].id == courseId) {
                return i
            }
        }
        return null
    }
}

class CoursesIndexView extends CoursesBase {
    render() {
        var courses = []
        if (this.props.loaded) {
            var baseUri = this.baseUri()

            this.props.courses.forEach((courseData) => {

                var location = courseData.course.location
                if (!location) {
                    location = courseData.course.center.name
                }

                courses.push(
                    <tr key={courseData.id}>
                        <td><Link to={`${baseUri}/courses/edit/${courseData.id}`}>{courseData.course.startDate}</Link></td>
                        <td>{location}</td>
                        <td>{courseData.course.type}</td>
                    </tr>
                )
            })
        } else {
            courses.push(
                <tr key="loading"><td colSpan="3">Loading....</td></tr>
            )
        }

        return (
            <div>
                <h3>Manage Courses</h3>
                <AddOneLink link={`${baseUri}/courses/add`} />
                <table className="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>{courses}</tbody>
                </table>
            </div>
        );
    }
}

class CoursesEditView extends CoursesBase {
    saveCourse(data) {
        console.log("Saved with data", data)
        // TODO an AJAX call for the save event
        this.props.router.push(this.baseUri() + '/courses')
    }
    render() {
        if (!this.props.loaded) {
            return <div>loading...</div>
        }
        var courseIndex = this.getCourseIndex(this.props.params.courseId)
        if (courseIndex === null){
            return <div>Course {this.props.params.courseId} not found????</div>
        }
        var modelKey = COURSES_FORM_KEY + `[${courseIndex}]`
        return (
            <div>
                <h3>Edit Course</h3>
                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourse.bind(this)}>
                    <div className="panel panel-default">
                        <div className="panel-heading">Course Details</div>
                        <div className="panel-body">
                            <SimpleField label="Start Date" model={modelKey+'.course.startDate'} />
                            <SimpleField label="Type" model={modelKey+'.course.type'} />
                            <SimpleField label="Location" model={modelKey+'.course.location'} />
                        </div>
                    </div>

                    <div className="panel panel-default">
                        <div className="panel-heading">Quarter Starting</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.quarterStartingTer'} />
                            <SimpleField label="Standard Starts" model={modelKey+'.quarterStartingStandardStarts'} />
                            <SimpleField label="Transfer In" model={modelKey+'.quarterStartingXfer'} />
                        </div>
                    </div>

                    <div className="panel panel-default">
                        <div className="panel-heading">Current</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.currentTer'} />
                            <SimpleField label="Standard Starts" model={modelKey+'.currentStandardStarts'} />
                            <SimpleField label="Transfer In" model={modelKey+'.currentXfer'} />
                        </div>
                    </div>


                    <div className="panel panel-default">
                        <div className="panel-heading">Completion</div>
                        <div className="panel-body">
                            <SimpleField label="Standard Starts" model={modelKey+'.completeStandardStarts'} />
                            <SimpleField label="Potentials" model={modelKey+'.potentials'} />
                            <SimpleField label="Registrations" model={modelKey+'.registrations'} />
                        </div>
                    </div>


                    <div className="panel panel-default">
                        <div className="panel-heading">Guest Game</div>
                        <div className="panel-body">
                            <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} />
                            <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} />
                            <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} />
                            <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} />
                        </div>
                    </div>

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

class CoursesAddView extends CoursesBase {
    saveCourse(data) {
        console.log("Saved with data", data)
        // TODO an AJAX call for the save event
        this.props.router.push(this.baseUri() + '/courses')
    }
    render() {
        if (!this.props.loaded) {
            return <div>loading...</div>
        }

        var modelKey = COURSES_FORM_KEY + '.add'

        return (
            <div>
                <h3>Add Course</h3>
                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourse.bind(this)}>
                    <div className="panel panel-default">
                        <div className="panel-heading">Course Details</div>
                        <div className="panel-body">
                            <SimpleField label="Start Date" model={modelKey+'.course.startDate'} />
                            <SimpleField label="Type" model={modelKey+'.course.type'} />
                            <SimpleField label="Location" model={modelKey+'.course.location'} />
                        </div>
                    </div>

                    <div className="panel panel-default">
                        <div className="panel-heading">Quarter Starting</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.quarterStartingTer'} />
                            <SimpleField label="Standard Starts" model={modelKey+'.quarterStartingStandardStarts'} />
                            <SimpleField label="Transfer In" model={modelKey+'.quarterStartingXfer'} />
                        </div>
                    </div>

                    <div className="panel panel-default">
                        <div className="panel-heading">Current</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.currentTer'} />
                            <SimpleField label="Standard Starts" model={modelKey+'.currentStandardStarts'} />
                            <SimpleField label="Transfer In" model={modelKey+'.currentXfer'} />
                        </div>
                    </div>


                    <div className="panel panel-default">
                        <div className="panel-heading">Guest Game</div>
                        <div className="panel-body">
                            <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} />
                            <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} />
                            <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} />
                            <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} />
                        </div>
                    </div>

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

const mapStateToProps = (state) => state.submission.course

const mapDispatchToProps = (dispatch) => bindActionCreators({ initializeCourses }, dispatch)

const connector = connect(mapStateToProps, mapDispatchToProps)

export const CoursesIndex = connector(CoursesIndexView)
export const CoursesEdit = connector(withRouter(CoursesEditView))
export const CoursesAdd = connector(withRouter(CoursesAddView))
