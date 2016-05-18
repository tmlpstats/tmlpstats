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

                var date = moment(courseData.course.startDate)
                var ter = courseData.currentTer || '-'
                var ss = courseData.currentStandardStarts || '-'
                var xfer = courseData.currentXfer || '-'

                courses.push(
                    <tr key={courseData.id}>
                        <td><Link to={`${baseUri}/courses/edit/${courseData.id}`}>{date.format("MMM D, YYYY")}</Link></td>
                        <td className="data-point">{courseData.course.type}</td>
                        <td>{location}</td>
                        <td className="data-point">{ter}</td>
                        <td className="data-point">{ss}</td>
                        <td className="data-point">{xfer}</td>
                    </tr>
                )
            })
        } else {
            courses.push(
                <tr key="courses-loading"><td colSpan="3">Loading....</td></tr>
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
                            <th className="data-point">Type</th>
                            <th>Location</th>
                            <th className="data-point">Total Ever Registered</th>
                            <th className="data-point">Standard Starts</th>
                            <th className="data-point">Transfered In</th>
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
        console.log("Saved course with data", data)

        Api.Course.update({
            course: data.course.id,
            data: {
                location: data.course.location,
                startDate: data.course.startDate,
                type: data.course.type
            }
        }).done((data) => {
            this.props.router.push(this.baseUri() + '/courses')
        })
    }
    saveCourseData(data) {
        console.log("Saved course data with data", data)

        Api.Course.setWeekData({
            course: data.course.id,
            reportingDate: data.statsReport.reportingDate,
            data: {
                quarterStartTer: data.quarterStartTer,
                quarterStartStandardStarts: data.quarterStartStandardStarts,
                quarterStartXfer: data.quarterStartXfer,
                currentTer: data.currentTer,
                currentStandardStarts: data.currentStandardStarts,
                currentXfer: data.currentXfer,
                completedStandardStarts: data.completedStandardStarts,
                potentials: data.potentials,
                registrations: data.registrations,
                guestsPromised: data.guestsPromised,
                guestsInvited: data.guestsInvited,
                guestsConfirmed: data.guestsConfirmed,
                guestsAttended: data.guestsAttended
            }
        }).done((data) => {
            this.props.router.push(this.baseUri() + '/courses')
        })
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
                            <SimpleField label="Type" model={modelKey+'.course.type'} customField={true}>
                                <select className="form-control">
                                    <option value="CAP">Access to Power</option>
                                    <option value="CPC">Power to Create</option>
                                </select>
                            </SimpleField>
                            <SimpleField label="Location" model={modelKey+'.course.location'} />

                            <div className="form-group">
                                <div className="col-sm-offset-2 col-sm-8">
                                    <button className="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Form>

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourseData.bind(this)}>

                <div className="row">
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Quarter Starting</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.quarterStartTer'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Standard Starts" model={modelKey+'.quarterStartStandardStarts'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Transfer In" model={modelKey+'.quarterStartXfer'} labelSize="col-md-4" divSize="col-md-6" />

                            <div className="form-group">
                                <div className="col-sm-offset-5 col-sm-5">
                                    <button className="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Current</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.currentTer'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Standard Starts" model={modelKey+'.currentStandardStarts'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Transfer In" model={modelKey+'.currentXfer'} labelSize="col-md-4" divSize="col-md-6" />

                            <div className="form-group">
                                <div className="col-sm-offset-5 col-sm-5">
                                    <button className="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>


                <div className="row">
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Completion</div>
                        <div className="panel-body">
                            <SimpleField label="Standard Starts" model={modelKey+'.completedStandardStarts'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Potentials" model={modelKey+'.potentials'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Registrations" model={modelKey+'.registrations'} labelSize="col-md-4" divSize="col-md-6" />

                            <div className="form-group">
                                <div className="col-sm-offset-5 col-sm-5">
                                    <button className="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Guest Game</div>
                        <div className="panel-body">
                            <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} labelSize="col-md-4" divSize="col-md-6" />
                            <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} labelSize="col-md-4" divSize="col-md-6" />

                            <div className="form-group">
                                <div className="col-sm-offset-5 col-sm-5">
                                    <button className="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                </Form>
            </div>
        )
    }
}

class CoursesAddView extends CoursesBase {
    saveCourse(data) {
        console.log("Created course with data", data)

        Api.Course.create({
            data: {
                center: this.props.params.centerId,
                location: data.course.location,
                startDate: data.course.startDate,
                type: data.course.type
            }
        }).done((data) => {
            this.loadCourses();
            this.props.router.push(this.baseUri() + '/courses')
        })
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
                    <SimpleField label="Start Date" model={modelKey+'.course.startDate'} />
                    <SimpleField label="Type" model={modelKey+'.course.type'} customField={true}>
                        <select className="form-control">
                            <option value="CAP">Access to Power</option>
                            <option value="CPC">Power to Create</option>
                        </select>
                    </SimpleField>
                    <SimpleField label="Location" model={modelKey+'.course.location'} />

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
