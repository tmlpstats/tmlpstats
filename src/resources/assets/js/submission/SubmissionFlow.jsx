import React from 'react'
import { Route, IndexRedirect } from 'react-router'
import * as Pages from './pages'

export default function SubmissionFlow() {
    return (
        <Route path="/center/:centerId/submission/:reportingDate" component={Pages.SubmissionFlowRoot}>
            <IndexRedirect to="scoreboard" />
            <Route path="scoreboard" component={Pages.Scoreboard} />
            <Route path="applications" component={Pages.ApplicationsIndex} />
            <Route path="applications/edit/:appId" component={Pages.ApplicationsEdit} />
            <Route path="applications/add" component={Pages.ApplicationsAdd} />
            <Route path="class_list" component={Pages.ClassListIndex} />
            <Route path="class_list/edit/:teamMemberId" component={Pages.ClassListEdit} />
            <Route path="class_list/add" component={Pages.ClassListAdd} />
            <Route path="courses" component={Pages.CoursesIndex} />
            <Route path="courses/edit/:courseId" component={Pages.CoursesEdit} />
            <Route path="courses/add" component={Pages.CoursesAdd} />
            <Route path="qtr_accountabilities" component={Pages.QuarterAccountabilities} />
            <Route path="review" component={Pages.Review} />
        </Route>
    )
}
