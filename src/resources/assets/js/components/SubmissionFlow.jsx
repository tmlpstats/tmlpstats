import React from 'react';
import { Route, IndexRedirect } from 'react-router'
import SubmissionNav from './Submission/Nav';
import * as Pages from './Submission/Pages';


const steps = new Map([
    // The steps key is some metadata about the steps, maybe redundant but we'll leave it for now.
    ['scoreboard', {name: 'Scoreboard'}],
    ['registrations', {name: 'Team Expansion'}],
    ['classlist', {name: 'Class List'}],
    ['courses', {name: 'Courses'}],
    ['review', {name: 'Review'}],
]);

class SubmissionFlowRoot extends React.Component {
    render() {
        return (
            <div className="row">
                <div className="col-md-2"><SubmissionNav params={this.props.params} steps={steps} /></div>
                <div className="col-md-10">{this.props.children}</div>
            </div>
        );
    }
}

export default function SubmissionFlow() {
    return (
        <Route path="/center/:centerId/submission" component={SubmissionFlowRoot}>
            <IndexRedirect to="scoreboard" />
            <Route path="scoreboard" component={Pages.Scoreboard} />
            <Route path="registrations" component={Pages.Registrations} />
            <Route path="classlist" component={Pages.ClassList} />
            <Route path="courses" component={Pages.Courses} />
            <Route path="review" component={Pages.Review} />
        </Route>
    );
}
