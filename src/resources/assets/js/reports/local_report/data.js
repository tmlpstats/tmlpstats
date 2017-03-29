import Immutable from 'immutable'

import { TabbedReportManager } from '../tabbed_report/manager'
import Api from '../../api'

const _baseKey = Immutable.Record({
    centerId: '',
    reportingDate: '',
    page: null
})

export class LocalKey extends _baseKey {
    queryParams() {
        return {center: this.centerId, reportingDate: this.reportingDate}
    }
}

export const reportData = new TabbedReportManager({
    prefix: 'reports/LocalReport',
    findRoot: (state) => state.reports.local_report.reportData,
    actions: {
        load: {
            api: Api.LocalReport.getReportPages,
            setLoaded: true
        }
    }
})
