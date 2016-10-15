import _ from 'lodash'
import React, { Component, PureComponent, PropTypes } from 'react'

import { objectAssign } from '../../reusable/ponyfill'
import { Typeahead } from '../../reusable/typeahead'
import { Form, Field, formActions, SimpleFormGroup } from '../../reusable/form_utils'
import { Alert, Panel, SubmitFlip } from '../../reusable/ui_basic'
import { rebind, connectRedux } from '../../reusable/dispatch'

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
        rebind(this, 'onSubmit')
    }

    checkLoading() {
        if (!this.props.accountabilities) {
            return false
        }
        const { loadState } = this.props.nqa
        if (!loadState.loaded) {
            const { centerId: center, reportingDate } = this.props.params
            return qtrAccountabilitiesData.conditionalLoad(this.props.dispatch, loadState, {center, reportingDate})
        }
        return true
    }

    render() {
        if (!this.checkLoading()) {
            return <div>Loading</div>
        }

        const MODEL = qtrAccountabilitiesData.opts.model
        const tabular = this.props.browser.greaterThan.medium

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
                    <SubmitFlip loadState={this.props.nqa.saveState}>Submit</SubmitFlip>
                </Form>
            )
        } else {
            return (
                <Form model={MODEL} className="form-horizontal nextQuarterAccountabilities">
                    {accountabilities}
                    <SubmitFlip loadState={this.props.nqa.saveState}>Submit</SubmitFlip>
                </Form>
            )
        }
    }

    onSubmit(data) {
        const { centerId, reportingDate } = this.props.params

        // This whole thing is to deal with that I haven't gotten a bulk save API action yet.
        // So we just call save a bunch of times instead.
        // This is also the wrong way to do it because all the saveState will clobber each other and hide real errors.
        let promises = []
        for (let key in data) {
            const item = objectAssign({id: key}, data[key])
            promises.push(this.props.dispatch(actions.saveAccountabilityInfo(centerId, reportingDate, item)))
        }
        Promise.all(promises)
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
                model={props.modelBase+'.name'} options={props.people.allNames}
                selected={[this.props.name]}
                value={props.name} allowNew={true}
                onChange={this.onChange}
                minLength={1}
                />
        )
    }

    onChange(names) {
        if (!names.length) {
            return
        }
        console.log('onchange', names)
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
            this.props.dispatch(formActions.change(this.props.modelBase, {
                applicationId: null,
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
