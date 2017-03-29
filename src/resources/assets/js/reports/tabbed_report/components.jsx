import { Link } from 'react-router'
import React, { Component, PropTypes, PureComponent } from 'react'
import Immutable from 'immutable'
import { routerShape } from 'react-router/lib/PropTypes'

import { createImmutableMemoize } from '../../reusable/immutable_utils'

const contextShape = PropTypes.shape({
    responsiveLabel: PropTypes.func.isRequired,
    getContent: PropTypes.func.isRequired,
    reportUri: PropTypes.func.isRequired,
    fullReport: PropTypes.object.isRequired
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

        reportContext.fullReport._root.children.forEach((id) => {
            const report = reportContext.fullReport[id]
            const active = (report.id == tabs[0])
            const uriBasis = report.type == 'grouping' ? [id, report.children[0]] : [id]
            const href = reportContext.reportUri(uriBasis)
            navTabs.push(
                <li className={active? 'active' : ''} key={id}><Link to={href}>{reportContext.responsiveLabel(report)}</Link></li>
            )

            if (active) {
                const contentTemp = Immutable.List(tabs.map((tab) => reportContext.getContent(tab)))
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
        contentVec: PropTypes.instanceOf(Immutable.List),
        level: PropTypes.number,
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
            const jump = (cid) => {
                this.context.router.push(reportContext.reportUri(path.slice(0, level).concat([cid])))
            }

            report.children.forEach((cid) => {
                const report = reportContext.fullReport[cid]
                const handler = () => jump(cid)
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
                    jump(report.children[0])
                })
            }
            return (
                <div style={activeStyle(active)}>
                    <h3>{report.name}</h3>
                    <div className="btn-group grouping" role="group">
                        {tabs}
                    </div>
                    <div>{content}</div>
                </div>
            )
        }

        // This is not a grouping, let's render a content tab.
        const pageContent = contentVec.get(level - 1)
        if (report.render == 'react' && pageContent) {
            // Render a modern component-based report. pageContent can be almost anything, but most likely is

            // NOTE - PageComponent must be a CamelCase variable or else the react compiler thinks it's a
            const PageComponent = reportContext.pageComponent(report)
            return <PageComponent initialData={pageContent} reportContext={reportContext} />
        } else {
            // Render a classic HTML report

            // yuck, jQuery legacy cruft
            const { $ } = window
            let divId = 'content-' + report.id
            console.log('rendering inner', report.id)
            let rawHtml = pageContent || $('#loader').html()
            let followScript
            if (/SCRIPTS_FOLLOW/.test(rawHtml)) {
                let parts = rawHtml.split('<!-- SCRIPTS_FOLLOW -->')
                rawHtml = parts[0]
                followScript = parts[1]
            }

            setTimeout(() => {
                const container = $(`#${divId}`)
                window.updateDates(container)
                window.initDataTables(undefined, undefined, container)
                if (followScript) {
                    console.log('Running follow-up-script')
                    $('body').append(followScript)
                }
            })
            let content = { __html: rawHtml }
            return <div id={divId} dangerouslySetInnerHTML={content} />
        }
    }
}

const hidden = {display: 'none'}
const normal = {}
function activeStyle(active) {
    return active? normal : hidden
}
