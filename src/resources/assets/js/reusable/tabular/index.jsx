import PropTypes from 'prop-types'
import React from 'react'
import Immutable from 'immutable'

import { defaultMemoize, createSelector } from 'reselect'
import { connect } from 'react-redux'

import { Glyphicon, cssClasses } from '../ui_basic'
import { compositeKey } from '../sort-helpers'
import { Column, TableState } from './types'
import { clickColumn } from './actions'


class TabularBase extends React.Component {
    static propTypes = {
        current: PropTypes.instanceOf(TableState),
        data: PropTypes.oneOfType([
            PropTypes.shape({forEach: PropTypes.func}),
            PropTypes.array,
        ]),
        tableClasses: PropTypes.string,
        idExtension: PropTypes.oneOfType([
            PropTypes.string,
            PropTypes.number,
        ])
    }

    constructor(props) {
        super(props)
        this._boundColumns = createBoundColumnsSelector(this)
        this.sortedRows = createSortedRowsSelector()
    }

    render() {
        const { props } = this
        let idExtension = props.idExtension? '-' + props.idExtension: ''
        const htmlId = `tbl-${this.config.name}${idExtension}`

        return (
            <table className={props.tableClasses} id={htmlId}>
                {this.renderHeading(htmlId)}
                {this.renderRows()}
            </table>
        )
    }

    boundColumns() {
        return this._boundColumns(this.config.columns, this.props.current.sort_by)
    }

    renderHeading(htmlId) {
        let headings = []
        const boundColumns = this.boundColumns()
        boundColumns.forEach((col) => {
            let ascDesc
            let classes = []
            if (col.sortable) {
                classes.push('tabular-sortable')
                if (col._sort) {
                    classes.push(col._sort)
                    // sort-by-alphabet{-alt} and sort-by-attributes{-alt}
                    const icon = 'sort-by-' + (col.sorter == 'string'? 'alphabet' : 'attributes') + (col._sort == 'asc'? '' : '-alt')
                    ascDesc = <Glyphicon icon={icon}  />
                }
            }
            headings.push(<th key={col.key} onClick={col._columnClick} className={cssClasses(classes)} aria-controls={htmlId}>{col.label}{ascDesc}</th>)
        })
        return (
            <thead><tr>{headings}</tr></thead>
        )
    }

    renderRows() {
        let rows = []
        const getPrimaryKey = this.config.getPrimaryKey
        this.sortedRows(this).forEach((row) => {
            let cols = []
            this.config.columns.forEach((col) => {
                const Component = col.component
                const data = col.selector(row) || col.default
                const rendered = Component? (<Component key={col.key} data={data} />) : <td key={col.key}>{data}</td>
                cols.push(rendered)
            })
            rows.push(<tr key={getPrimaryKey(row)}>{cols}</tr>)
        })
        return (
            <tbody>{rows}</tbody>
        )
    }

    onColumnClick(colKey, event) {
        event.persist()
        this.props.dispatch(clickColumn(this.config.name, colKey, event.ctrlKey || event.shiftKey || event.altKey))
        console.log('onColumnClick', colKey)
    }
}

function createSortedRowsSelector() {
    const sorterForColumns = defaultMemoize((columns, sort_by) => {
        const accessors = sort_by.map(x => columns.get(x[0]))
        const sortFunc = compositeKey(accessors.map((col, i) => [(col.sortSelector || col.selector), col.sorter, sort_by.get(i)[1]]).toJS())
        return { accessors, sortFunc }
    })
    return createSelector(
        (c) => c.props.data,
        (c) => c.props.current.sort_by,
        (c) => c.config.columns,
        (rows, sort_by, columns) => {
            if (!sort_by || !sort_by.size) {
                return rows
            }
            const { sortFunc } = sorterForColumns(columns, sort_by)
            return rows.slice().sort(sortFunc)
        }
    )
}

function createBoundColumnsSelector(component) {
    return defaultMemoize((columns, sort_by) => {
        const keyed_sort_by = Immutable.Map(sort_by)
        return columns.map((col) => {
            return col
                .set('_columnClick', component.onColumnClick.bind(component, col.key))
                .set('_sort', keyed_sort_by.get(col.key))
        })
    })
}

/**
 * Create a table component.
 * @param  object options [description]
 * @return React.Component A component used to render a table.
 */
export function buildTable(options) {
    const tableName = options.name
    const defaultState = options.defaultState || defaultTableState
    options.getPrimaryKey = options.getPrimaryKey || defaultPrimaryKeyFunc
    options.columns = Immutable.Seq.Indexed(options.columns)
        .map((c) => {
            return (c.set) ? c : new Column(c)
        })
        .toKeyedSeq()
        .mapEntries((x) => [x[1].key, x[1]])
        .toOrderedMap()

    class MyTable extends TabularBase {
        config = options
    }

    MyTable.defaultProps = {
        tableClasses: options.tableClasses || 'table'
    }

    MyTable.displayName = options.displayName || tableName

    if (options.noConnect) {
        return MyTable
    }

    function mapState(state) {
        return {
            current: state.tabular.tables.get(tableName, defaultState)
        }
    }

    return connect(mapState)(MyTable)
}

const defaultTableState = new TableState({})
const defaultPrimaryKeyFunc = x => x.id
