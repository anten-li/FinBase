"use strict";
class invest {
    constructor() {
        new authForm(usr => { this.onlogin(usr) });
    }

    onlogin(usr) {

    }
}

window.onload = ev => { new invest() };