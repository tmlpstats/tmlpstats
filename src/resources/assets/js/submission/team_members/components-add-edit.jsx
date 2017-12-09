import React from 'react'

import { Control, Form, CheckBox, SimpleField, BooleanSelect, SimpleSelect, SimpleFormGroup, NullableTextAreaControl, formActions } from '../../reusable/form_utils'
import { ModeSelectButtons, ButtonStateFlip, Alert, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'
import { delayDispatch, connectRedux, rebind } from '../../reusable/dispatch'
import { FormTypeahead } from '../../reusable/typeahead'

import { centerQuarterData } from '../core/data'
import DeleteWarning from '../core/DeleteWarning'
import { makeAccountabilitiesSelector, makeQuartersSelector } from '../core/selectors'
import { TEAM_MEMBER_FORM_KEY } from './reducers'
import { EXIT_CHOICES, EXIT_CHOICES_HELP } from './exit_choice'
import * as actions from './actions'
import { teamMemberText as fullName } from './data'
import { TeamMembersBase, GITW_LABELS, TDO_LABELS, TDO_OPTIONS } from './components-base'


const CHANGING_QUARTER_KEY = '_changingQuarter'
const getTeamAccountabilities = makeAccountabilitiesSelector('team')
const getValidStartQuarters = makeQuartersSelector('validStartQuarters')


// TODO split up the mapstate into a few functions based on what we actually use, and into a few modules
const teamMembersMapState = (state) => {
    const { centerQuarters, lookups } = state.submission.core
    const teamAccountabilities = getTeamAccountabilities(state)
    const validStartQuarters = getValidStartQuarters(state.submission.core)
    return {
        centerQuarters, lookups, teamAccountabilities,
        validStartQuarters,
        browser:state.browser,
        ...state.submission.team_members
    }
}

class _EditCreate extends TeamMembersBase {
    constructor(props) {
        super(props)
        rebind(this, 'enableYearQuarterChange', 'setExitChoice')
    }
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
                <ButtonStateFlip loadState={this.props.teamMembers.saveState} offset='col-md-offset-1 col-md-8' wrapGroup={true}>Save</ButtonStateFlip>
            </Form>
        )
    }

    renderBasicInfo(modelKey, { disableBasicInfo }) {
        return (
            <div>
                <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" disabled={disableBasicInfo} required={!disableBasicInfo} />
                <SimpleField label="Last Initial" model={modelKey+'.lastName'} divClass="col-md-6" disabled={disableBasicInfo} required={!disableBasicInfo} />
                <SimpleField label="Email" model={modelKey+'.email'} divClass="col-md-8" controlProps={{type: 'email'}} />
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
        const teamMember = this.props.currentMember
        let reviewerCheckbox
        if (!teamMember || teamMember.teamYear != 1) {
            reviewerCheckbox = <CheckBox model={modelKey+'.isReviewer'} label="Is Reviewer" />
        }

        return (
            <div>
                {this.renderYearQuarter(modelKey, options)}
                <SimpleFormGroup label="Settings" required={true}>
                    {reviewerCheckbox}
                    <CheckBox model={modelKey+'.atWeekend'} label="On team at weekend" />
                    <CheckBox model={modelKey+'.xferIn'} label="Transfer In" />
                </SimpleFormGroup>
                <SimpleFormGroup label="Comment" divClass="col-md-8">
                    <NullableTextAreaControl model={modelKey+'.comment'} />
                </SimpleFormGroup>
                <SimpleFormGroup label="Team Status">
                    {this.renderWithdrawGroup(modelKey, options)}
                </SimpleFormGroup>
            </div>
        )
    }

    enableYearQuarterChange(e) {
        this.props.dispatch(formActions.change(`${TEAM_MEMBER_FORM_KEY}.${CHANGING_QUARTER_KEY}`, true))
        e.preventDefault()
    }

    renderYearQuarter(modelKey, options) {
        const teamMember = this.props.currentMember

        const incomingQuarter = this.getCenterQuarter()
        let quarterSelect, yearSelect
        let output = []

        const { disableYearQuarter } = options
        if (disableYearQuarter) {
            quarterSelect = (
                <p className="form-control-static">
                    {centerQuarterData.getLabel(incomingQuarter)}&nbsp;
                    <a href="#" onClick={this.enableYearQuarterChange}>Change Year/Quarter</a>
                </p>
            )
            yearSelect = (
                <p className="form-control-static">Team {teamMember.teamYear}</p>
            )
        } else {
            if (teamMember[CHANGING_QUARTER_KEY]) {
                output.push(
                    <Alert key="a" alert="warning">
                        Changing team member's starting quarter is a very uncommon action, please check with your
                        Regional Statistician to ensure you are taking the right action.
                    </Alert>
                )
            }
            quarterSelect = (
                <SimpleSelect
                        model={modelKey+'.incomingQuarter'} items={this.props.validStartQuarters}
                        emptyChoice={teamMember.incomingQuarter? undefined : ' '}
                        keyProp="quarterId" getLabel={centerQuarterData.getLabel} />
            )

            yearSelect = (
                <Control.select model={modelKey+'.teamYear'} disabled={disableYearQuarter} className="form-control">
                    <option value="1">Team 1</option>
                    <option value="2">Team 2</option>
                </Control.select>
            )
        }

        output.push(
            <SimpleFormGroup key="b" label="Starting Quarter" required={!disableYearQuarter}>
                {quarterSelect}
            </SimpleFormGroup>
        )

        output.push(
            <SimpleFormGroup key="c" label="Team Year" divClass="col-md-4" required={!disableYearQuarter}>
                {yearSelect}
            </SimpleFormGroup>
        )
        return output
    }

    setExitChoice(c) {
        this.props.dispatch(actions.setExitChoice(c))
    }

    renderWithdrawGroup(modelKey, options) {
        const { currentMember } = this.props
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
            const validCodes = this.props.lookups.withdraw_codes.filter((code) => {
                return (code.context == 'team_member' || code.context == 'all') && code.active
            })
            content = (
                <div>
                    <Alert alert="info">&nbsp;{EXIT_CHOICES_HELP.wd}</Alert>
                    <label>Withdraw Reason</label>
                    <SimpleSelect
                            model={modelKey+'.withdrawCode'} items={validCodes}
                            labelProp="display" keyProp="id" emptyChoice=" " />
                </div>
            )
            break
        }
        return (
            <div>
                <ModeSelectButtons items={EXIT_CHOICES} current={currentMember.exitChoice} activeClasses="btn btn-primary active"
                                   onClick={this.setExitChoice} ariaGroupDesc="Sort Preferences" />
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

    renderRpp(modelKey) {
        return (
            <div>
                <SimpleField label="CAP Registrations" model={modelKey+'.rppCap'} divClass="col-md-2" />
                <SimpleField label="CPC Registrations" model={modelKey+'.rppCpc'} divClass="col-md-2" />
                <SimpleField label="LF Registrations" model={modelKey+'.rppLf'} divClass="col-md-2" />
            </div>
        )
    }

    renderGitwTdo(modelKey) {
        const { lookups } = this.props
        let tdoSelect = <BooleanSelect model={modelKey+'.tdo'} emptyChoice=" " labels={TDO_LABELS} className="form-control tdo" />
        if (lookups.user.canSubmitMultipleTdos) {
            tdoSelect = <SimpleSelect model={modelKey+'.tdo'} emptyChoice=" " items={TDO_OPTIONS} className="form-control tdo" keyProp='k' labelProp='k' />
        }
        return (
            <div>
                <SimpleFormGroup label="GITW" required={true}>
                    <BooleanSelect model={modelKey+'.gitw'} emptyChoice=" " labels={GITW_LABELS} className="form-control gitw" />
                </SimpleFormGroup>
                <SimpleFormGroup label="TDO" required={true} divClass="col-md-6 boolSelect">
                    {tdoSelect}
                </SimpleFormGroup>
            </div>
        )
    }

    saveTeamMember(data) {
        const { centerId, reportingDate } = this.props.params

        return this.props.dispatch(actions.stashTeamMember(centerId, reportingDate, data)).then((result) => {
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
@connectRedux(teamMembersMapState)
export class TeamMembersEdit extends _EditCreate {
    constructor(props) {
        super(props)
        rebind(this, 'deleteTeamMember')
    }

    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentMember, params, dispatch, teamMembers } = this.props
        if (!currentMember || currentMember.id != params.teamMemberId) {
            const item = teamMembers.data[params.teamMemberId]
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
        return { disableYearQuarter: !this.props.currentMember[CHANGING_QUARTER_KEY] }
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
                <h3>Edit Team Member {fullName(teamMember)}</h3>

                <MessagesComponent messages={messages} />

                {super.render()}

                {this.renderDeleteFlow()}
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
                <div className="row">
                    <div className={className}>
                        {this.renderRpp(modelKey, options)}
                    </div>
                    <div className={className}>&nbsp;</div>
                </div>
            </div>
        )
    }

    renderDeleteFlow() {
        const { currentMember, lookups } = this.props
        let extraConfirm, spiel
        if (!currentMember.id) {
            return
        } else if (currentMember.meta && currentMember.meta.canDelete) {
            spiel = <p>Deleting a team member will cause them to be permanently removed.</p>
        } else if (lookups.user.canOverrideDelete) {
            extraConfirm = fullName(currentMember)
            spiel = (
                <div>
                    <p><b>Regional Statisticians</b> - This team member cannot be deleted by a statistician.
                    It is a very rare occasion to delete a team member, note most cases you would want to
                    appropriately withdraw them, or transfer them.</p>

                    <p>You <i>can</i> override the delete, but please be advised that this
                    will change reports, including GITW/TDO values,
                    potentially held accountabilities, and more.</p>

                    <p>If you wish to continue, please enter the full name '{extraConfirm}' below.</p>
                </div>
            )
        } else {
            return
        }
        return (
            <div style={{maxWidth: '80em', marginTop: '3em'}}>
                <DeleteWarning
                        model={TEAM_MEMBER_FORM_KEY} noun="Team Member" spiel={spiel}
                        extraConfirm={extraConfirm} onSubmit={this.deleteTeamMember}
                        buttonState={this.props.teamMembers.saveState} />
            </div>
        )
    }

    deleteTeamMember(data) {
        data = Object.assign({}, data, {action: 'delete'})
        return this.saveTeamMember(data)
    }
}

@connectRedux(teamMembersMapState)
export class TeamMembersAdd extends _EditCreate {
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
