import { loadCourses } from '../courses/actions'
import { loadApplications } from '../applications/actions'
import { loadTeamMembers } from '../team_members/actions'
import { loadScoreboard } from '../scoreboard/actions'

export const loadPairs = [
    // [ action creator, loading state ]
    [loadApplications, (s) => s.applications.loading],
    [loadCourses, (s) => s.courses.loading],
    [loadTeamMembers, (s) => s.team_members.teamMembers.loadState],
    [loadScoreboard, (s) => s.scoreboard.loading]
]
