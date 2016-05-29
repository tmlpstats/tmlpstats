import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'
import { bindActionCreators } from 'redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, AddOneLink } from '../../reusable/form_utils'
import { COURSES_FORM_KEY } from './reducers'
import { initializeCourses } from './actions'

const courseTypeMap = {
    'CAP': 'Access to Power',
    'CPC': 'Power to Create'
}

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
            (course) => courseId == course.courseId
        )
    }

    getCourseIndex(courseId) {
        var courses = this.props.courses
        for (var i = 0; i < courses.length; i++) {
            if (courses[i].courseId == courseId) {
                return i
            }
        }
        return null
    }
}

class SaveButton extends React.Component {
    render() {
        if (this.props.hide) {
            return <div />
        }

        return (
            <div className="form-group">
                <div className={this.props.className}>
                    <button className="btn btn-primary" type="submit">Save</button>
                </div>
            </div>
        )
    }
}

class CoursesEditCourseView extends React.Component {
    saveCourse(data) {
        console.log("Saved course with data", data)

        Api.Course.update({
            course: data.courseId,
            data: {
                location: data.location,
                startDate: data.startDate,
                type: data.type
            }
        }).done((data) => {
            this.props.router.push(this.baseUri() + '/courses')
        })
    }
    render() {

        return (
            <div>
                <Form className="form-horizontal" model={this.props.model} onSubmit={this.saveCourse.bind(this)}>
                    <div className="panel panel-default">
                        <div className="panel-heading">Course Details</div>
                        <div className="panel-body">
                            <SimpleField label="Start Date" model={this.props.model+'.startDate'} />
                            <SimpleField label="Type" model={this.props.model+'.type'} customField={true}>
                                <select className="form-control">
                                    <option value="CAP">{courseTypeMap['CAP']}</option>
                                    <option value="CPC">{courseTypeMap['CPC']}</option>
                                </select>
                            </SimpleField>
                            <SimpleField label="Location" model={this.props.model+'.location'} />

                            <SaveButton className="col-sm-offset-2 col-sm-8" />
                        </div>
                    </div>
                </Form>
            </div>
        )
    }
}

class CoursesShowCourseView extends React.Component {
    render() {
        var course = this.props.course

        var location = course.location
        if (!location) {
            location = course.center.name
        }

        var type = courseTypeMap[course.type]
        var startDate = moment(course.startDate)

        return (
            <table className="table table-condensed no-border">
            <tbody>
                <tr>
                    <th style={{width: "8em"}}>Start Date</th>
                    <td>{startDate.format("MMM D, YYYY")}</td></tr>
                <tr>
                    <th>Type</th>
                    <td>{type}</td></tr>
                <tr>
                    <th>Location</th>
                    <td>{location}</td>
                </tr>
            </tbody>
            </table>
        )
    }
}

class CoursesCourseView extends React.Component {
    render() {
        return (
            <CoursesEditCourseView model={this.props.model} course={this.props.course} />
        )
    }
}

