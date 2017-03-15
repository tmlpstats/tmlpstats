import React, { PropTypes } from 'react'
import { Link } from 'react-router'
import { connect } from 'react-redux'

import { Form, CheckBox, SimpleField, BooleanSelect, BooleanSelectView, connectCustomField, SimpleSelect, SimpleFormGroup, AddOneLink } from '../../reusable/form_utils'
import { objectAssign } from '../../reusable/ponyfill'
import { ModeSelectButtons, SubmitFlip, Alert, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'
import { delayDispatch, rebind } from '../../reusable/dispatch'
import { FormTypeahead } from '../../reusable/typeahead'

import { SubmissionBase } from '../base_components'
import { centerQuarterData } from '../core/data'
import { makeAccountabilitiesSelector, makeQuartersSelector } from '../core/selectors'
import { TEAM_MEMBERS_COLLECTION_FORM_KEY, TEAM_MEMBER_FORM_KEY } from './reducers'
import { teamMembersSorts, teamMembersData } from './data'
import { EXIT_CHOICES, EXIT_CHOICES_HELP } from './exit_choice'
import * as actions from './actions'

const GITW_LABELS = ['Ineffective', 'Effective']
const TDO_LABELS = ['N', 'Y']

class TeamMembersBase extends SubmissionBase {
    teamMembersBaseUri() {
        return this.baseUri() + '/team_members'
    }

    // Check the loading state of our initial data, and dispatch a loadTeamMembers if we never loaded
    checkLoading() {
        const { teamMembers: {loadState: loading}, dispatch } = this.props
        if (loading.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            dispatch(actions.loadTeamMembers(centerId, reportingDate))
            return false
        }
        return (loading.state == 'loaded')
    }
}


const STATE_UPDATING = 'Updating'
const STATE_NOTHING = 'Nothing'
const STATE_SAVED = 'Saved'

class TeamMembersIndexView extends TeamMembersBase {
    componentWillMount() {
        rebind(this, 'saveWeeklyReporting', 'changeSort')
    }
    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const baseUri = this.teamMembersBaseUri()
        const { weeklySave, weeklyReporting: wr, teamMembers } = this.props
        var teamMemberRows = []
        var withdraws = []
        teamMembersData.iterItems(teamMembers, (teamMember, key) => {
            var updating = STATE_NOTHING
            if (wr.changed[key]) {
                updating = (weeklySave.loaded && wr.working && wr.working[key] >= wr.changed[key])? STATE_SAVED : STATE_UPDATING
            }

            let row = (
                <TeamMemberIndexRow
                        key={key} teamMember={teamMember} baseUri={baseUri}
                        updating={updating} accountabilities={this.props.lookups.accountabilities}
                        lookups={this.props.lookups} />
            )

            if (teamMember.withdrawCode || teamMember.xferOut) {
                withdraws.push(row)
            } else {
                teamMemberRows.push(row)
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
                <ModeSelectButtons items={teamMembersSorts} current={teamMembers.data.meta.sort_by}
                                   onClick={this.changeSort} ariaGroupDesc="Sort Preferences" />
                <Alert alert="info">
                    Tip: you can use the "tab" key to quickly jump through the GITW/TDO.
                    <p>Set each one with the keyboard using "E" "I" for GITW and "Y" "N" for TDO.
                    You can quick-save the GITW/TDO by hitting the enter key.</p>
                </Alert>
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
                                <SubmitFlip loadState={weeklySave} wrapGroup={false}>Save GITW/TDO changes</SubmitFlip>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <br />
                <AddOneLink link={`${baseUri}/add`} />
                {withdrawTable}
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
        this.props.dispatch(teamMembersData.changeSortCriteria(newSort))
    }
}

class GitwTdoLiveSelectView extends BooleanSelectView {
    onChange(e) {
        super.onChange(e)
        let bits = this.props.model.split('.')
        bits.reverse() // the model looks like path.<teamMemberid>.tdo so if we reverse it, we get the right answer
        this.props.dispatch(actions.weeklyReportingUpdated(bits[1]))
    }
}
const GitwTdoLiveSelect = connectCustomField(GitwTdoLiveSelectView)

class TeamMemberIndexRow extends React.PureComponent {
    static propTypes = {
        baseUri: PropTypes.string.isRequired,
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

        if (teamMember.withdrawCode || teamMember.xferOut) {
            let reason = 'Transfered to another team'
            if (teamMember.withdrawCode) {
                reason = lookups.withdraw_codes_by_id[teamMember.withdrawCode].display
            }
            return (
                <tr className={className}>
                    <td>
                        <Link to={`${this.props.baseUri}/edit/${teamMember.id}`}>
                            {teamMember.firstName} {teamMember.lastName}
                        </Link>
                    </td>
                    <td>T{teamMember.teamYear} Q{teamMember.quarterNumber}</td>
                    <td>{reason}</td>
                </tr>
            )
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

class _EditCreate extends TeamMembersBase {
    getCenterQuarter(quarterId) {
        const { currentMember, centerQuarters } = this.props
        if (!quarterId) {
            quarterId = currentMember.incomingQuarter
        }
        return centerQuarters.data[quarterId]
    }

    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentMember, centerQuarters } = this.props
        if (currentMember) {
            const iq = currentMember.incomingQuarter
            if (iq && !this.getCenterQuarter(iq)) {
                if (centerQuarters.loadState.available) {
                    const { centerId } = this.props.params
                    delayDispatch(this, centerQuarterData.load({center: centerId, quarter: iq}))
                }
                return false
            }
        }
        return true
    }

    isNewTeamMember() {
        const { currentMember } = this.props
        return (!currentMember.id || parseInt(currentMember.id) < 0)
    }

    render() {
        const modelKey = TEAM_MEMBER_FORM_KEY
        const options = this.getRenderOptions()

        return (
            <Form className="form-horizontal submissionTeamMembersEdit" model={modelKey} onSubmit={this.saveTeamMember.bind(this)}>
                {this.renderContent(modelKey, options)}
                <SubmitFlip loadState={this.props.teamMembers.saveState} offset='col-md-offset-1 col-md-8'>Save</SubmitFlip>
            </Form>
        )
    }

    renderBasicInfo(modelKey, { disableBasicInfo }) {
        return (
            <div>
                <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" disabled={disableBasicInfo} required={!disableBasicInfo} />
                <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" disabled={disableBasicInfo} required={!disableBasicInfo} />
                <SimpleField label="Email" model={modelKey+'.email'} divClass="col-md-8" customField={true}>
                    <input type="email" className="form-control" />
                </SimpleField>
                <SimpleField label="Phone" model={modelKey+'.phone'} divClass="col-md-6" disabled={disableBasicInfo} />
                <SimpleFormGroup label="Accountabilities">
                    <FormTypeahead
                            model={modelKey+'.accountabilities'} items={this.props.teamAccountabilities}
                            multiple={true} rows={1} keyProp="id" labelProp="display" />
                </SimpleFormGroup>
            </div>
        )
    }

    renderRegPrefs(modelKey, options) {
        const { disableYearQuarter } = options
        const incomingQuarter = this.getCenterQuarter()
        var yearQuarter
        if (disableYearQuarter) {
            yearQuarter = <p className="form-control-static">{centerQuarterData.getLabel(incomingQuarter)}</p>
        } else {
            yearQuarter = (
                <SimpleSelect
                        model={modelKey+'.incomingQuarter'} items={this.props.validStartQuarters} emptyChoice=" "
                        keyProp="quarterId" getLabel={centerQuarterData.getLabel} />
            )
        }

        let reviewerCheckbox
        if (!this.props.currentMember || this.props.currentMember.teamYear != 1) {
            reviewerCheckbox = <CheckBox model={modelKey+'.isReviewer'} label="Is Reviewer" />
        }

        return (
            <div>
                <SimpleField label="Team Year" model={modelKey+'.teamYear'} divClass="col-md-4" customField={true} required={!disableYearQuarter}>
                    <select disabled={disableYearQuarter} className="form-control">
                        <option value="1">Team 1</option>
                        <option value="2">Team 2</option>
                    </select>
                </SimpleField>
                <SimpleFormGroup label="Starting Quarter" required={!disableYearQuarter}>
                    {yearQuarter}
                </SimpleFormGroup>
                <SimpleFormGroup label="Settings" required={true}>
                    {reviewerCheckbox}
                    <CheckBox model={modelKey+'.atWeekend'} label="On team at weekend" />
                    <CheckBox model={modelKey+'.xferIn'} label="Transfer In" />
                </SimpleFormGroup>
                <SimpleField label="Comment" model={modelKey+'.comment'} divClass="col-md-8" customField={true}>
                    <textarea className="form-control" />
                </SimpleField>
                <SimpleFormGroup label="Team Status">
                    {this.renderWithdrawGroup(modelKey, options)}
                </SimpleFormGroup>
            </div>
        )
    }

    renderWithdrawGroup(modelKey, options) {
        const { currentMember } = this.props
        const setExitChoice = (c) => {this.props.dispatch(actions.setExitChoice(c))}
        var content
        switch (currentMember.exitChoice) {
        case 'xferOut':
            content = (
                <div>
                    <Alert alert="info">&nbsp;{EXIT_CHOICES_HELP.xferOut}</Alert>
                </div>
            )
            break
        case 'wbo':
            content = (
                <div>
                    <Alert alert="info">&nbsp;{EXIT_CHOICES_HELP.wbo}</Alert>
                    <CheckBox model={modelKey+'.rereg'} label="Rereg" />
                </div>
            )
            break
        case 'ctw':
            content = (
                <Alert alert="info">&nbsp;{EXIT_CHOICES_HELP.ctw}</Alert>
            )
            break
        case 'wd':
            content = (
                <div>
                    <Alert alert="info">&nbsp;{EXIT_CHOICES_HELP.wd}</Alert>
                    <label>Withdraw Reason</label>
                    <SimpleSelect
                            model={modelKey+'.withdrawCode'} items={this.props.lookups.withdraw_codes}
                            labelProp="display" keyProp="id" emptyChoice=" " />
                </div>
            )
            break
        }
        return (
            <div>
                <ModeSelectButtons items={EXIT_CHOICES} current={currentMember.exitChoice} activeClasses="btn btn-primary active"
                                   onClick={setExitChoice} ariaGroupDesc="Sort Preferences" />
                {content}
            </div>
        )
    }

    renderTravelRoom(modelKey) {
        return (
            <div>
                <SimpleFormGroup label="Travel Booked" divClass="col-md-6 boolSelect">
                    <BooleanSelect model={modelKey+'.travel'} style={{maxWidth: '4em'}} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Room Booked" divClass="col-md-6 boolSelect">
                    <BooleanSelect model={modelKey+'.room'} />
                </SimpleFormGroup>
            </div>
        )
    }

    renderGitwTdo(modelKey) {
        return (
            <div>
                <SimpleFormGroup label="GITW" required={true}>
                    <BooleanSelect model={modelKey+'.gitw'} emptyChoice=" " labels={GITW_LABELS} className="form-control gitw" />
                </SimpleFormGroup>
                <SimpleFormGroup label="TDO" required={true}>
                    <BooleanSelect model={modelKey+'.tdo'} emptyChoice=" " labels={TDO_LABELS} className="form-control boolSelect" />
                </SimpleFormGroup>

            </div>
        )
    }

    saveTeamMember(data) {
        const { centerId, reportingDate } = this.props.params

        this.props.dispatch(actions.stashTeamMember(centerId, reportingDate, data)).then((result) => {
            if (!result) {
                return
            }

            if (result.messages && result.messages.length) {
                scrollIntoView('react-routed-flow', 10)

                // Redirect to edit view if there are warning messages
                if (this.isNewTeamMember() && result.valid) {
                    this.context.router.push(this.teamMembersBaseUri() + '/edit/' + result.storedId)
                }
            } else if (result.valid) {
                this.context.router.push(this.teamMembersBaseUri())
                // Reset currentMember so if we visit Add again, it'll be blank
                delayDispatch(this.props.dispatch, actions.chooseTeamMember({}))
            }

            return result
        })
    }
}

// Detailed edit of class list
class TeamMembersEditView extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentMember, params, dispatch, teamMembers } = this.props
        if (!currentMember || currentMember.id != params.teamMemberId) {
            const item = teamMembers.data.collection[params.teamMemberId]
            if (item) {
                if (item.exitChoice == 'wd' && !item.withdrawCode) {
                    item.exitChoice = ''
                }
                delayDispatch(dispatch, actions.chooseTeamMember(item))
            }
            return false
        }
        return true
    }

    getRenderOptions() {
        return { disableYearQuarter: true }
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }
        const teamMember = this.props.currentMember
        let messages = []
        if (teamMember && teamMember.id) {
            messages = this.props.messages[teamMember.id]
        } else if (this.isNewTeamMember() && this.props.messages['create']) {
            messages = this.props.messages['create']
        }

        return (
            <div>
                <h3>Edit Team Member {teamMember.firstName} {teamMember.lastName}</h3>

                <MessagesComponent messages={messages} />

                {super.render()}
            </div>
        )
    }

    renderContent(modelKey, options) {
        const column = (this.props.browser.greaterThan.huge) ? 'col-lg-6' : 'col-lg-12'
        const className = column + ' tmBox'

        return (
            <div>
                <div className="row">
                    <div className={className}>
                        {this.renderTravelRoom(modelKey, options)}
                    </div>
                    <div className={className}>
                        {this.renderGitwTdo(modelKey, options)}
                    </div>
                </div>
                <div className="row">
                    <div className={className}>
                        {this.renderBasicInfo(modelKey, options)}
                    </div>
                    <div className={className}>
                        {this.renderRegPrefs(modelKey, options)}
                    </div>
                </div>
            </div>
        )
    }
}

