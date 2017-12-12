import React from 'react'
import { Link } from 'react-router'
import PropTypes from 'prop-types'

import { arrayFind } from '../../reusable/ponyfill'

import { Form, BooleanSelectView, SimpleSelect, connectCustomField, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, ButtonStateFlip, Alert } from '../../reusable/ui_basic'
import { delayDispatch, rebind, connectRedux } from '../../reusable/dispatch'
import { buildTable } from '../../reusable/tabular'
import { ProgramLeadersIndex } from '../program_leaders/components'

import { TEAM_MEMBERS_COLLECTION_FORM_KEY } from './reducers'
import { teamMembersData, teamMembersSorts, teamMemberText } from './data'
import * as actions from './actions'
import { TeamMembersBase, GITW_LABELS, TDO_LABELS, TDO_OPTIONS } from './components-base'

const STATE_UPDATING = 'Updating'
const STATE_NOTHING = 'Nothing'
const STATE_SAVED = 'Saved'

const TABLE_DEFAULT_COLS = [
    {key: 'name', label: 'Name', selector: teamMemberText},
    {key: 'year', label: 'Year', selector: row => row.teamYear, sorter: 'number'},
    {key: 'accountabilities', label: 'Accountabilities', default: ''
    /*, selector: row => row.map((accId) => accountabilities[accId].display).join(', ')*/},
]

const ClassListDefaultTable = buildTable({
    name: 'submission_team_members_default',
    columns: TABLE_DEFAULT_COLS.concat([
        {key: 'gitw', label: 'GITW', default: '', sortable: false},
        {key: 'tdo', label: 'TDO', default: '', sortable: false},
    ])
})

const ClassListTravelTable = buildTable({
    name: 'submission_team_members_travel',
    columns: TABLE_DEFAULT_COLS.concat([
        {key: 'travel', label: 'Travel', default: '', sortable: false},
        {key: 'room', label: 'Rooming', default: '', sortable: false},
    ])
})

const ClassListRppTable = buildTable({
    name: 'submission_team_members_rpp',
    columns: TABLE_DEFAULT_COLS.concat([
        {key: 'rppCap', label: 'RPP CAP', default: '', sortable: false},
        {key: 'rppCpc', label: 'RPP CPC', default: '', sortable: false},
        {key: 'rppLf', label: 'RPP LF', default: '', sortable: false},
    ])
})

const TABLES = [
    {key: 'submission_team_members_default', label: 'GITW/TDO', table: ClassListDefaultTable},
    {key: 'submission_team_members_travel', label: 'Travel/Room', table: ClassListTravelTable},
    {key: 'submission_team_members_rpp', label: 'Reg. Per Participant', table: ClassListRppTable},
]

@connectRedux()
export class TeamMembersIndex extends TeamMembersBase {
    static mapStateToProps() {
        return (state) => {
            const submission = state.submission
            const { weeklySave, weeklyReporting, teamMembers } = submission.team_members
            const { lookups } = state.submission.core
            const sortedMembers = teamMembersData.opts.getSortedMembers(teamMembers)
            return { weeklySave, weeklyReporting, teamMembers, lookups, sortedMembers }
        }
    }
    constructor(props) {
        super(props)
        rebind(this, 'saveWeeklyReporting', 'changeTableFormat')
    }
    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const baseUri = this.teamMembersBaseUri()
        const { weeklySave, weeklyReporting: wr, teamMembers, sortedMembers, lookups } = this.props
        var teamMemberRows = []
        var withdraws = []
        var members = []

        sortedMembers.forEach((teamMember) => {
            const key = teamMember.id
            members.push(teamMember)
            var updating = STATE_NOTHING
            if (wr.changed[key]) {
                updating = (weeklySave.loaded && wr.working && wr.working[key] >= wr.changed[key])? STATE_SAVED : STATE_UPDATING
            }

            if (teamMember.withdrawCode || teamMember.wbo || teamMember.xferOut) {
                withdraws.push(
                    <TeamMemberWithdrawnRow
                        key={key} teamMember={teamMember} baseUri={baseUri}
                        lookups={lookups} />
                )
            } else {
                teamMemberRows.push(
                    <TeamMemberIndexRow
                            key={key} teamMember={teamMember} baseUri={baseUri}
                            updating={updating} accountabilities={this.props.lookups.accountabilities}
                            lookups={this.props.lookups} />
                )
            }
        })

        let withdrawTable
        if (withdraws.length) {
            withdrawTable = (
                <div>
                <br/>
                    <h4>Withdraws/Transfers</h4>
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>{withdraws}</tbody>
                    </table>
                </div>
            )
        }

        let tdoOptions = '"Y" "N"'
        if (lookups.user.canSubmitMultipleTdos) {
            tdoOptions = '0 - 10'
        }

        const format = teamMembers.meta.get('format')
        const TableClass = arrayFind(TABLES, table => table.key === format).table

