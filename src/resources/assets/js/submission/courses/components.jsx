// POLYFILL
import { Promise, objectAssign } from '../../reusable/ponyfill'

// NORMAL CODE
import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { Field } from 'react-redux-form'

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
                location = this.props.lookups.center.name
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

class _EditCreate extends CoursesBase {
    render() {
        const modelKey = COURSES_FORM_KEY

        var currentDisabled = false
        var guestsDisabled = false
        var qstartDisabled = this.defaultQStartDisabled()
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

        var guestGameFields = this.getGuestGameFields(modelKey, guestsDisabled, completionDisabled)
        var completionFields = this.getCompletionFields(modelKey, completionDisabled)
        var currentBalanceFields = this.getCurrentBalanceFields(modelKey, qstartDisabled)

        return (
            <div>
                <h3>{this.title()}</h3>

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourseData.bind(this)}>

                <div className="row">
                <div className="col-md-12">
                    <SimpleField label="Type" model={modelKey+'.type'} customField={true} labelClass="col-md-2" divClass="col-md-4">
                        <select className="form-control">
                            <option value="CAP">{courseTypeMap['CAP']}</option>
                            <option value="CPC">{courseTypeMap['CPC']}</option>
                        </select>
                    </SimpleField>
                    <SimpleField label="Start Date" model={modelKey+'.startDate'} labelClass="col-md-2" divClass="col-md-4"/>
                    <SimpleField label="Location" model={modelKey+'.location'} labelClass="col-md-2" divClass="col-md-4"/>
                </div>
                </div>

                {currentBalanceFields}

                {guestGameFields}

                {completionFields}

                <div className="row">
                <div className="col-md-12">
                    <div className="form-group">
                        <div className="col-md-offset-2 col-md-12">
                            <LoadStateFlip loadState={this.props.saveCourse}>
                                <button className="btn btn-primary" type="submit">Save</button>
                            </LoadStateFlip>
                        </div>
                    </div>
                </div>
                </div>
                </Form>
            </div>
        )
    }
    // saveCourseData for now is the same between edit and create flows
    saveCourseData(data) {
        this.props.dispatch(saveCourse(this.props.params.centerId, this.reportingDateString(), data)).done((result) => {
            if (result.success && result.storedId) {
                data = objectAssign({}, data, {id: result.storedId})
                this.props.dispatch(coursesCollection.replaceItem(data))
            }
            this.props.router.push(this.baseUri() + '/courses')
        })
    }
    defaultQStartDisabled() {
        return true
    }
    getGuestGameFields(modelKey, guestsDisabled, completionDisabled) {
        var guestGameFields = ""
        if (!guestsDisabled) {
            if (!completionDisabled) {
                guestGameFields = (
                    <div className="row">
                    <div className="col-md-12">
                        <h4>Guest Game</h4>
                        <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} labelClass="col-md-2" divClass="col-md-2" />
                        <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} labelClass="col-md-2" divClass="col-md-2" />
                        <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} labelClass="col-md-2" divClass="col-md-2" />
                        <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} labelClass="col-md-2" divClass="col-md-2" />
                    </div>
                    </div>
                )
            } else {
                guestGameFields = (
                    <div className="row">
                    <div className="col-md-12">
                        <h4>Guest Game</h4>
                        <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} labelClass="col-md-2" divClass="col-md-2" />
                        <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} labelClass="col-md-2" divClass="col-md-2" />
                        <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} labelClass="col-md-2" divClass="col-md-2" />
                    </div>
                    </div>
                )
            }
        }
        return guestGameFields
    }
    getCompletionFields(modelKey, completionDisabled) {
        var completionFields = ""
        if (!completionDisabled) {
            completionFields = (
                <div className="row">
                <div className="col-md-12">
                    <h4>Completion</h4>
                    <SimpleField label="Standard Starts" model={modelKey+'.completedStandardStarts'} labelClass="col-md-2" divClass="col-md-2" />
                    <SimpleField label="Potentials" model={modelKey+'.potentials'} labelClass="col-md-2" divClass="col-md-2" />
                    <SimpleField label="Registrations" model={modelKey+'.registrations'} labelClass="col-md-2" divClass="col-md-2" />
                </div>
                </div>
            )
        }
        return completionFields
    }
    getCurrentBalanceFields(modelKey, qstartDisabled) {

        var rows = []
        var rowData = [
            {name: "Total Ever Registered", fieldSuffix: "Ter"},
            {name: "Standard Starts", fieldSuffix: "StandardStarts"},
            {name: "Transfer In", fieldSuffix: "Xfer"},
        ]
        rowData.forEach((row) => {
            var qstartModelStr = modelKey + '.quarterStart' + row.fieldSuffix
            var currentModelStr = modelKey + '.current' + row.fieldSuffix

            rows.push(
                <div className="row" key={row.fieldSuffix}>
                <div className="col-md-12">
                    <div className="form-group">
                        <label className="col-md-2 control-label">{row.name}</label>
                        <div className="col-md-2">
                            <Field model={qstartModelStr}><input type="text" className="form-control" disabled={qstartDisabled} /></Field>
                        </div>
                        <div className="col-md-2">
                            <Field model={currentModelStr}><input type="text" className="form-control" /></Field>
                        </div>
                    </div>
                </div>
                </div>
            )
        })

        return (
            <div className="row">
            <div className="col-md-12">
                <h4>Course Balance</h4>
                <div className="row">
                <div className="col-md-12">
                    <div className="form-group">
                        <div className="col-md-2 col-md-offset-2">
                            <label>Quarter Start</label>
                        </div>
                        <div className="col-md-2">
                            <label>Current</label>
                        </div>
                    </div>
                </div>
                </div>

                {rows}
            </div>
            </div>
        )
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
                this.props.dispatch(chooseCourse('', {
                    startDate: '',
                    type: 'CAP',
                    quarterStartTer: 0,
                    quarterStartStandardStarts: 0,
                    quarterStartXfer: 0
                }))
            }
        })
    }

    title() {
        return 'Create Course'
    }

    defaultQStartDisabled() {
        return false
    }

    render() {
        return super.render()
    }
}

const mapStateToProps = (state) => {
    return objectAssign({lookups: state.submission.core.lookups}, state.submission.courses)
}
const connector = connect(mapStateToProps)

export const CoursesIndex = connector(CoursesIndexView)
export const CoursesEdit = connector(withRouter(CoursesEditView))
export const CoursesAdd = connector(withRouter(CoursesAddView))
