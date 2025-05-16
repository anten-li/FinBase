"use strict";
class invest {
    usr;
    constructor() {
        new authForm(usr => { this.onlogin(usr) });
    }

    onlogin(usr) {
        this.usr = usr;

        alert("Здравствуйте " + this.usr.Name);
    }
}

window.onload = ev => { new invest() };