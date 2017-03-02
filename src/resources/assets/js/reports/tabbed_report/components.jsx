import { Link } from 'react-router'
import React, { Component, PropTypes, PureComponent } from 'react'
import Immutable from 'immutable'
import { routerShape } from 'react-router/lib/PropTypes'

import { createImmutableMemoize } from '../../reusable/immutable_utils'

const reportRootShape = PropTypes.shape({
    root: PropTypes.array.isRequired,
    children: PropTypes.object.isRequired
})

const contextShape = PropTypes.shape({
    responsiveLabel: PropTypes.func.isRequired,
    reportUri: PropTypes.func.isRequired,
    fullReport: reportRootShape.isRequired
})

export class TabbedReport extends Component {
    static propTypes = {
        fullReport: PropTypes.object,
        reportContext: contextShape,
        tabs: PropTypes.arrayOf(PropTypes.string),
    }

    constructor(props) {
        super(props)
        this.contentLookup = createImmutableMemoize()
    }

    render() {
        const { tabs, reportContext } = this.props
        let navTabs = []
        let content

        reportContext.fullReport.root.forEach((id) => {
            const report = reportContext.fullReport.children[id]
            const active = (report.id == tabs[0])
            const uriBasis = report.type == 'grouping' ? [id, report.children[0]] : [id]
            const href = reportContext.reportUri(uriBasis)
            navTabs.push(
                <li className={active? 'active' : ''} key={id}><Link to={href}>{reportContext.responsiveLabel(report)}</Link></li>
            )

            if (active) {
                const doneSet = reportContext.props.loader.done
                const contentTemp = Immutable.List(tabs.map((tab) => doneSet.get(tab, '')))
                const contentVec = this.contentLookup(contentTemp)
                content = <ReportContent key={id} report={report} path={tabs} active={active} contentVec={contentVec} reportContext={reportContext} />
            }
        })

        return (
            <div>
                <ul id="tabs" className="nav nav-tabs tabs-top brief-tabs">
                    {navTabs}
                </ul>
                <div>
                    <div className="tab-content">{content}</div>
                </div>
            </div>
        )
    }
}

export class ReportContent extends PureComponent {
    static propTypes = {
        reportContext: contextShape,
        path: PropTypes.arrayOf(PropTypes.string),

    }
    static defaultProps = {
        level: 1
    }
    static contextTypes = { router: routerShape }

    render() {
        const { report, active, reportContext, level, path, contentVec } = this.props

        if (report.type == 'grouping') {
            let tabs = []
            let content

            report.children.forEach((cid) => {
                const report = reportContext.fullReport.children[cid]
                const handler = () => {
                    this.context.router.push(reportContext.reportUri(path.slice(0, level).concat([cid])))
                }
                let class2 = 'btn-default'
                if (path && path[level] == cid) {
                    content = <ReportContent level={level + 1} path={path} active={true} contentVec={contentVec} reportContext={reportContext} report={report} />
                    class2 = 'btn-primary'
                }
                tabs.push(
                    <button key={cid} type="button" className={'btn ' + class2} onClick={handler}>{report.name}</button>
                )

            })
            if (path && !content) {
                setTimeout(() => {
                    this.context.router.push(reportContext.reportUri(path.concat([report.children[0]])))
                })
            }
            return (
                <div style={activeStyle(active)}>
                    <div className="btn-group grouping" role="group">
                        {tabs}
                    </div>
                    <div>{content}</div>
                </div>
            )
        } else {
            // yuck, jQuery legacy cruft
            const { $ } = window
            let divId = 'content-' + report.id
            console.log('rendering inner', report.id)
            setTimeout(() => {
                const container = $(`#${divId}`)
                window.updateDates(container)
                window.initDataTables(undefined, undefined, container)
            })
            let content = { __html: contentVec.get(level - 1) || $('#loader').html() }
            return <div id={divId} dangerouslySetInnerHTML={content} />
        }
    }
}

const hidden = {display: 'none'}
const normal = {}
function activeStyle(active) {
    return active? normal : hidden
}
