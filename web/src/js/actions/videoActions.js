import * as types from 'actions/actionTypes';

export function videoReady() {
  return (dispatch, getState, api) => {
    dispatch({
      type:    types.VIDEO_READY,
      isMuted: api.storage.getItem('video:isMuted', getState().video.isMuted)
    });
  };
}

export function videoTime(time) {
  return {
    type: types.VIDEO_TIME,
    time
  };
}

export function videoStatus(status) {
  return {
    type: types.VIDEO_STATUS,
    status
  };
}

export function videoTogglePlay() {
  return {
    type: types.VIDEO_TOGGLE_PLAY
  };
}

export function videoToggleMute() {
  return (dispatch, getState, api) => {
    dispatch({
      type: types.VIDEO_TOGGLE_MUTE
    });
    api.storage.setItem('video:isMuted', getState().video.isMuted);
  };
}
