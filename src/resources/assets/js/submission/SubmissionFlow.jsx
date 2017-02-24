import React from 'react'
import { Route, IndexRedirect, Redirect } from 'react-router'
import * as Pages from './pages'

export default function SubmissionFlow() {
    return (
        <Route path="/center/:centerId/submission/:reportingDate" component={Pages.SubmissionFlowRoot}>
            <IndexRedirect to="scoreboard" />
            <Route path="scoreboard" component={Pages.Scoreboard} onEnter={Pages.Scoreboard.onRouteEnter} />
            <Route path="applications" component={Pages.ApplicationsIndex} />
            <Route path="applications/edit/:appId" component={Pages.ApplicationsEdit} />
            <Route path="applications/add" component={Pages.ApplicationsAdd} />
            <Route path="team_members" component={Pages.TeamMembersIndex} />
            <Route path="team_members/edit/:teamMemberId" component={Pages.TeamMembersEdit} />
            <Route path="team_members/add" component={Pages.TeamMembersAdd} />
            <Route path="courses" component={Pages.CoursesIndex} />
            <Route path="courses/edit/:courseId" component={Pages.CoursesEdit} />
            <Route path="courses/add" component={Pages.CoursesAdd} />
            <Redirect from="qtr_accountabilities" to="next_qtr_accountabilities" />
            <Route path="next_qtr_accountabilities" component={Pages.QuarterAccountabilities} />
            <Route path="review" component={Pages.Review} onEnter={Pages.Review.onRouteEnter}  />
        </Route>
    )
}