        return (
            <Form model={TEAM_MEMBERS_COLLECTION_FORM_KEY} onSubmit={this.saveWeeklyReporting}>
                <h3>Class List</h3>
                <Alert alert="info">
                    Tip: you can use the "tab" key to quickly jump through the GITW/TDO.
                    <p>Set each one with the keyboard using "E" "I" for GITW and {tdoOptions} for TDO.
                    You can quick-save the GITW/TDO by hitting the enter key.</p>
                </Alert>
                <ModeSelectButtons
                        items={TABLES} current={teamMembers.meta.get('format')}
                        onClick={this.changeTableFormat} ariaGroupDesc="Sort Preferences" />
                <TableClass data={sortedMembers} />
                <table className="table submissionTeamMembers">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Accountability</th>
                            <th>GITW</th>
                            <th>TDO</th>
                        </tr>
                    </thead>
                    <tbody>{teamMemberRows}</tbody>
                    <tfoot>
                        <tr>
                            <td colSpan="4" style={{minWidth: '15em'}}>
                                <ButtonStateFlip loadState={weeklySave}>Save GITW/TDO changes</ButtonStateFlip>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <br />
                <AddOneLink link={`${baseUri}/add`} />
                {withdrawTable}
                <br />
                <ProgramLeadersIndex params={this.props.params} />
            </Form>
        )
    }

    saveWeeklyReporting(data) {
        if (this.props.weeklySave.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            delayDispatch(this, actions.weeklyReportingSubmit(
                centerId, reportingDate,
                this.props.weeklyReporting, data
            ))
        }
    }

    changeTableFormat(newFormat) {
        this.props.dispatch(teamMembersData.setMeta('format', newFormat))
    }
}

class TeamMemberWithdrawnRow extends React.PureComponent {
    static propTypes = {
        baseUri: PropTypes.string.isRequired,
        lookups: PropTypes.object.isRequired,
        teamMember: PropTypes.object.isRequired
    }

    render() {
        const { baseUri, teamMember, lookups } = this.props
        let reason = 'Transfered to another team'
        if (teamMember.withdrawCode) {
            reason = lookups.withdraw_codes_by_id[teamMember.withdrawCode].display
        } else if (teamMember.wbo) {
            reason = 'Well-being Issue'
        }
        return (
            <tr>
                <td>
                    <Link to={`${baseUri}/edit/${teamMember.id}`}>
                        {teamMemberText(teamMember)}
                    </Link>
                </td>
                <td>T{teamMember.teamYear} Q{teamMember.quarterNumber}</td>
                <td>{reason}</td>
            </tr>
        )
    }
}


class TeamMemberIndexRow extends React.PureComponent {
    static propTypes = {
        baseUri: PropTypes.string.isRequired,
        lookups: PropTypes.object.isRequired,
        updating: PropTypes.string.isRequired,
        teamMember: PropTypes.object.isRequired,
        accountabilities: PropTypes.object
    }

    render() {
        const { teamMember, updating, accountabilities, lookups } = this.props
        const modelKey = `${TEAM_MEMBERS_COLLECTION_FORM_KEY}.${teamMember.id}`
        var className, accountability
        if (updating == STATE_SAVED) {
            className = 'bg-success'
        } else if (updating == STATE_UPDATING) {
            className = 'bg-warning'
        }
        const acc = teamMember.accountabilities
        if (acc && acc.length) {
            accountability = acc.map((accId) => accountabilities[accId].display).join(', ')
        }

        let tdoSelect = <GitwTdoLiveSelect model={modelKey+'.tdo'} emptyChoice=" " labels={TDO_LABELS} />
        if (lookups.user.canSubmitMultipleTdos) {
            tdoSelect = (
                <SimpleSelect model={modelKey+'.tdo'} emptyChoice=" "
                              items={TDO_OPTIONS} keyProp='k' labelProp='k'
                              changeAction={actions.selectChangeAction} />
            )
        }

        return (
            <tr className={className}>
                <td>
                    <Link to={`${this.props.baseUri}/edit/${teamMember.id}`}>
                        {teamMemberText(teamMember)}
                    </Link>
                </td>
                <td>T{teamMember.teamYear} Q{teamMember.quarterNumber}</td>
                <td>{accountability}</td>
                <td className="gitw"><GitwTdoLiveSelect model={modelKey+'.gitw'} emptyChoice=" " labels={GITW_LABELS} /></td>
                <td className="tdo">{tdoSelect}</td>
            </tr>
        )
    }
}

@connectCustomField
class GitwTdoLiveSelect extends BooleanSelectView {
    onChange(e) {
        super.onChange(e)
        let bits = this.props.model.split('.')
        bits.reverse() // the model looks like path.<teamMemberid>.tdo so if we reverse it, we get the right answer
        this.props.dispatch(actions.weeklyReportingUpdated(bits[1]))
    }
}
