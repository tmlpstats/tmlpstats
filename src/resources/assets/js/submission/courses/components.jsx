// POLYFILL
import { objectAssign } from '../../reusable/ponyfill'

// NORMAL CODE
import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'
import { Field } from 'react-redux-form'
import moment from 'moment'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, SimpleDateInput, AddOneLink } from '../../reusable/form_utils'
import { collectionSortSelector, SORT_BY } from '../../reusable/sort-helpers'
import { delayDispatch } from '../../reusable/dispatch'
import { ModeSelectButtons, ButtonStateFlip, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'

import { COURSES_FORM_KEY } from './reducers'
import { coursesSorts, coursesCollection, courseTypeMap } from './data'
import { loadCourses, saveCourse, chooseCourse } from './actions'

const getSortedCourses = collectionSortSelector(coursesSorts)


class CoursesBase extends SubmissionBase {
    constructor(props) {
        super(props)
        this.checkLoading()
    }

    checkLoading() {
        const { loading, params } = this.props
        if (loading.state == 'new') {
            const { centerId, reportingDate } = params
            delayDispatch(this, loadCourses(centerId, reportingDate))
        }
        return (loading.state == 'loaded')
    }

    getCourseById(courseId) {
        return this.props.courses.data[courseId]
    }

    getCourseLocation(course) {
        let location = course.location
        if (!location) {
            location = this.props.lookups.center.name
        }

        return location
    }
}

class CoursesIndexView extends CoursesBase {
    renderCompleteCourseTable(courses) {
        if (!courses.length) {
            return <div />
        }

        const baseUri = this.baseUri()
        const completed = []

        courses.forEach((course) => {
            const startDate = moment(course.startDate)
            completed.push(
                <tr key={course.id}>
                    <td><Link to={`${baseUri}/courses/edit/${course.id}`}>{startDate.format('MMM D, YYYY')}</Link></td>
                    <td className="data-point">{courseTypeMap[course.type]}</td>
                    <td>{this.getCourseLocation(course)}</td>
                    <td className="data-point">{course.currentTer}</td>
                    <td className="data-point">{course.completedStandardStarts}</td>
                    <td className="data-point">{course.registrations}</td>
                    <td className="data-point">{course.guestsConfirmed || '-'}</td>
                </tr>
            )
        })

        return (
            <div>
            <br/>
            <h4>Past Courses</h4>
            <table className="table">
                <thead>
                    <tr>
                        <th style={{width: '8em'}}>Date</th>
                        <th className="data-point">Type</th>
                        <th>Location</th>
                        <th className="data-point">Total Ever Registered</th>
                        <th className="data-point">Completed Standard Starts</th>
                        <th className="data-point">Registered</th>
                        <th className="data-point">Guests Attended</th>
                    </tr>
                </thead>
                <tbody>{completed}</tbody>
            </table>
            </div>
        )
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading(this.props.loading)
        }
        const changeSort = (newSort) => this.props.dispatch(coursesCollection.setMeta(SORT_BY, newSort))
        const courses = []
        const completed = []
        const baseUri = this.baseUri()

        getSortedCourses(this.props.courses).forEach((course) => {
            const key = course.id
            const location = this.getCourseLocation(course)
            const startDate = moment(course.startDate)
            const qss = course.quarterStartStandardStarts || '-'
            const ss = course.currentStandardStarts || '-'
            const guestsConfirmed = course.guestsConfirmed || '-'

            const now = moment()
            if (now.diff(startDate, 'days') > 7) {
                completed.push(course)
                return
            }

            courses.push(
                <tr key={key}>
                    <td><Link to={`${baseUri}/courses/edit/${key}`}>{startDate.format('MMM D, YYYY')}</Link></td>
                    <td className="data-point">{courseTypeMap[course.type]}</td>
                    <td>{location}</td>
                    <td className="data-point">{qss}</td>
                    <td className="data-point">{ss}</td>
                    <td className="data-point">{guestsConfirmed}</td>
                </tr>
            )
        })
        return (
            <div>
                <h3>Manage Courses</h3>
                <ModeSelectButtons items={coursesSorts} current={this.props.courses.meta.get(SORT_BY)}
                                   onClick={changeSort} ariaGroupDesc="Sort Preferences" />
                <table className="table">
                    <thead>
                        <tr>
                            <th style={{width: '8em'}}>Date</th>
                            <th className="data-point">Type</th>
                            <th>Location</th>
                            <th className="data-point">Quarter Starting Standard Starts</th>
                            <th className="data-point">Standard Starts</th>
                            <th className="data-point">Guests Confirmed</th>
                        </tr>
                    </thead>
                    <tbody>{courses}</tbody>
                </table>
                <AddOneLink link={`${baseUri}/courses/add`} />
                {this.renderCompleteCourseTable(completed)}
            </div>
        )
    }
}

