import { Link } from 'react-router'

import { React, SubmissionBase } from '../base_components'


export default class SubmissionNav extends SubmissionBase {
    render() {
        const { tabbed, location, steps } = this.props

        let outSteps = []
        if (steps) {
            var baseUri = this.baseUri()
            steps.forEach((v) => {
                const destPath = `${baseUri}/${v.key}`
                const active = (location.pathname.substr(0, destPath.length) == destPath)
                if (!tabbed) {
                    const linkCls = 'list-group-item' + (active? ' active' : '')
                    outSteps.push(<Link key={v.key} className={linkCls} to={destPath}>{v.name}</Link>)
                } else {
                    outSteps.push(
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
                    {outSteps}
                </ul>
            )
        } else {
            return (
                <div className="list-group submission-nav">
                    {outSteps}
                </div>
            )
        }
    }
}
