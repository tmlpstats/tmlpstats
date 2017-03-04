import { TabbedReportManager } from '../tabbed_report/manager'
import ReportsMeta from '../meta'
import Api from '../../api'


export const reportData = new TabbedReportManager({
    prefix: 'reports/GlobalReport',
    findRoot: (state) => state.reports.global_report,
    report: ReportsMeta['Global'],
    actions: {
        load: {
            api: Api.GlobalReport.getReportPagesByDate
        }
    }
})
