// POLYFILL
import { Promise, objectAssign } from '../../reusable/ponyfill'

// NORMAL CODE
import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'

import { Field } from 'react-redux-form'

import { SubmissionBase, React } from '../base_components'
import { Form, SimpleField, SimpleSelect, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, SubmitFlip, MessagesComponent } from '../../reusable/ui_basic'

import { COURSES_FORM_KEY } from './reducers'
import { coursesSorts, coursesCollection, courseTypeMap, messages } from './data'
import { loadCourses, saveCourse, chooseCourse } from './actions'

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
        const changeSort = (newSort) => this.props.dispatch(coursesCollection.changeSortCriteria(newSort))
        const courses = []
        const baseUri = this.baseUri()

        coursesCollection.iterItems(this.props.courses, (course, key) => {
            let location = course.location
            if (!location) {
                location = this.props.lookups.center.name
            }
            const type = courseTypeMap[course.type]

            const startDate = moment(course.startDate)
            const ter = course.currentTer || '-'
            const ss = course.currentStandardStarts || '-'
            const xfer = course.currentXfer || '-'

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

        const messages = course ? this.props.messages[course.id] : []

        return (
            <div>
                <h3>{this.title()}</h3>

                <MessagesComponent messages={messages} />

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

                <SubmitFlip loadState={this.props.saveCourse} offset='col-sm-offset-2 col-sm-8'>Save</SubmitFlip>
                </Form>
            </div>
        )
    }
    // saveCourseData for now is the same between edit and create flows
    saveCourseData(data) {
        this.props.dispatch(saveCourse(this.props.params.centerId, this.reportingDateString(), data)).then((result) => {
            if (result.success && result.storedId) {
                data = objectAssign({}, data, {id: result.storedId, meta: result.meta})
                this.props.dispatch(coursesCollection.replaceItem(data))
                this.props.dispatch(chooseCourse(data.id, this.getCourseById(data.id)))
            }

            this.props.dispatch(messages.replace(data.id, result.messages))

            if (result.valid) {
                this.props.router.push(this.baseUri() + '/courses')
            }
        })
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
        if (completionState != 'hidden') {
            completionFields = (
                <div className="row">
                <div className="col-md-12">
                    <h4>Completion</h4>
                    <SimpleField label="Completed Standard Starts" model={modelKey+'.completedStandardStarts'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} />
                    <SimpleField label="Potentials" model={modelKey+'.potentials'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} />
                    <SimpleField label="Registrations" model={modelKey+'.registrations'} labelClass="col-md-2" divClass="col-md-2" disabled={completionState == 'disabled'} />
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

            rows.push(
                <div className="row" key={row.fieldSuffix}>
                <div className="col-md-12">
                    <div className="form-group">
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
    componentDidMount() {
        super.setupCourses().then(() => {
            const courseId = this.props.params.courseId
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
