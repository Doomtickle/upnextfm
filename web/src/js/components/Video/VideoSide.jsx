import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Player from 'components/Video/Player';
import Buttons from 'components/Video/Buttons';
import Playlist from 'components/Video/Playlist';

class VideoSide extends React.Component {
  static propTypes = {
    player:   PropTypes.object.isRequired,
    playlist: PropTypes.object.isRequired
  };

  render() {
    const { playlist } = this.props;

    return (
      <div className="up-room-side__video">
        <Player video={playlist.current} />
        <Buttons />
        <Playlist />
      </div>
    );
  }
}

function mapStateToProps(state) {
  return Object.assign({
    player:   state.player,
    playlist: state.playlist
  });
}

export default connect(mapStateToProps)(VideoSide);
