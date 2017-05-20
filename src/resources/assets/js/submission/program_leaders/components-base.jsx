import { SubmissionBase } from '../base_components'
import Immutable from 'immutable'
import * as actions from './actions'

export const ATTENDING_LABELS = ['N', 'Y']

export const ACCOUNTABILITY_DISPLAY = Immutable.OrderedMap([
    ['programManager', 'Program Manager'],
    ['classroomLeader', 'Classroom Leader'],
])

export class ProgramLeadersBase extends SubmissionBase {
    static mapStateToProps(state) {
        return state.submission.program_leaders
    }

    indexBaseUri() {
        return this.baseUri() + '/team_members'
    }

    programLeadersBaseUri() {
        return this.baseUri() + '/program_leaders'
    }

    // Check the loading state of our initial data, and dispatch a loadProgramLeaders if we never loaded
    checkLoading() {
        const { programLeaders: {loadState: loading}, dispatch } = this.props
        if (loading.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            dispatch(actions.loadProgramLeaders(centerId, reportingDate))
            return false
        }
        return (loading.state == 'loaded')
    }
}
