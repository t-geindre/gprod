var Vue       = require('vue');
var UserStore = require('../store/user');
var jQuery    = require('jquery');

module.exports = Vue.component('user-details', {
    template: '#user-details-template',
    delimiters: ['[[', ']]'],
    data: function() {
        return {
            profileModal: null,
            formData: {},
            formErrors: { fields: {} }
        }
    },
    mounted: function() {
        this.profileModal = jQuery('#profile-modal');
    },
    computed: {
        user: function()
        {
            return UserStore.state.user;
        },
        authenticated: function()
        {
            return UserStore.state.authenticated;
        },
        authenticating: function()
        {
            return UserStore.state.authenticating;
        },
        profileComplete: function() {
            return UserStore.state.complete;
        }
    },
    methods: {
        login: function() {
            UserStore.dispatch('login');
        },
        logout: function() {
            UserStore.dispatch('logout');
        },
        displayProfile: function() {
            this.formData = {
                goliveKey: this.user.golive_key,
                hipchatName: this.user.hipchat_name
            };
            this.formErrors = { fields: {} };
            this.profileModal.modal({
                backdrop: (this.profileComplete ? true : 'static'),
                keyboard: this.profileComplete,
                show: true
            });
        },
        hideProfile: function() {
            this.profileModal.modal('hide');
        },
        updateProfile: function() {
            UserStore.dispatch('update', this.formData).then(
                function(response) {
                    this.profileComplete = true;
                    this.hideProfile();
                }.bind(this),
                function(response) {
                    this.formErrors = response.data.errors;
                }.bind(this)
            );
        }
    },
    watch: {
        profileComplete: function() {
            if (!this.profileComplete) {
                this.displayProfile();
            }
        }
    }
});

