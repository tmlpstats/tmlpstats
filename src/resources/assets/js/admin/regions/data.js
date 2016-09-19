import { Schema, arrayOf } from 'normalizr'

import { objectAssign } from '../../reusable/ponyfill'
import SimpleReduxLoader from '../../reusable/redux_loader/simple'
import FormReduxLoader from '../../reusable/redux_loader/rrf'
import { LoadingMultiState } from '../../reusable/reducers'
import Api from '../../api'


/* DATA RELATIONS SCHEMA (normalizr) */

const regionQuarterSchema = new Schema('regionQuarters')
const centerSchema = new Schema('centers', {idAttribute: 'abbreviation'})
export const regionSchema = new Schema('regions')
regionSchema.define({
    centers: arrayOf(centerSchema),
    currentQuarter: regionQuarterSchema,
    quarters: arrayOf(regionQuarterSchema)
})

/* DATA MANAGEMENT LOADERS */

export const regionsData = new SimpleReduxLoader({
    prefix: 'admin/regions',
    loader: Api.Admin.Region.getRegion,
    transformData: (data) => {
        if (data.region && data.centers) {
            data = objectAssign({}, data.region, data, {quarter: null, region: undefined})
        }
        if (data.centers) {
            data.centers = data.centers.map((x) => {
                return objectAssign(x, {abbreviation: x.abbreviation.toLowerCase()})
            })
        }
        return data
    }
})

export const centersData = new SimpleReduxLoader({
    prefix: 'admin/centers'
})

export const scoreboardLockData = new FormReduxLoader({
    prefix: 'admin/scoreboardLock',
    model: 'admin.regions.scoreboardLock.data',
    loader: Api.Scoreboard.getScoreboardLockQuarter,
    extraLMS: ['saveState']
})

export const saveScoreboardLock = new LoadingMultiState('admin/scoreboardLock/saveState')
