import React from 'react';
import { Route, IndexRedirect } from 'react-router'
import { bindActionCreators } from 'redux'
import { connect } from 'react-redux'

import SubmissionNav from './Nav'
import * as Pages from './pages'


const steps = new Map([
    // The steps key is some metadata about the steps, maybe redundant but we'll leave it for now.
    ['scoreboard', {name: 'Scoreboard'}],
    ['applications', {name: 'Team Expansion'}],
    ['classlist', {name: 'Class List'}],
    ['courses', {name: 'Courses'}],
    ['review', {name: 'Review'}],
]);

class SubmissionFlowComponent extends React.Component {
    render() {
        return (
            <div className="row">
                <div className="col-md-2"><SubmissionNav params={this.props.params} steps={steps} location={this.props.location} /></div>
                <div className="col-md-10">
                    <div className="panel panel-default">
                        <div className="panel-body">
                            {this.props.children}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {}
}

const mapDispatchToProps = (dispatch) => bindActionCreators({}, dispatch)

const SubmissionFlowRoot = connect(mapStateToProps, mapDispatchToProps)(SubmissionFlowComponent)

export default function SubmissionFlow() {
    return (
        <Route path="/center/:centerId/submission" component={SubmissionFlowRoot}>
            <IndexRedirect to="scoreboard" />
            <Route path="scoreboard" component={Pages.Scoreboard} />
            <Route path="applications" component={Pages.ApplicationsIndex} />
            <Route path="applications/edit/:appId" component={Pages.ApplicationsEdit} />
            <Route path="classlist" component={Pages.ClassList} />
            <Route path="courses" component={Pages.CoursesIndex} />
            <Route path="courses/add" component={Pages.CoursesAdd} />
            <Route path="courses/edit/:courseId" component={Pages.CoursesEdit} />
            <Route path="review" component={Pages.Review} />
        </Route>
    );
}
