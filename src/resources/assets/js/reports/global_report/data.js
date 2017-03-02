import { TabbedReportManager } from '../tabbed_report/manager'
import Reports from '../../classic/reports-generated'
import Api from '../../api'


export const reportData = new TabbedReportManager({
    prefix: 'reports/GlobalReport',
    findRoot: (state) => state.reports.global_report,
    report: Reports['Global'],
    actions: {
        load: {
            api: Api.GlobalReport.getReportPagesByDate
        }
    }
})
