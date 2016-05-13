import React from 'react';
import { Link } from 'react-router'

export default class SubmissionNav extends React.Component {
    render() {
        var steps = [];
        var s = this.props.steps;
        if (s) {
            var centerId = this.props.params.centerId;
            s.forEach((v, k) => {
                var cb = () => this.props.setPage(k)
                steps.push(<li key={k}><b><Link to={`/center/${centerId}/submission/${k}`}>{v.name}</Link></b></li>);
            });
        }
        return (
            <ul>
                {steps}
            </ul>
        );
    }
}
