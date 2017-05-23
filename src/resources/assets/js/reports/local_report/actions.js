import { reportConfigData } from '../data'
import Api from '../../api'


export function loadConfig(reportKey) {
    const params = reportKey.queryParams()
    return reportConfigData.manager.load(params, {
        api: Api.LocalReport.reportViewOptions,

        successHandler(data, { dispatch }) {
            dispatch(reportConfigData.replaceItem(reportKey, data))
        }
    })
}
