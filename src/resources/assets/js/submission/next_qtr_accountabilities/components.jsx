import _ from 'lodash'
import PropTypes from 'prop-types'
import React, { Component, PureComponent } from 'react'

import { Form, formActions, SimpleFormGroup, NullableTextControl, Select } from '../../reusable/form_utils'
import { Alert, Panel, SubmitFlip, ModeSelectButtons, ButtonStateFlip } from '../../reusable/ui_basic'
import { rebind, connectRedux, delayDispatch } from '../../reusable/dispatch'
import { loadStateShape } from '../../reusable/shapes'

import { conditionalLoadApplications } from '../applications/actions'
import { getLabelTeamMember } from '../core/selectors'
import * as selectors from './selectors'
import * as actions from './actions'
import { qtrAccountabilitiesData } from './data'

export class QuarterAccountabilities extends Component {
    render() {
        return (
            <div>
                <h3>Next Quarter Accountabilities</h3>
                <TopAlert />
                <QuarterAccountabilitiesTable params={this.props.params} />
            </div>
        )
    }
}

export function TopAlert() {
    return (
        <Alert alert="info">
            After classroom 3: teams <b>must</b> fill out the following 4 accountabilities for the upcoming quarter:
            Team 1 Team Leader, Team 2 Team Leader, Statistician, Logistics.
            <br />
            We request you fill out other accountabilities as soon as you know them; this greatly helps the weekend
            teams with setting up the accountability clinics.
        </Alert>
    )
}

@connectRedux()
export class QuarterAccountabilitiesTable extends Component {
    static mapStateToProps(state) {
        return {
            accountabilities: selectors.repromisableAccountabilities(state),
            nqa: state.submission.next_qtr_accountabilities,
            lookups: state.submission.core.lookups,
            people: selectors.selectablePeople(state),
            browser: state.browser
        }
    }

    static propTypes = {
        nqa: PropTypes.object,
        autoSave: PropTypes.bool,
        lookups: PropTypes.object,
        people: PropTypes.object,
        accountabilities: PropTypes.array
    }

    constructor(props) {
        super(props)
        rebind(this, 'onSubmit', 'autoSaveSubmit')
        this.debouncedAutoSave = _.debounce(this.autoSaveSubmit, 600, {trailing: true, maxWait: 10000})
    }

    checkLoading() {
        if (!this.props.accountabilities) {
            return false
        }
        const { loadState } = this.props.nqa
        if (!loadState.loaded) {
            const { centerId: center, reportingDate } = this.props.params
            delayDispatch(this, conditionalLoadApplications(center, reportingDate))
            return qtrAccountabilitiesData.conditionalLoad(this.props.dispatch, loadState, {center, reportingDate})
        }
        return true
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.nqa !== this.props.nqa && nextProps.autoSave) {
            this.debouncedAutoSave()
        }
    }

    render() {
        if (!this.checkLoading()) {
            return <div>Loading</div>
        }

        const MODEL = qtrAccountabilitiesData.opts.model
        const tabular = this.props.browser.greaterThan.huge
        const submitButton = this.props.autoSave ? undefined : <SubmitFlip loadState={this.props.nqa.saveState}>Submit</SubmitFlip>

        const accountabilities = this.props.accountabilities.map((acc) => {
            return (
                <QuarterAccountabilitiesRow
                    key={acc.id} acc={acc} modelBase={MODEL}
                    entry={this.props.nqa.data[acc.id]}
                    lookups={this.props.lookups} people={this.props.people} tabular={tabular}
                    dispatch={this.props.dispatch} />
            )
        })
        if (tabular) {
            return (
                <Form model={MODEL} className="table-responsive" onSubmit={this.onSubmit}>
                    <table className="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>Person Type</th>
                                <th>Accountability</th>
                                <th>Team Member</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            {accountabilities}
                        </tbody>
                    </table>
                    {submitButton}
                </Form>
            )
        } else {
            return (
                <Form model={MODEL} className="form-horizontal nextQuarterAccountabilities" onSubmit={this.onSubmit}>
                    {accountabilities}
                </Form>
            )
        }
    }

    autoSaveSubmit() {
        if (!this.props.nqa.loadState.available) {
            setTimeout(this.debouncedAutoSave, 1000)
        } else {
            this.onSubmit(this.props.nqa.data)
        }
    }

    onSubmit(data) {
        const { centerId, reportingDate } = this.props.params

        const original = data._original || {}
        data = _.omit(data, '_original')

        let toSave = []
        for (let key in data) {
            if (!original || original[key] !== data[key]) {
                toSave.push(data[key])
            }
        }
        if (toSave.length) {
            this.props.dispatch(actions.batchSaveAccountabilityInfo(centerId, reportingDate, toSave))
        }
        return false
    }
}