class TeamMembersAddView extends _EditCreate {
    defaultTeamMember = {exitChoice: '', teamYear: '1', atWeekend: false}
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentMember, dispatch } = this.props
        if (!currentMember || currentMember.id || !currentMember.teamYear) {
            delayDispatch(dispatch, actions.chooseTeamMember(this.defaultTeamMember))
            return false
        }
        return true

    }
    getRenderOptions() {
        return {}
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }
        return (
            <div>
                <h3>Add Team Member</h3>
                {super.render()}
            </div>
        )
    }

    renderContent(modelKey, options) {
        let messages = (this.props.messages? this.props.messages['create'] : null)
        return (
            <div>
                <div id="add-messages">
                    <MessagesComponent messages={messages || []} />
                </div>
                <div className="row">
                    <div className="col-lg-12">
                        <h4>Basic Info</h4>
                        {this.renderBasicInfo(modelKey, options)}
                        <h4>Setup</h4>
                        {this.renderRegPrefs(modelKey, options)}
                    </div>
                </div>
                <div className="row">
                    <div className="col-lg-12">
                        {this.renderTravelRoom(modelKey, options)}
                        {this.renderGitwTdo(modelKey, options)}
                    </div>
                </div>
            </div>
        )
    }
}

const getTeamAccountabilities = makeAccountabilitiesSelector('team')
const getValidStartQuarters = makeQuartersSelector('validStartQuarters')

const mapStateToProps = (state) => {
    const { centerQuarters, lookups } = state.submission.core
    const teamAccountabilities = getTeamAccountabilities(state)
    const validStartQuarters = getValidStartQuarters(state.submission.core)
    const n = {centerQuarters, lookups, teamAccountabilities, validStartQuarters, browser:state.browser}
    return objectAssign(n, state.submission.team_members)
}
const connector = connect(mapStateToProps)

export const TeamMembersIndex = connector(TeamMembersIndexView)
export const TeamMembersEdit = connector(TeamMembersEditView)
export const TeamMembersAdd = connector(TeamMembersAddView)
