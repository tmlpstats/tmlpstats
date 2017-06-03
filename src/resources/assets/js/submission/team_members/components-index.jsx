import _ from 'lodash'
import React, { PropTypes } from 'react'
import { Link } from 'react-router'
import { defaultMemoize } from 'reselect'

import { Form, BooleanSelectView, connectCustomField, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, ButtonStateFlip, Alert } from '../../reusable/ui_basic'
import { delayDispatch, rebind, connectRedux } from '../../reusable/dispatch'
import { collectionSortSelector } from '../../reusable/sort-helpers'
import { ProgramLeadersIndex } from '../program_leaders/components'

import { TEAM_MEMBERS_COLLECTION_FORM_KEY } from './reducers'
import { teamMembersData, teamMembersSorts } from './data'
import * as actions from './actions'
import { TeamMembersBase, GITW_LABELS, TDO_LABELS } from './components-base'

const getSortedMembers = collectionSortSelector(teamMembersSorts)


const STATE_UPDATING = 'Updating'
const STATE_NOTHING = 'Nothing'
const STATE_SAVED = 'Saved'

@connectRedux()
export class TeamMembersIndex extends TeamMembersBase {
    static mapStateToProps() {
        return (state) => {
            const submission = state.submission
            const { weeklySave, weeklyReporting, teamMembers } = submission.team_members
            const { lookups } = state.submission.core
            const sortedMembers = getSortedMembers(teamMembers)
            return { weeklySave, weeklyReporting, teamMembers, lookups, sortedMembers }
        }
    }
    constructor(props) {
        super(props)
        rebind(this, 'saveWeeklyReporting', 'changeSort')
    }
    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const baseUri = this.teamMembersBaseUri()
        const { weeklySave, weeklyReporting: wr, teamMembers, sortedMembers , lookups } = this.props
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
        return (
            <Form model={TEAM_MEMBERS_COLLECTION_FORM_KEY} onSubmit={this.saveWeeklyReporting}>
                <h3>Class List</h3>
                <Alert alert="info">
                    Tip: you can use the "tab" key to quickly jump through the GITW/TDO.
                    <p>Set each one with the keyboard using "E" "I" for GITW and "Y" "N" for TDO.
                    You can quick-save the GITW/TDO by hitting the enter key.</p>
                </Alert>
                <ModeSelectButtons
                        items={teamMembersSorts} current={teamMembers.meta.get('sort_by')}
                        onClick={this.changeSort} ariaGroupDesc="Sort Preferences" />
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

    changeSort(newSort) {
        this.props.dispatch(teamMembersData.setMeta('sort_by', newSort))
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
                        {teamMember.firstName} {teamMember.lastName}
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
        updating: PropTypes.string.isRequired,
        teamMember: PropTypes.object.isRequired,
        accountabilities: PropTypes.object
    }

    render() {
        const { teamMember, updating, accountabilities } = this.props
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

        return (
            <tr className={className}>
                <td>
                    <Link to={`${this.props.baseUri}/edit/${teamMember.id}`}>
                        {teamMember.firstName} {teamMember.lastName}
                    </Link>
                </td>
                <td>T{teamMember.teamYear} Q{teamMember.quarterNumber}</td>
                <td>{accountability}</td>
                <td className="gitw"><GitwTdoLiveSelect model={modelKey+'.gitw'} emptyChoice=" " labels={GITW_LABELS} /></td>
                <td className="tdo"><GitwTdoLiveSelect model={modelKey+'.tdo'} emptyChoice=" " labels={TDO_LABELS} /></td>
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
