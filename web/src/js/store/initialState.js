export default {
  nav: {
    isDrawerOpen: false
  },
  auth: {
    username:          '',
    isLoginDialogOpen: false,
    isAuthenticated:   false,
    isSubmitting:      false,
    error:             null
  },
  register: {
    isRegisterDialogOpen: false,
    isRegistered:         false,
    isSubmitting:         false,
    error:                null
  },
  users: [
    {
      username: 'headzoo',
      avatar:   'https://headzoo.r.worldssl.net/images/me.jpg',
      profile:  '/u/headzoo',
      role:     'user'
    },
    {
      username: 'az4521',
      avatar:   'https://api.adorable.io/avatars/285/az4521%40headzoo.io',
      profile:  '/u/az4521',
      role:     'user'
    }
  ],
  room: {
    name:       '',
    inputValue: '',
    users:      ['headzoo', 'az4521'],
    messages:   [
      {
        id:      1,
        date:    new Date(),
        from:    'headzoo',
        message: 'Well, you just kind of copied and pasted from the other components, so ...'
      },
      {
        id:      2,
        date:    new Date(),
        from:    'az4521',
        message: 'alright, i\'m at the cobble together stage'
      },
      {
        id:      3,
        date:    new Date(),
        from:    'headzoo',
        message: 'It\'s all good'
      },
      {
        id:      4,
        date:    new Date(),
        from:    'az4521',
        message: 'should i cram buttons into the containers or try and make seperate classes for them?'
      }
    ]
  },
  video: {

  },
  playlist: {

  }
};
