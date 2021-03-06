const Vue = require('vue')
const VueResource = require('vue-resource')

Vue.use(VueResource);

module.exports = new function () {
  var accessToken = false;
  var baseUrl = '';

    // Deployments
  this.getLiveDeployment = function (id) {
    var evtSrc = new window.EventSource(
      baseUrl + 'deployments/' + id + '/live',
      {
        withCredentials: true,
        headers: {'Authorization': 'token ' + accessToken}
      }
     );

    evtSrc.onerror = (error) => {
      console.log(error);
      evtSrc.close();
    }

    return evtSrc;
  }

  this.getDeployment = function (id) {
    return this.get('deployments/' + id);
  }

    // Config
  this.setAccessToken = function (token) {
    accessToken = token;
  }

  this.setBaseUrl = function (url) {
    baseUrl = url;
  }

    // Internals
  this.get = function (route, options = {}) {
    options.headers = Object.assign(
      {
        'Authorization': 'token ' + accessToken,
        'Accept': 'text/event-stream'
      },
      options.headers
     );

    return Vue.http(
      Object.assign(
       { method: 'get', url: baseUrl + route },
       options
      )
     );
  }
}();
