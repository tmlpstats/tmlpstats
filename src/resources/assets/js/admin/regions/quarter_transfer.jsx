import React from 'react'
import { withRouter } from 'react-router'

import { connectRedux, delayDispatch } from '../../reusable/dispatch'
import { Alert } from '../../reusable/ui_basic'

import { CenterList, CenterUpdateSelector } from './center_selector'
import RegionBase from './RegionBase'
import * as actions from './actions'

const mapStateToProps = (state) => state.admin.regions
const MODEL = 'admin.regions.quarterTransfer.data'

@connectRedux(mapStateToProps)
export class RegionQuarterTransfers extends RegionBase {
    render() {
        if (!this.checkRegions()) {
            return <div>Loading...</div>
        }
        const baseUri = this.regionQuarterBaseUri()
        const linkPrefix = `${baseUri}/manage_transfers/from`
        return (
            <CenterList centers={this.regionCenters().centers}
                        linkPrefix={linkPrefix} />
        )
    }
}

@withRouter
@connectRedux(mapStateToProps)
export class RunQuarterTransfer extends RegionBase {
    checkTransfer() {
        const { centerId, quarterId } = this.props.params
        const { data, loadState } = this.props.quarterTransfer
        if ((!data || data.centerId != centerId) && loadState.available) {
            const { region } = this.regionCenters()
            if (region) {
                delayDispatch(this, actions.initializeQuarterTransferData(centerId, quarterId))
            }
            return false
        }
        return loadState.loaded
    }

    manageTransfersUrl() {
        return this.regionQuarterBaseUri() + '/manage_transfers'
    }

    render() {
        if (!this.checkRegions() || !this.checkTransfer()) {
            return <div>Loading...</div>
        }

        const { dispatch, centers, params: { centerId }, quarterTransfer: { data, saveState }, router } = this.props

        let center
        const otherCenter = data.applyCenter.length != 1 || data.applyCenter[0] != centerId
        if (!otherCenter) {
            center = centers.data[centerId]
        }

        const onSubmit = (center, quarter) => actions.runQuarterTransfer(center, quarter)

        return (
            <div>
                <h2>Transfer Quarter Data - {otherCenter ? 'Multiple Centers' : center.name}</h2>
                <Alert alert="info">
                    Transfer team members, applications, and courses from the previous quarter to new quarter.
                </Alert>
                <CenterUpdateSelector model={MODEL}
                                      buttonLabel="Copy"
                                      centerId={centerId}
                                      centers={this.regionCenters().centers}
                                      data={data}
                                      saveState={saveState}
                                      onSubmit={onSubmit}
                                      onCompleteUrl={this.manageTransfersUrl()}
                                      dispatch={dispatch}
                                      router={router} />
                <div style={{paddingTop: '20em'}}>&nbsp;</div>
            </div>
        )
    }
}
