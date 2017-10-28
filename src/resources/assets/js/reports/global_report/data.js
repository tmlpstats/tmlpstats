import Immutable from 'immutable'

import { TabbedReportManager } from '../tabbed_report/manager'
import Api from '../../api'

const _baseKey = Immutable.Record({
    regionAbbr: '',
    reportingDate: '',
    page: null
})

export class GlobalReportKey extends _baseKey {
    queryParams() {
        return {region: this.regionAbbr, reportingDate: this.reportingDate}
    }
}



export const reportData = new TabbedReportManager({
    prefix: 'reports/GlobalReport',
    findRoot: (state) => state.reports.global_report,
    actions: {
        load: {
            api: Api.GlobalReport.getReportPagesByDate
        }
    }
})
