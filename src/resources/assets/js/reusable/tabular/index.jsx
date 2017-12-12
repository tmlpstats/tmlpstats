/**
 The tabular library provides a way to make sortable table components with other features.

 Features include:
    * Efficient sorting clicking on column headings including compound sorts
    * Manages its own state without connecting more reducers
    * Tries to minimize expensive re-renders

 To use it, you first must create a component with buildTable:

const MyTable  = buildTable({
    name: 'unique_slug',
    columns: [
        {key: 'name', label: 'Name', default: 'N/A'}
        {key: 'acc', label: 'Accountability', selector: row => row.accountability.display},
        {key: 'updatedAt', label: 'Updated At', sorter: 'moment',
         headingClasses: ['hidden-print'], component: PrettyDate}
    ]
})

Columns specify the columns in the table. Column specifiers are objects with
the following properties:

|    Property    |    Type   |                    Description                    |       Default        |
| -------------: | --------- | ------------------------------------------------- | -------------------- |
|            key | string    | Required. Must be a unique key for this column.   |                      |
|          label | string    | Required. Heading label.                          |                      |
|       selector | func      | A function to select the value for this column.   | x => x[key]          |
|        default | <any>     | Default value if the result of selector is empty  | undefined            |
|      component | Component | A react component to render this row.             | <internal component> |
|       sortable | bool      | Set to false to disable column sorting            | true                 |
|         sorter | string    | Sort method, one of 'string', 'number', 'moment'. | 'string'             |
|   sortSelector | func      | A selector to find the sort candidate value.      | <selector>           |
| headingClasses | array     | Array of additional CSS classes                   | []                   |


*/
import PropTypes from 'prop-types'
import React from 'react'
import Immutable from 'immutable'
import ImmutablePropTypes from 'react-immutable-proptypes'

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
        ]),
        columnContext: PropTypes.any
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
            let classes = col.headingClasses || []
            if (col.sortable) {
                classes.push('tabular-sortable')
                if (col._sort) {
                    classes.push(col._sort)
                    // sort-by-alphabet{-alt} and sort-by-attributes{-alt}
                    const icon = 'sort-by-' + (col.sorter == 'string'? 'alphabet' : 'attributes') + (col._sort == 'asc'? '' : '-alt')
                    ascDesc = <Glyphicon icon={icon} />
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
        const {getPrimaryKey, columns} = this.config
        this.sortedRows(this).forEach((row) => {
            rows.push(<TabularRow key={getPrimaryKey(row)} row={row} columns={columns} columnContext={this.props.columnContext} />)
        })
        return (
            <tbody>{rows}</tbody>
        )
    }

    onColumnClick(colKey, event) {
        event.persist()
        const column = this.config.columns.get(colKey)
        if (column.sortable) {
            this.props.dispatch(clickColumn(this.config.name, colKey, event.ctrlKey || event.shiftKey || event.altKey))
        } else {
            console.log('clicked on un-sortable column ' + colKey)
        }
    }
}

function createSortedRowsSelector() {
    const sorterForColumns = defaultMemoize((columns, sort_by) => {
        const sortKeys = sort_by.map((colSort) => {
            const col = columns.get(colSort.column)
            return [(col.sortSelector || col.selector), col.sorter, colSort.direction]
        })
        return compositeKey(sortKeys.toJS())
    })
    return createSelector(
        (c) => c.props.data,
        (c) => c.props.current.sort_by,
        (c) => c.config.columns,
        (rows, sort_by, columns) => {
            if (!sort_by || !sort_by.size) {
                return rows
            }
            const sortFunc = sorterForColumns(columns, sort_by)
            return rows.slice().sort(sortFunc)
        }
    )
}

function createBoundColumnsSelector(component) {
    return defaultMemoize((columns, sort_by) => {
        const keyed_sort_by = Immutable.Map(sort_by.map(x => [x.column, x.direction]))
        return columns.map((col) => {
            return col
                .set('_columnClick', component.onColumnClick.bind(component, col.key))
                .set('_sort', keyed_sort_by.get(col.key))
        })
    })
}

class TabularRow extends React.PureComponent {
    static propTypes = {
        row: PropTypes.any.isRequired,
        columns: ImmutablePropTypes.orderedMapOf(
            PropTypes.instanceOf(Column),  // Value type
            PropTypes.string // Key type
        ),
        columnContext: PropTypes.any
    }

    render() {
        const { row, columns } = this.props
        let cols = []
        columns.forEach((col) => {
            const Component = col.component
            const data = col.selector(row) || col.default
            const rendered = Component? (<Component key={col.key} data={data} columnContext={this.props.columnContext} />) : <td key={col.key}>{data}</td>
            cols.push(rendered)
        })
        return <tr>{cols}</tr>
    }
}

/**
 * Create a table component.
 * @param  object options An object with the following properties:
 *
 *            name | string | Required. An identifier for this table for property storage.
 *         columns | array  | Required. An array of column specifier objects. See top of module for docs.
 *   getPrimaryKey | func   | A selector to get the 'primary key' of a row. defaults to x => x.id
 *     displayName | string | DisplayName primarily for React debugging use.
 *
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
