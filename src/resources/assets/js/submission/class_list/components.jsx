import { Link, withRouter } from 'react-router'
import { connect } from 'react-redux'
import { Field } from 'react-redux-form'

import { Form, SimpleField, SimpleSelect, AddOneLink } from '../../reusable/form_utils'
import { Promise, objectAssign, arrayFind } from '../../reusable/ponyfill'
import { ModeSelectButtons, LoadStateFlip } from '../../reusable/ui_basic'

import { SubmissionBase, React } from '../base_components'
import { TEAM_MEMBERS_COLLECTION_FORM_KEY, TEAM_MEMBER_FORM_KEY } from './reducers'
import { classListSorts, teamMembersCollection } from './data'
import * as actions from './actions'

const GITW_CHOICES = [
    {key: false, label: 'Ineffective'},
    {key: true, label: 'Effective'}
]

const TDO_CHOICES = [
    {key: 0, label: 'N'},
    {key: 1, label: 'Y'}
]

class ClassListBase extends SubmissionBase {
    // Check the loading state of our initial data, and dispatch a loadClassList if we never loaded
    checkLoading() {
        const { loading, dispatch } = this.props
        if (loading.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            dispatch(actions.loadClassList(centerId, reportingDate))
            return false
        }
        return (loading.state == 'loaded')
    }
}

class ClassListIndexView extends ClassListBase {
    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const baseUri = this.baseUri()
        const changeSort = (newSort) => this.props.dispatch(teamMembersCollection.changeSortCriteria(newSort))
        var teamMemberRows = []
        teamMembersCollection.iterItems(this.props.teamMembers, (teamMember, key) => {
            teamMemberRows.push(
                <TeamMemberIndexRow key={key} teamMember={teamMember} baseUri={baseUri} />
            )
        })

        return (
            <div>
                <h3>Class List</h3>
                <ModeSelectButtons items={classListSorts} current={this.props.teamMembers.meta.sort_by}
                                   onClick={changeSort} ariaGroupDesc="Sort Preferences" />
                <table className="table submissionClassList">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Year</th>
                            <th>GITW</th>
                            <th>TDO</th>
                        </tr>
                    </thead>
                    <tbody>{teamMemberRows}</tbody>
                </table>
                <AddOneLink link={`${baseUri}/class_list/add`} />
            </div>
        )
    }
}

class TeamMemberIndexRow extends React.PureComponent {
    render() {
        const { teamMember } = this.props
        const modelKey = `${TEAM_MEMBERS_COLLECTION_FORM_KEY}.${teamMember.id}`
        return (
            <tr>
                <td>
                    <Link to={`${this.props.baseUri}/class_list/edit/${teamMember.id}`}>
                        {teamMember.firstName} {teamMember.lastName}
                    </Link>
                </td>
                <td>T{teamMember.teamYear}</td>
                <td className="gitw"><SimpleSelect model={modelKey+'.gitw'} items={GITW_CHOICES} emptyChoice=" " /></td>
                <td className="tdo"><SimpleSelect model={modelKey+'.tdo'} items={TDO_CHOICES} emptyChoice=" " /></td>
            </tr>
        )
    }
}

class _EditCreate extends ClassListBase {
    render() {
        const modelKey = TEAM_MEMBER_FORM_KEY
        const options = this.getRenderOptions()

        return (
            <Form className="form-horizontal" model={modelKey} onSubmit={this.saveTeamMember.bind(this)}>
                <div className="row">
                    <div className="col-lg-6">
                        {this.renderBasicInfo(modelKey, options)}
                    </div>
                    <div className="col-lg-6">
                        {this.renderRegPrefs(modelKey, options)}
                    </div>
                </div>
                <div className="row">
                    <div className="col-lg-6">
                        {this.renderTravelRoom(modelKey, options)}
                    </div>
                    <div className="col-lg-6">
                        {this.renderGitwTdo(modelKey, options)}
                    </div>
                </div>
                <div className="form-group">
                    <div className="col-sm-offset-2 col-sm-8">
                        <LoadStateFlip loadState={this.props.loading}>
                            <button className="btn btn-primary" type="submit">Save</button>
                        </LoadStateFlip>
                    </div>
                </div>
            </Form>
        )
    }

    renderBasicInfo(modelKey, { disableBasicInfo, disableYearQuarter }) {
        return (
            <div>
                <SimpleField label="First Name" model={modelKey+'.firstName'} divClass="col-md-6" disabled={disableBasicInfo} />
                <SimpleField label="Last Name" model={modelKey+'.lastName'} divClass="col-md-6" disabled={disableBasicInfo} />
                <SimpleField label="Team Year" model={modelKey+'.teamYear'} divClass="col-md-4" customField={true}>
                    <select disabled={disableYearQuarter}>
                        <option value="1">Team 1</option>
                        <option value="2">Team 2</option>
                    </select>
                </SimpleField>
                <SimpleField label="Email" model={modelKey+'.email'} divClass="col-md-8" />
            </div>
        )
    }

    renderRegPrefs() {
        return <div>TODO isReviewer atWeekend xferIn xferOut ctw withdrawCode</div>
    }

    renderTravelRoom() {
        return <div>TODO travel room</div>
    }

    renderGitwTdo() {
        return <div>TODO GITW TDO</div>
    }
}

// Detailed edit of class list
class ClassListEditView extends _EditCreate {
    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentMember, params, dispatch, teamMembers } = this.props
        if (!currentMember || currentMember.id != params.teamMemberId) {
            const item = teamMembers.collection[params.teamMemberId]
            if (item) {
                setTimeout(() => {
                    dispatch(actions.chooseTeamMember(item))
                })
            }
            return false
        }
        return true
    }

    getRenderOptions() {
        return { disableYearQuarter: true }
    }

    saveTeamMember() {
        // TODO
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }
        return (
            <div>
                <h3>Edit Team Member</h3>
                {super.render()}
            </div>
        )
    }
}

class ClassListAddView extends _EditCreate {
    getRenderOptions() {
        return {}
    }

    saveTeamMember() {
        // TODO
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
}

const mapStateToProps = (state) => state.submission.class_list

const connector = connect(mapStateToProps)

export const ClassListIndex = connector(ClassListIndexView)
export const ClassListEdit = connector(ClassListEditView)
export const ClassListAdd = connector(ClassListAddView)
