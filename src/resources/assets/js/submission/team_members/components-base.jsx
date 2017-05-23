import { SubmissionBase } from '../base_components'
import * as actions from './actions'

export const GITW_LABELS = ['Ineffective', 'Effective']
export const TDO_LABELS = ['N', 'Y']

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
