import { Link } from 'react-router'

import { React, SubmissionBase } from '../base_components'

export default class SubmissionNav extends SubmissionBase {
    render() {
        var steps = []
        const s = this.props.steps
        if (s) {
            var baseUri = this.baseUri()
            s.forEach((v) => {
                var destPath = `${baseUri}/${v.key}`
                var cls = 'list-group-item'
                if (this.props.location.pathname.substr(0, destPath.length) == destPath) {
                    cls += ' active'
                }
                steps.push(<Link key={v.key} className={cls} to={destPath}>{v.name}</Link>)
            })
        }
        return (
            <div className="list-group">
                {steps}
            </div>
        )
    }
}