@connectRedux()
class AccSaveButton extends Component {
    static mapStateToProps(state, ownProps) {
        const nqa = state.submission.next_qtr_accountabilities
        return {saveState: nqa.saveState, form: nqa.forms[ownProps.itemId], err: nqa.data[ownProps.itemId]._err}
    }
    static propTypes = {
        saveState: loadStateShape,
        itemId: PropTypes.number,
        form: PropTypes.object,
        err: PropTypes.node
    }

    render() {
        const { form, saveState } = this.props
        let disabled = form.$form.pristine
        let errAlert
        if (saveState.state == 'failed' && saveState.error && this.props.err) {
            errAlert = <Alert alert="danger">{this.props.err}</Alert>
        }
        return (
            <SimpleFormGroup label="">
                <ButtonStateFlip loadState={this.props.saveState} disabled={disabled}>Save</ButtonStateFlip>
                {errAlert}
            </SimpleFormGroup>
        )
    }
}


class QuarterAccountabilitiesRow extends PureComponent {
    static propTypes = {
        dispatch: PropTypes.func.isRequired,
        modelBase: PropTypes.string.isRequired,
        acc: PropTypes.object,
        entry: PropTypes.object,
        lookups: PropTypes.object,
        people: PropTypes.object,
        tabular: PropTypes.bool,
    }

    static defaultProps = {
        entry: {name: ''}
    }

    constructor(props) {
        super(props)
        rebind(this, 'changeMode')
    }

    render() {
        const { acc, modelBase, tabular, entry } = this.props

        const model = modelBase + '.' + acc.id

        const mode = currentMode(entry)

        const modeSelect = <ModeSelectButtons items={PERSON_MODES} current={mode} onClick={this.changeMode} />

        const tmSelectField = (
            <PersonInput model={model} people={this.props.people} entry={this.props.entry} />
        )
        const emailField = <NullableTextControl model={model+'.email'} className="form-control nqEmail" disabled={!entry.name} />
        const phoneField = <NullableTextControl model={model+'.phone'} className="form-control nqPhone" disabled={!entry.name} />
        const notesField = <NullableTextControl model={model+'.notes'} className="form-control nqNotes" disabled={!entry.name} />
        const isRequired = _.includes(requiredAccountabilities, acc.name)

        if (tabular) {
            const required = isRequired? '*': undefined
            return (
                <tr>
                    <th>{acc.display}{required}</th>
                    <th>{modeSelect}</th>
                    <td>{tmSelectField}</td>
                    <td>{emailField}</td>
                    <td>{phoneField}</td>
                    <td>{notesField}</td>
                </tr>
            )
        } else {
            const color = isRequired? 'primary' : 'default'
            const modeLookup = PERSON_MODES_LOOKUP[mode]
            const tmSelectLabel =  modeLookup ? (modeLookup.altLabel || modeLookup.label) : 'Person'
            return (
                <Panel color={color} heading={acc.display + (isRequired? '*' : '')} headingLevel="h3">
                    <SimpleFormGroup label="Choose From">
                        {modeSelect}
                    </SimpleFormGroup>
                    <SimpleFormGroup label={tmSelectLabel}>
                        {tmSelectField}
                    </SimpleFormGroup>
                    <SimpleFormGroup label="Email">{emailField}</SimpleFormGroup>
                    <SimpleFormGroup label="Phone">{phoneField}</SimpleFormGroup>
                    <SimpleFormGroup label="Notes">{notesField}</SimpleFormGroup>
                    <AccSaveButton itemId={acc.id} />
                </Panel>
            )
        }
    }

