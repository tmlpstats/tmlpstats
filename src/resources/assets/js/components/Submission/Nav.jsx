import { Link } from 'react-router'

import { React, SubmissionBase } from './Base'

export default class SubmissionNav extends SubmissionBase {
    render() {
        var steps = [];
        var s = this.props.steps;
        if (s) {
            var baseUri = this.baseUri()
            s.forEach((v, k) => {
                var destPath = `${baseUri}/${k}`
                var cls="list-group-item"
                if (this.props.location.pathname.startsWith(destPath)) {
                    cls += " active"
                }
                steps.push(<Link key={k} className={cls} to={destPath}>{v.name}</Link>);
            });
        }
        return (
            <div className="list-group">
                {steps}
            </div>
        );
    }
}
