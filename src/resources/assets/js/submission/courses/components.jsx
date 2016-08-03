// POLYFILL
import { Promise, objectAssign } from '../../reusable/ponyfill'

// NORMAL CODE
import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, SimpleSelect, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, LoadStateFlip } from '../../reusable/ui_basic'

import { COURSES_FORM_KEY } from './reducers'
import { coursesSorts, coursesCollection, courseTypeMap } from './data'
import { loadCourses, saveCourse, chooseCourse } from './actions'
import CourseStatus from './CourseStatus'

class CoursesBase extends SubmissionBase {
    componentDidMount() {
        this.setupCourses()
    }

    setupCourses() {
        if (this.props.loading.state == 'new') {
            return this.props.dispatch(loadCourses(this.props.params.centerId, this.reportingDateString()))
        }
        return Promise.resolve(null)
    }

    getCourseById(courseId) {
        return this.props.courses.collection[courseId]
    }
}

class CoursesIndexView extends CoursesBase {
    render() {
        if (!this.props.loading.loaded) {
            return this.renderBasicLoading()
        }
        var changeSort = (newSort) => this.props.dispatch(coursesCollection.changeSortCriteria(newSort))
        var courses = []
        var baseUri = this.baseUri()

        coursesCollection.iterItems(this.props.courses, (course, key) => {
            var location = course.location
            if (!location) {
                location = course.center.name
            }
            var type = courseTypeMap[course.type]

            var startDate = moment(course.startDate)
            var ter = course.currentTer || '-'
            var ss = course.currentStandardStarts || '-'
            var xfer = course.currentXfer || '-'

            courses.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/courses/edit/${key}`}>{startDate.format("MMM D, YYYY")}</Link></td>
                    <td className="data-point">{type}</td>
                    <td>{location}</td>
                    <td className="data-point">{ter}</td>
                    <td className="data-point">{ss}</td>
                    <td className="data-point">{xfer}</td>
                </tr>
            )
        })
        return (
            <div>
                <h3>Manage Courses</h3>
                <ModeSelectButtons items={coursesSorts} current={this.props.courses.meta.sort_by}
                                   onClick={changeSort} ariaGroupDesc="Sort Preferences" />
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
                <AddOneLink link={`${baseUri}/courses/add`} />
            </div>
        )
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
                    <LoadStateFlip loadState={this.props.saveCourse}>
                        <button className="btn btn-primary" type="submit">Save</button>
                    </LoadStateFlip>
                </div>
            </div>
        )
    }
}

class _EditCreate extends CoursesBase {
    render() {
        const modelKey = COURSES_FORM_KEY

        var currentDisabled = false
        var guestsDisabled = false
        var qstartDisabled = true
        var completionDisabled = true

        if (this.props.currentCourse) {
            var course = this.props.currentCourse

            var now = moment.utc()
            var startDate = moment.utc(course.startDate)
            var courseReportDate = moment.utc(startDate).add(6, 'days')

            var dayDiff = now.diff(courseReportDate, 'days')

            if (dayDiff === 0) {
                // We're on the week after the course
                completionDisabled = false
            } else if (dayDiff > 0) {
                // The course happened more than 7 days ago
                currentDisabled = true
                guestsDisabled = true
            }
        }

                // <CoursesCourseView model={modelKey} course={course} />
        return (
            <div>
                <h3>{this.title()}</h3>

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourse.bind(this)}>

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
    // saveCourse for now is the same between edit and create flows
    saveCourse(data) {
        this.props.dispatch(saveCourse(this.props.params.centerId, this.reportingDateString(), data)).done((result) => {
            if (result.success && result.storedId) {
                data = objectAssign({}, data, {id: result.storedId})
                this.props.dispatch(coursesCollection.replaceItem(data))
            }
            this.props.router.push(this.baseUri() + '/courses')
        })
    }
}

class CoursesEditView extends _EditCreate {
    componentDidMount() {
        super.setupCourses().then(() => {
            var courseId = this.props.params.courseId
            if (!this.props.currentCourse || this.props.currentCourse.id != courseId) {
                this.props.dispatch(chooseCourse(courseId, this.getCourseById(courseId)))
            }
        })
    }

    title() {
        return 'Edit Course'
    }

    render() {
        const courseId = this.props.params.courseId

        if (!this.props.loading.loaded || !this.props.currentCourse || this.props.currentCourse.id != courseId) {
            return <div>{this.props.loading.state}...</div>
        }
        return super.render()
    }

}

class CoursesAddView extends _EditCreate {
    componentDidMount() {
        super.setupCourses().then(() => {
            if (this.props.currentCourse) {
                this.props.dispatch(chooseCourse('', {startDate: '', type: ''}))
            }
        })
    }

    title() {
        return 'Create Course'
    }

    render() {
        return super.render()
    }
}




// class CoursesEditCourseView extends React.Component {
//     saveCourse(data) {
//         console.log("Saved course with data", data)

//         Api.Course.update({
//             course: data.courseId,
//             data: {
//                 location: data.location,
//                 startDate: data.startDate,
//                 type: data.type
//             }
//         }).done((data) => {
//             this.props.router.push(this.baseUri() + '/courses')
//         })
//     }
//     render() {

//         return (
//             <div>
//                 <Form className="form-horizontal" model={this.props.model} onSubmit={this.saveCourse.bind(this)}>
//                     <div className="panel panel-default">
//                         <div className="panel-heading">Course Details</div>
//                         <div className="panel-body">
//                             <SimpleField label="Start Date" model={this.props.model+'.startDate'} />
//                             <SimpleField label="Type" model={this.props.model+'.type'} customField={true}>
//                                 <select className="form-control">
//                                     <option value="CAP">{courseTypeMap['CAP']}</option>
//                                     <option value="CPC">{courseTypeMap['CPC']}</option>
//                                 </select>
//                             </SimpleField>
//                             <SimpleField label="Location" model={this.props.model+'.location'} />

//                             <SaveButton className="col-sm-offset-2 col-sm-8" />
//                         </div>
//                     </div>
//                 </Form>
//             </div>
//         )
//     }
// }

// class CoursesShowCourseView extends React.Component {
//     render() {
//         var course = this.props.course

//         var location = course.location
//         if (!location) {
//             location = course.center.name
//         }

//         var type = courseTypeMap[course.type]
//         var startDate = moment(course.startDate)

//         return (
//             <table className="table table-condensed no-border">
//             <tbody>
//                 <tr>
//                     <th style={{width: "8em"}}>Start Date</th>
//                     <td>{startDate.format("MMM D, YYYY")}</td></tr>
//                 <tr>
//                     <th>Type</th>
//                     <td>{type}</td></tr>
//                 <tr>
//                     <th>Location</th>
//                     <td>{location}</td>
//                 </tr>
//             </tbody>
//             </table>
//         )
//     }
// }

// class CoursesCourseView extends React.Component {
//     render() {
//         return (
//             <CoursesEditCourseView model={this.props.model} course={this.props.course} />
//         )
//     }
// }

const mapStateToProps = (state) => {
    return objectAssign({lookups: state.submission.core.lookups}, state.submission.courses)
}
const connector = connect(mapStateToProps)

export const CoursesIndex = connector(CoursesIndexView)
export const CoursesEdit = connector(withRouter(CoursesEditView))
export const CoursesAdd = connector(withRouter(CoursesAddView))