    changeMode(key) {
        if (this.props.entry[PERSON_MODE_KEY] != key) {
            this.props.dispatch(formActions.merge(`${this.props.modelBase}.${this.props.acc.id}`, {
                [PERSON_MODE_KEY]: key,
                application: (key == 'application')? '': null,
                teamMember: (key == 'team_member')? '': null,
                email: '',
                phone: '',
                name: ''
            }))
        }
    }
}

const PERSON_MODES = [
    {key: 'team_member', label: 'Current Team Member', altLabel: 'Team Member', objLabel: 'a team member', relevantProp: 'teamMember'},
    {key: 'application', label: 'Incoming T1/T2', objLabel: 'an application', relevantProp: 'application'},
    {key: 'other', label: 'Other', relevantProp: 'name'}
]
const PERSON_MODES_LOOKUP = _.keyBy(PERSON_MODES, 'key')
const PERSON_MODE_KEY = '_personMode'

class PersonInput extends PureComponent {
    static propTypes = {
        model: PropTypes.string.isRequired,
        people: PropTypes.object,
        entry: PropTypes.object,
    }

    constructor(props) {
        super(props)
        rebind(this, 'changeTeamMember', 'changeApp')
    }

    render() {
        const { entry, model, people } = this.props
        const mode = currentMode(entry)

        let warning
        if (mode &&  mode != 'other' && !entry.name) {
            const modeItem = PERSON_MODES_LOOKUP[mode]
            warning = (
                <Alert alert="info">You must select {modeItem.objLabel} below to proceed</Alert>
            )
        }

        let mainView
        switch (mode) {
        case 'team_member':
            mainView = (
                <Select
                        model={model+'.teamMember'} items={people.orderedTeamMembers}
                        keyProp="teamMemberId" getLabel={getLabelTeamMember}
                        changeAction={this.changeTeamMember} emptyChoice=" " />
            )
            break
        case 'application':
            mainView = (
                <Select
                        model={model+'.application'} items={people.orderedApplications}
                        keyProp="id" getLabel={getLabelApp}
                        changeAction={this.changeApp} emptyChoice=" " />
            )
            break
        case 'other':
            warning = (
                <Alert alert="info">
                    "Other" is only used in special situations, if the person who will be
                    this accountability is someone not currently incoming or on this team.
                </Alert>
            )
            mainView = (
                <NullableTextControl model={model+'.name'} />
            )
        }
        return (
            <div>
                {warning}
                {mainView}
            </div>
        )
    }

    changeTeamMember(fieldModel, tmId) {
        const tmd = this.props.people.teamMembers[tmId]
        let toUpdate = {
            teamMember: tmId,
            application: null,
            name: getLabelTeamMember(tmd)
        }
        this.updateEmailPhone(toUpdate, tmd.teamMember.person)
        return formActions.merge(this.props.model, toUpdate)
    }

    changeApp(fieldModel, appId) {
        const application = this.props.people.applications[appId]
        let toUpdate = {
            application: appId,
            teamMember: null,
            name: getLabelApp(application)
        }
        this.updateEmailPhone(toUpdate, application)
        return formActions.merge(this.props.model, toUpdate)
    }

    updateEmailPhone(toUpdate, input) {
        if (input) {
            EMAIL_PHONE.forEach((k) => {
                // only obliterate email and phone if they exist
                if (input[k]) {
                    toUpdate[k] = input[k]
                } else {
                    toUpdate[k] = ''
                }
            })
        }
    }
}

const getLabelApp = (app) => `${app.firstName} ${app.lastName}`

const requiredAccountabilities = ['t1tl', 't2tl', 'statistician', 'logistics']

const EMAIL_PHONE = ['email', 'phone']

function currentMode(entry) {
    let mode = entry[PERSON_MODE_KEY]
    if (mode) {
        return mode
    }
    for (let modeItem of PERSON_MODES) {
        if (entry[modeItem.relevantProp]) {
            return modeItem.key
        }
    }
}
