import React from 'react'

import { Control, Form, CheckBox, SimpleField, BooleanSelect, SimpleSelect, SimpleFormGroup, NullableTextAreaControl } from '../../reusable/form_utils'
import { ModeSelectButtons, ButtonStateFlip, Alert, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'
import { delayDispatch, connectRedux } from '../../reusable/dispatch'
import { FormTypeahead } from '../../reusable/typeahead'

import { centerQuarterData } from '../core/data'
import { makeAccountabilitiesSelector, makeQuartersSelector } from '../core/selectors'
import { TEAM_MEMBER_FORM_KEY } from './reducers'
import { EXIT_CHOICES, EXIT_CHOICES_HELP } from './exit_choice'
import * as actions from './actions'
import { TeamMembersBase, GITW_LABELS, TDO_LABELS } from './components-base'


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
                <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" disabled={disableBasicInfo} required={!disableBasicInfo} />
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
                <SimpleFormGroup label="Team Year" divClass="col-md-4" required={!disableYearQuarter}>
                    <Control.select model={modelKey+'.teamYear'} disabled={disableYearQuarter} className="form-control">
                        <option value="1">Team 1</option>
                        <option value="2">Team 2</option>
                    </Control.select>
                </SimpleFormGroup>
                <SimpleFormGroup label="Starting Quarter" required={!disableYearQuarter}>
                    {yearQuarter}
                </SimpleFormGroup>
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
@connectRedux(teamMembersMapState)
export class TeamMembersEdit extends _EditCreate {
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