class _EditCreate extends CoursesBase {
    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading(this.props.loading)
        }

        const modelKey = COURSES_FORM_KEY

        let currentState = 'visible'
        let guestsState = 'visible'
        let qstartState = 'visible'
        let completionState = 'hidden'

        let course = undefined
        if (this.props.currentCourse && this.props.currentCourse.meta) {
            course = this.props.currentCourse

            if (course.meta.canEditCompletion) {
                completionState = 'visible'
            } else if (course.meta.isPastCourse) {
                completionState = 'disabled'
            }

            if (!course.meta.canEditCurrent) {
                currentState = 'disabled'
            }

            if (!course.meta.canEditGuestGame) {
                guestsState = 'disabled'
            }

            if (!course.meta.canEditQuarterStart) {
                qstartState = 'disabled'
            }
        }

        const guestGameFields = this.getGuestGameFields(modelKey, guestsState, completionState)
        const completionFields = this.getCompletionFields(modelKey, completionState)
        const currentBalanceFields = this.getCurrentBalanceFields(modelKey, qstartState, currentState)

        let messages = []
        if (course && course.id) {
            messages = this.props.messages[course.id]
        } else if (this.isNewCourse() && this.props.messages['create']) {
            messages = this.props.messages['create']
        }

        return (
            <div>
                <h3>{this.title()}</h3>

                <MessagesComponent messages={messages} />

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveCourseData.bind(this)}>

                <div className="row">
                <div className="col-md-12">
                    <SimpleField label="Type" model={modelKey+'.type'} customField={true} labelClass="col-md-2" divClass="col-md-4" required={true} >
                        <select className="form-control">
                            <option value="CAP">{courseTypeMap['CAP']}</option>
                            <option value="CPC">{courseTypeMap['CPC']}</option>
                        </select>
                    </SimpleField>
                    <SimpleDateInput label="Start Date" model={modelKey+'.startDate'} labelClass="col-md-2" divClass="col-md-4" required={true} />
                    <SimpleField label="Location" model={modelKey+'.location'} labelClass="col-md-2" divClass="col-md-4"/>
                </div>
                </div>

                {currentBalanceFields}

                {guestGameFields}

                {completionFields}

                <ButtonStateFlip loadState={this.props.saveCourse} offset='col-sm-offset-2 col-sm-8' wrapGroup={true}>Save</ButtonStateFlip>
                </Form>
            </div>
        )
    }
    // saveCourseData for now is the same between edit and create flows
    saveCourseData(data) {
        this.props.dispatch(saveCourse(this.props.params.centerId, this.reportingDateString(), data)).then((result) => {
            if (!result) {
                return
            }

            if (result.messages && result.messages.length) {
                scrollIntoView('react-routed-flow')

                // Redirect to edit view if there are warning messages
                if (this.isNewCourse() && result.valid) {
                    this.props.router.push(this.baseUri() + '/courses/edit/' + result.storedId)
                }
            } else if (result.valid) {
                this.props.router.push(this.baseUri() + '/courses')
            }

            this.props.dispatch(chooseCourse(this.getCourseById(result.storedId)))
        })
    }

    // Return true if this is a new app
    isNewCourse() {
        const { currentCourse } = this.props
        return (!currentCourse.id || parseInt(currentCourse.id) < 0)
    }

    getGuestGameFields(modelKey, guestsState, completionState) {
        let guestGameFields = ''
        if (guestsState != 'hidden') {
            if (completionState != 'hidden') {
                guestGameFields = (
                    <div className="row">
                    <div className="col-md-12">
                        <h4>Guest Game</h4>
                        <SimpleField label="Guests Promised" model={modelKey+'.guestsPromised'} labelClass="col-md-2" divClass="col-md-2" disabled={guestsState == 'disabled'} />
                        <SimpleField label="Guests Invited" model={modelKey+'.guestsInvited'} labelClass="col-md-2" divClass="col-md-2" disabled={guestsState == 'disabled'} />
                        <SimpleField label="Guests Confirmed" model={modelKey+'.guestsConfirmed'} labelClass="col-md-2" divClass="col-md-2" disabled={guestsState == 'disabled'} />
                        <SimpleField label="Guests Attended" model={modelKey+'.guestsAttended'} labelClass="col-md-2" divClass="col-md-2" disabled={guestsState == 'disabled'} />
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
    getCompletionFields(modelKey, completionState) {
        let completionFields = ''

        let required = false
        if (completionState != 'disabled') {
            required = true
        }

        if (completionState != 'hidden') {
            completionFields = (
                <div className="row">
                <div className="col-md-12">
                    <h4>Completion</h4>
                    <SimpleField label="Completed Standard Starts" model={modelKey+'.completedStandardStarts'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} required={required} />
                    <SimpleField label="Potentials" model={modelKey+'.potentials'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} required={required} />
                    <SimpleField label="Registrations" model={modelKey+'.registrations'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} required={required} />
                </div>
                </div>
            )
        }
        return completionFields
    }
    getCurrentBalanceFields(modelKey, qstartState, currentState) {

        const rows = []
        const rowData = [
            {name: 'Total Ever Registered', fieldSuffix: 'Ter'},
            {name: 'Standard Starts', fieldSuffix: 'StandardStarts'},
            {name: 'Transfer In', fieldSuffix: 'Xfer'},
        ]
        rowData.forEach((row) => {
            const qstartModelStr = modelKey + '.quarterStart' + row.fieldSuffix
            const currentModelStr = modelKey + '.current' + row.fieldSuffix

            let requiredClass = ''
            if (currentState != 'disabled') {
                requiredClass = 'required'
            }

            rows.push(
                <div className="row" key={row.fieldSuffix}>
                <div className="col-md-12">
                    <div className={requiredClass + ' form-group'}>
                        <label className="col-md-2 control-label">{row.name}</label>
                        <div className="col-md-2">
                            <Field model={qstartModelStr}><input type="text" className="form-control" disabled={qstartState == 'disabled'} /></Field>
                        </div>
                        <div className="col-md-2">
                            <Field model={currentModelStr}><input type="text" className="form-control" disabled={currentState == 'disabled'} /></Field>
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
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentCourse, params: { courseId } } = this.props
        if (!currentCourse || currentCourse.id != courseId) {
            let course = this.getCourseById(courseId)
            if (course) {
                delayDispatch(this, chooseCourse(course))
                return false
            }
        }
        return true
    }

    title() {
        return 'Edit Course'
    }
}

class CoursesAddView extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentCourse } = this.props
        if (!currentCourse || currentCourse.id) {
            const blankCourse = {
                startDate: '',
                type: 'CAP',
                quarterStartTer: 0,
                quarterStartStandardStarts: 0,
                quarterStartXfer: 0
            }
            delayDispatch(this, chooseCourse(blankCourse))
            return false
        }
        return true
    }

    title() {
        return 'Create Course'
    }
}

const mapStateToProps = (state) => {
    return objectAssign({lookups: state.submission.core.lookups}, state.submission.courses)
}
const connector = connect(mapStateToProps)

export const CoursesIndex = connector(CoursesIndexView)
export const CoursesEdit = connector(withRouter(CoursesEditView))
export const CoursesAdd = connector(withRouter(CoursesAddView))
