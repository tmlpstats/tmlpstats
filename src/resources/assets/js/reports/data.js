import ImmutableMapLoader from '../reusable/redux_loader/ImmutableMapLoader'
import Api from '../api'

export const reportConfig = new ImmutableMapLoader({
    prefix: 'reports/ReportConfig',
    actions: {
        load: {
            api: Api.LocalReport.reportViewOptions,
            setLoaded: true
        }
    }
})
