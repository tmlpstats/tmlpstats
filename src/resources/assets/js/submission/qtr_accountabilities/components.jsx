import _ from 'lodash'
import React, { Component, PureComponent } from 'react'

import { Typeahead } from '../../reusable/typeahead'
import { Form, Field, formActions, SimpleFormGroup } from '../../reusable/form_utils'
import { Alert, Panel, SubmitFlip } from '../../reusable/ui_basic'
import { rebind, connectRedux } from '../../reusable/dispatch'

import * as selectors from './selectors'

export class QuarterAccountabilities extends React.Component {
    render() {
        return (
            <div>
                <h3>Next Quarter Accountabilities</h3>
                <Alert alert="info">
                    Fill this form out after classroom 3 to indicate accountabilities for next quarter
                </Alert>
                <QuarterAccountabilitiesTable />
            </div>
        )
    }
}

@connectRedux()
export class QuarterAccountabilitiesTable extends Component {
    static mapStateToProps(state) {
        return {
            accountabilities: selectors.repromisableAccountabilities(state),
            qa: state.submission.qtr_accountabilities,
            lookups: state.submission.core.lookups,
            people: selectors.selectablePeople(state),
            browser: state.browser
        }
    }

    render() {
        if (!this.props.accountabilities) {
            return <div>Loading</div>
        }
        const MODEL='submission.qtr_accountabilities'
        const tabular = this.props.browser.greaterThan.medium

        const accountabilities = this.props.accountabilities.map((acc) => {
            return (
                <QuarterAccountabilitiesRow key={acc.id} acc={acc} modelBase={MODEL} lookups={this.props.lookups} people={this.props.people} tabular={tabular} dispatch={this.props.dispatch} />
            )
        })
        if (tabular) {
            return (
                <Form model={MODEL} className="table-responsive">
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
                    <SubmitFlip loadState={{state: 'loaded'}}>Submit</SubmitFlip>
                </Form>
            )
        } else {
            return (
                <Form model={MODEL} className="form-horizontal nextQuarterAccountabilities">
                    {accountabilities}
                    <SubmitFlip loadState={{state: 'loaded'}}>Submit</SubmitFlip>
                </Form>
            )
        }
    }
}


class QuarterAccountabilitiesRow extends PureComponent {

    render() {
        const { acc, modelBase, tabular } = this.props

        const model = modelBase + '.' + acc.id

        const tmSelectField = (
            <PersonInput people={this.props.people} modelBase={model} changeAction={this.changeAction} dispatch={this.props.dispatch} />
        )
        const emailField = <Field model={model+'.email'}><input type="text" className="form-control nqEmail" /></Field>
        const phoneField = <Field model={model+'.phone'}><input type="text" className="form-control nqPhone" /></Field>
        const notesField = <Field model={model+'.notes'}><input type="text" className="form-control nqNotes" /></Field>

        if (tabular) {
            return (
                <tr>
                    <th>{acc.display}</th>
                    <td>{tmSelectField}</td>
                    <td>{emailField}</td>
                    <td>{phoneField}</td>
                    <td>{notesField}</td>
                </tr>
            )
        } else {
            const color = _.includes(requiredAccountabilities, acc.name) ? 'primary' : 'default'
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

class PersonInput extends React.PureComponent {
    constructor(props) {
        super(props)
        rebind(this, 'onChange', 'onOptionSelected')
    }

    render() {
        const { props } = this
        return (
            <Typeahead
                model={props.modelBase+'.tmId'} options={props.people.allNames}
                allowCustomValues={6}
                onOptionSelected={this.onOptionSelected}
                onChange={this.onChange} />
        )
    }

    onChange(e) {
        console.log('event target', e.target, 'value', e.target.value)
    }

    onOptionSelected(name, event) {
        console.log('onOptionSelected', arguments)
        const { nameToKey, team_members } = this.props.people
        const key = nameToKey[name]
        if (!key) {
            // In this case, it's a custom entry.
            this.props.dispatch(formActions.merge(this.props.modelBase, {
                teamMemberId: null,
                applicationId: null,
                name: name
            }))
        } else if (key[0] == 'teamMember') {
            const tmd = team_members[key[2]]
            this.props.dispatch(formActions.change(this.props.modelBase, {
                teamMemberId: tmd.teamMemberId,
                name: name,
                email: tmd.teamMember.person.email,
                phone: tmd.teamMember.person.phone
            }))
        } else if (key[0] == 'application') {
            // TODO actually implement this
            this.props.dispatch(formActions.merge(this.props.modelBase, {
                teamMemberId: null,
                applicationId: key[1],
                name: name
            }))
        }
    }

}

const requiredAccountabilities = ['t1tl', 't2tl', 'statistician', 'logistics']
