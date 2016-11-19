import _ from 'lodash'
import React, { Component, PureComponent, PropTypes } from 'react'

import { objectAssign } from '../../reusable/ponyfill'
import { Typeahead } from '../../reusable/typeahead'
import { Form, Field, formActions, SimpleFormGroup } from '../../reusable/form_utils'
import { Alert, Panel, SubmitFlip } from '../../reusable/ui_basic'
import { rebind, connectRedux, delayDispatch } from '../../reusable/dispatch'

import { conditionalLoadApplications } from '../applications/actions'
import * as selectors from './selectors'
import * as actions from './actions'
import { qtrAccountabilitiesData } from './data'

export class QuarterAccountabilities extends Component {
    render() {
        return (
            <div>
                <h3>Next Quarter Accountabilities</h3>
                <Alert alert="info">
                    Fill this form out after classroom 3 to indicate accountabilities for next quarter
                </Alert>
                <QuarterAccountabilitiesTable params={this.props.params} />
            </div>
        )
    }
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
        const tabular = this.props.browser.greaterThan.medium
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
                    {submitButton}
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

    render() {
        const { acc, modelBase, tabular } = this.props

        const model = modelBase + '.' + acc.id

        const tmSelectField = (
            <PersonInput people={this.props.people} modelBase={model} dispatch={this.props.dispatch} name={this.props.entry.name} />
        )
        const emailField = <Field model={model+'.email'}><input type="text" className="form-control nqEmail" /></Field>
        const phoneField = <Field model={model+'.phone'}><input type="text" className="form-control nqPhone" /></Field>
        const notesField = <Field model={model+'.notes'}><input type="text" className="form-control nqNotes" /></Field>
        const isRequired = _.includes(requiredAccountabilities, acc.name)

        if (tabular) {
            const required = isRequired? '*': undefined
            return (
                <tr>
                    <th>{acc.display}{required}</th>
                    <td>{tmSelectField}</td>
                    <td>{emailField}</td>
                    <td>{phoneField}</td>
                    <td>{notesField}</td>
                </tr>
            )
        } else {
            const color = isRequired? 'primary' : 'default'
            return (
                <Panel color={color} heading={acc.display} headingLevel="h3">
                    <SimpleFormGroup label="Team Member">{tmSelectField}</SimpleFormGroup>
                    <SimpleFormGroup label="Email">{emailField}</SimpleFormGroup>
                    <SimpleFormGroup label="Phone">{phoneField}</SimpleFormGroup>
                </Panel>
            )
        }
    }
}

class PersonInput extends PureComponent {
    static propTypes = {
        people: PropTypes.object,
        modelBase: PropTypes.string.isRequired,
        name: PropTypes.string,
        dispatch: PropTypes.func.isRequired
    }

    constructor(props) {
        super(props)
        rebind(this, 'onChange')
    }

    render() {
        const { props } = this
        return (
            <Typeahead
                options={props.people.allNames}
                selected={[this.props.name]}
                allowNew={true}
                onChange={this.onChange}
                minLength={1}
                />
        )
    }

    onChange(names) {
        if (!names.length) {
            return
        }
        const name = names[0]
        if (name.customOption) {
            // In this case, it's a custom entry.
            this.props.dispatch(formActions.merge(this.props.modelBase, {
                teamMemberId: null,
                applicationId: null,
                name: name.label
            }))
            return
        }

        const { nameToKey, team_members } = this.props.people
        const key = nameToKey[name]

        if (key[0] == 'teamMember') {
            const tmd = team_members[key[2]]
            let toUpdate = {
                applicationId: null,
                teamMemberId: tmd.teamMemberId,
                name: name
            }
            const person = tmd.teamMember.person
            EMAIL_PHONE.forEach((k) => {
                // only obliterate email and phone if they exist
                if (person[k]) {
                    toUpdate[k] = person[k]
                }
            })
            this.props.dispatch(formActions.merge(this.props.modelBase, toUpdate))
        } else if (key[0] == 'application') {
            // TODO hoist team member email if available
            this.props.dispatch(formActions.merge(this.props.modelBase, {
                teamMemberId: null,
                applicationId: key[1],
                name: name
            }))
        }
    }

}

const requiredAccountabilities = ['t1tl', 't2tl', 'statistician', 'logistics']

const EMAIL_PHONE = ['email', 'phone']
