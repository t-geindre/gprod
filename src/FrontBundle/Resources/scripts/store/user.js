const Vuex = require('vuex')
const Vue = require('vue')
const GithubClient = require('../lib/github-client')
const ApiClient = require('../lib/api-client')

Vue.use(Vuex);

module.exports = new Vuex.Store({
  state: {
    authenticated: false,
    authenticating: false,
    user: {},
    complete: true
  },
  mutations: {
    authenticated: function (state, authenticated) {
      state.authenticated = authenticated;
      state.authenticating = false;
    },
    authenticating: function (state, authenticating) {
      state.authenticating = authenticating;
    },
    user: function (state, user) {
      state.user = user;
    },
    complete: function (state, complete) {
      state.complete = complete;
    }
  },
  actions: {
    update: function (context, data) {
      return new Promise(function (resolve, reject) {
        ApiClient.updateUser(
        Object.assign(context.state.user, data)
       ).then(function (response) {
         context.commit('user', response.data.entity);
         context.commit('complete', true);
         resolve(response);
       }, function (response) {
         reject(response);
       });
      })
    },
    login: function (context, redirect = true) {
      context.commit('authenticating', true);
      var resolveResponse = {};
      return new Promise(function (resolve, reject) {
        GithubClient.authenticate({redirect: redirect})
        .then(function (data) {
          resolveResponse = data;
          if (data.authenticated) {
            return ApiClient.getUser({
              headers: {
                'X-AUTH-TOKEN': data.auth.access_token
              }
            })
          }
          resolve(data);
        })
        .then(function (response) {
          context.commit('user', response.data.user);
          context.commit('complete', response.data.complete);
          context.commit('authenticated', true);
          resolve(resolveResponse);
        })
        .catch(function (response) {
          context.commit('authenticated', false);
          reject(response);
        });
      });
    },
    logout: function (context) {
      context.commit('authenticated', false);
      context.commit('user', {});
      GithubClient.clearAuthCookie();
    }
  }
})