class CoursesIndexView extends CoursesBase {
    render() {
        var courses = []
        if (this.props.loaded) {
            var baseUri = this.baseUri()

            this.props.courses.forEach((courseData) => {

                var location = courseData.location
                if (!location) {
                    location = courseData.center.name
                }
                var type = courseTypeMap[courseData.type]

                var startDate = moment(courseData.startDate)
                var ter = courseData.currentTer || '-'
                var ss = courseData.currentStandardStarts || '-'
                var xfer = courseData.currentXfer || '-'

                courses.push(
                    <tr key={courseData.courseId}>
                        <td><Link to={`${baseUri}/courses/edit/${courseData.courseId}`}>{startDate.format("MMM D, YYYY")}</Link></td>
                        <td className="data-point">{type}</td>
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
                            <th style={{width: '8em'}}>Date</th>
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
    saveCourseData(data) {
        console.log("Saved course data with data", data)

        // Get the date for the week this data is for, not the week the original data was from
        var now = new Date()
        var reportingDate = new Date(data.statsReport.reportingDate)
        if (reportingDate < now) {
            reportingDate.setDate(reportingDate.getDate()+7);
        }

        var fields = [
            'quarterStartTer',
            'quarterStartStandardStarts',
            'quarterStartXfer',
            'currentTer',
            'currentStandardStarts',
            'currentXfer',
            'completedStandardStarts',
            'potentials',
            'registrations',
            'guestsPromised',
            'guestsInvited',
            'guestsConfirmed',
            'guestsAttended',
        ]

        var weekData = {}

        fields.forEach((field) => {
            weekData[field] = data[field]
        })

        Api.Course.setWeekData({
            course: data.courseId,
            reportingDate: reportingDate,
            data: weekData
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

        var course = this.props.courses[courseIndex]
        var now = moment.utc()
        var startDate = moment.utc(course.startDate)
        var courseReportDate = moment.utc(startDate).add(6, 'days')

        var dayDiff = now.diff(courseReportDate, 'days')

        var currentDisabled = false
        var guestsDisabled = false
        var qstartDisabled = true
        var completionDisabled = true

        if (dayDiff === 0) {
            // We're on the week after the course
            completionDisabled = false
        } else if (dayDiff > 0) {
            // The course happened more than 7 days ago
            currentDisabled = true
            guestsDisabled = true
        }

        return (
            <div>
                <h3>Edit Course</h3>
                <CoursesCourseView model={modelKey} course={course} />

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourseData.bind(this)}>

                <div className="row">
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Current</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.currentTer'} labelClass="col-md-4" divClass="col-md-6" disabled={currentDisabled} />
                            <SimpleField label="Standard Starts" model={modelKey+'.currentStandardStarts'} labelClass="col-md-4" divClass="col-md-6" disabled={currentDisabled} />
                            <SimpleField label="Transfer In" model={modelKey+'.currentXfer'} labelClass="col-md-4" divClass="col-md-6" disabled={currentDisabled} />

                            <SaveButton hide={currentDisabled} className="col-sm-offset-4 col-sm-6" />
                        </div>
                    </div>
                </div>
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Guest Game</div>
                        <div className="panel-body">
                            <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} labelClass="col-md-4" divClass="col-md-6" disabled={guestsDisabled} />
                            <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} labelClass="col-md-4" divClass="col-md-6" disabled={guestsDisabled} />
                            <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} labelClass="col-md-4" divClass="col-md-6" disabled={guestsDisabled} />
                            <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} labelClass="col-md-4" divClass="col-md-6" disabled={guestsDisabled} />

                            <SaveButton hide={guestsDisabled} className="col-sm-offset-4 col-sm-6" />
                        </div>
                    </div>
                </div>
                </div>


                <div className="row">
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Completion</div>
                        <div className="panel-body">
                            <SimpleField label="Standard Starts" model={modelKey+'.completedStandardStarts'} labelClass="col-md-4" divClass="col-md-6" disabled={completionDisabled} />
                            <SimpleField label="Potentials" model={modelKey+'.potentials'} labelClass="col-md-4" divClass="col-md-6" disabled={completionDisabled} />
                            <SimpleField label="Registrations" model={modelKey+'.registrations'} labelClass="col-md-4" divClass="col-md-6" disabled={completionDisabled} />

                            <SaveButton hide={completionDisabled} className="col-sm-offset-4 col-sm-6" />
                        </div>
                    </div>
                </div>
                <div className="col-md-6">
                    <div className="panel panel-default">
                        <div className="panel-heading">Quarter Starting</div>
                        <div className="panel-body">
                            <SimpleField label="Total Ever Registered" model={modelKey+'.quarterStartTer'} labelClass="col-md-4" divClass="col-md-6" disabled={qstartDisabled} />
                            <SimpleField label="Standard Starts" model={modelKey+'.quarterStartStandardStarts'} labelClass="col-md-4" divClass="col-md-6" disabled={qstartDisabled} />
                            <SimpleField label="Transfer In" model={modelKey+'.quarterStartXfer'} labelClass="col-md-4" divClass="col-md-6" disabled={qstartDisabled} />

                            <SaveButton hide={qstartDisabled} className="col-sm-offset-4 col-sm-6" />
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
                location: data.location,
                startDate: data.startDate,
                type: data.type
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
                    <SimpleField label="Start Date" model={modelKey+'.startDate'} />
                    <SimpleField label="Type" model={modelKey+'.type'} customField={true}>
                        <select className="form-control">
                            <option value="CAP">Access to Power</option>
                            <option value="CPC">Power to Create</option>
                        </select>
                    </SimpleField>
                    <SimpleField label="Location" model={modelKey+'.location'} />

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
