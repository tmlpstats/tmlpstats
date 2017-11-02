import { SubmissionBase } from '../base_components'
import * as actions from './actions'

export const GITW_LABELS = ['Ineffective', 'Effective']
export const TDO_LABELS = ['N', 'Y']
export const TDO_OPTIONS = [
    {k:0}, {k:1}, {k:2},
    {k:3}, {k:4}, {k:5},
    {k:6}, {k:7}, {k:8},
    {k:9}, {k:10},
]

export class TeamMembersBase extends SubmissionBase {
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
