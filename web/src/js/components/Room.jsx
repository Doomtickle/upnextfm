import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Hidden from 'material-ui/Hidden';
import Grid from 'material-ui/Grid';
import { authUsername } from 'actions/authActions';
import { layoutToggleLoginDialog, layoutToggleRegisterDialog, layoutToggleHelpDialog, layoutErrorMessage } from 'actions/layoutActions';
import Progress from 'components/Video/Progress';
import HelpDialog from 'components/Dialogs/HelpDialog';
import LoginDialog from 'components/Dialogs/LoginDialog';
import RegisterDialog from 'components/Dialogs/RegisterDialog';
import ChatSide from 'components/Chat/ChatSide';
import VideoSide from 'components/Video/VideoSide';
import VideoNav from 'components/VideoNav';
import Nav from 'components/Nav';
import ErrorSnackbar from 'components/ErrorSnackbar';

class Room extends React.Component {
  static propTypes = {
    roomName:  PropTypes.string.isRequired,
    socketURI: PropTypes.string.isRequired,
    username:  PropTypes.string,
    auth:      PropTypes.object,
    layout:    PropTypes.object
  };

  constructor(props) {
    super(props);
    if (props.username) {
      props.dispatch(authUsername(props.username));
    }
  }

  handleCloseErrorSnackbar = () => {
    this.props.dispatch(layoutErrorMessage(''));
  };

  render() {
    const { roomName, socketURI, auth, layout, dispatch } = this.props;

    return (
      <div>
        <Nav auth={auth} roomName={roomName} />
        <Hidden smUp>
          <VideoNav />
        </Hidden>
        <Hidden smUp>
          <Progress />
        </Hidden>
        <div className="up-room">
          <Grid item xs={12} md={layout.colsChatSide}>
            <ChatSide roomName={roomName} socketURI={socketURI} />
          </Grid>
          <Grid item xs={12} md={layout.colsVideoSide}>
            <VideoSide />
          </Grid>
        </div>
        <ErrorSnackbar
          errorMessage={layout.errorMessage}
          errorDuration={layout.errorDuration}
          onClose={this.handleCloseErrorSnackbar}
        />
        <HelpDialog
          isOpen={layout.isHelpDialogOpen}
          onClose={() => { dispatch(layoutToggleHelpDialog()); }}
        />
        <LoginDialog
          isOpen={layout.isLoginDialogOpen}
          onClose={() => { dispatch(layoutToggleLoginDialog()); }}
        />
        <RegisterDialog
          isOpen={layout.isRegisterDialogOpen}
          onClose={() => { dispatch(layoutToggleRegisterDialog()); }}
        />
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    auth:   Object.assign({}, state.auth),
    layout: Object.assign({}, state.layout)
  };
}

export default connect(mapStateToProps)(Room);
