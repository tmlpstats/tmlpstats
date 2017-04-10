import Immutable from 'immutable'

import Api from '../api'
import { lookupsData } from './manager'


export const regionCentersData = lookupsData.addScope({
    scope: 'region_centers',

    load(regionId) {
        const scope = this
        return scope.attemptCached(regionId, (dispatch) => {
            return dispatch(scope.manager.load({region: regionId}, {
                api: Api.Lookups.getRegionCenters,
                transformData(data) {
                    return Immutable.OrderedMap()
                        .withMutations((map) => {
                            for (let key in data) {
                                let value = data[key]
                                value.abbreviation = value.abbreviation.toLowerCase()
                                map.set(key, value)
                            }
                        })
                        .sortBy(x => x.abbreviation)
                },
                successHandler(data) {
                    dispatch(scope.replaceItem(regionId, data))
                }
            }))
        })
    }
})
