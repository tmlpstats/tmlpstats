import { Link } from 'react-router'

import { React, SubmissionBase } from '../base_components'


export default class SubmissionNav extends SubmissionBase {
    render() {
        const { tabbed } = this.props

        var steps = []
        const s = this.props.steps
        if (s) {
            var baseUri = this.baseUri()
            s.forEach((v) => {
                var destPath = `${baseUri}/${v.key}`
                var active = (this.props.location.pathname.substr(0, destPath.length) == destPath)
                if (!tabbed) {
                    var linkCls = 'list-group-item' + (active? ' active' : '')
                    steps.push(<Link key={v.key} className={linkCls} to={destPath}>{v.name}</Link>)
                } else {
                    steps.push(
                        <li key={v.key} role="presentation" className={active? 'active' : ''}>
                            <Link to={destPath}>{v.name}</Link>
                        </li>
                    )
                }
            })
        }
        if (tabbed) {
            return (
                <ul className="nav nav-tabs submission-nav">
                    {steps}
                </ul>
            )
        } else {
            return (
                <div className="list-group submission-nav">
                    {steps}
                </div>
            )
        }
    }
}
