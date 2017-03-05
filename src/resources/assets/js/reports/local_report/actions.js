import { reportConfig } from '../data'

export function loadConfig(reportKey) {
    const params = reportKey.queryParams()
    return reportConfig.load(params, {
        successHandler(data, { dispatch }) {
            dispatch(reportConfig.replaceItem(reportKey, data))
        }
    })
}
