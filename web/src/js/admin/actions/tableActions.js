import * as types from './types';
import { uiLoading } from './uiActions';

/**
 * @param {string} entityName
 * @param {number} page
 * @param {string} filter
 * @returns {Function}
 */
export function tableLoad(entityName, page = 1, filter = '') {
  return (dispatch) => {
    dispatch(uiLoading(true));

    const filterParam = encodeURIComponent(filter);
    const config = {
      method:      'GET',
      credentials: 'same-origin',
      headers:     {
        Accept: 'application/json'
      }
    };

    return fetch(`/admin/entity/${entityName}/collection/${page}?filter=${filterParam}`, config)
      .then((resp) => {
        dispatch(uiLoading(false));

        if (!resp.ok) {
          throw new Error('Table load failed.');
        }
        return resp.json();
      })
      .then((table) => {
        dispatch({
          type: types.TABLE_LOAD,
          table
        });
      })
      .catch((error) => {
        dispatch(uiLoading(false));
        console.error(error);
      });
  };
}

/**
 * @param {string} filter
 * @returns {{type: TABLE_CHANGE_FILTER, filter: string}}
 */
export function tableChangeFilter(filter) {
  return {
    type: types.TABLE_CHANGE_FILTER,
    filter
  };
}
