import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { tableChangeFilter } from 'admin/actions/tableActions';
import Loader from 'components/Loader';

function filterRows(columnKeys, rows) {
  return rows.map((row) => {
    const newRow = {};
    Object.keys(row).forEach((key) => {
      if (columnKeys.indexOf(key) !== -1) {
        newRow[key] = row[key];
      }
    });

    return newRow;
  });
}

const TableRow = ({ row, onClick }) => (
  <tr onClick={(e) => { onClick(e, row); }}>
    {Object.keys(row).map(key => (
      <td key={key}>
        {row[key]}
      </td>
    ))}
  </tr>
);

TableRow.propTypes = {
  row:     PropTypes.object,
  onClick: PropTypes.func
};

TableRow.defaultProps = {
  row:     {},
  onClick: () => {}
};

class Table extends React.Component {
  static propTypes = {
    onClickRow:     PropTypes.func,
    onSubmitFilter: PropTypes.func
  };

  static defaultProps = {
    onClickRow:     () => {},
    onSubmitFilter: () => {}
  };

  handleChangeFilter = (e) => {
    this.props.dispatch(tableChangeFilter(e.target.value));
  };

  handleKeyDownFilter = (e) => {
    if (e.keyCode === 13) {
      this.props.onSubmitFilter(e, this.props.filter);
    }
  };

  render() {
    const { columns, rows, filter, isLoading, onClickRow } = this.props;

    if (isLoading) {
      return <Loader />;
    }

    const columnKeys = Object.keys(columns);

    return (
      <div className="row">
        <div className="input-field col s6">
          <input
            type="text"
            id="table-filter"
            value={filter}
            onChange={this.handleChangeFilter}
            onKeyDown={this.handleKeyDownFilter}
          />
          <label htmlFor="table-filter">Filter</label>
        </div>
        <div className="col s12">
          <table className="striped responsive-table upa-table">
            <thead>
              <tr>
                {Object.keys(columns).map(key => (
                  <th key={key}>
                    {columns[key]}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {filterRows(columnKeys, rows).map((row, i) => (
                <TableRow key={i} row={row} onClick={onClickRow} />
              ))}
            </tbody>
          </table>
        </div>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return assign({}, state.table);
}

export default connect(mapStateToProps)(Table);
